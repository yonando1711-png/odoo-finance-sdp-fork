<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Tanda Terima Uang Muka</title>
    <style>
        @page {
            margin: 30px 40px 60px 40px;
        }
        body {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-page {
            page-break-after: always;
            position: relative;
        }
        .invoice-page:last-child {
            page-break-after: auto;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(255, 0, 0, 0.15);
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
            z-index: -1;
        }
        .company-header {
            margin-bottom: 5px;
        }
        .company-header td {
            vertical-align: top;
        }
        .company-name {
            font-size: 11px;
            font-weight: bold;
        }
        .company-address {
            font-size: 9px;
            color: #334155;
        }
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1px;
            text-decoration: underline;
            margin: 15px 0 20px 0;
            color: #0f172a;
        }
        .header-info-table {
            margin-bottom: 20px;
        }
        .header-info-table td {
            padding: 4px 0;
            font-size: 10.5px;
            vertical-align: top;
        }
        .field-label {
            font-weight: bold;
            color: #1e293b;
            width: 170px;
        }
        .field-colon {
            width: 15px;
            text-align: center;
        }
        .bordered-table {
            border: 1px solid #000;
            margin: 15px 0;
        }
        .bordered-table td {
            padding: 6px 10px;
            font-size: 10.5px;
            border-bottom: 1px solid #cbd5e1;
        }
        .bordered-table tr:last-child td {
            border-bottom: none;
        }
        .amount-table {
            width: 100%;
            margin-top: 10px;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
        }
        .amount-table td {
            padding: 6px 10px;
            font-size: 11px;
        }
        .terbilang-box {
            border: 1.5px solid #000;
            padding: 8px 12px;
            margin: 15px 0 10px 0;
            font-size: 11px;
            background-color: #f8fafc;
        }
        .legal-notice {
            font-style: italic;
            font-size: 9.5px;
            color: #334155;
            margin: 10px 0 25px 0;
            line-height: 1.4;
        }
        .footer-table {
            margin-top: 30px;
        }
        .footer-table td {
            vertical-align: top;
        }
        .signature-box {
            text-align: center;
            width: 200px;
            float: right;
        }
        .signature-line {
            margin-top: 60px;
            border-bottom: 1px solid #000;
            width: 160px;
            display: inline-block;
        }
        .doc-control {
            position: absolute;
            bottom: -35px;
            left: 0;
            font-size: 8px;
            color: #64748b;
        }
        .print-timestamp {
            position: absolute;
            bottom: -35px;
            right: 0;
            font-size: 8px;
            color: #1e293b;
        }
    </style>
