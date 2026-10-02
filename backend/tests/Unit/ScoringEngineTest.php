<?php

namespace Tests\Unit;

use App\Domain\Scoring\ScoringEngine;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Phase 2B — PHP scoring port against the 14,641-case count oracle. */
class ScoringEngineTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function framework(): array
    {
        static $f = null;

        return $f ??= json_decode((string) file_get_contents(__DIR__.'/../../resources/framework/framework-1.0.0.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    /** @param list<int> $counts @return array<string, mixed> */
    private static function scored(array $counts): array
    {
        $f = self::framework();

        return ScoringEngine::score(ScoringEngine::answersFromCounts($f, $counts), $f);
    }

    public function test_all_oracle_cases_match_exactly(): void
    {
        $oracle = json_decode((string) file_get_contents(__DIR__.'/../Fixtures/scoring-count-oracle.json'), true, 512, JSON_THROW_ON_ERROR);
        $keys = array_column(self::framework()['domains'], 'key');
        $this->assertSame($oracle['domainOrder'], $keys);
        $this->assertCount(14641, $oracle['cases']);

        foreach ($oracle['cases'] as $case) {
            $r = self::scored($case['yesCounts']);
            $label = implode(',', $case['yesCounts']);
            $this->assertSame($case['overallYes'], $r['yes'], $label);
            $this->assertSame($case['overallBasisPoints'], $r['overallBasisPoints'], $label);
            $this->assertSame($case['overallBand'], $r['band'], $label);
            $this->assertSame($case['domainBands'], array_column($r['domains'], 'band'), $label);
            $focus = array_map(fn ($k) => array_search($k, $keys, true), $r['priorities']);
            $this->assertSame($case['focusDomainIndexes'], $focus, $label);
        }
    }

    /** @return array<string, array{float|int, string}> */
    public static function boundaries(): array
    {
        return [
            '0' => [0, 'foundational'], '40' => [40, 'foundational'], '40.5' => [40.5, 'developing'],
            '65' => [65, 'developing'], '65.5' => [65.5, 'advanced'], '80' => [80, 'advanced'],
            '80.5' => [80.5, 'best_in_class'], '100' => [100, 'best_in_class'],
        ];
    }

    #[DataProvider('boundaries')]
    public function test_band_boundaries(float|int $score, string $band): void
    {
        $this->assertSame($band, ScoringEngine::classify($score));
        $this->assertSame($band, ScoringEngine::classifyBasisPoints((int) round($score * 100)));
    }

    public function test_each_domain_band_has_actions_copied_from_the_framework(): void
    {
        $f = self::framework();
        foreach ([0, 5, 7, 10] as $n) {
            foreach (self::scored([$n, $n, $n, $n])['domains'] as $i => $d) {
                $this->assertSame($f['domains'][$i]['recommendations'][$d['band']], $d['actions']);
            }
        }
    }

    public function test_focus_area_ties_break_by_domain_order(): void
    {
        $keys = array_column(self::framework()['domains'], 'key');
        $r = self::scored([5, 5, 5, 5]);
        $this->assertSame(array_slice($keys, 0, 3), $r['priorities']);
        $this->assertTrue($r['hasTie']);
        $this->assertSame([$keys[1], $keys[3], $keys[0]], self::scored([9, 1, 9, 1])['priorities']);
    }

    public function test_all_one_hundred(): void
    {
        $r = self::scored([10, 10, 10, 10]);
        $this->assertSame(100, $r['overall']);
        $this->assertSame('best_in_class', $r['band']);
        $this->assertSame(self::framework()['interpretations']['best_in_class'], $r['interpretation']);
    }

    public function test_null_is_not_no_and_incomplete_answers_are_refused(): void
    {
        $f = self::framework();
        $answers = ScoringEngine::answersFromCounts($f, [10, 10, 10, 10]);
        $answers['1.10'] = null;
        $this->assertSame(39, ScoringEngine::completion($answers));
        $this->expectException(InvalidArgumentException::class);
        ScoringEngine::score($answers, $f);
    }

    public function test_unknown_question_ids_are_refused(): void
    {
        $f = self::framework();
        $this->assertContains('1.10', ScoringEngine::questionIds($f));
        $answers = ScoringEngine::answersFromCounts($f, [1, 1, 1, 1]);
        unset($answers['1.10']);
        $answers['1.11'] = 'yes';
        $this->expectException(InvalidArgumentException::class);
        ScoringEngine::assertAnswers($answers, $f);
    }
}
