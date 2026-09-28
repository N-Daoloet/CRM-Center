<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(Request $request): View
    {
        $query = Campaign::query()
            // ->orderByDesc('created_at')
            ->orderByDesc('id');
        $search = $request->input('search');
        $status = $request->input('status');
        $campaignType = $request->input('campaign_type');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('campaign_type', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($campaignType) {
            $query->where('campaign_type', $campaignType);
        }

        $campaigns = $query->withCount(['products', 'customers'])->get();
        // dd($campaigns);
        $summary = [
            'total' => Campaign::count(),
            'active' => Campaign::where('status', 'active')->count(),
            'planned' => Campaign::where('status', 'planned')->count(),
            'completed' => Campaign::where('status', 'completed')->count(),
            'products' => Campaign::withCount('products')->get()->sum('products_count'),
        ];

        $campaignTypes = ['รายชื่อ', 'โปรโมชั่นสินค้า'];

        return view('campaigns.index', compact('campaigns', 'search', 'status', 'campaignType', 'campaignTypes', 'summary'));
    }

    public function create(): View
    {
        $products = Product::query()->orderBy('name')->get();

        return view('campaigns.create', compact('products'));
    }

    public function edit(Campaign $campaign): View
    {
        $campaign->load(['products', 'customers']);
        $campaignType = $this->resolveCampaignType($campaign);

        if ($campaignType === 'โปรโมชั่นสินค้า') {
            $products = Product::query()->orderBy('name')->get();
            $selectedProductPrices = $campaign->products->mapWithKeys(function (Product $product) {
                return [$product->id => $product->pivot->special_price];
            });
            $productOptions = $products->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => (float) ($product->sale_price ?? $product->price ?? 0),
                ];
            });
            $initialProducts = $campaign->products->map(function (Product $product) {
                return [
                    'product_id' => $product->id,
                    'special_price' => (float) $product->pivot->special_price,
                ];
            })->values();
            [$pricingType, $pricingValue] = $this->campaignPricing($campaign);

            return view('campaigns.edit-promotion', compact(
                'campaign',
                'products',
                'selectedProductPrices',
                'productOptions',
                'initialProducts',
                'pricingType',
                'pricingValue'
            ));
        }

        $customers = Customer::query()
            ->with('sales')
            ->orderBy('name')
            ->get()
            ->map(function (Customer $customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'total_purchase' => (float) $customer->sales->sum('total_amount'),
                    'is_new_customer' => $customer->sales->isEmpty(),
                ];
            });
        $selectedCustomerIds = $campaign->customers->modelKeys();

        return view('campaigns.edit-by-name', compact('campaign', 'customers', 'selectedCustomerIds'));
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        return $this->resolveCampaignType($campaign) === 'โปรโมชั่นสินค้า'
            ? $this->updatePromotion($request, $campaign)
            : $this->updateByName($request, $campaign);
    }

    public function updatePromotion(Request $request, Campaign $campaign): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|string|max:50',
            'pricing_type' => 'nullable|in:percent,fixed',
            'pricing_value' => 'nullable|numeric|min:0',
            'products' => 'nullable|array',
            'products.*' => 'array',
            'products.*.product_id' => 'nullable|integer|exists:products,id',
            'products.*.special_price' => 'nullable|numeric|min:0',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'required|integer|distinct|exists:products,id',
            'special_prices' => 'nullable|array',
            'special_prices.*' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($campaign, $data, $request) {
            $existingPrices = $campaign->products()
                ->pluck('campaign_product.special_price', 'products.id');
            $selectedProducts = [];

            $submittedProducts = collect($data['products'] ?? [])
                ->filter(fn (array $entry) => !empty($entry['product_id']))
                ->mapWithKeys(fn (array $entry) => [(int) $entry['product_id'] => $entry['special_price'] ?? null]);

            foreach ($data['product_ids'] ?? [] as $productId) {
                $submittedProducts[(int) $productId] = $request->input("special_prices.{$productId}");
            }

            foreach ($submittedProducts as $productId => $submittedPrice) {
                $productId = (int) $productId;
                $product = Product::query()->find($productId);
                $specialPrice = $submittedPrice !== null && $submittedPrice !== ''
                    ? (float) $submittedPrice
                    : (float) ($existingPrices[$productId] ?? $product->sale_price ?? $product->price ?? 0);

                $selectedProducts[$productId] = [
                    'special_price' => number_format($specialPrice, 2, '.', ''),
                ];
            }

            $pricingType = $data['pricing_type'] ?? 'percent';
            $pricingValue = (float) ($data['pricing_value'] ?? 10);
            $discountedTotal = $this->calculateDiscountedCampaignTotal(
                array_keys($selectedProducts),
                $pricingType,
                $pricingValue
            );

            $campaign->update([
                'name' => $data['name'],
                'campaign_type' => 'โปรโมชั่นสินค้า',
                'description' => $data['description'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $data['status'] ?? 'active',
                'pricing_type' => $pricingType,
                'pricing_value' => $pricingValue,
                'discounted_total' => $discountedTotal,
            ]);

            $campaign->products()->sync($selectedProducts);
            $campaign->customers()->sync([]);
        });

        return redirect()->route('campaigns.index')->with('success', 'อัปเดตแคมเปญเรียบร้อยแล้ว');
    }

    public function updateByName(Request $request, Campaign $campaign): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|string|max:50',
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'required|integer|distinct|exists:customers,id',
        ]);

        DB::transaction(function () use ($campaign, $data) {
            $campaign->update([
                'name' => $data['name'],
                'campaign_type' => 'รายชื่อ',
                'description' => $data['description'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $data['status'] ?? 'active',
            ]);

            $campaign->customers()->sync($data['customer_ids']);
            $campaign->products()->sync([]);
        });

        return redirect()->route('campaigns.index')->with('success', 'อัปเดตแคมเปญเรียบร้อยแล้ว');
    }

    private function resolveCampaignType(Campaign $campaign): string
    {
        if ($campaign->relationLoaded('products')) {
            $hasProducts = $campaign->products->isNotEmpty();
        } else {
            $hasProducts = $campaign->products()->exists();
        }

        return $hasProducts || $campaign->campaign_type === 'โปรโมชั่นสินค้า'
            ? 'โปรโมชั่นสินค้า'
            : 'รายชื่อ';
    }

    private function campaignPricing(Campaign $campaign): array
    {
        if ($campaign->pricing_type && $campaign->pricing_value !== null) {
            return [$campaign->pricing_type, (float) $campaign->pricing_value];
        }

        if ($campaign->products->isEmpty()) {
            return ['percent', 10];
        }

        $basePrices = $campaign->products->map(fn (Product $product) =>
            (float) ($product->sale_price ?? $product->price ?? 0)
        );
        $specialPrices = $campaign->products->map(fn (Product $product) =>
            (float) $product->pivot->special_price
        );
        $baseTotal = $basePrices->sum();
        $specialTotal = $specialPrices->sum();

        if ($baseTotal <= 0) {
            return ['fixed', $specialTotal];
        }

        $discountRatios = $campaign->products
            ->filter(fn (Product $product) => (float) ($product->sale_price ?? $product->price ?? 0) > 0)
            ->map(fn (Product $product) =>
                (float) $product->pivot->special_price / (float) ($product->sale_price ?? $product->price)
            );

        $ratiosMatch = $discountRatios->isNotEmpty()
            && $discountRatios->max() - $discountRatios->min() <= 0.0001;

        if ($ratiosMatch && $specialTotal <= $baseTotal) {
            $percent = (($baseTotal - $specialTotal) / $baseTotal) * 100;
            return ['percent', round(max(0, min(100, $percent)), 2)];
        }

        return ['fixed', round($specialTotal, 2)];
    }

    private function calculateDiscountedCampaignTotal(array $productIds, string $pricingType, float $pricingValue): float
    {
        if (empty($productIds)) {
            return 0.00;
        }

        $originalTotal = Product::query()
            ->whereIn('id', $productIds)
            ->get(['sale_price', 'price'])
            ->sum(fn (Product $product) => (float) ($product->sale_price ?? $product->price ?? 0));

        if ($originalTotal <= 0) {
            return 0.00;
        }

        $discountedTotal = $pricingType === 'percent'
            ? $originalTotal * (1 - (min($pricingValue, 100) / 100))
            : $pricingValue;

        return round(max(0, $discountedTotal), 2);
    }

    public function createByName(): View
    {
        $customers = Customer::query()
            ->with('sales')
            ->orderBy('name')
            ->get()
            ->map(function (Customer $customer) {
                $totalPurchase = (float) $customer->sales->sum('total_amount');

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'total_purchase' => $totalPurchase,
                    'is_new_customer' => $customer->sales->count() === 0,
                    'created_at' => $customer->created_at?->toDateString(),
                ];
            });
        // dd($customers);
        return view('campaigns.create-by-name', compact('customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|string|max:50',
            'pricing_type' => 'nullable|in:percent,fixed',
            'pricing_value' => 'nullable|numeric|min:0',
            'products' => 'nullable|array',
            'products.*.product_id' => 'nullable|exists:products,id',
            'products.*.special_price' => 'nullable|numeric|min:0',
        ]);

        $selectedProducts = [];
        foreach ($data['products'] ?? [] as $entry) {
            $isSelected = isset($entry['selected'])
                ? !empty($entry['selected'])
                : !empty($entry['product_id']) && array_key_exists('special_price', $entry);

            if (!$isSelected || empty($entry['product_id'])) {
                continue;
            }

            if (!isset($entry['special_price']) || $entry['special_price'] === '') {
                throw ValidationException::withMessages([
                    'products' => 'กรุณากรอกราคาโปรโมชั่นสำหรับสินค้าที่เลือก',
                ]);
            }

            $selectedProducts[(int) $entry['product_id']] = [
                'special_price' => number_format((float) $entry['special_price'], 2, '.', ''),
            ];
        }

        $pricingType = $data['pricing_type'] ?? 'percent';
        $pricingValue = (float) ($data['pricing_value'] ?? 10);
        $discountedTotal = $this->calculateDiscountedCampaignTotal(
            array_keys($selectedProducts),
            $pricingType,
            $pricingValue
        );

        DB::transaction(function () use ($data, $selectedProducts, $pricingType, $pricingValue, $discountedTotal) {
            $campaign = Campaign::create(array_merge($data, [
                'campaign_type' => 'โปรโมชั่นสินค้า',
                'pricing_type' => $pricingType,
                'pricing_value' => $pricingValue,
                'discounted_total' => $discountedTotal,
            ]));

            if ($selectedProducts) {
                $campaign->products()->sync($selectedProducts);
            }
        });

        return redirect()->route('campaigns.index')->with('success', 'สร้างแคมเปญเรียบร้อยแล้ว');
    }

    public function storeByName(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|string|max:50',
            'customers' => 'required|array|min:1',
            'customers.*.customer_id' => 'required|exists:customers,id',
        ]);

        $campaign = Campaign::create([
            'name' => $data['name'],
            'campaign_type' => 'รายชื่อ',
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'] ?? 'active',
        ]);

        $customerIds = collect($request->input('customers', []))
            ->pluck('customer_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($customerIds) {
            $campaign->customers()->sync($customerIds);
        }

        return redirect()->route('campaigns.index')->with('success', 'สร้างแคมเปญรายชื่อเรียบร้อยแล้ว');
    }
}
