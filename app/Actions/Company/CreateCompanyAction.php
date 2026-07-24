<?php

namespace App\Actions\Company;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateCompanyAction
{
    public function execute(User $user, array $data, ?UploadedFile $logo = null): Tenant
    {
        return DB::transaction(function () use ($user, $data, $logo) {
            $logoPath = null;

            if ($logo) {
                $logoPath = $logo->store('logos', 'public');
            }

            $tenant = Tenant::create([
                'uuid' => (string) Str::uuid(),
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'rfc' => $data['rfc'] ?? null,
                'logo' => $logoPath,
                'status' => 'active',
            ]);

            Branch::create([
                'tenant_id' => $tenant->id,
                'name' => 'Sucursal Principal',
                'code' => 'MAIN',
                'is_active' => true,
            ]);

            $user->update(['tenant_id' => $tenant->id]);

            setPermissionsTeamId($tenant->id);
            $user->assignRole('admin');

            return $tenant;
        });
    }
}
