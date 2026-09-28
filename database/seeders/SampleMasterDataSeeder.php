<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleMasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedUsers();
        $this->seedCustomers();
        $this->seedProducts();
        $this->seedSales();
    }

    protected function seedUsers(): void
    {
        $users = [
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com'],
            ['name' => 'Carol Lee', 'email' => 'carol@example.com'],
            ['name' => 'David Kim', 'email' => 'david@example.com'],
            ['name' => 'Emma Brown', 'email' => 'emma@example.com'],
            ['name' => 'Frank Wilson', 'email' => 'frank@example.com'],
            ['name' => 'Grace Chen', 'email' => 'grace@example.com'],
            ['name' => 'Henry Patel', 'email' => 'henry@example.com'],
            ['name' => 'Ivy Nguyen', 'email' => 'ivy@example.com'],
            ['name' => 'Jack Garcia', 'email' => 'jack@example.com'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => bcrypt('password123'),
                ]
            );
        }
    }

    protected function seedCustomers(): void
    {
        $customers = [
            ['name' => 'Sompong Rattanakorn', 'email' => 'customer1@example.com', 'phone' => '0811111111', 'address' => 'Bangkok'],
            ['name' => 'Nattapong Saengsri', 'email' => 'customer2@example.com', 'phone' => '0822222222', 'address' => 'Chiang Mai'],
            ['name' => 'Kanya Nualsri', 'email' => 'customer3@example.com', 'phone' => '0833333333', 'address' => 'Phuket'],
            ['name' => 'Preecha Boonmee', 'email' => 'customer4@example.com', 'phone' => '0844444444', 'address' => 'Khon Kaen'],
            ['name' => 'Ratchanee Promchai', 'email' => 'customer5@example.com', 'phone' => '0855555555', 'address' => 'Pattaya'],
            ['name' => 'Chaiwat Siriwan', 'email' => 'customer6@example.com', 'phone' => '0866666666', 'address' => 'Nakhon Ratchasima'],
            ['name' => 'Mali Supaporn', 'email' => 'customer7@example.com', 'phone' => '0877777777', 'address' => 'Hua Hin'],
            ['name' => 'Anan Kittipong', 'email' => 'customer8@example.com', 'phone' => '0888888888', 'address' => 'Ayutthaya'],
            ['name' => 'Suda Wongsiri', 'email' => 'customer9@example.com', 'phone' => '0899999999', 'address' => 'Lampang'],
            ['name' => 'Tawan Yodchai', 'email' => 'customer10@example.com', 'phone' => '0800000000', 'address' => 'Songkhla'],
        ];

        foreach ($customers as $index => $customer) {
            DB::table('customers')->updateOrInsert(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'phone' => $customer['phone'],
                    'address' => $customer['address'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    protected function seedProducts(): void
    {
        $products = [
            ['name' => 'Laptop Pro 14', 'sku' => 'LP-001', 'price' => 39900, 'stock' => 15, 'description' => 'Business laptop'],
            ['name' => 'Wireless Mouse', 'sku' => 'WM-002', 'price' => 990, 'stock' => 50, 'description' => 'Ergonomic mouse'],
            ['name' => 'Mechanical Keyboard', 'sku' => 'MK-003', 'price' => 2490, 'stock' => 30, 'description' => 'RGB keyboard'],
            ['name' => '27" Monitor', 'sku' => 'MON-004', 'price' => 7990, 'stock' => 20, 'description' => '4K UHD monitor'],
            ['name' => 'USB-C Hub', 'sku' => 'HUB-005', 'price' => 1490, 'stock' => 40, 'description' => 'Multi-port hub'],
            ['name' => 'Webcam 1080p', 'sku' => 'CAM-006', 'price' => 1890, 'stock' => 25, 'description' => 'Full HD webcam'],
            ['name' => 'Portable SSD 1TB', 'sku' => 'SSD-007', 'price' => 3490, 'stock' => 18, 'description' => 'Fast storage'],
            ['name' => 'Desk Lamp', 'sku' => 'LMP-008', 'price' => 890, 'stock' => 35, 'description' => 'LED desk lamp'],
            ['name' => 'Noise Cancelling Headset', 'sku' => 'HST-009', 'price' => 2990, 'stock' => 22, 'description' => 'Audio headset'],
            ['name' => 'Smart Speaker', 'sku' => 'SPK-010', 'price' => 1590, 'stock' => 28, 'description' => 'Voice assistant speaker'],
        ];

        foreach ($products as $product) {
            DB::table('products')->updateOrInsert(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'description' => $product['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    protected function seedSales(): void
    {
        $customerIds = DB::table('customers')->pluck('id')->all();
        $productIds = DB::table('products')->pluck('id')->all();

        if (empty($customerIds) || empty($productIds)) {
            return;
        }

        $sales = [];
        for ($i = 1; $i <= 10; $i++) {
            $customerId = $customerIds[($i - 1) % count($customerIds)];
            $productId = $productIds[($i - 1) % count($productIds)];
            $quantity = 1 + ($i % 3);
            $unitPrice = DB::table('products')->where('id', $productId)->value('price');
            $sales[] = [
                'id' => $i,
                'customer_id' => $customerId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $unitPrice * $quantity,
                'sale_date' => now()->subDays(10 - $i)->toDateString(),
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($sales as $sale) {
            DB::table('sales')->updateOrInsert(['id' => $sale['id']], $sale);
        }
    }
}
