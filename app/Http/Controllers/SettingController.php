<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'show_dashboard' => Setting::get('show_dashboard', '1'),
        ];
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        Setting::set('show_dashboard', $request->has('show_dashboard') ? '1' : '0');
        return back()->with('success', 'Settings updated successfully.');
    }
}
