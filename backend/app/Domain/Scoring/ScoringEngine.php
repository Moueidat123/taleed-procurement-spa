<?php

namespace App\Domain\Scoring;

use InvalidArgumentException;

/**
 * Exact PHP port of src/domain/scoring.ts (PLAN-CONTRACT §2 "Phase 2B").
 * Deterministic, complete-only scoring. Question IDs are strings ("1.10" ≠ "1.1");
 * answers are "yes" | "no" | null, and null is not "no". The overall score uses
 * integer basis points so 2.5-point steps never drift.
 */
final class ScoringEngine
{
    public static function classifyBasisPoints(int $bp): string
    {
        if ($bp < 0 || $bp > 10000) {
            throw new InvalidArgumentException('Invalid score.');
        }

        return match (true) {
            $bp <= 4000 => 'foundational',
            $bp <= 6500 => 'developing',
            $bp <= 8000 => 'advanced',
            default => 'best_in_class',
        };
    }

    public static function classify(float|int $score): string
    {
        if (! is_finite((float) $score) || $score < 0 || $score > 100) {
            throw new InvalidArgumentException('Invalid score.');
        }

        return match (true) {
            $score <= 40 => 'foundational',
            $score <= 65 => 'developing',
            $score <= 80 => 'advanced',
            default => 'best_in_class',
        };
    }

    /**
     * @param  array<string, mixed>  $framework
     * @return list<string>
     */
    public static function questionIds(array $framework): array
    {
        $ids = [];
        foreach ($framework['domains'] as $domain) {
            foreach ($domain['questions'] as $question) {
                $ids[] = (string) $question['id'];
            }
        }

        return $ids;
    }

    /**
     * @param  array<string, mixed>  $answers
     * @param  array<string, mixed>  $framework
     */
    public static function assertAnswers(array $answers, array $framework, bool $complete = false): void
    {
        $ids = self::questionIds($framework);
        $keys = array_map('strval', array_keys($answers));

        if (count($keys) !== count($ids) || array_diff($keys, $ids) !== []) {
            throw new InvalidArgumentException('Answers do not match the pinned framework.');
        }

        foreach ($ids as $id) {
            $answer = $answers[$id] ?? null;
            if ($answer !== 'yes' && $answer !== 'no' && $answer !== null) {
                throw new InvalidArgumentException("Invalid answer for {$id}.");
            }
            if ($complete && $answer === null) {
                throw new InvalidArgumentException("Answer question {$id} before submitting.");
            }
        }
    }

    /** @param array<string, mixed> $answers */
    public static function completion(array $answers): int
    {
        return count(array_filter($answers, fn ($a) => $a === 'yes' || $a === 'no'));
    }

    /**
     * @param  array<string, mixed>  $answers
     * @param  array<string, mixed>  $framework
     * @return array<string, mixed>
     */
    public static function score(array $answers, array $framework): array
    {
        self::assertAnswers($answers, $framework, true);

        $domains = [];
        foreach ($framework['domains'] as $domain) {
            $yes = 0;
            foreach ($domain['questions'] as $question) {
                if (($answers[(string) $question['id']] ?? null) === 'yes') {
                    $yes++;
                }
            }
            $score = $yes * 10;
            $band = self::classify($score);
            $domains[] = [
                'key' => $domain['key'],
                'name' => $domain['name'],
                'order' => (int) $domain['order'],
                'yes' => $yes,
                'score' => $score,
                'band' => $band,
                'actions' => $domain['recommendations'][$band],
            ];
        }

        $yes = array_sum(array_column($domains, 'yes'));
        $bp = $yes * 250;
        $band = self::classifyBasisPoints($bp);

        // Lowest score first; ties broken by domain order.
        $sorted = $domains;
        usort($sorted, fn ($a, $b) => [$a['score'], $a['order']] <=> [$b['score'], $b['order']]);
        $scores = array_column($domains, 'score');

        return [
            'yes' => $yes,
            'overall' => $bp % 100 === 0 ? intdiv($bp, 100) : $bp / 100,
            'overallBasisPoints' => $bp,
            'band' => $band,
            'domains' => $domains,
            'priorities' => array_column(array_slice($sorted, 0, 3), 'key'),
            'hasTie' => count(array_unique($scores)) !== count($scores),
            'interpretation' => $framework['interpretations'][$band],
        ];
    }

    /**
     * Build a full answer set with the first N questions of each domain "yes".
     *
     * @param  array<string, mixed>  $framework
     * @param  list<int>  $counts
     * @return array<string, string>
     */
    public static function answersFromCounts(array $framework, array $counts): array
    {
        if (count($counts) !== 4) {
            throw new InvalidArgumentException('Four counts from 0–10 are required.');
        }
        foreach ($counts as $n) {
            if (! is_int($n) || $n < 0 || $n > 10) {
                throw new InvalidArgumentException('Four counts from 0–10 are required.');
            }
        }

        $answers = [];
        foreach ($framework['domains'] as $d => $domain) {
            foreach ($domain['questions'] as $i => $question) {
                $answers[(string) $question['id']] = $i < $counts[$d] ? 'yes' : 'no';
            }
        }

        return $answers;
    }
}
