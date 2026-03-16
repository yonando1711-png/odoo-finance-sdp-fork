@extends('layouts.app')

@section('title', 'Application Settings')
@section('subtitle', 'Configure application behavior')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <form action="{{ route('settings.update') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <div class="space-y-4">
                <h3 class="text-lg font-semibold border-b border-slate-200 dark:border-slate-700 pb-2">General Settings</h3>
                
                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-100 dark:border-slate-800">
                    <div>
                        <p class="font-medium">Show Dashboard</p>
                        <p class="text-xs text-slate-500 mt-1">When enabled, the dashboard will be shown as the landing page. If disabled, you'll be redirected to the Import page.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="show_dashboard" value="1" {{ $settings['show_dashboard'] === '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 dark:peer-focus:ring-emerald-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-600"></div>
                    </label>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
