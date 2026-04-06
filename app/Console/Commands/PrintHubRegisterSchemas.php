<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PrintHubService;
use App\Models\JournalEntry;
use App\Models\InvoiceRental;
use App\Models\InvoiceDriver;
use App\Models\InvoiceOther;
use App\Models\InvoiceVehicle;

class PrintHubRegisterSchemas extends Command
{
    protected $signature = 'printhub:register-schemas';
    protected $description = 'Register data schemas with Print Hub for template design';

    public function handle(PrintHubService $service)
    {
        $this->info('Starting schema registration with Print Hub...');

        $this->registerJournalSchema($service);
        $this->registerRentalSchema($service);
        $this->registerDriverSchema($service);
        $this->registerOtherSchema($service);
        $this->registerVehicleSchema($service);

        $this->info('Schema registration complete!');
        return 0;
    }

    protected function registerJournalSchema(PrintHubService $service)
    {
        $this->line('  - Registering: journal_entry');
        
        $fields = [
            'move_name'           => ['type' => 'string', 'label' => 'Voucher Number'],
            'date'                => ['type' => 'date',   'label' => 'Date'],
            'partner_name'        => ['type' => 'string', 'label' => 'Partner Name'],
            'ref'                 => ['type' => 'string', 'label' => 'Reference'],
            'journal_name'        => ['type' => 'string', 'label' => 'Journal'],
            'amount_total_signed' => ['type' => 'number', 'label' => 'Total Amount'],
        ];

        $tables = [
            'lines' => [
                'label' => 'Journal Lines',
                'columns' => [
                    'account_code' => ['label' => 'Account Code'],
                    'account_name' => ['label' => 'Account Name'],
                    'debit'        => ['label' => 'Debit'],
                    'credit'       => ['label' => 'Credit'],
                    'display_name' => ['label' => 'Label'],
                ]
            ]
        ];

        $sample = JournalEntry::with('lines')->latest()->first();
        $sampleData = $sample ? $this->prepareSample($sample) : null;

        $service->registerSchema('journal_entry', [
            'label'       => 'Journal Voucher (SDP)',
            'fields'      => $fields,
            'tables'      => $tables,
            'sample_data' => $sampleData
        ]);
    }

    protected function registerRentalSchema(PrintHubService $service)
    {
        $this->line('  - Registering: invoice_rental');

        $fields = [
            'name'                     => ['type' => 'string', 'label' => 'Invoice Number'],
            'invoice_date'             => ['type' => 'date',   'label' => 'Invoice Date'],
            'partner_name'             => ['type' => 'string', 'label' => 'Customer Name'],
            'ref'                      => ['type' => 'string', 'label' => 'PO/Reference'],
            'amount_untaxed'           => ['type' => 'number', 'label' => 'Untaxed Amount'],
            'amount_tax'               => ['type' => 'number', 'label' => 'Tax Amount'],
            'amount_total'             => ['type' => 'number', 'label' => 'Total Amount'],
            'bc_manager'               => ['type' => 'string', 'label' => 'BC Manager'],
            'bc_spv'                   => ['type' => 'string', 'label' => 'BC Supervisor'],
            'partner_address_complete' => ['type' => 'string', 'label' => 'Full Address'],
            'narration'                => ['type' => 'string', 'label' => 'Notes'],
        ];

        $tables = [
            'lines' => [
                'label' => 'Invoice Items',
                'columns' => [
                    'description'   => ['label' => 'Description'],
                    'serial_number' => ['label' => 'Serial Number'],
                    'actual_start'  => ['label' => 'Start Date'],
                    'actual_end'    => ['label' => 'End Date'],
                    'quantity'      => ['label' => 'Qty'],
                    'uom'           => ['label' => 'UoM'],
                    'price_unit'    => ['label' => 'Unit Price'],
                ]
            ]
        ];

        $sample = InvoiceRental::with('lines')->latest()->first();
        $sampleData = $sample ? $this->prepareSample($sample) : null;

        $service->registerSchema('invoice_rental', [
            'label'       => 'Rental Invoice (SDP)',
            'fields'      => $fields,
            'tables'      => $tables,
            'sample_data' => $sampleData
        ]);
    }

