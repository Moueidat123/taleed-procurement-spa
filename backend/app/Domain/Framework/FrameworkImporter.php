<?php

namespace App\Domain\Framework;

use App\Models\AuditEvent;
use App\Models\FrameworkDomain;
use App\Models\FrameworkQuestion;
use App\Models\FrameworkVersion;
use App\Models\RecommendationAction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

/**
 * Phase 2B — loads framework JSON into the versioned tables as a draft, and
 * publishes a draft with an approval reference. Published versions never change.
 */
final class FrameworkImporter
{
    public const BANDS = ['foundational', 'developing', 'advanced', 'best_in_class'];

    public const SCORING_VERSION = '1.0.0';

    /** @param array<string, mixed> $data */
    public function import(array $data, ?string $sourcePath = null): FrameworkVersion
    {
        $this->validate($data);

        if ($sourcePath !== null) {
            if (! is_file($sourcePath) || hash_file('sha256', $sourcePath) !== $data['sourceSha256']) {
                throw new InvalidArgumentException('Source workbook checksum does not match the framework.');
            }
        }

        if (FrameworkVersion::query()->where('semantic_version', $data['version'])->exists()) {
            throw new LogicException("Framework version {$data['version']} already exists.");
        }

        return DB::transaction(function () use ($data) {
            $version = FrameworkVersion::create([
                'semantic_version' => $data['version'],
                'status' => 'draft',
                'title' => $data['title'],
                'source_file_name' => $data['sourceFile'],
                'source_sha256' => $data['sourceSha256'],
                'content_sha256' => self::contentHash($data),
                'scoring_version' => self::SCORING_VERSION,
                'interpretations' => $data['interpretations'],
            ]);

            foreach ($data['domains'] as $d) {
                $domain = FrameworkDomain::create([
                    'framework_version_id' => $version->id,
                    'key' => $d['key'],
                    'title' => $d['name'],
                    'position' => (int) $d['order'],
                ]);
                foreach ($d['questions'] as $i => $q) {
                    FrameworkQuestion::create([
                        'framework_version_id' => $version->id,
                        'domain_id' => $domain->id,
                        'source_question_id' => (string) $q['id'],
                        'position' => $i + 1,
                        'text' => $q['text'],
                        'source_cell' => $q['sourceCell'],
                    ]);
                }
                foreach (self::BANDS as $band) {
                    foreach ($d['recommendations'][$band] as $i => $a) {
                        RecommendationAction::create([
                            'framework_version_id' => $version->id,
                            'domain_id' => $domain->id,
                            'band' => $band,
                            'position' => $i + 1,
                            'source_action_id' => $a['id'],
                            'text' => $a['text'],
                            'source_cell' => $a['sourceCell'],
                        ]);
                    }
                }
            }

            AuditEvent::record(action: 'framework.imported', targetType: 'framework_version', targetId: $version->id,
                metadata: ['version' => $version->semantic_version, 'contentSha256' => $version->content_sha256]);

            return $version;
        });
    }

    public function publish(FrameworkVersion $version, string $approvalReference, ?string $approverId = null): FrameworkVersion
    {
        if ($version->isPublished()) {
            throw new LogicException('Framework version is already published.');
        }
        if (trim($approvalReference) === '') {
            throw new InvalidArgumentException('An approval reference is required to publish.');
        }

        $version->forceFill([
            'status' => 'published',
            'approval_reference' => trim($approvalReference),
            'approved_by' => $approverId,
            'published_at' => now(),
        ])->save();

        AuditEvent::record(action: 'framework.published', actorId: $approverId, targetType: 'framework_version',
            targetId: $version->id, metadata: ['version' => $version->semantic_version, 'approvalReference' => $version->approval_reference]);

        return $version;
    }

    /** @param array<string, mixed> $data */
    public static function contentHash(array $data): string
    {
        $content = ['domains' => $data['domains'], 'interpretations' => $data['interpretations']];

        return hash('sha256', (string) json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /** @param array<string, mixed> $data */
    private function validate(array $data): void
    {
        foreach (['version', 'title', 'sourceFile', 'sourceSha256', 'domains', 'interpretations'] as $key) {
            if (! isset($data[$key])) {
                throw new InvalidArgumentException("Framework is missing {$key}.");
            }
        }
        if (count($data['domains']) !== 4) {
            throw new InvalidArgumentException('Framework must have exactly 4 domains.');
        }
        $ids = [];
        foreach ($data['domains'] as $d) {
            if (count($d['questions'] ?? []) !== 10) {
                throw new InvalidArgumentException("Domain {$d['key']} must have exactly 10 questions.");
            }
            foreach ($d['questions'] as $q) {
                $ids[] = (string) $q['id'];
            }
            foreach (self::BANDS as $band) {
                if (empty($d['recommendations'][$band])) {
                    throw new InvalidArgumentException("Domain {$d['key']} has no {$band} actions.");
                }
            }
        }
        if (count(array_unique($ids)) !== 40) {
            throw new InvalidArgumentException('Question IDs must be unique.');
        }
        foreach (self::BANDS as $band) {
            if (! isset($data['interpretations'][$band])) {
                throw new InvalidArgumentException("Missing {$band} interpretation.");
            }
        }
    }
}
