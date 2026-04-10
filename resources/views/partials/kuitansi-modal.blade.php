<!-- Kuitansi Settings Modal Fragment -->
@php
    $printLog = \App\Models\PrintLog::firstWhere('invoice_name', $invoice->name);
    $kuitansiLines = [];
    if ($printLog && $printLog->kuitansi_pembayaran) {
        $kuitansiLines = explode("\n", $printLog->kuitansi_pembayaran);
    }
    
    // Determine dynamic routes based on invoice prefix
    $printUrlHtmlStr = '#';
    $printUrlPdfStr = '#';
    if (str_starts_with($invoice->name, 'INVRS')) {
        $printUrlHtmlStr = route('invoice-rental.kuitansi-html', $invoice);
        $printUrlPdfStr  = route('invoice-rental.kuitansi-pdf', $invoice);
    } elseif (str_starts_with($invoice->name, 'INVDV')) {
        $printUrlHtmlStr = route('invoice-driver.kuitansi-html', $invoice);
        $printUrlPdfStr  = route('invoice-driver.kuitansi-pdf', $invoice);
    } elseif (str_starts_with($invoice->name, 'INVOT')) {
        $printUrlHtmlStr = route('invoice-other.kuitansi-html', $invoice);
        $printUrlPdfStr  = route('invoice-other.kuitansi-pdf', $invoice);
    } elseif (str_starts_with($invoice->name, 'INVCR')) {
        $printUrlHtmlStr = route('invoice-vehicle.kuitansi-html', $invoice);
        $printUrlPdfStr  = route('invoice-vehicle.kuitansi-pdf', $invoice);
    }
@endphp

