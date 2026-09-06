<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function edit(): View
    {
        $this->authorize('manage-administration');

        return view('admin.settings.edit', [
            'organization_name' => Setting::getValue('organization_name', config('app.name')),
            'allow_negative_balance' => Setting::allowsNegativeBalance(),
            'logo_path' => Setting::getValue('logo_path'),
        ]);
    }

    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        $avant = [
            'organization_name' => Setting::getValue('organization_name'),
            'allow_negative_balance' => Setting::getValue('allow_negative_balance'),
            'logo_path' => Setting::getValue('logo_path'),
        ];

        Setting::setValue('organization_name', $request->string('organization_name'));
        Setting::setValue('allow_negative_balance', $request->boolean('allow_negative_balance'));

        if ($request->hasFile('logo')) {
            $ancien = (string) Setting::getValue('logo_path', '');
            $path = $request->file('logo')->store('logos', 'public');
            Setting::setValue('logo_path', $path);

            if ($ancien !== '' && $ancien !== $path) {
                Storage::disk('public')->delete($ancien);
            }
        }

        if ($request->boolean('supprimer_logo') && ! $request->hasFile('logo')) {
            $ancien = (string) Setting::getValue('logo_path', '');
            if ($ancien !== '') {
                Storage::disk('public')->delete($ancien);
            }
            Setting::setValue('logo_path', '');
        }

        $this->audit->log('modification_parametres', null, $avant, [
            'organization_name' => $request->string('organization_name')->toString(),
            'allow_negative_balance' => $request->boolean('allow_negative_balance') ? '1' : '0',
            'logo_path' => Setting::getValue('logo_path'),
        ]);

        return back()->with('success', 'Paramètres enregistrés.');
    }
}
