<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleReturnController extends Controller
{
    public function index(Request $request): View
    {
        $query = SaleReturn::with(['sale', 'customer', 'product']);
        $search = $request->input('search');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhereHas('product', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhereHas('sale', function ($sub) use ($search) {
                    $sub->where('id', $search);
                })->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $returns = $query->latest()->get();

        return view('sale_returns.index', compact('returns', 'search'));
    }

    public function create(Request $request): View
    {
        $sales = Sale::with(['customer', 'product', 'items.product'])
            ->withSum('returns as returned_quantity', 'quantity')
            ->latest()
            ->get()
            ->map(function ($sale) {
                $returned = (int) ($sale->returned_quantity ?? 0);
                $singleItemQuantity = $sale->items->count() === 1
                    ? (int) $sale->items->first()->quantity
                    : (int) $sale->quantity;

                $sale->returnable_quantity = max(0, $singleItemQuantity - $returned);
                $sale->is_single_item = $sale->items->count() <= 1;
                if ($sale->items->count() === 1) {
                    $sale->return_product_name = $sale->items->first()->product->name ?? '-';
                } else {
                    $sale->return_product_name = $sale->product->name ?? '-';
                }

                return $sale;
            })
            ->filter(fn ($sale) => $sale->returnable_quantity > 0 && $sale->is_single_item)
            ->values();

        $selectedSaleId = (int) $request->query('sale_id');

        return view('sale_returns.create', compact('sales', 'selectedSaleId'));
    }

    public function report(Request $request): View
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $baseQuery = SaleReturn::query();

        if ($from) {
            $baseQuery->whereDate('return_date', '>=', $from);
        }

        if ($to) {
            $baseQuery->whereDate('return_date', '<=', $to);
        }

        $summary = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total_returns, COALESCE(SUM(quantity), 0) as total_qty, COALESCE(SUM(total_amount), 0) as total_amount')
            ->first();

        $dailySummary = (clone $baseQuery)
            ->selectRaw('return_date, COUNT(*) as total_returns, SUM(quantity) as total_qty, SUM(total_amount) as total_amount')
            ->groupBy('return_date')
            ->orderByDesc('return_date')
            ->get();

        $monthlySummary = (clone $baseQuery)
            ->selectRaw("DATE_FORMAT(return_date, '%Y-%m') as return_month, COUNT(*) as total_returns, SUM(quantity) as total_qty, SUM(total_amount) as total_amount")
            ->groupBy('return_month')
            ->orderByDesc('return_month')
            ->get();

        $productSummary = (clone $baseQuery)
            ->join('products', 'sale_returns.product_id', '=', 'products.id')
            ->selectRaw('products.id as product_id, products.name as product_name, COUNT(sale_returns.id) as total_returns, SUM(sale_returns.quantity) as total_qty, SUM(sale_returns.total_amount) as total_amount')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_amount')
            ->get();

        return view('sale_returns.report', compact('from', 'to', 'summary', 'dailySummary', 'monthlySummary', 'productSummary'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'quantity' => 'required|integer|min:1',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $sale = Sale::with(['product', 'items'])->lockForUpdate()->findOrFail($data['sale_id']);

            if ($sale->items->count() > 1) {
                throw ValidationException::withMessages([
                    'sale_id' => 'รายการขายนี้มีหลายสินค้า ยังไม่รองรับการคืนจากเอกสารนี้',
                ]);
            }

            $sourceProductId = $sale->items->count() === 1
                ? (int) $sale->items->first()->product_id
                : (int) $sale->product_id;

            $sourceQuantity = $sale->items->count() === 1
                ? (int) $sale->items->first()->quantity
                : (int) $sale->quantity;

            $sourceUnitPrice = $sale->items->count() === 1
                ? (float) $sale->items->first()->unit_price
                : (float) $sale->unit_price;

            $product = Product::lockForUpdate()->findOrFail($sourceProductId);
            $returnedQty = (int) SaleReturn::where('sale_id', $sale->id)->lockForUpdate()->sum('quantity');
            $returnable = $sourceQuantity - $returnedQty;

            if ($data['quantity'] > $returnable) {
                throw ValidationException::withMessages([
                    'quantity' => "จำนวนคืนเกินสิทธิ์คืนที่เหลือ ({$returnable})",
                ]);
            }

            $unitPrice = $sourceUnitPrice;

            SaleReturn::create([
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'product_id' => $sourceProductId,
                'quantity' => $data['quantity'],
                'unit_price' => $unitPrice,
                'total_amount' => $unitPrice * $data['quantity'],
                'return_date' => $data['return_date'],
                'status' => 'completed',
                'reason' => $data['reason'] ?? null,
            ]);

            $product->increment('stock', $data['quantity']);
        });

        return redirect()->route('sale-returns.index')->with('success', 'บันทึกการคืนสินค้าเรียบร้อยแล้ว');
    }

    public function edit(SaleReturn $saleReturn): View
    {
        $saleReturn->load(['sale', 'customer', 'product']);

        $returnedOther = (int) SaleReturn::where('sale_id', $saleReturn->sale_id)
            ->where('id', '!=', $saleReturn->id)
            ->sum('quantity');

        $returnableQuantity = max(0, $saleReturn->sale->quantity - $returnedOther);

        return view('sale_returns.edit', compact('saleReturn', 'returnableQuantity'));
    }

    public function update(Request $request, SaleReturn $saleReturn): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $saleReturn) {
            $saleReturn = SaleReturn::with('sale')->lockForUpdate()->findOrFail($saleReturn->id);
            $product = Product::lockForUpdate()->findOrFail($saleReturn->product_id);

            $returnedOther = (int) SaleReturn::where('sale_id', $saleReturn->sale_id)
                ->where('id', '!=', $saleReturn->id)
                ->lockForUpdate()
                ->sum('quantity');

            $maxAllowed = $saleReturn->sale->quantity - $returnedOther;
            if ($data['quantity'] > $maxAllowed) {
                throw ValidationException::withMessages([
                    'quantity' => "จำนวนคืนเกินสิทธิ์คืนที่เหลือ ({$maxAllowed})",
                ]);
            }

            $delta = $data['quantity'] - $saleReturn->quantity;
            if ($delta < 0 && $product->stock < abs($delta)) {
                throw ValidationException::withMessages([
                    'quantity' => 'ไม่สามารถลดจำนวนคืนได้ เพราะ stock ปัจจุบันไม่พอ',
                ]);
            }

            if ($delta > 0) {
                $product->increment('stock', $delta);
            } elseif ($delta < 0) {
                $product->decrement('stock', abs($delta));
            }

            $saleReturn->update([
                'quantity' => $data['quantity'],
                'total_amount' => $saleReturn->unit_price * $data['quantity'],
                'return_date' => $data['return_date'],
                'reason' => $data['reason'] ?? null,
            ]);
        });

        return redirect()->route('sale-returns.index')->with('success', 'อัปเดตการคืนสินค้าเรียบร้อยแล้ว');
    }

    public function destroy(SaleReturn $saleReturn): RedirectResponse
    {
        DB::transaction(function () use ($saleReturn) {
            $saleReturn = SaleReturn::lockForUpdate()->findOrFail($saleReturn->id);
            $product = Product::lockForUpdate()->findOrFail($saleReturn->product_id);

            if ($product->stock < $saleReturn->quantity) {
                throw ValidationException::withMessages([
                    'stock' => 'ไม่สามารถลบรายการคืนสินค้าได้ เพราะ stock ปัจจุบันน้อยกว่าจำนวนที่จะย้อนกลับ',
                ]);
            }

            $product->decrement('stock', $saleReturn->quantity);
            $saleReturn->delete();
        });

        return redirect()->route('sale-returns.index')->with('success', 'ลบรายการคืนสินค้าเรียบร้อยแล้ว');
    }
}
