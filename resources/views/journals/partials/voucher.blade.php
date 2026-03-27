        <table class="header-table">
            <tr>
                <td style="vertical-align: middle;">
                    <div class="header-move-name">{{ $entry->move_name }}</div>
                </td>
                <td style="text-align: right; vertical-align: middle;">
                    @php
                        $isPdf = $isPdf ?? true;
                        $logoPath = $isPdf ? public_path('images/logo.png') : asset('images/logo.png');
                        $logoExists = $isPdf ? file_exists(public_path('images/logo.png')) : true;
                    @endphp
                    @if($logoExists)
                        <img src="{{ $logoPath }}" class="logo-img" alt="Logo">
                    @endif
                </td>
            </tr>
        </table>
        
        <table class="info-table">
            <tr>
                <td style="width: 50%; padding-right: 20px;">
                    <table style="width: 100%; table-layout: auto;">
                        <tr>
                            <td class="info-label">Reference</td>
                            <td>
                                {{ $entry->ref }}
                                @if($entry->payment_reference)
                                    {{ $entry->ref ? ' / ' : '' }} {{ $entry->payment_reference }}
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%;">
                    <table style="width: 100%; table-layout: auto;">
                        <tr>
                            <td class="info-label" style="width: 130px;">Accounting Date</td>
                            <td>{{ \Carbon\Carbon::parse($entry->date)->locale('id')->isoFormat('D MMMM YYYY') }}</td>
                        </tr>
                        <tr>
                            <td class="info-label" style="width: 130px;">Journal</td>
                            <td>{{ $entry->journal_name }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        
        <table class="lines-table">
            <thead>
                <tr>
                    <th class="col-account">Account</th>
                    <th class="col-partner">Partner</th>
                    <th class="col-label">Label</th>
                    <th class="col-debit text-right">Debit</th>
                    <th class="col-credit text-right">Credit</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalDebit = 0;
                    $totalCredit = 0;
                @endphp
                @foreach($entry->lines as $line)
                    @php
                        $totalDebit += $line->debit;
                        $totalCredit += $line->credit;
                    @endphp
                    <tr>
                        <td class="col-account">{{ $line->account_code }}<br>{{ $line->account_name }}</td>
                        <td class="col-partner">{{ $entry->partner_name }}</td>
                        <td class="col-label">
                            {{ $line->display_name }}
                            @php
                                $billRef = !empty($line->ref) ? $line->ref : $entry->payment_reference;
                            @endphp
                            @if($billRef)
                                <br><small>({{ $billRef }})</small>
                            @endif
                        </td>
                        <td class="col-debit text-right">Rp&nbsp;{{ $line->debit == 0 ? '0' : number_format($line->debit, 0, ',', '.') }}</td>
                        <td class="col-credit text-right">Rp&nbsp;{{ $line->credit == 0 ? '0' : number_format($line->credit, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="5" style="padding: 5px 0;"></td>
                </tr>
                <tr class="totals-row">
                    <td colspan="3"></td>
                    <td class="text-right">Rp&nbsp;{{ number_format($totalDebit, 0, ',', '.') }}</td>
                    <td class="text-right">Rp&nbsp;{{ number_format($totalCredit, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
