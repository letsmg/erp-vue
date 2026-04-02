<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #334155; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; color: #0f172a; }
        .header p { margin: 5px 0 0; color: #64748b; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 8px; text-align: left; font-weight: bold; text-transform: uppercase; font-size: 8px; color: #475569; }
        td { border: 1px solid #e2e8f0; padding: 8px; vertical-align: top; }
        tr:nth-child(even) { background-color: #f1f5f9; }
        
        .status-badge { padding: 2px 6px; rounded: 4px; font-weight: bold; text-transform: uppercase; font-size: 7px; }
        .status-active { background-color: #dcfce7; color: #166534; }
        .status-blocked { background-color: #fee2e2; color: #991b1b; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px; }
        
        .group-header { background-color: #e2e8f0; font-weight: bold; padding: 4px 8px; margin-top: 10px; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('icon.ico');
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
        <p>Gerado em: {{ $date }}</p>
    </div>

    @php
        $fieldLabels = [
            'name' => 'Nome Completo',
            'document_number' => 'CPF/CNPJ',
            'email' => 'E-mail',
            'phone1' => 'Telefone',
            'zip_code' => 'CEP',
            'street' => 'Logradouro',
            'number' => 'Nº',
            'neighborhood' => 'Bairro',
            'city' => 'Cidade',
            'state' => 'UF',
            'total_purchases' => 'Total Comprado',
            'last_purchase_date' => 'Última Compra'
        ];
    @endphp

    <table>
        <thead>
            <tr>
                @foreach($fields as $field)
                    <th>{{ strtoupper($fieldLabels[$field] ?? $field) }}</th>
                @endforeach
                <th style="width: 50px; text-align: center;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
            <tr>
                @foreach($fields as $field)
                    <td>
                        @if($field === 'email')
                            {{ $client->user->email ?? 'N/A' }}
                        @elseif($field === 'total_purchases')
                            R$ {{ number_format($client->total_purchases ?? 0, 2, ',', '.') }}
                        @elseif($field === 'last_purchase_date')
                            {{ $client->sales->max('created_at')?->format('d/m/Y') ?? 'Nenhuma' }}
                        @elseif(in_array($field, ['zip_code', 'street', 'number', 'neighborhood', 'city', 'state']))
                            @php 
                                $addr = $client->addresses->where('is_delivery_address', true)->first() ?: $client->addresses->first();
                            @endphp
                            {{ $addr ? $addr->$field : '-' }}
                        @else
                            {{ $client->$field }}
                        @endif
                    </td>
                @endforeach
                <td style="text-align: center;">
                    <span class="status-badge {{ $client->is_active ? 'status-active' : 'status-blocked' }}">
                        {{ $client->is_active ? 'ATIVO' : 'BLOQUEADO' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        ERP VUE LARAVEL - Relatório Gerado em {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>
