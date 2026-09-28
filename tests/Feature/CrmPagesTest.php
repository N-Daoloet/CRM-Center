<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CrmPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_crm_pages_are_available(): void
    {
        $this->actingAs(User::factory()->create());

        $pages = ['/customers', '/products', '/sales', '/campaigns', '/calls', '/salesmen'];

        foreach ($pages as $page) {
            $response = $this->get($page);
            $response->assertStatus(200);
        }
    }

    public function test_customer_search_filters_results(): void
    {
        $this->actingAs(User::factory()->create());

        Customer::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'phone' => '0810000001',
            'address' => 'Bangkok',
        ]);

        Customer::create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'phone' => '0810000002',
            'address' => 'Chiang Mai',
        ]);

        $response = $this->get('/customers?search=Alice');

        $response->assertStatus(200);
        $response->assertSee('Alice Johnson');
        $response->assertDontSee('Bob Smith');
    }

    public function test_customer_create_page_has_step_by_step_address_fields(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/customers/create');

        $response->assertStatus(200);
        $response->assertSee('provinceSelect');
        $response->assertSee('districtSelect');
        $response->assertSee('subdistrictSelect');
    }

    public function test_customer_store_saves_flag_and_channel(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post('/customers', [
            'name' => 'Charlie Brown',
            'email' => 'charlie@example.com',
            'phone' => '0812345678',
            'flag' => '1',
            'channel' => '2',
            'address' => '123/4 Test Road',
        ]);

        $response->assertRedirect('/customers');
        $this->assertDatabaseHas('customers', [
            'email' => 'charlie@example.com',
            'flag' => '1',
            'channel' => '2',
        ]);
    }

    public function test_customer_edit_page_and_update_work(): void
    {
        $this->actingAs(User::factory()->create());

        $customer = Customer::create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'phone' => '0811111111',
            'flag' => 1,
            'channel' => 1,
            'address' => 'Old address',
        ]);

        $response = $this->get('/customers/' . $customer->id . '/edit');
        $response->assertStatus(200);

        $updateResponse = $this->put('/customers/' . $customer->id, [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '0822222222',
            'flag' => 2,
            'channel' => 2,
            'address' => 'New address',
            'status' => 'inactive',
        ]);

        $updateResponse->assertRedirect('/customers');
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'flag' => 2,
            'channel' => 2,
        ]);
    }

    public function test_salesmen_create_page_is_available(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/salesmen/create');

        $response->assertStatus(200);
    }

    public function test_campaign_by_name_can_store_selected_customers(): void
    {
        $this->actingAs(User::factory()->create());

        $customerA = Customer::create([
            'name' => 'Lucy New',
            'email' => 'lucy@example.com',
            'phone' => '0810010001',
            'address' => 'Bangkok',
        ]);

        $customerB = Customer::create([
            'name' => 'Mina Big Buyer',
            'email' => 'mina@example.com',
            'phone' => '0810010002',
            'address' => 'Chiang Mai',
        ]);

        $response = $this->post('/campaigns/by-name', [
            'name' => 'ลูกค้า VIP',
            'campaign_type' => 'รายชื่อ',
            'start_date' => '2026-08-15',
            'end_date' => '2026-08-31',
            'status' => 'active',
            'description' => 'ลูกค้ารายชื่อ',
            'customers' => [
                ['customer_id' => $customerA->id],
                ['customer_id' => $customerB->id],
            ],
        ]);

        $response->assertRedirect('/campaigns');

        $campaign = Campaign::where('name', 'ลูกค้า VIP')->firstOrFail();
        $this->assertSame('รายชื่อ', $campaign->campaign_type);

        $this->assertDatabaseHas('campaign_customer', [
            'campaign_id' => $campaign->id,
            'customer_id' => $customerA->id,
        ]);

        $this->assertDatabaseHas('campaign_customer', [
            'campaign_id' => $campaign->id,
            'customer_id' => $customerB->id,
        ]);
    }

    public function test_campaign_can_store_products_with_special_prices(): void
    {
        $this->actingAs(User::factory()->create());

        $productA = Product::create([
            'name' => 'สินค้า A',
            'sku' => 'PRD000001',
            'price' => 1200,
            'real_price' => 1200,
            'sale_price' => 1200,
            'stock' => 10,
            'low_stock_threshold' => 2,
            'description' => 'สินค้า A',
        ]);

        $productB = Product::create([
            'name' => 'สินค้า B',
            'sku' => 'PRD000002',
            'price' => 850,
            'real_price' => 850,
            'sale_price' => 850,
            'stock' => 8,
            'low_stock_threshold' => 2,
            'description' => 'สินค้า B',
        ]);

        $response = $this->post('/campaigns', [
            'name' => 'โปรโมชันปลายปี',
            'description' => 'ลดราคาสินค้าแคมเปญ',
            'start_date' => '2026-08-15',
            'end_date' => '2026-08-31',
            'status' => 'active',
            'pricing_type' => 'percent',
            'pricing_value' => 20,
            'products' => [
                ['product_id' => $productA->id, 'special_price' => 990],
                ['product_id' => $productB->id, 'special_price' => 650],
            ],
        ]);

        $response->assertRedirect('/campaigns');

        $campaign = Campaign::where('name', 'โปรโมชันปลายปี')->firstOrFail();
        $this->assertSame('โปรโมชั่นสินค้า', $campaign->campaign_type);
        $this->assertSame('percent', $campaign->pricing_type);
        $this->assertEquals(20, $campaign->pricing_value);
        $this->assertEquals(1640, $campaign->discounted_total);

        $this->assertDatabaseHas('campaign_product', [
            'campaign_id' => $campaign->id,
            'product_id' => $productA->id,
            'special_price' => '990.00',
        ]);

        $this->assertDatabaseHas('campaign_product', [
            'campaign_id' => $campaign->id,
            'product_id' => $productB->id,
            'special_price' => '650.00',
        ]);
    }

    public function test_product_promotion_has_a_separate_edit_flow(): void
    {
        $this->actingAs(User::factory()->create());

        $productA = Product::create([
            'name' => 'สินค้าเดิม',
            'sku' => 'EDIT000001',
            'price' => 1000,
            'real_price' => 1000,
            'sale_price' => 1000,
            'stock' => 10,
            'low_stock_threshold' => 2,
            'description' => 'สินค้าเดิม',
        ]);
        $productB = Product::create([
            'name' => 'สินค้าใหม่',
            'sku' => 'EDIT000002',
            'price' => 800,
            'real_price' => 800,
            'sale_price' => 800,
            'stock' => 10,
            'low_stock_threshold' => 2,
            'description' => 'สินค้าใหม่',
        ]);
        $campaign = Campaign::create([
            'name' => 'แคมเปญเดิม',
            'campaign_type' => 'โปรโมชั่นสินค้า',
            'description' => 'รายละเอียดเดิม',
            'start_date' => '2026-08-15',
            'end_date' => '2026-08-31',
            'status' => 'active',
        ]);
        $campaign->products()->attach($productA->id, ['special_price' => 900]);

        $this->get(route('campaigns.edit', $campaign))
            ->assertOk()
            ->assertSee('แก้ไขโปรโมชั่นสินค้า')
            ->assertSee('โปรโมชั่นสินค้า')
            ->assertSee('EDIT000001')
            ->assertSee('"product_id":1', false)
            ->assertSee('name="pricing_value" type="number" min="0" step="0.01" value="10"', false)
            ->assertDontSee('ลูกค้าเป้าหมาย');

        $response = $this->put(route('campaigns.updatePromotion', $campaign), [
            'name' => 'แคมเปญแก้ไขแล้ว',
            'description' => 'รายละเอียดที่แก้ไข',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'status' => 'planned',
            'pricing_type' => 'fixed',
            'pricing_value' => 1250,
            'products' => [
                ['product_id' => $productB->id, 'special_price' => 650],
            ],
        ]);

        $response->assertRedirect('/campaigns');
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => 'แคมเปญแก้ไขแล้ว',
            'campaign_type' => 'โปรโมชั่นสินค้า',
            'description' => 'รายละเอียดที่แก้ไข',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'status' => 'planned',
            'pricing_type' => 'fixed',
            'pricing_value' => '1250.00',
            'discounted_total' => '1250.00',
        ]);
        $this->assertDatabaseHas('campaign_product', [
            'campaign_id' => $campaign->id,
            'product_id' => $productB->id,
            'special_price' => '650.00',
        ]);
        $this->assertDatabaseMissing('campaign_product', [
            'campaign_id' => $campaign->id,
            'product_id' => $productA->id,
        ]);

        $this->assertDatabaseCount('campaign_customer', 0);
    }

    public function test_name_campaign_has_a_separate_edit_flow(): void
    {
        $this->actingAs(User::factory()->create());

        $customerA = Customer::create([
            'name' => 'ลูกค้าเดิม',
            'email' => 'old-campaign-customer@example.com',
            'phone' => '0810011001',
            'address' => 'Bangkok',
        ]);
        $customerB = Customer::create([
            'name' => 'ลูกค้าใหม่',
            'email' => 'new-campaign-customer@example.com',
            'phone' => '0810011002',
            'address' => 'Chiang Mai',
        ]);
        $campaign = Campaign::create([
            'name' => 'แคมเปญรายชื่อเดิม',
            'campaign_type' => 'รายชื่อ',
            'description' => 'รายละเอียดเดิม',
            'start_date' => '2026-08-15',
            'end_date' => '2026-08-31',
            'status' => 'active',
        ]);
        $campaign->customers()->attach($customerA->id);

        $this->get(route('campaigns.edit', $campaign))
            ->assertOk()
            ->assertSee('แก้ไขแคมเปญรายชื่อ')
            ->assertSee('ลูกค้าเดิม')
            ->assertDontSee('สินค้าในโปรโมชั่น');

        $response = $this->put(route('campaigns.updateByName', $campaign), [
            'name' => 'แคมเปญรายชื่อแก้ไขแล้ว',
            'description' => 'ปรับรายละเอียดรายชื่อ',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'status' => 'active',
            'customer_ids' => [$customerB->id],
        ]);

        $response->assertRedirect('/campaigns');
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => 'แคมเปญรายชื่อแก้ไขแล้ว',
            'campaign_type' => 'รายชื่อ',
        ]);
        $this->assertDatabaseHas('campaign_customer', [
            'campaign_id' => $campaign->id,
            'customer_id' => $customerB->id,
        ]);
        $this->assertDatabaseMissing('campaign_customer', [
            'campaign_id' => $campaign->id,
            'customer_id' => $customerA->id,
        ]);
    }
}
