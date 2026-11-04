<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OdooService;
use App\Services\SyncService;
use App\Models\UninvoicedShortTerm;
use App\Models\UninvoicedSoldOut;
use Illuminate\Support\Facades\Log;

class SyncUninvoicedRentals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-uninvoiced-rentals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically synchronize uninvoiced rentals (Long Term, Short Term, Sold Out) from Odoo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automated sync for all Uninvoiced categories...');
        $odoo = new OdooService();
        $syncService = new SyncService();

        // 1. Long Term / Subscription Rentals
        $this->info('Syncing Long Term / Subscription...');
        try {
            $soIds = $odoo->getUninvoicedSoIds();
            
            if (empty($soIds)) {
                $this->info('No uninvoiced long term rental periods found.');
            } else {
                $chunks = array_chunk($soIds, 500);
                $totalSaved = 0;
                
                foreach ($chunks as $index => $chunk) {
                    $isFirstChunk = ($index === 0);
                    $result = $odoo->fetchUninvoicedRentalsBySoIds($chunk);
                    if (!empty($result)) {
                        $totalSaved += $syncService->saveUninvoicedRentals($result, $isFirstChunk);
                    }
                }
                
                $this->info("Successfully synced {$totalSaved} long term uninvoiced rentals.");
                Log::info("Automated Uninvoiced Long Term Sync completed. Synced {$totalSaved} records.");
            }
        } catch (\Exception $e) {
            $this->error('Long Term sync failed: ' . $e->getMessage());
            Log::error('Automated Uninvoiced Long Term Sync failed: ' . $e->getMessage());
        }

        // 2. Short Term Rentals
        $this->info('Syncing Short Term Rentals...');
        try {
            $shortTermSoIds = $odoo->getUninvoicedShortTermSoIds();

            if (empty($shortTermSoIds)) {
                UninvoicedShortTerm::truncate();
                $this->info('No uninvoiced short term orders found. Table cleared.');
            } else {
                $chunks = array_chunk($shortTermSoIds, 100);
                $totalSaved = 0;

                foreach ($chunks as $index => $chunk) {
                    $isFirstChunk = ($index === 0);
                    $result = $odoo->fetchUninvoicedShortTermsBySoIds($chunk);
                    if (!empty($result)) {
                        $totalSaved += $syncService->saveUninvoicedShortTerms($result, $isFirstChunk);
                    }
                }

                $this->info("Successfully synced {$totalSaved} short term uninvoiced rentals.");
                Log::info("Automated Uninvoiced Short Term Sync completed. Synced {$totalSaved} records.");
            }
        } catch (\Exception $e) {
            $this->error('Short Term sync failed: ' . $e->getMessage());
            Log::error('Automated Uninvoiced Short Term Sync failed: ' . $e->getMessage());
        }

        // 3. Sold Out SOs
        $this->info('Syncing Sold Out Orders...');
        try {
            $soldOutSoIds = $odoo->getUninvoicedSoldOutSoIds();

            if (empty($soldOutSoIds)) {
                UninvoicedSoldOut::truncate();
                $this->info('No uninvoiced sold out orders found. Table cleared.');
            } else {
                $chunks = array_chunk($soldOutSoIds, 100);
                $totalSaved = 0;

                foreach ($chunks as $index => $chunk) {
                    $isFirstChunk = ($index === 0);
                    $result = $odoo->fetchUninvoicedSoldOutsBySoIds($chunk);
                    if (!empty($result)) {
                        $totalSaved += $syncService->saveUninvoicedSoldOuts($result, $isFirstChunk);
                    }
                }

                $this->info("Successfully synced {$totalSaved} sold out uninvoiced orders.");
                Log::info("Automated Uninvoiced Sold Out Sync completed. Synced {$totalSaved} records.");
            }
        } catch (\Exception $e) {
            $this->error('Sold Out sync failed: ' . $e->getMessage());
            Log::error('Automated Uninvoiced Sold Out Sync failed: ' . $e->getMessage());
        }

        $this->info('All Uninvoiced categories synced successfully.');
    }
}
