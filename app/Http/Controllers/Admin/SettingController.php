<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Log\Logger;
use Illuminate\View\View;

use function Illuminate\Log\log;

class SettingController extends Controller
{
    public function __construct(
        private SettingsService $settingsService
    ) {}
    
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    { 
        $user = auth()->user();
        $settings = Setting::orderBy('group')
                ->orderBy('key')
                ->paginate(20);
           
        $groups = Setting::distinct('group')
                ->pluck('group')
                ->sort();
 
        return view('admin.settings.index', compact('user', 'settings', 'groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $groups = Setting::distinct('group')->pluck('group')->sort();
        return view('admin.settings.create', compact('groups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if(!auth()->user()->isSuperAdmin()){
            return back()->with('error', 'You do not have permission to create setttings');
        }
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings,key',
            'value' => 'nullable',
            'type' => 'required|in:string,boolean,integer,json,file',
            'group' => 'required|string|max:255',
            'is_public' => 'boolean',
            'is_encrypted' => 'boolean',
            'description' => 'nullable|string|max:1000'
        ]);
        
        Setting::create($validated);
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Setting $setting): View
    {
        return view('admin.settings.show', compact('setting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Setting $setting): View
    {
        $groups = Setting::distinct('group')->pluck('group')->sort();
        return view('admin.settings.edit', compact('setting', 'groups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Setting $setting): RedirectResponse
    {
        if(!auth()->user()->isSuperAdmin()){
            return back()->with('error', 'You do not have permission to update record');
        }

        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings,key,' . $setting->id,
            'value' => 'nullable',
            'type' => 'required|in:string,boolean,integer,json,file',
            'group' => 'required|string|max:255',
            'is_public' => 'boolean',
            'is_encrypted' => 'boolean',
            'description' => 'nullable|string|max:1000'
        ]);
        
        $setting->update($validated);
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Setting $setting): RedirectResponse
    {
        if(!auth()->user()->isSuperAdmin()){
            return back()->with('error', 'You do not have permission to delete this record');
        }
        $setting->delete();
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting deleted successfully.');
    }
    
    /**
     * Quick update multiple settings
     */
    public function quickUpdate(Request $request): RedirectResponse
    {
        $data = $request->except(['_token', '_method']);
        
        foreach ($data as $key => $value) {
            $this->settingsService->set($key, $value);
        }
        
        return back()->with('success', 'Settings updated successfully.');
    }
    
    /**
     * Clear all settings cache
     */
    public function clearCache(): RedirectResponse
    {
        $this->settingsService->flush();
        
        return back()->with('success', 'Settings cache cleared successfully.');
    }
    
    /**
     * Export settings as JSON
     */
    public function export()
    {
        $settings = Setting::all()->toArray();
        
        return response()->json($settings, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="settings-export.json"'
        ]);
    }
}
