<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function customersIndex(Request $request): View
    {
        $query = Customer::query();
        $search = $request->input('search');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(10)->appends($request->only('search'));

        return view('customers.index', compact('customers', 'search'));
    }

    public function customersCreate(): View
    {
        return view('customers.create');
    }

    public function customersStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        Customer::create($data);

        return redirect()->route('customers.index')->with('success', 'เพิ่มลูกค้าเรียบร้อยแล้ว');
    }

    public function productsIndex(Request $request): View
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

        $products = $query->latest()->paginate(10)->appends($request->only('search'));

        return view('products.index', compact('products', 'search'));
    }

    public function productsCreate(): View
    {
        return view('products.create');
    }

    public function productsStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'เพิ่มสินค้าเรียบร้อยแล้ว');
    }

    public function salesIndex(Request $request): View
    {
        $query = Sale::with(['customer', 'product']);
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

        $sales = $query->latest()->paginate(10)->appends($request->only(['search', 'status']));

        return view('sales.index', compact('sales', 'search', 'status'));
    }

    public function salesCreate(): View
    {
        $customers = Customer::all();
        $products = Product::all();

        return view('sales.create', compact('customers', 'products'));
    }

    public function salesStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'sale_date' => 'required|date',
            'status' => 'nullable|string',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $data['unit_price'] = $product->price;
        $data['total_amount'] = $product->price * $data['quantity'];

        Sale::create($data);

        return redirect()->route('sales.index')->with('success', 'บันทึกการขายเรียบร้อยแล้ว');
    }

    public function salesmenIndex(Request $request): View
    {
        $query = User::query()->where('role', 'sale');
        $search = $request->input('search');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $salesmen = $query->latest()->paginate(10)->appends($request->only('search'));

        $supervisors = User::query()
            ->where('role', 'supervisor')
            ->orderBy('name')
            ->get();

        $totalTeams = $supervisors->count();
        $totalMembers = User::query()->where('role', 'sale')->count();
        $unassignedMembers = User::query()->where('role', 'sale')->count();

        return view('salesmen.index', compact('salesmen', 'search', 'supervisors', 'totalTeams', 'totalMembers', 'unassignedMembers'));
    }

    public function salesmenCreate(): View
    {
        $supervisors = User::query()->where('role', 'supervisor')->orderBy('name')->get();

        return view('salesmen.create', compact('supervisors'));
    }

    public function salesmenStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:100',
            'supervisor_id' => 'nullable|exists:users,id',
            'sales_target' => 'nullable|numeric|min:0',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt('secret1234'),
            'role' => 'sale',
        ]);

        if (!empty($data['supervisor_id'])) {
            SetupTeam::create([
                'supervisor_user_id' => $data['supervisor_id'],
                'member_user_id' => $user->id,
                'sales_target' => $data['sales_target'] ?? 0,
            ]);
        }

        return redirect()->route('salesmen.index')->with('success', 'เพิ่มพนักงานขายเรียบร้อยแล้ว');
    }

    public function salesmenTeamDetail($salesmanId): View
    {
        $supervisor = User::findOrFail($salesmanId);
        $teamMembers = SetupTeam::where('supervisor_user_id', $supervisor->id)
            ->with('member')
            ->get();
        $availableMembers = User::query()
            ->where('role', 'sale')
            ->where('id', '!=', $supervisor->id)
            ->whereDoesntHave('setupTeamsAsMember')
            ->orderBy('name')
            ->get();

        return view('salesmen.team-detail', compact('supervisor', 'teamMembers', 'availableMembers'));
    }

    public function salesmenTeamAssign(Request $request, $salesmanId): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => 'required|exists:users,id',
            'sales_target' => 'nullable|numeric|min:0',
        ]);

        $supervisor = User::findOrFail($salesmanId);
        $member = User::findOrFail($data['member_id']);

        SetupTeam::updateOrCreate(
            [
                'supervisor_user_id' => $supervisor->id,
                'member_user_id' => $member->id,
            ],
            [
                'sales_target' => $data['sales_target'] ?? 0,
            ]
        );

        return back()->with('success', 'เพิ่มพนักงานในทีมเรียบร้อยแล้ว');
    }

    public function salesmenTeamRemove(Request $request, $salesmanId): RedirectResponse
    {
        $supervisor = User::findOrFail($salesmanId);
        $memberId = $request->input('member_id');

        SetupTeam::where('supervisor_user_id', $supervisor->id)
            ->where('member_user_id', $memberId)
            ->delete();

        return back()->with('success', 'ลบพนักงานจากทีมเรียบร้อยแล้ว');
    }

    public function salesmenTeamUpdateTarget(Request $request, $salesmanId): RedirectResponse
    {
        $supervisor = User::findOrFail($salesmanId);
        $data = $request->validate([
            'member_id' => 'required|exists:users,id',
            'sales_target' => 'nullable|numeric|min:0',
        ]);

        SetupTeam::where('supervisor_user_id', $supervisor->id)
            ->where('member_user_id', $data['member_id'])
            ->update(['sales_target' => $data['sales_target']]);

        return back()->with('success', 'อัปเดตเป้าหมายยอดขายเรียบร้อยแล้ว');
    }

    public function campaignsIndex(Request $request): View
    {
        $query = Campaign::query();
        $search = $request->input('search');
        $status = $request->input('status');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $campaigns = $query->latest()->paginate(10)->appends($request->only(['search', 'status']));

        return view('campaigns.index', compact('campaigns', 'search', 'status'));
    }

    public function campaignsCreate(): View
    {
        return view('campaigns.create');
    }

    public function campaignsStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|string|max:50',
        ]);

        Campaign::create($data);

        return redirect()->route('campaigns.index')->with('success', 'สร้างแคมเปญเรียบร้อยแล้ว');
    }

    public function callsIndex(Request $request): View
    {
        $query = Call::with(['customer', 'salesman', 'campaign']);
        $search = $request->input('search');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhereHas('salesman', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhereHas('campaign', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhere('result', 'like', "%{$search}%");
            });
        }

        $calls = $query->latest()->paginate(10)->appends($request->only('search'));

        return view('calls.index', compact('calls', 'search'));
    }

    public function callsCreate(): View
    {
        $customers = Customer::all();
        $salesmen = User::query()->where('role', 'sale')->get();
        $campaigns = Campaign::all();

        return view('calls.create', compact('customers', 'salesmen', 'campaigns'));
    }

    public function callsStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'salesman_id' => 'required|exists:users,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'call_time' => 'required|date',
            'result' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        Call::create($data);

        return redirect()->route('calls.index')->with('success', 'บันทึกการโทรเรียบร้อยแล้ว');
    }
}
