<?php

namespace Tests\Feature\Assessments;

use App\Domain\Framework\FrameworkImporter;
use App\Models\AssessmentCycle;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use LogicException;
use Tests\TestCase;

/** Phase 2B — framework import, publish, immutability and one open cycle. */
class FrameworkImportTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function data(): array
    {
        return json_decode((string) file_get_contents(base_path('resources/framework/framework-1.0.0.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function importer(): FrameworkImporter
    {
        return app(FrameworkImporter::class);
    }

    public function test_import_creates_a_draft_with_all_content_and_verifies_the_workbook(): void
    {
        $v = $this->importer()->import($this->data(), base_path('resources/framework/source-workbook-v01.xlsx'));

        $this->assertSame('draft', $v->status);
        $this->assertSame(4, $v->domains()->count());
        $this->assertSame(40, $v->questions()->count());
        $this->assertTrue($v->questions()->where('source_question_id', '1.10')->exists());
        $this->assertGreaterThan(0, $v->actions()->count());
        $this->assertSame(64, strlen($v->content_sha256));
        $this->assertDatabaseHas('audit_events', ['action' => 'framework.imported', 'target_id' => $v->id]);
    }

    public function test_a_wrong_workbook_checksum_is_refused(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->importer()->import($this->data(), base_path('composer.json'));
    }

    public function test_the_same_version_cannot_be_imported_twice(): void
    {
        $this->importer()->import($this->data());
        $this->expectException(LogicException::class);
        $this->importer()->import($this->data());
    }

    public function test_malformed_content_is_refused(): void
    {
        $data = $this->data();
        array_pop($data['domains'][0]['questions']);
        $this->expectException(InvalidArgumentException::class);
        $this->importer()->import($data);
    }

    public function test_publish_requires_an_approval_reference_and_then_locks_the_version(): void
    {
        $v = $this->importer()->import($this->data());

        try {
            $this->importer()->publish($v, '  ');
            $this->fail('Blank approval reference must be refused.');
        } catch (InvalidArgumentException) {
        }

        $this->importer()->publish($v, 'D-19 local test approval');
        $this->assertTrue($v->fresh()->isPublished());
        $this->assertDatabaseHas('audit_events', ['action' => 'framework.published', 'target_id' => $v->id]);

        $this->expectException(LogicException::class);
        $v->fresh()->update(['title' => 'Changed']);
    }

    public function test_a_published_version_cannot_be_deleted(): void
    {
        $v = $this->importer()->import($this->data());
        $this->importer()->publish($v, 'ref');
        $this->expectException(LogicException::class);
        $v->fresh()->delete();
    }

    public function test_only_one_cycle_can_be_open(): void
    {
        $v = $this->importer()->import($this->data());
        $make = fn (string $status) => AssessmentCycle::create([
            'framework_version_id' => $v->id, 'title' => 'Cycle', 'status' => $status,
            'opens_at' => now()->subDay(), 'closes_at' => now()->addMonth(),
        ]);

        $open = $make('open');
        $make('closed');
        $this->assertTrue($open->isOpen());

        $this->expectException(QueryException::class);
        $make('open');
    }
}