<div x-data="{ 
    kuitansiModalOpen: false, 
    saveStatus: null, 
    printContract: false, 
    useOverride: false,
    async saveKuitansi() {
        this.saveStatus = 'saving';
        try {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('invoice_name', '{{ $invoice->name }}');
            
            // Collect all pembayaran inputs within this modal
            const inputs = this.$el.querySelectorAll('input[name^=\'pembayaran_\']');
            inputs.forEach(input => {
                formData.append(input.name, input.value);
            });

            const response = await fetch('{{ route('kuitansi.override.update') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                this.saveStatus = 'success';
                setTimeout(() => { this.saveStatus = null; }, 2500);
            } else {
                this.saveStatus = 'error';
                setTimeout(() => { this.saveStatus = null; }, 3000);
            }
        } catch (error) {
            this.saveStatus = 'error';
            setTimeout(() => { this.saveStatus = null; }, 3000);
        }
    }
}" class="{{ isset($isDropdown) && $isDropdown ? 'block w-full' : 'inline-flex items-center relative' }}">
    
    @if(isset($isMainButton) && $isMainButton)
        <button type="button" @click.prevent="kuitansiModalOpen = true" title="Print Kuitansi" class="text-slate-400 hover:text-amber-600 transition-colors flex items-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
        </button>
    @elseif(isset($isDropdown) && $isDropdown)
        <button type="button" @click="kuitansiModalOpen = true" class="flex items-center gap-2 px-3 py-1.5 w-full text-xs text-left text-slate-700 dark:text-slate-300 hover:bg-amber-50 dark:hover:bg-amber-900/20 hover:text-amber-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Print Kuitansi Options
        </button>
    @else
        <button type="button" @click="kuitansiModalOpen = true" class="px-3 py-1.5 bg-amber-500 text-white text-xs font-medium rounded-lg hover:bg-amber-600 transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Print Kuitansi
        </button>
    @endif

    <!-- Modal Background -->
    <div x-show="kuitansiModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <!-- Modal Panel -->
        <div class="relative font-sans whitespace-normal text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-2xl w-full mx-4 border border-slate-200 dark:border-slate-700 overflow-hidden">
            
            <!-- Notification Toast -->
            <div x-show="saveStatus" x-transition.opacity class="absolute top-4 left-1/2 -translate-x-1/2 z-[60] px-4 py-2 rounded-full shadow-lg flex items-center gap-2 text-sm font-semibold transition-all transform duration-300" 
                 :class="saveStatus === 'saving' ? 'bg-indigo-600 text-white shadow-indigo-600/30' : (saveStatus === 'success' ? 'bg-emerald-500 text-white shadow-emerald-500/30' : 'bg-red-500 text-white shadow-red-500/30')">
                <template x-if="saveStatus === 'saving'">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </template>
                <template x-if="saveStatus === 'success'">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </template>
                <span x-text="saveStatus === 'success' ? 'Settings Saved!' : (saveStatus === 'saving' ? 'Saving...' : 'Error!')"></span>
            </div>

            <div class="modal-content-wrapper">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Print Kuitansi ({{ $invoice->name }})</h3>
                    <button type="button" @click="kuitansiModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <!-- Settings Body -->
                <div class="px-6 py-5 space-y-6 max-h-[70vh] overflow-y-auto">
                    
                    <!-- Checkboxes -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex items-center space-x-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer select-none">
                            <input type="checkbox" x-model="printContract" class="w-5 h-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:bg-slate-800 dark:border-slate-600">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold">Print No Contract/PO</span>
                                <span class="text-[10px] text-slate-500">Show contract reference in kuitansi body</span>
                            </div>
                        </label>

                        <label class="flex items-center space-x-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer select-none">
                            <input type="checkbox" x-model="useOverride" class="w-5 h-5 rounded border-slate-300 text-amber-600 focus:ring-amber-500 dark:bg-slate-800 dark:border-slate-600">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold">Override Keterangan</span>
                                <span class="text-[10px] text-slate-500">Use custom description line(s)</span>
                            </div>
                        </label>
                    </div>

                    <!-- Override Area -->
                    <div x-show="useOverride" x-transition.opacity class="space-y-4">
                        <div class="bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200 text-xs px-4 py-3 rounded-md border border-amber-200 dark:border-amber-800">
                            <strong>Note:</strong> Custom lines below will only be used if "Override Keterangan" is checked. Max 4 lines, 110 chars each.
                        </div>

                        @for ($i = 1; $i <= 4; $i++)
                            <div>
                                <label class="block text-[10px] font-bold tracking-wider text-slate-400 uppercase mb-1">Row {{ $i }}</label>
                                <input type="text" name="pembayaran_{{ $i }}" 
                                       value="{{ $kuitansiLines[$i-1] ?? '' }}" 
                                       maxlength="110" 
                                       class="w-full text-xs font-mono px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all shadow-sm" 
                                       placeholder="Type custom value for row {{ $i }}...">
                            </div>
                        @endfor
                        
                        <div class="flex justify-end">
                            <button type="button" @click="saveKuitansi()" class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition-colors shadow-sm flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Save Custom Content
                            </button>
                        </div>
                    </div>

                    <div x-show="!useOverride" x-transition.opacity class="py-10 text-center opacity-40">
                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <p class="text-sm font-medium">Standard Keterangan will be used</p>
                    </div>
                </div>

                <!-- Footer Operations -->
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex flex-col md:flex-row items-center justify-between gap-4 shrink-0">
                    <div class="flex items-center gap-2">
                        <a :href="'{{ $printUrlPdfStr }}' + '{{ strpos($printUrlPdfStr, '?') !== false ? '&' : '?' }}' + 'show_contract=' + (printContract ? '1' : '0') + '&use_override=' + (useOverride ? '1' : '0')" 
                           target="_blank" 
                           class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-md active:scale-95 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Download PDF
                        </a>
                        <a :href="'{{ $printUrlHtmlStr }}' + '{{ strpos($printUrlHtmlStr, '?') !== false ? '&' : '?' }}' + 'show_contract=' + (printContract ? '1' : '0') + '&use_override=' + (useOverride ? '1' : '0')" 
                           target="_blank" 
                           class="px-5 py-2.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-bold rounded-xl hover:bg-slate-300 dark:hover:bg-slate-600 transition-all shadow-sm active:scale-95 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Print Browser
                        </a>
                    </div>

                    <button type="button" @click="kuitansiModalOpen = false" class="px-5 py-2.5 text-slate-500 hover:text-slate-800 dark:hover:text-slate-300 text-sm font-bold transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
