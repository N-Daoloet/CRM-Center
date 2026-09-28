<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedProvinces();
        $this->seedDistricts();
        $this->seedSubDistricts();
    }

    protected function seedProvinces(): void
    {
        $path = base_path('public/data/provinces.json');
        if (! file_exists($path)) {
            return;
        }

        $records = [];
        foreach (json_decode(file_get_contents($path), true) as $row) {
            $records[] = [
                'id' => (int) $row['id'],
                'name_th' => $row['name_th'],
                'name_en' => $row['name_en'],
                'geography_id' => isset($row['geography_id']) ? (int) $row['geography_id'] : null,
                'created_at' => $this->toTimestamp($row['created_at'] ?? null),
                'updated_at' => $this->toTimestamp($row['updated_at'] ?? null),
                'deleted_at' => $this->toTimestamp($row['deleted_at'] ?? null),
            ];

            if (count($records) >= 500) {
                DB::table('provinces')->upsert($records, ['id']);
                $records = [];
            }
        }

        if ($records) {
            DB::table('provinces')->upsert($records, ['id']);
        }
    }

    protected function seedDistricts(): void
    {
        $path = base_path('public/data/districts.json');
        if (! file_exists($path)) {
            return;
        }

        $records = [];
        foreach (json_decode(file_get_contents($path), true) as $row) {
            $records[] = [
                'id' => (int) $row['id'],
                'name_th' => $row['name_th'],
                'name_en' => $row['name_en'],
                'province_id' => isset($row['province_id']) ? (int) $row['province_id'] : null,
                'created_at' => $this->toTimestamp($row['created_at'] ?? null),
                'updated_at' => $this->toTimestamp($row['updated_at'] ?? null),
                'deleted_at' => $this->toTimestamp($row['deleted_at'] ?? null),
            ];

            if (count($records) >= 500) {
                DB::table('districts')->upsert($records, ['id']);
                $records = [];
            }
        }

        if ($records) {
            DB::table('districts')->upsert($records, ['id']);
        }
    }

    protected function seedSubDistricts(): void
    {
        $path = base_path('public/data/sub_districts.json');
        if (! file_exists($path)) {
            return;
        }

        $records = [];
        foreach (json_decode(file_get_contents($path), true) as $row) {
            $records[] = [
                'id' => (int) $row['id'],
                'zip_code' => isset($row['zip_code']) ? (int) $row['zip_code'] : null,
                'name_th' => $row['name_th'],
                'name_en' => $row['name_en'],
                'district_id' => isset($row['district_id']) ? (int) $row['district_id'] : null,
                'lat' => $row['lat'] ?? null,
                'long' => $row['long'] ?? null,
                'created_at' => $this->toTimestamp($row['created_at'] ?? null),
                'updated_at' => $this->toTimestamp($row['updated_at'] ?? null),
                'deleted_at' => $this->toTimestamp($row['deleted_at'] ?? null),
            ];

            if (count($records) >= 500) {
                DB::table('sub_districts')->upsert($records, ['id']);
                $records = [];
            }
        }

        if ($records) {
            DB::table('sub_districts')->upsert($records, ['id']);
        }
    }

    protected function toTimestamp(?string $value): ?string
    {
        if ($value === null || $value === '' || strtolower($value) === 'null') {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
