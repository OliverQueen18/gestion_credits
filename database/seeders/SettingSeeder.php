<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'organization_name' => config('portefeuille.organization_name'),
            'logo_path' => '',
            'allow_negative_balance' => '0',
            'credit_reference_prefix' => config('portefeuille.credit_reference_prefix'),
            'repayment_reference_prefix' => config('portefeuille.repayment_reference_prefix'),
        ];

        foreach ($settings as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
