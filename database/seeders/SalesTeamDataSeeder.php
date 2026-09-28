<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesTeamDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedSalesmen();
        $this->seedCampaigns();
        $this->seedCalls();
    }

    protected function seedSalesmen(): void
    {
        $salesmen = [
            ['name' => 'Niran Boonmee', 'email' => 'niran@example.com', 'phone' => '0611111111', 'position' => 'Senior Sales'],
            ['name' => 'Kritsada Pongsri', 'email' => 'kritsada@example.com', 'phone' => '0622222222', 'position' => 'Account Executive'],
            ['name' => 'Manee Chaiyaporn', 'email' => 'manee@example.com', 'phone' => '0633333333', 'position' => 'Business Development'],
            ['name' => 'Somchai Yimchai', 'email' => 'somchai@example.com', 'phone' => '0644444444', 'position' => 'Sales Manager'],
            ['name' => 'Pimchanok Raksakul', 'email' => 'pimchanok@example.com', 'phone' => '0655555555', 'position' => 'Inside Sales'],
            ['name' => 'Thanawat Suksri', 'email' => 'thanawat@example.com', 'phone' => '0666666666', 'position' => 'Territory Sales'],
            ['name' => 'Ornicha Boonsri', 'email' => 'ornicha@example.com', 'phone' => '0677777777', 'position' => 'Customer Success'],
            ['name' => 'Jakkrapong Muanmai', 'email' => 'jakkrapong@example.com', 'phone' => '0688888888', 'position' => 'Field Sales'],
            ['name' => 'Suwimol Jaroen', 'email' => 'suwimol@example.com', 'phone' => '0699999999', 'position' => 'Account Manager'],
            ['name' => 'Chalermchai Kraisorn', 'email' => 'chalermchai@example.com', 'phone' => '0600000000', 'position' => 'Sales Specialist'],
        ];

        foreach ($salesmen as $salesman) {
            DB::table('salesmen')->updateOrInsert(
                ['email' => $salesman['email']],
                [
                    'name' => $salesman['name'],
                    'phone' => $salesman['phone'],
                    'position' => $salesman['position'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    protected function seedCampaigns(): void
    {
        $campaigns = [
            ['name' => 'Summer Promotion', 'description' => 'Drive seasonal sales', 'start_date' => '2026-06-01', 'end_date' => '2026-06-30', 'status' => 'active'],
            ['name' => 'Back to School', 'description' => 'Target students and parents', 'start_date' => '2026-07-01', 'end_date' => '2026-08-15', 'status' => 'active'],
            ['name' => 'Holiday Offers', 'description' => 'Festive package promotion', 'start_date' => '2026-10-01', 'end_date' => '2026-12-31', 'status' => 'planned'],
            ['name' => 'Corporate Bundle', 'description' => 'B2B bulk sales campaign', 'start_date' => '2026-05-01', 'end_date' => '2026-05-31', 'status' => 'completed'],
            ['name' => 'New Customer Welcome', 'description' => 'Welcome campaign for new leads', 'start_date' => '2026-04-01', 'end_date' => '2026-04-30', 'status' => 'completed'],
            ['name' => 'Referral Program', 'description' => 'Incentivize referrals', 'start_date' => '2026-08-01', 'end_date' => '2026-09-30', 'status' => 'active'],
            ['name' => 'Product Launch', 'description' => 'Launch new product line', 'start_date' => '2026-09-01', 'end_date' => '2026-09-30', 'status' => 'planned'],
            ['name' => 'VIP Client Outreach', 'description' => 'Exclusive outreach for VIP clients', 'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'completed'],
            ['name' => 'Year-End Clearance', 'description' => 'Discount campaign', 'start_date' => '2026-11-01', 'end_date' => '2026-12-15', 'status' => 'planned'],
            ['name' => 'Loyalty Reward', 'description' => 'Reward repeat customers', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'active'],
        ];

        foreach ($campaigns as $campaign) {
            DB::table('campaigns')->updateOrInsert(
                ['name' => $campaign['name']],
                [
                    'description' => $campaign['description'],
                    'start_date' => $campaign['start_date'],
                    'end_date' => $campaign['end_date'],
                    'status' => $campaign['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    protected function seedCalls(): void
    {
        $customerIds = DB::table('customers')->pluck('id')->all();
        $salesmanIds = DB::table('salesmen')->pluck('id')->all();
        $campaignIds = DB::table('campaigns')->pluck('id')->all();

        if (empty($customerIds) || empty($salesmanIds) || empty($campaignIds)) {
            return;
        }

        for ($i = 1; $i <= 10; $i++) {
            DB::table('calls')->updateOrInsert(
                ['id' => $i],
                [
                    'customer_id' => $customerIds[($i - 1) % count($customerIds)],
                    'salesman_id' => $salesmanIds[($i - 1) % count($salesmanIds)],
                    'campaign_id' => $campaignIds[($i - 1) % count($campaignIds)],
                    'call_time' => now()->subDays(10 - $i)->format('Y-m-d H:i:s'),
                    'result' => ['completed', 'follow_up', 'no_answer', 'scheduled'][$i % 4],
                    'notes' => 'Sample call note ' . $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