</head>
<body>
    @foreach($invoices as $invoice)
    <div class="invoice-page">
        @if(($enableWatermark ?? '1') === '1' && isset($invoice->print_count) && $invoice->print_count > 0)
            <div class="watermark">DUPLICATE - {{ $invoice->print_count }}</div>
        @endif

        {{-- Company Header & Top Right Info --}}
        <table class="company-header">
            <tr>
                <td style="width: 55%;">
                    @php
                        $isPdfRequest = request()->is('*/pdf');
                        $logoSource = $isPdfRequest ? public_path('images/logo.png') : asset('images/logo.png');
                    @endphp
                    <img src="{{ $logoSource }}" style="max-height: 45px; max-width: 180px; margin-bottom: 5px;" alt="Logo"><br>
                    <span class="company-name">PT. SURYA DARMA PERKASA</span><br>
                    <span class="company-address">
                        JL. DAAN MOGOT KM.1 NO. 99, JAKARTA BARAT<br>
                        (021) 56977708, 5661060
                    </span>
                </td>
                <td style="width: 45%; text-align: right; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: bold; width: 70px; text-align: left;">Nomor</td>
                            <td style="width: 10px; text-align: center;">:</td>
                            <td style="font-weight: bold; text-align: left;">{{ $invoice->name }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; text-align: left;">Tanggal</td>
                            <td style="text-align: center;">:</td>
                            <td style="text-align: left;">{{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Document Title --}}
        <div class="doc-title">TANDA TERIMA UANG MUKA</div>

        {{-- Customer & Receipt Info --}}
        <table class="header-info-table">
            <tr>
                <td class="field-label">TELAH TERIMA DARI</td>
                <td class="field-colon">:</td>
                <td style="font-weight: bold;">{{ strtoupper($invoice->partner_name) }}</td>
            </tr>
            <tr>
                <td class="field-label">NPWP</td>
                <td class="field-colon">:</td>
                <td>{{ $invoice->partner_npwp ?: '00.000.000.0-000.000' }}</td>
            </tr>
            <tr>
                <td class="field-label">Tanggal</td>
                <td class="field-colon">:</td>
                <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '' }}</td>
            </tr>
            <tr>
                <td class="field-label" style="padding-top: 15px;">UNTUK PEMBAYARAN</td>
                <td class="field-colon" style="padding-top: 15px;">:</td>
                <td style="padding-top: 15px; font-weight: bold; font-size: 11px;">
                    {{ $invoice->payment_description }}
                </td>
            </tr>
        </table>

        {{-- Financial Amounts Table --}}
        <table class="amount-table">
            <tr>
                <td style="font-weight: bold; width: 170px;">JUMLAH PEMBAYARAN</td>
                <td style="width: 30px;">Rp.</td>
                <td style="text-align: right; width: 120px; font-weight: bold;">
                    {{ number_format($invoice->amount_untaxed, 0, ',', '.') }}
                </td>
                <td style="font-weight: bold; width: 60px; text-align: right;">PPN</td>
                <td style="width: 30px; text-align: right;">Rp.</td>
                <td style="text-align: right; width: 120px; font-weight: bold;">
                    {{ number_format($invoice->amount_tax, 0, ',', '.') }}
                </td>
            </tr>
            <tr style="font-size: 11.5px;">
                <td style="font-weight: bold;">TOTAL PEMBAYARAN</td>
                <td style="font-weight: bold;">Rp.</td>
                <td style="text-align: right; font-weight: bold; font-size: 12px;" colspan="4">
                    {{ number_format($invoice->amount_total, 0, ',', '.') }}
                </td>
            </tr>
        </table>

        {{-- Terbilang Box --}}
        <div class="terbilang-box">
            <strong>Terbilang :</strong>
            <em>{{ ucwords(\App\Helpers\Terbilang::convert($invoice->amount_total)) }} Rp. #</em>
        </div>

        {{-- Legal Notice --}}
        <div class="legal-notice">
            Tanda Terima Uang Muka akan berlaku sebagai bukti pembayaran yang sah apabila dana sudah efektif di rekening PT. Surya Darma Perkasa.
        </div>

        {{-- Signature Section --}}
        <table class="footer-table">
            <tr>
                <td style="width: 60%;"></td>
                <td style="width: 40%; text-align: center;">
                    <div style="font-weight: bold; margin-bottom: 50px;">Penerima</div>
                    <div style="border-bottom: 1px solid #000; width: 160px; margin: 0 auto;"></div>
                </td>
            </tr>
        </table>

        {{-- Document Control Code & Dynamic Print Timestamp Footer --}}
        @php
            $picCode = $invoice->invoice_pic ? strtoupper(substr(trim($invoice->invoice_pic), 0, 3)) : 'SDP';
            $dateCode = date('dmy');
            $printNum = str_pad($invoice->print_count ?: 1, 2, '0', STR_PAD_LEFT);
            $timestampCode = "{$picCode}/{$dateCode}/{$printNum}";
        @endphp

        <div style="margin-top: 40px; border-top: 1px solid #000; padding-top: 5px; position: relative;">
            <div style="float: left; font-size: 8.5px; color: #475569;">
                SDP/FR/BC/02, Rev.01
            </div>
            <div style="float: right; font-size: 8.5px; font-weight: bold; color: #1e293b;">
                {{ $timestampCode }}
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
    @endforeach

    @if(isset($isHtml) && $isHtml)
    <script>
        window.onload = function() {
            if (window.self === window.top) {
                setTimeout(function() { window.print(); }, 500);
            }
        }
    </script>
    @endif
</body>
</html>
