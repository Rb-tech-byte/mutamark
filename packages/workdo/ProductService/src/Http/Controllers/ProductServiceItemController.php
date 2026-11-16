<?php

namespace Workdo\ProductService\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\ProductService\Events\CreateProductServiceItem;
use Workdo\ProductService\Events\DestroyProductServiceItem;
use Workdo\ProductService\Events\UpdateProductServiceItem;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\ProductService\Models\ProductServiceTax;
use Workdo\ProductService\Models\ProductServiceCategory;
use Workdo\ProductService\Models\WarehouseStock;
use Workdo\ProductService\Http\Requests\StoreProductServiceItemRequest;
use Workdo\ProductService\Http\Requests\UpdateProductServiceItemRequest;
use Workdo\ProductService\Models\ProductServiceUnit;

class ProductServiceItemController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-product-service-item') || Auth::user()->type === 'company' || Auth::user()->type === 'superadmin'){
            $items = ProductServiceItem::select('id', 'name', 'sku', 'sale_price', 'purchase_price', 'tax_ids', 'tax_type', 'category_id', 'unit', 'type', 'image', 'description', 'long_description', 'created_at')
                ->with(['category:id,name', 'unitRelation:id,unit_name', 'warehouseStocks:product_id,quantity'])
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-product-service-item') || Auth::user()->type === 'company' || Auth::user()->type === 'superadmin') {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-product-service-item')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->where('created_by', creatorId());
                    }
                })
                ->when(request('name'), fn($q) => $q->where('name', 'like', '%' . request('name') . '%'))
                ->when(request('type'), fn($q) => $q->where('type', request('type')))
                ->when(request('category_id'), fn($q) => $q->where('category_id', request('category_id')))
                ->when(request('is_active') !== null, fn($q) => $q->where('is_active', request('is_active')))
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            // Add total quantity for each item
            $items->getCollection()->transform(function ($item) {
                $item->total_quantity = $item->warehouseStocks->sum('quantity');
                return $item;
            });

            $categories = ProductServiceCategory::where('created_by', creatorId())->get(['id', 'name']);

            return Inertia::render('ProductService/Items/Index', [
                'items' => $items,
                'categories' => $categories,
            ]);
        }
        return redirect()->route('dashboard')->with('error', __('Permission denied'));
    }

    public function create()
    {
        if(Auth::user()->can('create-product-service-item') || Auth::user()->can('manage-product-service-item') || Auth::user()->type === 'company' || Auth::user()->type === 'superadmin'){
            $taxes = ProductServiceTax::where('created_by', creatorId())->get(['id', 'tax_name', 'rate']);
            $categories = ProductServiceCategory::where('created_by', creatorId())->get(['id', 'name']);
            $units = ProductServiceUnit::where('created_by', creatorId())->get(['id', 'unit_name']);
            $warehouses = Warehouse::where('is_active', true)->where('created_by', creatorId())->get(['id', 'name']);

            return Inertia::render('ProductService/Items/Create', [
                'taxes' => $taxes,
                'categories' => $categories,
                'units' => $units,
                'warehouses' => $warehouses,
            ]);
        }
        return redirect()->route('product-service.items.index')->with('error', __('Permission denied'));
    }

    public function store(StoreProductServiceItemRequest $request)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
        \App\Services\CompanyAccess::requirePermission('create-product-service-item');
        if(Auth::user()->can('create-product-service-item')){
            $validated = $request->validated();
            $item = new ProductServiceItem();
            $item->name = $validated['name'];
            $item->sku = $validated['sku'];
            $item->tax_ids = (!empty($validated['tax_ids'])) ? array_map('intval', $validated['tax_ids']) : null;
            $item->tax_type = $validated['tax_type'] ?? 'exclusive';
            $item->category_id = $validated['category_id'] === 'none' ? null : $validated['category_id'];
            $item->description = $validated['description'] ?? null;
            $item->long_description = $validated['long_description'] ?? null;
            $item->sale_price = $validated['sale_price'];
            $item->purchase_price = $validated['purchase_price'];
            $item->unit = $validated['unit'] === 'none' ? null : $validated['unit'];
            $item->type = $validated['type'];
            $item->creator_id = Auth::id();
            $item->created_by = creatorId();

            // Handle image path from media library
            if (!empty($validated['image'])) {
                $item->image = basename($validated['image']);
            }

            // Handle multiple images
            if (!empty($validated['images'])) {
                $imageNames = array_map('basename', $validated['images']);
                $item->images = json_encode($imageNames);
            }

            $item->save();

            // Create warehouse stock entry if warehouse and quantity are provided
            if (isset($validated['warehouse_id']) && $validated['warehouse_id'] !== 'none' && isset($validated['quantity'])) {
                WarehouseStock::create([
                    'product_id' => $item->id,
                    'warehouse_id' => $validated['warehouse_id'],
                    'quantity' => $validated['quantity'] ?? 0,
                ]);
            }

            // Dispatch event for packages to handle their fields
            CreateProductServiceItem::dispatch($request, $item);

            return $request->expectsJson() ? response()->json($item->fresh(), 201) : redirect()->route('product-service.items.index')->with('success', __('The item has been created successfully.'));
        }
        return redirect()->route('product-service.items.index')->with('error', __('Permission denied'));
    
        }, 3);
    }

    public function show(ProductServiceItem $item)
    {
        \App\Services\CompanyAccess::requirePermission('view-product-service-item');
        \App\Services\CompanyAccess::check($item);
        if(Auth::user()->can('view-product-service-item') || Auth::user()->can('manage-product-service-item') || Auth::user()->type === 'company' || Auth::user()->type === 'superadmin'){
            $item->load(['category', 'unitRelation', 'warehouseStocks.warehouse:id,name']);

            // Load taxes if tax_ids exist
            $taxes = [];
            if ($item->tax_ids) {
                $taxIds = $item->tax_ids;
                if (!empty($taxIds) && is_array($taxIds) && count($taxIds) > 0) {
                    $taxes = ProductServiceTax::whereIn('id', $taxIds)
                        ->where('created_by', creatorId())
                        ->get(['id', 'tax_name', 'rate'])
                        ->toArray();
                }
            }

            $itemData = $item->toArray();
            $itemData['taxes'] = $taxes;
            $itemData['total_quantity'] = $item->warehouseStocks->sum('quantity');
            $itemData['warehouse_stocks'] = $item->warehouseStocks->map(function($stock) {
                return [
                    'warehouse_name' => $stock->warehouse->name,
                    'quantity' => $stock->quantity
                ];
            });

            return Inertia::render('ProductService/Items/Show', [
                'item' => $itemData,
            ]);
        }
        return redirect()->route('product-service.items.index')->with('error', __('Permission denied'));
    }

    public function edit(ProductServiceItem $item)
    {
        \App\Services\CompanyAccess::check($item);
        if(Auth::user()->can('edit-product-service-item') || Auth::user()->can('manage-product-service-item') || Auth::user()->type === 'company' || Auth::user()->type === 'superadmin'){
            $taxes = ProductServiceTax::where('created_by', creatorId())->get(['id', 'tax_name', 'rate']);
            $categories = ProductServiceCategory::where('created_by', creatorId())->get(['id', 'name']);
            $units = ProductServiceUnit::where('created_by', creatorId())->get(['id', 'unit_name']);
            $warehouses = Warehouse::where('is_active', true)->where('created_by', creatorId())->get(['id', 'name']);

            // Load the item with all necessary fields
            $item->load(['category', 'unitRelation']);

            return Inertia::render('ProductService/Items/Edit', [
                'item' => $item,
                'taxes' => $taxes,
                'categories' => $categories,
                'units' => $units,
                'warehouses' => $warehouses,
            ]);
        }
        return redirect()->route('product-service.items.index')->with('error', __('Permission denied'));
    }



    public function update(UpdateProductServiceItemRequest $request, ProductServiceItem $item)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $item) {
        $item = ProductServiceItem::where('created_by', creatorId())->lockForUpdate()->findOrFail($item->id);

        \App\Services\CompanyAccess::requirePermission('edit-product-service-item');
        \App\Services\CompanyAccess::check($item);
        if(Auth::user()->can('edit-product-service-item') || Auth::user()->can('manage-product-service-item') || Auth::user()->type === 'company' || Auth::user()->type === 'superadmin'){
            $validated = $request->validated();

            if (isset($validated['quantity'])) {
                $warehouseId = $validated['warehouse_id'] ?? $item->warehouse_id;
                $currentQuantity = $warehouseId ? WarehouseStock::where('product_id', $item->id)->where('warehouse_id', $warehouseId)->sum('quantity') : 0;
                if (abs((float) $validated['quantity'] - (float) $currentQuantity) > 0.00001) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['quantity' => 'Use a stock count adjustment to change inventory quantity.']);
                }
            }
            $item->name = $validated['name'];
            $item->sku = $validated['sku'];
            $item->tax_ids = (!empty($validated['tax_ids'])) ? array_map('intval', $validated['tax_ids']) : null;
            $item->tax_type = $validated['tax_type'] ?? 'exclusive';
            $item->category_id = $validated['category_id'] === 'none' ? null : $validated['category_id'];
            $item->description = $validated['description'];
            $item->long_description = $validated['long_description'] ?? null;
            $item->sale_price = $validated['sale_price'];
            $item->purchase_price = $validated['purchase_price'];
            $item->unit = $validated['unit'] === 'none' ? null : $validated['unit'];
            $item->type = $validated['type'];

            // Handle image path from media library
            if (isset($validated['image'])) {
                $item->image = !empty($validated['image']) ? basename($validated['image']) : null;
            }

            // Handle multiple images
            if (isset($validated['images'])) {
                if (!empty($validated['images'])) {
                    $imageNames = array_map('basename', $validated['images']);
                    $item->images = json_encode($imageNames);
                } else {
                    $item->images = null;
                }
            }

            $item->save();

            // Quantities are changed through receipts, transfers and audited stock counts.
            // Dispatch event for packages to handle their fields
            UpdateProductServiceItem::dispatch($request, $item);

            return $request->expectsJson() ? response()->json($item->fresh(), 200) : redirect()->route('product-service.items.index')->with('success', __('The item details are updated successfully.'));
        }
        return redirect()->route('product-service.items.index')->with('error', __('Permission denied'));
    
        }, 3);
    }

    public function destroy(ProductServiceItem $item)
    {
        \App\Services\CompanyAccess::requirePermission('delete-product-service-item');
        \App\Services\CompanyAccess::check($item);
        if(Auth::user()->can('delete-product-service-item') || Auth::user()->can('manage-product-service-item') || Auth::user()->type === 'company' || Auth::user()->type === 'superadmin'){
            if ($item->image) {
                delete_file($item->image);
            }

            if ($item->images) {
                $images = json_decode($item->images, true);
                if (is_array($images)) {
                    foreach ($images as $image) {
                        delete_file($image);
                    }
                }
            }

            WarehouseStock::where('product_id', $item->id)->delete();

            DestroyProductServiceItem::dispatch($item);
            $item->delete();
            return back()->with('success', __('The item has been deleted.'));
        }
        return back()->with('error', __('Permission denied'));
    }

    public function stockIndex()
    {
        if(Auth::user()->can('manage-stock') || Auth::user()->type === 'company' || Auth::user()->type === 'superadmin'){
            $stocks = ProductServiceItem::select('id', 'name', 'sku')
                ->with(['warehouseStocks:product_id,warehouse_id,quantity'])
                ->where('created_by', creatorId())
                ->where('type', '!=', 'service')
                ->when(request('name'), fn($q) => $q->where('name', 'like', '%' . request('name') . '%'))
                ->when(request('sku'), fn($q) => $q->where('sku', 'like', '%' . request('sku') . '%'))
                ->latest()
                ->paginate(request('per_page', 10))
                ->withQueryString();

            // Add total quantity for each item
            $stocks->getCollection()->transform(function ($item) {
                $item->total_quantity = $item->warehouseStocks->sum('quantity');
                return $item;
            });

            $warehouses = \App\Models\Warehouse::where('created_by', creatorId())->where('is_active', true)->get(['id', 'name']);

            return Inertia::render('ProductService/Stock/Index', [
                'stocks' => $stocks,
                'warehouses' => $warehouses,
            ]);
        }
        return redirect()->route('dashboard')->with('error', __('Permission denied'));
    }

    public function apiIndex()
    {
        if(Auth::user()->can('manage-product-service-item') || Auth::user()->can('manage-any-product-service-item') || Auth::user()->can('manage-own-product-service-item')){
            try {
                $query = ProductServiceItem::query();

                if(Auth::user()->can('manage-any-product-service-item')) {
                   $query->where('created_by', creatorId());
                } elseif(Auth::user()->can('manage-own-product-service-item')) {
                    $query->where('creator_id', Auth::id());
                }
                $items = $query->get(['id', 'name', 'sku', 'sale_price', 'tax_ids', 'tax_type', 'description']);

                $transformedItems = $items->map(function ($item) {
                    $taxes = [];
                    if ($item->tax_ids) {
                        $taxIds = is_string($item->tax_ids) ? json_decode($item->tax_ids, true) : $item->tax_ids;
                        if (!empty($taxIds) && is_array($taxIds)) {
                            $taxes = ProductServiceTax::whereIn('id', $taxIds)
                                ->get(['id', 'tax_name', 'rate'])
                                ->toArray();
                        }
                    }

                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'sku' => $item->sku,
                        'sale_price' => $item->sale_price,
                        'tax_type' => $item->tax_type ?? 'exclusive',
                        'description' => $item->description,
                        'taxes' => $taxes
                    ];
                });

                return response()->json($transformedItems);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to fetch products'], 500);
            }
        }
        return response()->json(['error' => 'Permission denied'], 403);
    }

    public function stockStore(Request $request)
    {
        if(Auth::user()->can('create-stock')){
            $request->validate([
                'product_id' => 'required|exists:product_service_items,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'quantity' => 'required|numeric|min:0'
            ]);

            $existingStock = WarehouseStock::where('product_id', $request->product_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->first();

            if ($existingStock) {
                $existingStock->increment('quantity', $request->quantity);
            } else {
                WarehouseStock::create([
                    'product_id' => $request->product_id,
                    'warehouse_id' => $request->warehouse_id,
                    'quantity' => $request->quantity,
                ]);
            }

            return back()->with('success', __('Stock entry created successfully.'));
        }
        return back()->with('error', __('Permission denied'));
    }

    public function downloadTemplate(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->can('create-product-service-item') && !$user->can('manage-product-service-item') && $user->type !== 'company' && $user->type !== 'superadmin')) {
            return redirect()->route('product-service.items.index')->with('error', __('Permission denied'));
        }

        $category = ProductServiceCategory::where('created_by', creatorId())->value('name') ?? 'General';
        $unit = ProductServiceUnit::where('created_by', creatorId())->value('unit_name') ?? 'Pcs';
        $tax = ProductServiceTax::where('created_by', creatorId())->value('tax_name') ?? '';
        $warehouse = Warehouse::where('is_active', true)->where('created_by', creatorId())->value('name') ?? '';

        $columns = [
            'name',
            'sku',
            'type',
            'sale_price',
            'purchase_price',
            'category',
            'unit',
            'taxes',
            'tax_type',
            'warehouse',
            'quantity',
            'description',
            'long_description',
        ];

        $sampleRows = [
            [
                'Sample Product A',
                'SKU-SAMPLE-001',
                'product',
                '25000',
                '18000',
                $category,
                $unit,
                $tax,
                'exclusive',
                $warehouse,
                '50',
                'Short product description',
                'Detailed product specifications',
            ],
            [
                'Sample Consulting Service',
                'SRV-SAMPLE-001',
                'service',
                '150000',
                '0',
                $category,
                $unit,
                $tax,
                'inclusive',
                '',
                '',
                'Standard service package',
                'Detailed scope of service',
            ],
        ];

        $format = strtolower((string) $request->query('format', 'csv'));
        if ($format === 'xlsx') {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray($columns, null, 'A1');
            $sheet->fromArray($sampleRows, null, 'A2');

            return response()->streamDownload(function () use ($spreadsheet) {
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save('php://output');
            }, 'product_service_items_template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="product_service_items_template.csv"',
        ];

        return response()->streamDownload(function () use ($columns, $sampleRows) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $columns);
            foreach ($sampleRows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'product_service_items_template.csv', $headers);
    }

    public function import(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->can('create-product-service-item') && $user->type !== 'company' && $user->type !== 'superadmin')) {
            return redirect()->route('product-service.items.index')->with('error', __('Permission denied'));
        }

        $request->validate([
            'file' => 'required|file|max:10240',
            'update_existing' => 'nullable|boolean',
        ]);

        $uploadedFile = $request->file('file');
        $ext = strtolower((string) $uploadedFile->getClientOriginalExtension());
        if (!in_array($ext, ['csv', 'xlsx', 'xls'], true)) {
            return back()->withErrors([
                'file' => __('Unsupported file type (. :ext). Please upload a .csv, .xlsx, or .xls file.', ['ext' => $ext ?: 'unknown']),
            ])->with('error', __('Unsupported file type. Please upload a .csv, .xlsx, or .xls file.'));
        }

        $path = $uploadedFile->getRealPath();
        if (!$path || !is_readable($path)) {
            return back()->withErrors(['file' => __('Unable to read the uploaded file.')])->with('error', __('Unable to read the uploaded file.'));
        }

        try {
            $rows = [];
            if ($ext === 'csv') {
                // Verify file is not binary (e.g. renamed image/pdf/zip)
                $sampleBytes = file_get_contents($path, false, null, 0, 2048);
                if ($sampleBytes === false || str_contains($sampleBytes, "\0")) {
                    throw new \RuntimeException('Invalid CSV content');
                }
                $handle = fopen($path, 'r');
                if ($handle === false) {
                    throw new \RuntimeException('Unable to open CSV file');
                }
                while (($line = fgetcsv($handle)) !== false) {
                    $rows[] = $line;
                }
                fclose($handle);
            } else {
                $detectedType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($path);
                if (!in_array($detectedType, ['Xlsx', 'Xls'], true)) {
                    throw new \RuntimeException('Detected type mismatch: ' . $detectedType);
                }
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($detectedType);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($path);
                $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            }
        } catch (\Throwable $e) {
            return back()->withErrors([
                'file' => __('Invalid or corrupted :ext file. Please download the template and try again.', ['ext' => strtoupper($ext)]),
            ])->with('error', __('Invalid or corrupted spreadsheet file. Please upload a valid .csv, .xlsx, or .xls file.'));
        }

        if (count($rows) === 0 || empty($rows[0])) {
            return back()->withErrors(['file' => __('The uploaded file is empty or missing a header row.')])
                ->with('error', __('The uploaded file is empty or missing a header row.'));
        }

        $updateExisting = $request->boolean('update_existing');
        $companyId = creatorId();

        $categories = ProductServiceCategory::where('created_by', $companyId)->get(['id', 'name']);
        $units = ProductServiceUnit::where('created_by', $companyId)->get(['id', 'unit_name']);
        $taxes = ProductServiceTax::where('created_by', $companyId)->get(['id', 'tax_name']);
        $warehouses = Warehouse::where('is_active', true)->where('created_by', $companyId)->get(['id', 'name']);

        $categoryByName = $categories->keyBy(fn($c) => mb_strtolower(trim($c->name)));
        $categoryById = $categories->keyBy('id');
        $unitByName = $units->keyBy(fn($u) => mb_strtolower(trim($u->unit_name)));
        $unitById = $units->keyBy('id');
        $taxByName = $taxes->keyBy(fn($t) => mb_strtolower(trim($t->tax_name)));
        $taxById = $taxes->keyBy('id');
        $warehouseByName = $warehouses->keyBy(fn($w) => mb_strtolower(trim($w->name)));
        $warehouseById = $warehouses->keyBy('id');

        $rawHeader = array_shift($rows);

        // Normalize header names and strip UTF-8 BOM
        $header = array_map(function ($col) {
            $col = preg_replace('/^\xEF\xBB\xBF/', '', (string) $col);
            $col = strtolower(trim($col));
            return str_replace([' ', '-'], '_', $col);
        }, $rawHeader);

        if (!in_array('name', $header, true) || !in_array('sku', $header, true)) {
            return back()->withErrors([
                'file' => __('Header row must include at least "name" and "sku" columns.'),
            ])->with('error', __('Header row must include at least "name" and "sku" columns. Please download the template.'));
        }

        $createdCount = 0;
        $updatedCount = 0;
        $rowErrors = [];
        $rowNumber = 1;

        foreach ($rows as $rawRow) {
            $rowNumber++;
            if (!is_array($rawRow) || count(array_filter($rawRow, fn($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $row = [];
            foreach ($header as $idx => $colName) {
                if ($colName !== '') {
                    $row[$colName] = isset($rawRow[$idx]) ? trim((string) $rawRow[$idx]) : '';
                }
            }

            $name = $row['name'] ?? '';
            $sku = $row['sku'] ?? '';
            $type = strtolower($row['type'] ?? 'product') ?: 'product';
            $taxType = strtolower($row['tax_type'] ?? 'exclusive') ?: 'exclusive';
            if (!in_array($taxType, ['exclusive', 'inclusive'], true)) {
                $rowErrors[] = __('Row :row (:sku): Invalid tax_type ":tax_type". Allowed: exclusive, inclusive.', [
                    'row' => $rowNumber,
                    'sku' => $sku,
                    'tax_type' => $taxType,
                ]);
                continue;
            }
            $salePrice = $row['sale_price'] ?? '';
            $purchasePrice = $row['purchase_price'] ?? '';

            if ($name === '' || $sku === '') {
                $rowErrors[] = __('Row :row: "name" and "sku" are required.', ['row' => $rowNumber]);
                continue;
            }

            if (!in_array($type, ['product', 'service', 'part'], true)) {
                $rowErrors[] = __('Row :row (:sku): Invalid type ":type". Allowed: product, service, part.', [
                    'row' => $rowNumber,
                    'sku' => $sku,
                    'type' => $type,
                ]);
                continue;
            }

            if ($salePrice === '' || !is_numeric($salePrice) || (float) $salePrice < 0) {
                $rowErrors[] = __('Row :row (:sku): "sale_price" must be a non-negative number.', [
                    'row' => $rowNumber,
                    'sku' => $sku,
                ]);
                continue;
            }

            if ($purchasePrice === '' || !is_numeric($purchasePrice) || (float) $purchasePrice < 0) {
                $rowErrors[] = __('Row :row (:sku): "purchase_price" must be a non-negative number.', [
                    'row' => $rowNumber,
                    'sku' => $sku,
                ]);
                continue;
            }

            $existingItem = ProductServiceItem::where('created_by', $companyId)
                ->where('sku', $sku)
                ->first();

            if ($existingItem && !$updateExisting) {
                $rowErrors[] = __('Row :row (:sku): SKU already exists (enable "Update existing" to overwrite).', [
                    'row' => $rowNumber,
                    'sku' => $sku,
                ]);
                continue;
            }

            // Resolve category
            $categoryId = null;
            $rawCat = $row['category'] ?? ($row['category_id'] ?? '');
            if ($rawCat !== '') {
                if (is_numeric($rawCat) && $categoryById->has((int) $rawCat)) {
                    $categoryId = (int) $rawCat;
                } elseif ($categoryByName->has(mb_strtolower($rawCat))) {
                    $categoryId = $categoryByName->get(mb_strtolower($rawCat))->id;
                }
            }

            // Resolve unit
            $unitId = null;
            $rawUnit = $row['unit'] ?? ($row['unit_id'] ?? '');
            if ($rawUnit !== '') {
                if (is_numeric($rawUnit) && $unitById->has((int) $rawUnit)) {
                    $unitId = (int) $rawUnit;
                } elseif ($unitByName->has(mb_strtolower($rawUnit))) {
                    $unitId = $unitByName->get(mb_strtolower($rawUnit))->id;
                }
            }

            // Resolve taxes (comma or semicolon separated)
            $taxIds = null;
            $rawTaxes = $row['taxes'] ?? ($row['tax_ids'] ?? '');
            if ($rawTaxes !== '') {
                $resolvedTaxes = [];
                foreach (preg_split('/[;,]+/', $rawTaxes) as $taxToken) {
                    $taxToken = trim($taxToken);
                    if ($taxToken === '') {
                        continue;
                    }
                    if (is_numeric($taxToken) && $taxById->has((int) $taxToken)) {
                        $resolvedTaxes[] = (int) $taxToken;
                    } elseif ($taxByName->has(mb_strtolower($taxToken))) {
                        $resolvedTaxes[] = $taxByName->get(mb_strtolower($taxToken))->id;
                    }
                }
                if (!empty($resolvedTaxes)) {
                    $taxIds = array_values(array_unique($resolvedTaxes));
                }
            }

            // Resolve warehouse & quantity
            $warehouseId = null;
            $rawWarehouse = $row['warehouse'] ?? ($row['warehouse_id'] ?? '');
            if ($rawWarehouse !== '') {
                if (is_numeric($rawWarehouse) && $warehouseById->has((int) $rawWarehouse)) {
                    $warehouseId = (int) $rawWarehouse;
                } elseif ($warehouseByName->has(mb_strtolower($rawWarehouse))) {
                    $warehouseId = $warehouseByName->get(mb_strtolower($rawWarehouse))->id;
                }
            }

            $quantity = null;
            $rawQuantity = $row['quantity'] ?? '';
            if ($rawQuantity !== '' && is_numeric($rawQuantity) && (float) $rawQuantity >= 0) {
                $quantity = (float) $rawQuantity;
            }

            \Illuminate\Support\Facades\DB::transaction(function () use (
                $existingItem,
                $name,
                $sku,
                $type,
                $salePrice,
                $purchasePrice,
                $categoryId,
                $unitId,
                $taxIds,
                $taxType,
                $warehouseId,
                $quantity,
                $row,
                $companyId,
                &$createdCount,
                &$updatedCount
            ) {
                $item = $existingItem ?? new ProductServiceItem();
                $item->name = $name;
                $item->sku = $sku;
                $item->type = $type;
                $item->sale_price = (float) $salePrice;
                $item->purchase_price = (float) $purchasePrice;
                $item->category_id = $categoryId;
                $item->unit = $unitId;
                $item->tax_ids = $taxIds;
                $item->tax_type = $taxType;
                $item->description = ($row['description'] ?? '') !== '' ? $row['description'] : $item->description;
                $item->long_description = ($row['long_description'] ?? '') !== '' ? $row['long_description'] : $item->long_description;

                if (!$existingItem) {
                    $item->creator_id = Auth::id();
                    $item->created_by = $companyId;
                }

                $item->save();

                if ($type !== 'service' && $warehouseId !== null && $quantity !== null) {
                    WarehouseStock::updateOrCreate(
                        ['product_id' => $item->id, 'warehouse_id' => $warehouseId],
                        ['quantity' => $quantity]
                    );
                }

                if ($existingItem) {
                    $updatedCount++;
                } else {
                    $createdCount++;
                }
            });
        }

        $totalSuccess = $createdCount + $updatedCount;
        $summary = __('Import complete: :created created, :updated updated.', [
            'created' => $createdCount,
            'updated' => $updatedCount,
        ]);

        if (!empty($rowErrors)) {
            $previewErrors = implode(' | ', array_slice($rowErrors, 0, 5));
            if (count($rowErrors) > 5) {
                $previewErrors .= ' ' . __('(+:more more)', ['more' => count($rowErrors) - 5]);
            }

            return back()
                ->with($totalSuccess > 0 ? 'success' : 'error', $totalSuccess > 0 ? $summary : __('No items were imported.'))
                ->with('error', $previewErrors);
        }

        return redirect()->route('product-service.items.index')->with('success', $summary);
    }
}
