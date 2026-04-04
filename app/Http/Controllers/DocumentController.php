<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Document;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function show(Document $document): View
    {
        $this->authorize('view', $document);

        $document->load('uploader');

        return view('app.documents.show', [
            'document' => $document,
        ]);
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Document::class);

        $query = Document::query()->with('uploader')->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->string('category')->toString());
        }

        if ($request->filled('q')) {
            $needle = '%'.$request->string('q')->toString().'%';
            $query->where(function ($builder) use ($needle): void {
                $builder
                    ->where('name', 'like', $needle)
                    ->orWhere('url', 'like', $needle)
                    ->orWhere('file_path', 'like', $needle);
            });
        }

        return view('app.documents.index', [
            'documents' => $query->paginate(20)->withQueryString(),
            'categories' => ['meeting-notes', 'financial-report', 'legal', 'research', 'photo', 'other'],
        ]);
    }

    public function download(Document $document)
    {
        $this->authorize('view', $document);

        if ($document->file_path) {
            $fileName = basename($document->file_path);

            return response()->download(Storage::disk('public')->path($document->file_path), $fileName);
        }

        if ($document->url) {
            return redirect()->away($document->url);
        }

        abort(404);
    }

    public function store(DocumentRequest $request, ActivityLogService $activityLogService): RedirectResponse
    {
        $this->authorize('create', Document::class);

        $payload = $request->validated();

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('documents', 'public');
        }

        $document = Document::query()->create([
            'name' => $payload['name'],
            'category' => $payload['category'],
            'url' => $payload['url'] ?? null,
            'file_path' => $filePath,
            'uploaded_by' => $request->user()->id,
        ]);

        $activityLogService->documentCreated($request->user(), $document);

        return back()->with('status', 'Document added.');
    }

    public function destroy(Document $document, Request $request, ActivityLogService $activityLogService): RedirectResponse
    {
        $this->authorize('delete', $document);

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $activityLogService->documentDeleted($request->user(), $document);
        $document->delete();

        return back()->with('status', 'Document deleted.');
    }
}
