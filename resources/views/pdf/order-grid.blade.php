<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order {{ $order->number }} - PDF Grid</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            background: white;
        }
        
        .pdf-container {
            width: 612px; /* 8.5 inches at 72 DPI */
            height: 792px; /* 11 inches at 72 DPI */
            margin: 0 auto;
            position: relative;
            padding: 36px 0; /* 0.5 inch top/bottom margins only */
        }
        
        .grid-container {
            display: table;
            width: 100%;
            height: 720px; /* Full height minus margins */
            table-layout: fixed;
        }
        
        .grid-row {
            display: table-row;
            height: {{ $cellSize }}px;
        }
        
        .grid-cell {
            display: table-cell;
            width: {{ $cellSize }}px;
            height: {{ $cellSize }}px;
            position: relative;
            vertical-align: top;
            border: 1px solid #e0e0e0;
        }
        
        .cell-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        
        .cell-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
            pointer-events: none;
        }
        
        .overlay-svg {
            width: 100%;
            height: 100%;
            opacity: 0.8;
        }
        
        .empty-cell {
            background-color: #f5f5f5;
            border: 1px dashed #ccc;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .footer {
            position: absolute;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="pdf-container">
        <div class="header">
            Order #{{ $order->number }} - Generated on {{ now()->format('Y-m-d H:i:s') }}
        </div>
        
        <div class="grid-container">
            @for ($row = 0; $row < 3; $row++)
                <div class="grid-row">
                    @for ($col = 0; $col < $cellsPerRow; $col++)
                        @php
                            $index = $row * $cellsPerRow + $col;
                            $imagePath = $images[$index] ?? null;
                        @endphp
                        
                        <div class="grid-cell {{ !$imagePath ? 'empty-cell' : '' }}">
                            @if ($imagePath && file_exists($imagePath))
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($imagePath)) }}" 
                                     alt="Grid Image {{ $index + 1 }}" 
                                     class="cell-image">
                                
                                <div class="cell-overlay">
                                    @if (file_exists($overlayPath))
                                        @if (str_ends_with($overlayPath, '.svg'))
                                            {!! file_get_contents($overlayPath) !!}
                                        @else
                                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($overlayPath)) }}" 
                                                 alt="Overlay" 
                                                 class="overlay-svg">
                                        @endif
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endfor
                </div>
            @endfor
        </div>
        
        <div class="footer">
            PDF generated at 300 DPI | Cell size: {{ $cellSize }}px | Order ID: {{ $order->id }}
        </div>
    </div>
</body>
</html>