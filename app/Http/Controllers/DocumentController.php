<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Services\CompanyAccess;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DocumentController extends Controller
{
    private function query()
    {
        if (in_array(auth()->user()->type, ['vendor','client','customer'])) return Document::where('created_by',creatorId())->where('creator_id',auth()->id());
        if (auth()->user()->can('manage-any-media')) return Document::where('created_by',creatorId());
        return Document::where('created_by', creatorId())->where(function ($q) {
            $q->where('creator_id', auth()->id())->orWhere(function ($shared) {
                $shared->where('access_level', 'company')->where('is_confidential', false);
            });
        });
    }

    private function document(int $id): Document
    {
        return $this->query()->findOrFail($id);
    }

    public function index(Request $request)
    {
        CompanyAccess::requirePermission('manage-media');
        $request->validate(['search' => 'nullable|string|max:100', 'per_page' => 'sometimes|integer|min:1|max:100']);
        $documents = $this->query()->withCount('versions')
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->input('search').'%'))
            ->latest()->paginate($request->integer('per_page', 15))->withQueryString();
        return $request->expectsJson() ? response()->json($documents) : Inertia::render('Documents/Index', [
            'documents' => $documents, 'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request, bool $supplierUpload = false)
    {
        \App\Services\TransactionSafety::requireTransactionalTables(['documents', 'document_versions', 'document_sequences']);
        if (!$supplierUpload) CompanyAccess::requirePermission('create-media');
        $data = $request->validate([
            'title' => 'required|string|max:255', 'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000', 'access_level' => 'required|in:company,private',
            'is_confidential' => 'sometimes|boolean', 'expiry_date' => 'nullable|date',
            'entity_type' => ['nullable', Rule::in(array_keys($this->entities()))],
            'entity_id' => 'nullable|required_with:entity_type|integer|min:1',
            'file' => $this->fileRules(),
        ]);
        if (!empty($data['entity_id'])) {
            abort_unless(!empty($data['entity_type']), 422);
            $model = $this->entities()[$data['entity_type']];
            $record = $model::findOrFail($data['entity_id']);
            CompanyAccess::check($record);
        }
        $path = $request->file('file')->store('documents/'.creatorId(), 'local');
        abort_unless($path, 500, 'Unable to store document.');
        try {
            $document = DB::transaction(function () use ($data, $request, $path) {
                unset($data['file']);
                $document = Document::create($data + [
                    'document_number' => DocumentNumberService::next('DOC'),
                    'file_path' => $path, 'file_name' => basename($request->file('file')->getClientOriginalName()),
                    'file_type' => $request->file('file')->getMimeType(), 'file_size' => $request->file('file')->getSize(),
                    'creator_id' => auth()->id(), 'created_by' => creatorId(),
                ]);
                $this->recordVersion($document, 'Initial upload');
                return $document;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }
        return $request->expectsJson() ? response()->json($document, 201) : back()->with('success', __('Document uploaded.'));
    }

    public function show(Request $request, int $document)
    {
        CompanyAccess::requirePermission('manage-media');
        $record = $this->document($document)->load('versions.uploader:id,name');
        return $request->expectsJson() ? response()->json($record) : Inertia::render('Documents/Show', ['document' => $record]);
    }

    public function version(Request $request, int $document)
    {
        \App\Services\TransactionSafety::requireTransactionalTables(['documents', 'document_versions']);
        CompanyAccess::requirePermission('create-media');
        $record = $this->document($document);
        abort_unless((int) $record->creator_id === auth()->id() || auth()->user()->can('manage-any-media'), 403);
        $data = $request->validate(['file' => $this->fileRules(), 'change_notes' => 'required|string|max:1000']);
        $path = $request->file('file')->store('documents/'.creatorId(), 'local');
        abort_unless($path, 500, 'Unable to store document.');
        try {
            DB::transaction(function () use ($document, $request, $data, $path) {
                $record = $this->query()->lockForUpdate()->findOrFail($document);
                $record->update([
                    'file_path' => $path, 'file_name' => basename($request->file('file')->getClientOriginalName()),
                    'file_type' => $request->file('file')->getMimeType(), 'file_size' => $request->file('file')->getSize(),
                ]);
                $this->recordVersion($record, $data['change_notes']);
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }
        return $request->expectsJson() ? response()->json(['message' => 'Version uploaded.']) : back()->with('success', __('Version uploaded.'));
    }

    public function download(int $document, ?int $version = null)
    {
        CompanyAccess::requirePermission('download-media');
        $record = $this->document($document);
        $file = $version ? $record->versions()->findOrFail($version) : $record;
        abort_unless(Storage::disk('local')->exists($file->file_path), 404);
        return Storage::disk('local')->download($file->file_path, $file->file_name, ['X-Content-Type-Options' => 'nosniff']);
    }

    public function destroy(Request $request, int $document)
    {
        CompanyAccess::requirePermission('delete-media');
        $record = $this->document($document);
        // Shared read access is not authority to delete somebody else's document.
        abort_unless((int) $record->creator_id === auth()->id() || auth()->user()->can('manage-any-media'), 403);
        $record->delete(); // Keep files and versions for retention/recovery.
        return $request->expectsJson() ? response()->noContent() : redirect()->route('documents.index')->with('success', __('Document archived.'));
    }

    private function recordVersion(Document $document, string $notes): void
    {
        $document->versions()->create([
            'version_number' => ($document->versions()->max('version_number') ?? 0) + 1,
            'file_path' => $document->file_path, 'file_name' => $document->file_name,
            'file_size' => $document->file_size, 'change_notes' => $notes, 'uploaded_by' => auth()->id(),
        ]);
    }

    private function fileRules(): array
    {
        return ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,webp', 'extensions:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,webp'];
    }

    private function entities(): array
    {
        return [
            'purchase_order' => \App\Models\PurchaseOrder::class,
            'customer' => \Workdo\Account\Models\Customer::class, 'supplier' => \Workdo\Account\Models\Vendor::class,
            'employee' => \Workdo\Hrm\Models\Employee::class, 'product' => \Workdo\ProductService\Models\ProductServiceItem::class,
            'invoice' => \App\Models\SalesInvoice::class, 'purchase' => \App\Models\PurchaseInvoice::class,
            'project' => \Workdo\Taskly\Models\Project::class, 'asset' => \App\Models\FixedAsset::class,
            'payment' => \Workdo\Account\Models\CustomerPayment::class,
        ];
    }
}
