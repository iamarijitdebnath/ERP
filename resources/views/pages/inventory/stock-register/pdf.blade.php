<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Stock Register</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #ffffff;
        }
        .filters {
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .filters strong {
            margin-right: 5px;
        }
        .filters span {
            margin-right: 15px;
        }
        
        .item-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .item-header {
            background-color: #ffffff;
            padding: 8px;
            border: 1px solid #e5e7eb;
            border-bottom: none;
        }
        .item-title {
            font-size: 12px;
            font-weight: bold;
        }
        .item-code {
            font-size: 10px;
            color: #000000;
            margin-top: 2px;
        }
        .uom-badge {
            background-color: #ffffff;
            border: 1px solid #000000;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 9px;
            color: #000000;
            margin-left: 5px;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #ffffff; 
            color: #000000;
            text-transform: uppercase;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-green {
            color: #000000;
        }
        .text-red {
            color: #000000;
        }
        .type-badge {
            background-color: #ffffff;
            color: #000000;
            padding: 2px 4px;
            border-radius: 3px;
            border: 1px solid #000000;
            font-size: 8px;
        }
        .no-records {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>COMPANY NAME</h1>
        {{-- <p>Generated on {{ now()->format('d-m-Y H:i A') }}</p> --}}
    </div>

    <div class="filters">
        <p>
            <strong>Warehouse:</strong> <span>{{ $warehouseName }}</span>
            <strong>Date Range:</strong> 
            @if(isset($filters['date_from']) && isset($filters['date_to']))
                <span>{{ $filters['date_from'] }} to {{ $filters['date_to'] }}</span>
            @else
                <span>-</span>
            @endif
        </p>
    </div>

    @if(empty($reportData))
        <div class="no-records">
            No records found for the selected criteria.
        </div>
    @else
        @foreach($reportData as $data)
            <div class="item-section">
                <div class="item-header">
                    <div class="item-title">
                        {{ $data['item']['name'] }}
                        <span class="item-code">(Code: {{ $data['item']['code'] ?? 'N/A' }})</span>
                    </div>
                    {{-- @if(isset($data['item']['uom']))
                        <span class="uom-badge">{{ $data['item']['uom']['name'] }}</span>
                    @endif --}}
                </div>  
                
                <table>
                    <thead>
                        <tr>
                            <th style="width: 70px;">Date</th>
                            <th style="width: 60px;">Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th style="width: 60px;">Ref</th>
                            <th class="text-right" style="width: 60px;">In</th>
                            <th class="text-right" style="width: 60px;">Out</th>
                            <th class="text-right" style="width: 70px;">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Opening Balance -->
                        <tr>
                            <td colspan="5" class="font-bold">Opening Balance</td>
                            <td class="text-right">-</td>
                            <td class="text-right">-</td>
                            <td class="text-right font-bold">{{ $data['opening_balance'] }}</td>
                        </tr>

                        <!-- Transactions -->
                        @foreach($data['transactions'] as $tx)
                            <tr>
                                <td>{{ $tx['date'] }}</td>
                                <td>
                                    <span class="type-badge">{{ $tx['type'] }}</span>
                                </td>
                                <td>{{ $tx['from'] }}</td>
                                <td>{{ $tx['to'] }}</td>
                                <td>{{ $tx['ref'] }}</td>
                                <td class="text-right text-green @if($tx['in'] !== '-') font-bold @endif">{{ $tx['in'] }}</td>
                                <td class="text-right text-red @if($tx['out'] !== '-') font-bold @endif">{{ $tx['out'] }}</td>
                                <td class="text-right font-bold">{{ $tx['balance'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

</body>
</html>
