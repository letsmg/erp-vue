<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #4f46e5; margin-bottom: 20px; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #4f46e5; font-size: 18px; }
        .header p { margin: 5px 0 0; color: #666; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; text-align: left; color: #475569; text-transform: uppercase; font-size: 10px; }
        td { border: 1px solid #e2e8f0; padding: 8px; vertical-align: middle; }
        
        .img-box { width: 65px; height: 65px; background: #f1f5f9; text-align: center; border-radius: 4px; overflow: hidden; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; }
        
        .price { color: #16a34a; font-weight: bold; white-space: nowrap; }
        .brand-info { color: #64748b; font-size: 9px; margin-top: 2px; text-transform: uppercase; }
        .stock-badge { background: #f1f5f9; padding: 2px 5px; border-radius: 3px; font-weight: bold; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('icon.png');
        $base64Logo = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $base64Logo = 'data:image/x-icon;base64,' . base64_encode($logoData);
        }
    @endphp

    <div class="header">
        <div style="margin-bottom: 15px;">
            @if($base64Logo)
                <img src="{{ $base64Logo }}" style="height: 30px; vertical-align: middle; margin-right: 10px;">
            @endif
            <span style="font-size: 24px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: -1px; vertical-align: middle;">
                ERP<span style="color: #4f46e5;">VUE LARAVEL</span>
            </span>
        </div>
        <h1>{{ $title }}</h1>
        <p>Relatório gerado em: {{ $date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                @if($type === 'analitico') 
                    <th style="width: 70px; text-align: center;">Foto</th> 
                @endif
                <th>Produto / Detalhes</th>
                <th>Fornecedor</th>
                <th style="width: 60px; text-align: center;">Estoque</th>
                <th style="width: 100px; text-align: right;">Preço Venda</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                @if($type === 'analitico')
                <td class="img-box" style="width: 70px; text-align: center;">
                    @php
                    $base64 = null;
                    
                    // Como usamos toArray(), acessamos como array
                    $images = $product['images'] ?? [];
                    $firstImage = collect($images)->first();

                    if ($firstImage) {
                        $pathNoBanco = $firstImage['path']; // Ex: ejKWZ...jpg
                        
                        // Use public_path apontando para o link simbólico 'storage'
                        // Isso é o que o navegador usaria: public/storage/products/...
                        $fullPath = public_path('storage/products/' . $pathNoBanco);

                        if (file_exists($fullPath)) {
                            $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
                            $fileData = @file_get_contents($fullPath);
                            if ($fileData) {
                                $base64 = 'data:image/' . $extension . ';base64,' . base64_encode($fileData);
                            }
                        }
                    }
                @endphp

                    @if($base64)
                        <img src="{{ $base64 }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                    @else
                        <div style="font-size: 8px; color: #94a3b8;">
                            Sem Foto<br>
                            <span style="font-size: 6px; color: red;">{{ !$firstImage ? 'Vazio no DB' : 'Arq. não achado' }}</span>
                        </div>
                    @endif
                </td>
                @endif
                
                <td>
                    <div style="font-weight: bold; font-size: 12px;">{{ $product['description'] }}</div>
                    <div class="brand-info">
                        {{ $product['brand'] ?? 'S/ Marca' }} 
                        @if(!empty($product['model'])) | Mod: {{ $product['model'] }} @endif
                    </div>
                    <div style="font-size: 8px; color: #94a3b8; margin-top: 3px;">ID: #{{ $product['id'] }}</div>
                </td>
                
                <td>{{ $product['supplier']['company_name'] ?? 'Não informado' }}</td>
                
                <td style="text-align: center;">
                    <span class="stock-badge">{{ $product['stock_quantity'] }}</span>
                </td>
                
                <td style="text-align: right;">
                    <span class="price">R$ {{ number_format($product['sale_price'], 2, ',', '.') }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistema de Gestão - Página gerada automaticamente via DomPDF
    </div>
</body>
</html>