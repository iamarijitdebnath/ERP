<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Stock Valuation Report</title>
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
            color: #666;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
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
        .font-mono {
            font-family: monospace;
        }
        .font-bold {
            font-weight: bold;
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
        <h1>SCIENCE & SURGICAL</h1>
        <p>Stock Valuation Report</p>
    </div>

    <div class="filters">
        <p>
            <strong>Valuation Method:</strong> <span>{{ $filters['method'] }}</span>
            <strong>Date (Upto):</strong> <span>{{ $filters['date'] }}</span>
        </p>
    </div>

    @if(empty($reportData))
        <div class="no-records">
            No stock found for the selected criteria.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">Item Code</th>
                    <th>Item Name</th>
                    <th>Warehouse</th>
                    <th class="text-center" style="width: 50px;">UOM</th>
                    <th class="text-right" style="width: 80px;">Quantity</th>
                    <th class="text-right" style="width: 80px;">Rate</th>
                    <th class="text-right" style="width: 100px;">Total Value</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach($reportData as $item)
                    @php $grandTotal += $item['value']; @endphp
                    <tr>
                        <td>{{ $item['item_code'] }}</td>
                        <td>{{ $item['item_name'] }}</td>
                        <td>{{ $item['warehouse_name'] }}</td>
                        <td class="text-center">{{ $item['uom'] }}</td>
                        <td class="text-right font-mono">{{ number_format($item['quantity'], 2) }}</td>
                        <td class="text-right font-mono">{{ number_format($item['rate'], 2) }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($item['value'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6" class="text-right">Grand Total:</th>
                    <th class="text-right font-bold font-mono">{{ number_format($grandTotal, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    @endif

</body>
</html>