    protected function registerDriverSchema(PrintHubService $service)
    {
        $this->line('  - Registering: invoice_driver');

        $fields = [
            'name'           => ['type' => 'string', 'label' => 'Invoice Number'],
            'invoice_date'   => ['type' => 'date',   'label' => 'Invoice Date'],
            'partner_name'   => ['type' => 'string', 'label' => 'Customer Name'],
            'ref'            => ['type' => 'string', 'label' => 'Reference'],
            'amount_total'   => ['type' => 'number', 'label' => 'Total Amount'],
            'manager_name'   => ['type' => 'string', 'label' => 'Manager'],
            'spv_name'       => ['type' => 'string', 'label' => 'Supervisor'],
        ];

        $tables = [
            'lines' => [
                'label' => 'Invoice Lines',
                'columns' => [
                    'description' => ['label' => 'Description'],
                    'quantity'    => ['label' => 'Qty'],
                    'price_unit'  => ['label' => 'Unit Price'],
                ]
            ]
        ];

        $sample = InvoiceDriver::with('lines')->latest()->first();
        $sampleData = $sample ? $this->prepareSample($sample) : null;

        $service->registerSchema('invoice_driver', [
            'label'       => 'Driver Invoice (SDP)',
            'fields'      => $fields,
            'tables'      => $tables,
            'sample_data' => $sampleData
        ]);
    }

    protected function registerOtherSchema(PrintHubService $service)
    {
        $this->line('  - Registering: invoice_other');
        $sample = InvoiceOther::with('lines')->latest()->first();
        $sampleData = $sample ? $this->prepareSample($sample) : null;

        $service->registerSchema('invoice_other', [
            'label'  => 'Other Invoice (SDP)',
            'fields' => [
                'name'         => ['type' => 'string', 'label' => 'Invoice Number'],
                'invoice_date' => ['type' => 'date',   'label' => 'Invoice Date'],
                'partner_name' => ['type' => 'string', 'label' => 'Customer Name'],
                'amount_total' => ['type' => 'number', 'label' => 'Total Amount'],
            ],
            'tables' => [
                'lines' => [
                    'label' => 'Lines',
                    'columns' => [
                        'description' => ['label' => 'Description'],
                        'quantity'    => ['label' => 'Qty'],
                    ]
                ]
            ],
            'sample_data' => $sampleData
        ]);
    }

    protected function registerVehicleSchema(PrintHubService $service)
    {
        $this->line('  - Registering: invoice_vehicle');
        $sample = InvoiceVehicle::with('lines')->latest()->first();
        $sampleData = $sample ? $this->prepareSample($sample) : null;

        $service->registerSchema('invoice_vehicle', [
            'label'  => 'Vehicle Sales (SDP)',
            'fields' => [
                'name'         => ['type' => 'string', 'label' => 'Invoice Number'],
                'invoice_date' => ['type' => 'date',   'label' => 'Invoice Date'],
                'partner_name' => ['type' => 'string', 'label' => 'Customer Name'],
            ],
            'tables' => [
                'lines' => [
                    'label' => 'Lines',
                    'columns' => [
                        'description' => ['label' => 'Description'],
                    ]
                ]
            ],
            'sample_data' => $sampleData
        ]);
    }

    protected function prepareSample($model)
    {
        $data = $model->toArray();
        // Flatten some dates
        foreach (['date', 'invoice_date', 'actual_start', 'actual_end'] as $key) {
            if (isset($data[$key]) && is_object($data[$key])) {
                $data[$key] = $data[$key]->format('Y-m-d');
            }
        }
        return $data;
    }
}
