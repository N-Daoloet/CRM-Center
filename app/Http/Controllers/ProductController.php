<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query();
        $search = $request->input('search');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->with('images')->latest()->get();

        return view('products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        $nextSku = $this->generateNextSku();

        return view('products.create', compact('nextSku'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'real_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
            'low_stock_threshold' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
            'description' => 'nullable|string',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:2048',
            'image' => 'nullable|image|max:2048',
        ]);

        $data['sku'] = $this->generateNextSku();

        $data['price'] = $data['sale_price'];

        unset($data['images'], $data['image']);

        $product = Product::create($data);
        $this->saveProductImages($request, $product);

        return redirect()->route('products.index')->with('success', 'เพิ่มสินค้าเรียบร้อยแล้ว');
    }

    public function edit(Product $product): View
    {
        $product->load('images');

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'real_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'stock_in' => 'nullable|integer|min:0',
            'stock_out' => 'nullable|integer|min:0',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:2048',
            'image' => 'nullable|image|max:2048',
        ]);

        $stockIn = (int) ($data['stock_in'] ?? 0);
        $stockOut = (int) ($data['stock_out'] ?? 0);
        $newStock = $product->stock + $stockIn - $stockOut;

        if ($newStock < 0) {
            throw ValidationException::withMessages([
                'stock_out' => 'จำนวนตัดสต็อกมากกว่าสินค้าคงเหลือ',
            ]);
        }

        $updateData = [
            'name' => $data['name'],
            'real_price' => $data['real_price'],
            'sale_price' => $data['sale_price'],
            'price' => $data['sale_price'],
            'low_stock_threshold' => $data['low_stock_threshold'],
            'is_active' => $data['is_active'],
            'description' => $data['description'] ?? null,
            'stock' => $newStock,
        ];

        $product->update($updateData);
        $this->saveProductImages($request, $product);

        return redirect()->route('products.index')->with('success', 'อัปเดตสินค้าเรียบร้อยแล้ว');
    }

    private function uploadProductImage(UploadedFile $image): string
    {
        $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $destination = public_path('uploads/products');

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $image->move($destination, $fileName);

        return 'uploads/products/' . $fileName;
    }

    private function saveProductImages(Request $request, Product $product): void
    {
        $images = $request->file('images', []);
        $images = is_array($images) ? $images : [];
        if ($request->hasFile('image')) {
            $images[] = $request->file('image');
        }

        foreach ($images as $image) {
            if (!$image instanceof UploadedFile) {
                continue;
            }

            $path = $this->uploadProductImage($image);
            $product->images()->create(['image_path' => $path]);

            if (!$product->image_path) {
                $product->update(['image_path' => $path]);
            }
        }
    }

    private function generateNextSku(): string
    {
        $prefix = 'PRD';
        $latest = Product::query()
            ->where('sku', 'like', $prefix . '%')
            ->latest('id')
            ->value('sku');

        $nextNumber = 1;
        if ($latest && preg_match('/^' . $prefix . '(\d+)$/', $latest, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        do {
            $candidate = $prefix . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (Product::query()->where('sku', $candidate)->exists());

        return $candidate;
    }
}
