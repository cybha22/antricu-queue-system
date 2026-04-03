<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::paginate(10);
        return response()->json($settings);
    }

    public function update(Request $request, Setting $setting)
    {
        $request->validate([
            'institution_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:2048',
        ]);

        $data = $request->only('institution_name', 'address', 'phone');

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $setting->update($data);

        return response()->json($setting);
    }
}
