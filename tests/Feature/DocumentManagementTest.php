<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_member_can_view_documents_page(): void
    {
        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $this->actingAs($member)
            ->get(route('documents.index'))
            ->assertOk()
            ->assertSee('Documents');
    }

    public function test_finance_can_upload_document_file_and_activity_is_logged(): void
    {
        Storage::fake('public');

        $finance = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $file = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');

        $this->actingAs($finance)
            ->post(route('documents.store'), [
                'name' => 'Monthly Report',
                'category' => 'financial-report',
                'file' => $file,
            ])
            ->assertRedirect();

        $document = Document::query()->first();

        $this->assertNotNull($document);
        $this->assertDatabaseHas('documents', [
            'name' => 'Monthly Report',
            'category' => 'financial-report',
            'uploaded_by' => $finance->id,
        ]);

        Storage::disk('public')->assertExists($document->file_path);

        $this->assertDatabaseHas('activities', [
            'action' => 'document-create',
            'user_id' => $finance->id,
        ]);
    }

    public function test_member_cannot_add_document(): void
    {
        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $this->actingAs($member)
            ->post(route('documents.store'), [
                'name' => 'Blocked',
                'category' => 'other',
                'url' => 'https://example.com/doc',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_delete_document_and_activity_is_logged(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'status' => 'active',
            'role' => 'admin',
        ]);

        $owner = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $path = UploadedFile::fake()->create('policy.pdf', 80, 'application/pdf')->store('documents', 'public');

        $document = Document::query()->create([
            'name' => 'Policy File',
            'category' => 'legal',
            'file_path' => $path,
            'uploaded_by' => $owner->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('documents.destroy', $document))
            ->assertRedirect();

        $this->assertDatabaseMissing('documents', [
            'id' => $document->id,
        ]);

        Storage::disk('public')->assertMissing($path);

        $this->assertDatabaseHas('activities', [
            'action' => 'document-delete',
            'user_id' => $admin->id,
        ]);
    }
}
