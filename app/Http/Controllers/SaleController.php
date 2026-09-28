<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Campaign;
use App\Models\District;
use App\Models\Product;
use App\Models\Province;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SubDistrict;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    private const FIXED_VAT_RATE = 7.0;

    public function overview(Request $request): View
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $baseQuery = Sale::query();
        if ($from) {
            $baseQuery->whereDate('sale_date', '>=', $from);
        }
        if ($to) {
            $baseQuery->whereDate('sale_date', '<=', $to);
        }

        $summary = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total_orders, COALESCE(SUM(quantity), 0) as total_qty, COALESCE(SUM(total_amount), 0) as total_amount')
            ->first();

        $dailySales = (clone $baseQuery)
            ->selectRaw('sale_date, COUNT(*) as total_orders, SUM(quantity) as total_qty, SUM(total_amount) as total_amount')
            ->groupBy('sale_date')
            ->orderByDesc('sale_date')
            ->limit(15)
            ->get();

        $productRanking = (clone $baseQuery)
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->selectRaw('products.id as product_id, products.name as product_name, products.sku as product_sku, COUNT(sales.id) as order_count, SUM(sales.quantity) as total_qty, SUM(sales.total_amount) as total_amount')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_amount')
            ->orderByDesc('total_qty')
            ->limit(20)
            ->get();

        return view('sales.overview', compact('from', 'to', 'summary', 'dailySales', 'productRanking'));
    }

    public function ordersIndex(Request $request): View
    {
        return view('sales.orders');
    }

    public function ordersData(Request $request): JsonResponse
    {
        $draw = (int) $request->input('draw', 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 10);
        $length = $length > 0 ? min($length, 100) : 10;
        $search = trim((string) data_get($request->input('search'), 'value', ''));

        $query = Sale::query()
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->leftJoin('products', 'sales.product_id', '=', 'products.id')
            ->leftJoin(DB::raw('(SELECT sale_id, SUM(quantity) as returned_quantity FROM sale_returns GROUP BY sale_id) as sr'), 'sr.sale_id', '=', 'sales.id')
            ->leftJoin(DB::raw('(SELECT sale_id, COUNT(*) as item_count, MIN(id) as first_item_id FROM sale_items GROUP BY sale_id) as si'), 'si.sale_id', '=', 'sales.id')
            ->leftJoin('sale_items as primary_item', 'primary_item.id', '=', 'si.first_item_id')
            ->leftJoin('campaigns as primary_campaign', 'primary_campaign.id', '=', 'primary_item.campaign_id')
            ->select([
                'sales.id',
                'sales.quantity',
                'sales.total_amount',
                'sales.status',
                'sales.sale_date',
                DB::raw('COALESCE(customers.name, "-") as customer_name'),
                DB::raw('COALESCE(products.name, "-") as product_name'),
                'primary_campaign.name as campaign_name',
                DB::raw('COALESCE(sr.returned_quantity, 0) as returned_quantity'),
                DB::raw('COALESCE(si.item_count, 0) as item_count'),
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('sales.id', 'like', "%{$search}%")
                    ->orWhere('customers.name', 'like', "%{$search}%")
                    ->orWhere('products.name', 'like', "%{$search}%")
                    ->orWhere('sales.status', 'like', "%{$search}%");
            });
        }

        $totalRecords = (int) Sale::count();
        $filteredRecords = (int) (clone $query)->count();

        $orderableColumns = [
            0 => 'sales.id',
            1 => 'customer_name',
            2 => 'product_name',
            3 => 'sales.quantity',
            4 => 'sales.total_amount',
            5 => 'sales.status',
            6 => 'sales.id',
        ];

        $orderColumnIndex = (int) data_get($request->input('order'), '0.column', 0);
        $orderDir = strtolower((string) data_get($request->input('order'), '0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderColumn = $orderableColumns[$orderColumnIndex] ?? 'sales.id';

        $rows = $query
            ->orderBy($orderColumn, $orderDir)
            ->orderBy('sales.id', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function ($sale) {
            $returnable = max(0, (int) $sale->quantity - (int) $sale->returned_quantity);
            $orderNo = '#SO-' . str_pad((string) $sale->id, 4, '0', STR_PAD_LEFT);
            $isMultiItem = (int) $sale->item_count > 1;
            $productLabel = $sale->campaign_name ?: ($isMultiItem
                ? $sale->product_name . ' +' . ((int) $sale->item_count - 1) . ' รายการ'
                : $sale->product_name);

            $editUrl = route('sales.orders.edit', $sale->id);
            $returnUrl = route('sale-returns.create', ['sale_id' => $sale->id]);
            $invoiceUrl = route('sales.invoice');
            $taxInvoiceUrl = route('sales.invoice') . '?mode=tax';
            $purchaseOrderUrl = route('sales.orders.purchase-order', $sale->id);

            $actionHtml = '<div class="action-buttons">';
            $actionHtml .= '<a href="' . e($editUrl) . '" class="btn secondary">แก้ไข</a>';
            // if ($isMultiItem) {
            //     $actionHtml .= '<span class="btn secondary" style="opacity:.65; pointer-events:none;">คืนหลายสินค้า</span>';
            // } elseif ($returnable > 0) {
            //     $actionHtml .= '<a href="' . e($returnUrl) . '" class="btn secondary">คืนสินค้า</a>';
            // } else {
            //     $actionHtml .= '<span class="btn secondary" style="opacity:.65; pointer-events:none;">คืนครบแล้ว</span>';
            // }
            $actionHtml .= '<a href="' . e($invoiceUrl) . '" class="btn">ออกใบแจ้งหนี้</a>';
            $actionHtml .= '<a href="' . e($taxInvoiceUrl) . '" class="btn print-btn">ออกใบกำกับภาษี</a>';
            $actionHtml .= '<a href="' . e($purchaseOrderUrl) . '" class="btn secondary">ใบสั่งซื้อ</a>';
            $actionHtml .= '</div>';

            return [
                'order_no' => e($orderNo),
                'customer_name' => e($sale->customer_name),
                'product_name' => e($productLabel),
                'quantity' => number_format((int) $sale->quantity),
                'total_amount' => number_format((float) $sale->total_amount, 2),
                'status' => e($sale->status),
                'actions' => $actionHtml,
            ];
        })->values();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function index(Request $request): View
    {
        $query = Sale::with(['customer', 'product'])
            ->withSum('returns as returned_quantity', 'quantity');
        $search = $request->input('search');
        $status = $request->input('status');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhereHas('product', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $sales = $query->latest()->get();

        return view('sales.index', compact('sales', 'search', 'status'));
    }

    public function create(): View
    {
        $customers = Customer::all();
        $products = Product::orderBy('name')->get();
        $productCatalog = $products->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'stock' => $product->stock,
            'price' => (float) ($product->sale_price ?? $product->price),
        ])->values();
        $today = now()->toDateString();
        $campaigns = Campaign::query()
            ->where('campaign_type', 'โปรโมชั่นสินค้า')
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->with(['products:id,name,sku,stock'])
            ->orderBy('name')
            ->get()
            ->map(fn (Campaign $campaign) => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'product_ids' => $campaign->products->pluck('id')->values(),
                'anchor_product_id' => $campaign->products->first()?->id,
                'stock' => $campaign->products->min('stock'),
                'price' => (float) ($campaign->discounted_total ?? $campaign->products->sum(fn (Product $product) => (float) $product->pivot->special_price)),
                'products' => $campaign->products->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock' => $product->stock,
                ])->values(),
            ])->values();

        return view('sales.create', compact('customers', 'products', 'campaigns', 'productCatalog'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'shipping_address' => 'nullable|string|max:2000',
            'shipping_province_id' => 'nullable|integer|exists:provinces,id',
            'shipping_district_id' => 'nullable|integer|exists:districts,id',
            'shipping_sub_district_id' => 'nullable|integer|exists:sub_districts,id',
            'shipping_zipcode' => 'nullable|string|max:20',
            'shipping_address_line' => 'nullable|string|max:2000',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.campaign_id' => 'nullable|integer|exists:campaigns,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'sale_date' => 'required|date',
            'status' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $normalizedItems = $this->normalizeItems($data['products']);
            if ($normalizedItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'products' => 'กรุณาเลือกรายการสินค้าอย่างน้อย 1 รายการ',
                ]);
            }

            $campaignIds = $normalizedItems->pluck('campaign_id')->filter()->unique();
            $campaigns = $campaignIds->isEmpty()
                ? collect()
                : Campaign::query()
                    ->whereIn('id', $campaignIds)
                    ->where('campaign_type', 'โปรโมชั่นสินค้า')
                    ->where('status', 'active')
                    ->whereDate('start_date', '<=', $data['sale_date'])
                    ->whereDate('end_date', '>=', $data['sale_date'])
                    ->with('products')
                    ->get()
                    ->keyBy('id');

            $stockRequirements = [];
            foreach ($normalizedItems as $item) {
                if ($item['campaign_id'] === null) {
                    $stockRequirements[$item['product_id']] = ($stockRequirements[$item['product_id']] ?? 0) + $item['quantity'];
                    continue;
                }

                $campaign = $campaigns->get($item['campaign_id']);
                if (!$campaign || !$campaign->products->contains('id', $item['product_id'])) {
                    throw ValidationException::withMessages([
                        'products' => 'โปรโมชั่นที่เลือกไม่ถูกต้อง หรือไม่อยู่ในช่วงใช้งาน',
                    ]);
                }

                foreach ($campaign->products as $component) {
                    $stockRequirements[$component->id] = ($stockRequirements[$component->id] ?? 0) + $item['quantity'];
                }
            }

            $products = Product::query()
                ->lockForUpdate()
                ->whereIn('id', array_keys($stockRequirements))
                ->get()
                ->keyBy('id');

            foreach ($stockRequirements as $productId => $quantity) {
                $product = $products->get($productId);
                if (!$product || $quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'products' => 'จำนวนสินค้า ' . ($product->name ?? '') . ' คงเหลือไม่เพียงพอสำหรับรายการที่เลือก',
                    ]);
                }
            }

            $saleItems = [];
            $subtotal = 0;
            $totalQty = 0;

            foreach ($normalizedItems as $item) {
                $product = $products->get($item['product_id']);
                if (!$product) {
                    throw ValidationException::withMessages([
                        'products' => 'ไม่พบข้อมูลสินค้า',
                    ]);
                }

                $unitPrice = (float) ($product->sale_price ?? $product->price);
                if ($item['campaign_id'] !== null) {
                    $campaign = $campaigns->get($item['campaign_id']);
                    $unitPrice = (float) ($campaign->discounted_total ?? $campaign->products->sum(fn (Product $component) => (float) $component->pivot->special_price));
                }
                $lineSubtotal = $unitPrice * $item['quantity'];

                $subtotal += $lineSubtotal;
                $totalQty += $item['quantity'];

                $saleItems[] = [
                    'product_id' => $product->id,
                    'campaign_id' => $item['campaign_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'line_subtotal' => $lineSubtotal,
                    'line_total' => $lineSubtotal,
                ];
            }

            $customer = Customer::query()->findOrFail($data['customer_id']);
            $shippingPayload = $this->resolveShippingPayload($data, $customer);

            $discountAmount = min((float) ($data['discount_amount'] ?? 0), $subtotal);
            $vatRate = self::FIXED_VAT_RATE;
            $taxable = max(0, $subtotal - $discountAmount);
            $vatAmount = $taxable * ($vatRate / 100);
            $grandTotal = $taxable + $vatAmount;

            $primaryItem = $saleItems[0];

            $sale = Sale::create([
                'customer_id' => $data['customer_id'],
                ...$shippingPayload,
                'product_id' => $primaryItem['product_id'],
                'quantity' => $totalQty,
                'unit_price' => $primaryItem['unit_price'],
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'total_amount' => $grandTotal,
                'sale_date' => $data['sale_date'],
                'status' => $data['status'] ?? 'completed',
            ]);

            $sale->items()->createMany($saleItems);

            foreach ($stockRequirements as $productId => $quantity) {
                $products[$productId]->decrement('stock', $quantity);
            }
        });

        return redirect()->route('sales.index')->with('success', 'บันทึกการขายเรียบร้อยแล้ว');
    }

    public function edit(Sale $sale): View
    {
        $sale->load(['customer', 'product', 'items']);
        $customers = Customer::all();
        $products = Product::orderBy('name')->get();
        $returnedQty = (int) $sale->returns()->sum('quantity');

        return view('sales.edit', compact('sale', 'customers', 'products', 'returnedQty'));
    }

    public function update(Request $request, Sale $sale): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'shipping_address' => 'nullable|string|max:2000',
            'shipping_province_id' => 'nullable|integer|exists:provinces,id',
            'shipping_district_id' => 'nullable|integer|exists:districts,id',
            'shipping_sub_district_id' => 'nullable|integer|exists:sub_districts,id',
            'shipping_zipcode' => 'nullable|string|max:20',
            'shipping_address_line' => 'nullable|string|max:2000',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'discount_amount' => 'nullable|numeric|min:0',
            'sale_date' => 'required|date',
            'status' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $sale) {
            $sale = Sale::with(['returns', 'items'])->lockForUpdate()->findOrFail($sale->id);
            $returnedQty = (int) $sale->returns()->lockForUpdate()->sum('quantity');
            if ($returnedQty > 0) {
                throw ValidationException::withMessages([
                    'products' => "ไม่สามารถแก้ไขรายการสินค้าได้ เนื่องจากมีการคืนสินค้าแล้ว ({$returnedQty})",
                ]);
            }

            $oldItems = $sale->items;
            if ($oldItems->isEmpty() && $sale->product_id) {
                $oldItems = collect([
                    new SaleItem([
                        'product_id' => $sale->product_id,
                        'quantity' => $sale->quantity,
                    ]),
                ]);
            }

            $normalizedItems = $this->normalizeItems($data['products']);
            if ($normalizedItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'products' => 'กรุณาเลือกรายการสินค้าอย่างน้อย 1 รายการ',
                ]);
            }

            $candidateProductIds = $normalizedItems->pluck('product_id')
                ->merge($oldItems->pluck('product_id'))
                ->unique()
                ->values();

            $products = Product::query()
                ->lockForUpdate()
                ->whereIn('id', $candidateProductIds)
                ->get()
                ->keyBy('id');

            foreach ($oldItems as $oldItem) {
                $product = $products->get($oldItem->product_id);
                if ($product) {
                    $product->increment('stock', (int) $oldItem->quantity);
                }
            }

            $saleItems = [];
            $subtotal = 0;
            $totalQty = 0;

            foreach ($normalizedItems as $item) {
                $product = $products->get($item['product_id']);
                if (!$product) {
                    throw ValidationException::withMessages([
                        'products' => 'ไม่พบข้อมูลสินค้า',
                    ]);
                }

                if ($item['quantity'] > $product->stock) {
                    throw ValidationException::withMessages([
                        'products' => "สินค้า {$product->name} มีคงเหลือไม่พอ",
                    ]);
                }

                $unitPrice = (float) ($product->sale_price ?? $product->price);
                $lineSubtotal = $unitPrice * $item['quantity'];

                $subtotal += $lineSubtotal;
                $totalQty += $item['quantity'];

                $saleItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'line_subtotal' => $lineSubtotal,
                    'line_total' => $lineSubtotal,
                ];
            }

            $customer = Customer::query()->findOrFail($data['customer_id']);
            $shippingPayload = $this->resolveShippingPayload($data, $customer);

            $discountAmount = min((float) ($data['discount_amount'] ?? 0), $subtotal);
            $vatRate = self::FIXED_VAT_RATE;
            $taxable = max(0, $subtotal - $discountAmount);
            $vatAmount = $taxable * ($vatRate / 100);
            $grandTotal = $taxable + $vatAmount;

            foreach ($saleItems as $item) {
                $products[$item['product_id']]->decrement('stock', $item['quantity']);
            }

            $primaryItem = $saleItems[0];

            $sale->update([
                'customer_id' => $data['customer_id'],
                ...$shippingPayload,
                'product_id' => $primaryItem['product_id'],
                'quantity' => $totalQty,
                'unit_price' => $primaryItem['unit_price'],
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'total_amount' => $grandTotal,
                'sale_date' => $data['sale_date'],
                'status' => $data['status'] ?? 'completed',
            ]);

            $sale->items()->delete();
            $sale->items()->createMany($saleItems);
        });

        return redirect()->route('sales.orders')->with('success', 'อัปเดตการขายเรียบร้อยแล้ว');
    }

    public function purchaseOrder(Sale $sale): View
    {
        return view('sales.purchase_order', $this->buildPurchaseOrderData($sale));
    }

    private function buildPurchaseOrderData(Sale $sale): array
    {
        $sale->load(['customer', 'product', 'items.product', 'items.campaign']);

        $items = $sale->items;
        if ($items->isEmpty() && $sale->product_id) {
            $items = collect([
                new SaleItem([
                    'product_id' => $sale->product_id,
                    'quantity' => $sale->quantity,
                    'unit_price' => $sale->unit_price,
                    'line_total' => $sale->total_amount,
                ]),
            ]);
            $items[0]->setRelation('product', $sale->product);
        }

        $documentNo = 'PO-' . date('Ym', strtotime((string) $sale->sale_date)) . '-' . str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT);

        return [
            'sale' => $sale,
            'customer' => $sale->customer,
            'items' => $items,
            'documentNo' => $documentNo,
            'issueDate' => $sale->sale_date,
            'subtotal' => (float) $sale->subtotal,
            'discountAmount' => (float) $sale->discount_amount,
            'vatRate' => (float) ($sale->vat_rate ?: self::FIXED_VAT_RATE),
            'vatAmount' => (float) $sale->vat_amount,
            'grandTotal' => (float) $sale->total_amount,
            'shippingAddress' => $sale->shipping_address ?: ($sale->customer->address ?? '-'),
            'company' => [
                'name' => 'CRM Center Co., Ltd.',
                'address' => '123/45 ถนนสุขุมวิท เขตวัฒนา กรุงเทพมหานคร 10110',
                'phone' => '02-123-4567',
                'tax_id' => '3-1000-00000-00-0',
            ],
        ];
    }

    private function resolveShippingPayload(array $data, Customer $customer): array
    {
        $provinceId = isset($data['shipping_province_id']) ? (int) $data['shipping_province_id'] : null;
        $districtId = isset($data['shipping_district_id']) ? (int) $data['shipping_district_id'] : null;
        $subDistrictId = isset($data['shipping_sub_district_id']) ? (int) $data['shipping_sub_district_id'] : null;

        $provinceName = $provinceId ? (string) Province::query()->whereKey($provinceId)->value('name_th') : '';
        $districtName = $districtId ? (string) District::query()->whereKey($districtId)->value('name_th') : '';
        $subDistrict = $subDistrictId
            ? SubDistrict::query()->select('name_th', 'zip_code')->whereKey($subDistrictId)->first()
            : null;

        $subDistrictName = (string) ($subDistrict->name_th ?? '');
        $zipcode = trim((string) ($data['shipping_zipcode'] ?? ''));
        if ($zipcode === '') {
            $zipcode = (string) ($subDistrict->zip_code ?? '');
        }

        $addressLine = trim((string) ($data['shipping_address_line'] ?? ''));
        $composedAddress = trim(implode(' ', array_filter([
            $addressLine,
            $subDistrictName,
            $districtName,
            $provinceName,
            $zipcode,
        ])));

        $shippingAddress = trim((string) ($data['shipping_address'] ?? ''));
        if ($shippingAddress === '') {
            $shippingAddress = $composedAddress !== '' ? $composedAddress : (string) ($customer->address ?? '');
        }

        return [
            'shipping_address' => $shippingAddress,
            'shipping_province_id' => $provinceId,
            'shipping_district_id' => $districtId,
            'shipping_sub_district_id' => $subDistrictId,
            'shipping_zipcode' => $zipcode,
            'shipping_address_line' => $addressLine,
        ];
    }

    private function normalizeItems(array $items)
    {
        return collect($items)
            ->map(function ($item) {
                return [
                    'product_id' => (int) ($item['product_id'] ?? 0),
                    'quantity' => (int) ($item['quantity'] ?? 0),
                    'campaign_id' => !empty($item['campaign_id']) ? (int) $item['campaign_id'] : null,
                ];
            })
            ->filter(fn ($item) => $item['product_id'] > 0 && $item['quantity'] > 0)
            ->groupBy(fn ($item) => $item['product_id'] . ':' . ($item['campaign_id'] ?? ''))
            ->map(function ($group) {
                $item = $group->first();
                return [
                    'product_id' => $item['product_id'],
                    'quantity' => (int) $group->sum('quantity'),
                    'campaign_id' => $item['campaign_id'],
                ];
            })
            ->values();
    }
}
