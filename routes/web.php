<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalesmanController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Salesman;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();
        $previousMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth();

        $currentMonthSales = (float) Sale::query()
            ->whereBetween('sale_date', [$currentMonthStart->toDateString(), $currentMonthEnd->toDateString()])
            ->sum('total_amount');

        $previousMonthSales = (float) Sale::query()
            ->whereBetween('sale_date', [$previousMonthStart->toDateString(), $previousMonthEnd->toDateString()])
            ->sum('total_amount');

        $salesChangePercent = $previousMonthSales > 0
            ? (($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100
            : ($currentMonthSales > 0 ? 100 : 0);

        $totalCustomers = Customer::count();
        $newCustomersThisMonth = Customer::query()
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->count();

        $totalSalesmen = Salesman::count();
        $activeCampaigns = Campaign::query()->where('status', 'active')->count();
        $preparingCampaigns = Campaign::query()->where('status', 'preparing')->count();

        $months = collect(range(5, 0))->map(function (int $offset) use ($now) {
            return $now->copy()->subMonthsNoOverflow($offset);
        })->push($now->copy());

        $monthlySalesRows = Sale::query()
            ->selectRaw('YEAR(sale_date) as year, MONTH(sale_date) as month, SUM(total_amount) as total')
            ->whereDate('sale_date', '>=', $months->first()->copy()->startOfMonth()->toDateString())
            ->groupByRaw('YEAR(sale_date), MONTH(sale_date)')
            ->get()
            ->keyBy(fn ($row) => sprintf('%04d-%02d', $row->year, $row->month));

        $thaiMonths = [
            1 => 'ม.ค.',
            2 => 'ก.พ.',
            3 => 'มี.ค.',
            4 => 'เม.ย.',
            5 => 'พ.ค.',
            6 => 'มิ.ย.',
            7 => 'ก.ค.',
            8 => 'ส.ค.',
            9 => 'ก.ย.',
            10 => 'ต.ค.',
            11 => 'พ.ย.',
            12 => 'ธ.ค.',
        ];

        $chartData = $months->map(function (Carbon $monthDate) use ($monthlySalesRows, $thaiMonths) {
            $key = $monthDate->format('Y-m');
            $total = (float) optional($monthlySalesRows->get($key))->total;

            return [
                'label' => $thaiMonths[(int) $monthDate->format('n')],
                'total' => $total,
            ];
        });

        $maxMonthlySales = max(1, (float) $chartData->max('total'));

        $chartData = $chartData->map(function (array $item) use ($maxMonthlySales) {
            $item['height'] = (int) round(($item['total'] / $maxMonthlySales) * 100);
            return $item;
        });

        return view('dashboard', [
            'totalSales' => $currentMonthSales,
            'salesChangePercent' => $salesChangePercent,
            'totalCustomers' => $totalCustomers,
            'newCustomersThisMonth' => $newCustomersThisMonth,
            'totalSalesmen' => $totalSalesmen,
            'activeCampaigns' => $activeCampaigns,
            'preparingCampaigns' => $preparingCampaigns,
            'chartData' => $chartData,
        ]);
    })->name('dashboard');

    Route::get('/api/provinces', [AddressController::class, 'provinces'])->name('api.provinces');
    Route::get('/api/districts', [AddressController::class, 'districts'])->name('api.districts');
    Route::get('/api/subdistricts', [AddressController::class, 'subdistricts'])->name('api.subdistricts');

Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
Route::get('/sales/overview', [SaleController::class, 'overview'])->name('sales.overview');
Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
Route::get('/sale-returns', [SaleReturnController::class, 'index'])->name('sale-returns.index');
Route::get('/sale-returns/create', [SaleReturnController::class, 'create'])->name('sale-returns.create');
Route::get('/sale-returns/report', [SaleReturnController::class, 'report'])->name('sale-returns.report');
Route::post('/sale-returns', [SaleReturnController::class, 'store'])->name('sale-returns.store');
Route::get('/sale-returns/{saleReturn}/edit', [SaleReturnController::class, 'edit'])->name('sale-returns.edit');
Route::put('/sale-returns/{saleReturn}', [SaleReturnController::class, 'update'])->name('sale-returns.update');
Route::delete('/sale-returns/{saleReturn}', [SaleReturnController::class, 'destroy'])->name('sale-returns.destroy');

Route::get('/sales/orders', [SaleController::class, 'ordersIndex'])->name('sales.orders');
Route::get('/sales/orders/data', [SaleController::class, 'ordersData'])->name('sales.orders.data');
Route::get('/sales/orders/create', [SaleController::class, 'create'])->name('sales.orders.create');
Route::get('/sales/orders/{sale}/edit', [SaleController::class, 'edit'])->name('sales.orders.edit');
Route::put('/sales/orders/{sale}', [SaleController::class, 'update'])->name('sales.orders.update');
Route::get('/sales/orders/{sale}/purchase-order', [SaleController::class, 'purchaseOrder'])->name('sales.orders.purchase-order');

Route::get('/sales/invoice', function () {
    return view('sales.invoice');
})->name('sales.invoice');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');

Route::get('/calls', [CallController::class, 'index'])->name('calls.index');
Route::get('/calls/create', [CallController::class, 'create'])->name('calls.create');
Route::post('/calls', [CallController::class, 'store'])->name('calls.store');

Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
Route::put('/campaigns/{campaign}/promotion', [CampaignController::class, 'updatePromotion'])->name('campaigns.updatePromotion');
Route::put('/campaigns/{campaign}/by-name', [CampaignController::class, 'updateByName'])->name('campaigns.updateByName');
Route::get('/campaigns/{campaign}/edit', [CampaignController::class, 'edit'])->name('campaigns.edit');
Route::put('/campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
Route::get('/campaigns/create-by-name', [CampaignController::class, 'createByName'])->name('campaigns.createByName');
Route::post('/campaigns/by-name', [CampaignController::class, 'storeByName'])->name('campaigns.storeByName');

Route::get('/salesmen', [SalesmanController::class, 'index'])->name('salesmen.index');
Route::get('/salesmen/create', [SalesmanController::class, 'create'])->name('salesmen.create');
Route::post('/salesmen', [SalesmanController::class, 'store'])->name('salesmen.store');
Route::get('/salesmen/{salesman}/team', [SalesmanController::class, 'teamDetail'])->name('salesmen.team.detail');
Route::post('/salesmen/{salesman}/team/assign', [SalesmanController::class, 'teamAssign'])->name('salesmen.team.assign');
Route::post('/salesmen/{salesman}/team/remove', [SalesmanController::class, 'teamRemove'])->name('salesmen.team.remove');
Route::post('/salesmen/{salesman}/team/target', [SalesmanController::class, 'teamUpdateTarget'])->name('salesmen.team.target');

    // Route::get('/campaigns/create-by-name', function () {
    //     return view('campaigns.create-by-name');
    // })->name('campaigns.createByName');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/supervisors/{supervisor}/team-target', [SettingsController::class, 'updateTeamTarget'])
        ->name('settings.supervisors.team-target');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');

    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');

    Route::get('/sales-dashboard', function () {
        return view('sales.dashboard');
    })->name('sales.dashboard');
// });
