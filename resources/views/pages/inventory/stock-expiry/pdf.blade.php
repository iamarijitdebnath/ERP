<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Stock Expiry Report</title>
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
        .status-badge {
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 9px;
            border: 1px solid #ccc;
        }
        .status-expired {
            background-color: #fee2e2;
            color: #b91c1c;
            border-color: #fecaca;
        }
        .status-nearing {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }
        .status-valid {
            background-color: #d1fae5;
            color: #065f46;
            border-color: #a7f3d0;
        }
        
        .no-records {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
        }
        
        tr.expired td {
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>COMPANY NAME</h1>
        <p>Stock Expiry Report</p>
    </div>

    <div class="filters">
        <p>
            <strong>Warehouse:</strong> <span>{{ $warehouseName }}</span>
            <strong>Expiry Range:</strong> 
            @if($filters['date_range_type'] === 'this_month')
                <span>Expiring This Month</span>
            @elseif($filters['date_range_type'] === 'next_month')
                 <span>Expiring Next Month</span>
            @elseif($filters['date_range_type'] === 'this_year')
                 <span>Expiring This Year</span>
            @elseif(!empty($filters['date_from']) && !empty($filters['date_to']))
                <span>{{ $filters['date_from'] }} to {{ $filters['date_to'] }}</span>
            @else
                <span>All Dates</span>
            @endif
        </p>
    </div>

    @if(empty($reportData))
        <div class="no-records">
            No stock found with expiry dates for the selected criteria.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="width: 80px;">Batch No</th>
                    <th class="text-right" style="width: 80px;">Quantity</th>
                    <th class="text-center" style="width: 80px;">Expiry Date</th>
                    <th class="text-right" style="width: 60px;">Days Left</th>
                    <th style="width: 80px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $item)
                    <tr class="@if($item['status'] === 'expired') expired @endif">
                        <td>
                            {{ $item['item_name'] }} <br>
                            <span style="font-size: 8px; color: #666;">{{ $item['item_code'] }}</span>
                        </td>
                        <td style="font-family: monospace;">{{ $item['batch_no'] }}</td>
                        <td class="text-right">{{ $item['quantity'] }} {{ $item['uom'] }}</td>
                        <td class="text-center">{{ $item['exp_date'] }}</td>
                        <td class="text-right">{{ $item['days_left'] }}</td>
                        <td>
                            @if($item['status'] === 'expired')
                                <span class="status-badge status-expired">Expired</span>
                            @elseif($item['status'] === 'nearing_expiry')
                                <span class="status-badge status-nearing">Expiring Soon</span>
                            @else
                                <span class="status-badge status-valid">Valid</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>
</html>
