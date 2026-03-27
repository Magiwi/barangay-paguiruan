<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResidentSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $residents = User::where('role', 'resident')
            ->where('status', 'approved')
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
            })
            ->with('purokRelation:id,name')
            ->limit(10)
            ->get([
                'id',
                'first_name',
                'middle_name',
                'last_name',
                'age',
                'contact_number',
                'house_no',
                'street_name',
                'sitio_subdivision',
                'purok_id',
            ]);

        $results = $residents->map(function (User $u): array {
            $address = trim(implode(', ', array_filter([
                trim(implode(' ', array_filter([$u->house_no, $u->street_name]))),
                $u->sitio_subdivision,
                $u->purokRelation?->name,
            ])));

            return [
                'id' => $u->id,
                'full_name' => trim("{$u->first_name} {$u->middle_name} {$u->last_name}"),
                'first_name' => (string) ($u->first_name ?? ''),
                'middle_name' => (string) ($u->middle_name ?? ''),
                'last_name' => (string) ($u->last_name ?? ''),
                'age' => $u->age,
                'contact_number' => (string) ($u->contact_number ?? ''),
                'address' => $address,
                'purok_name' => $u->purokRelation?->name ?? '—',
            ];
        });

        return response()->json($results->values());
    }
}
