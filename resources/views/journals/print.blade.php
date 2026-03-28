<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Print Journal Entries</title>
    <style>
        /* Base styles */
        body {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 0;
            background: #f1f5f9;
        }
        .print-container {
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            vertical-align: top;
            padding: 6px;
        }
        .header-move-name {
            font-size: 26px;
            font-weight: bold;
            color: #1a237e;
            letter-spacing: -0.5px;
        }
        .logo-img {
            max-height: 40px;
            max-width: 160px;
        }
        .info-label {
            font-weight: bold;
            color: #1c3254;
            width: 100px;
        }

        /* Lines table */
        .lines-table {
            margin-top: 0;
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
        }
        .lines-table tr {
            page-break-inside: avoid;
        }
        .lines-table th {
            text-align: left;
            border-bottom: 1px solid #cbd5e1;
            font-weight: bold;
            padding: 10px 5px;
            color: #000000;
            font-size: 11px;
        }
        .lines-table > tbody > tr > td {
            padding: 10px 5px;
            font-size: 10px;
            word-wrap: break-word;
        }
        .col-account { width: 20%; padding-right: 10px; }
        .col-partner { width: 17%; padding-right: 10px; }
        .col-label   { width: 23%; padding-right: 10px; }
        .col-debit   { width: 20%; text-align: right; white-space: nowrap; }
        .col-credit  { width: 20%; text-align: right; white-space: nowrap; }

        .totals-row td {
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            font-weight: bold;
            color: #000000;
            padding-top: 12px;
            padding-bottom: 12px;
            font-size: 11px;
            white-space: nowrap;
        }
        .lines-table > tbody > tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right { text-align: right; }

        /* ========= REPEATING HEADER INSIDE THEAD ========= */
        .lines-table thead tr:first-child > td {
            border: none !important;
            border-bottom: 1px solid #cbd5e1 !important;
            background-color: white !important;
            padding: 0 0 0 0 !important;
        }

        /* Kill ALL borders inside the metadata area */
        .metadata-table,
        .metadata-table thead,
        .metadata-table tbody,
        .metadata-table tfoot,
        .metadata-table tr,
        .metadata-table th,
        .metadata-table td {
            border: none !important;
            border-top: none !important;
            border-bottom: none !important;
            border-left: none !important;
            border-right: none !important;
            box-shadow: none !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }
        .metadata-table > tbody > tr > td,
        .metadata-table > tr > td {
            padding: 3px 4px !important;
        }

        @if(($paperSize ?? 'A5') === 'A4')
            /* ========= A4 MODE: 2 vouchers per A4 page ========= */
            .voucher-page {
                background: #fff;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
                margin-bottom: 20px;
                padding: 15px 20px;
                min-height: 200px;
            }
            .voucher-half {
                padding: 10px 0;
                overflow: hidden;
                max-height: 135mm; /* Half of A4 minus margins */
                box-sizing: border-box;
            }
            .voucher-half:first-child {
                border-bottom: 1px dashed #94a3b8;
                margin-bottom: 10px;
            }
        @else
            /* ========= A5 MODE: 1 voucher per page, free-flow ========= */
            .voucher-entry {
                padding: 20px 25px;
                background: #fff;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
                margin-bottom: 20px;
            }
        @endif

        /* ========= PRINT SPECIFICS ========= */
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: none;
            }
            .print-container {
                box-shadow: none;
                max-width: none;
                width: 100%;
                margin: 0;
                padding: 0;
            }

            /* THIS is the key: tell browser to treat thead as a repeating header */
            .lines-table thead {
                display: table-header-group;
            }

            @if(($paperSize ?? 'A5') === 'A4')
                @page { size: A4 portrait; margin: 10mm; }
                .voucher-page {
                    box-shadow: none;
                    margin-bottom: 0;
                    padding: 5mm;
                    page-break-after: always;
                    page-break-inside: avoid;
                }
                .voucher-page:last-child {
                    page-break-after: auto;
                }
                .voucher-half {
                    max-height: none;
                    overflow: hidden;
                    height: 128mm; /* ~half of A4 height minus padding */
                    padding: 5px 0;
                    box-sizing: border-box;
                }
                .voucher-half:first-child {
                    border-bottom: 1px dashed #94a3b8;
                    margin-bottom: 5px;
                }
            @else
                @page { size: A5 landscape; margin: 10mm; }
                .voucher-entry {
                    box-shadow: none;
                    padding: 0;
                    margin-bottom: 0;
                    page-break-after: always;
                }
                .voucher-entry:last-child {
                    page-break-after: auto;
                }
            @endif
        }
    </style>
</head>
<body>
    <div class="print-container">
        @if(($paperSize ?? 'A5') === 'A4')
            {{-- A4 Mode: 2 vouchers per page --}}
            @foreach($entries->chunk(2) as $chunk)
            <div class="voucher-page">
                @foreach($chunk as $entry)
                    <div class="voucher-half">
                        @include('journals.partials.voucher', ['entry' => $entry, 'isPdf' => false])
                    </div>
                @endforeach
            </div>
            @endforeach
        @else
            {{-- A5 Mode: 1 voucher per page, free-flowing --}}
            @foreach($entries as $entry)
            <div class="voucher-entry">
                @include('journals.partials.voucher', ['entry' => $entry, 'isPdf' => false])
            </div>
            @endforeach
        @endif
    </div>

    <!-- Auto trigger print dialog -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
