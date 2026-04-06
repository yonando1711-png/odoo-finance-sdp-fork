<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PrintHubService;

class PreferenceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $service = new PrintHubService();
        $printersData = $service->getPrinters();
        $printers = $printersData['success'] ? $printersData['printers'] : [];
        
        return view('profile.preferences', compact('user', 'printers'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $preferences = $user->preferences ?? [];
        $preferences['default_printer'] = $request->default_printer;
        
        $user->preferences = $preferences;
        $user->save();
        
        return back()->with('success', 'Preferences updated successfully.');
    }
}
