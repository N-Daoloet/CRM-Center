<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Province;
use App\Models\SubDistrict;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function provinces(): JsonResponse
    {
        $provinces = Province::query()
            ->select('id', 'name_th')
            ->orderBy('name_th')
            ->get();

        return response()->json(
            $provinces->map(fn ($province) => [
                'id' => $province->id,
                'name' => $province->name_th,
            ])->values()
        );
    }

    public function districts(Request $request): JsonResponse
    {
        $query = District::query()->select('id', 'name_th', 'province_id');

        if ($request->filled('province_id')) {
            $query->where('province_id', $request->input('province_id'));
        }

        $districts = $query->orderBy('name_th')->get();

        return response()->json(
            $districts->map(fn ($district) => [
                'id' => $district->id,
                'name' => $district->name_th,
                'province_id' => $district->province_id,
            ])->values()
        );
    }

    public function subdistricts(Request $request): JsonResponse
    {
        $query = SubDistrict::query()->select('id', 'name_th', 'district_id', 'zip_code');

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->input('district_id'));
        }

        $subdistricts = $query->orderBy('name_th')->get();

        return response()->json(
            $subdistricts->map(fn ($subdistrict) => [
                'id' => $subdistrict->id,
                'name' => $subdistrict->name_th,
                'district_id' => $subdistrict->district_id,
                'zip_code' => $subdistrict->zip_code,
            ])->values()
        );
    }
}
