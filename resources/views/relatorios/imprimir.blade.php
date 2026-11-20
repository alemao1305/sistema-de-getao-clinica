<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório - {{ ucfirst($fonteDados) }}</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 10mm; font-size: 10pt; }
        }
        body {
            font-family: Arial, sans-serif;
            color: #1e293b;
            margin: 20px auto;
            max-width: 1000px;
            padding: 20px;
            background: #fff;
        }
        .header {
            border-bottom: 2px solid #0d9488;
            padding-bottom: 15px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .header h1 {
            margin: 0;
            font-size: 18pt;
            color: #0f766e;
        }
        .header p {
            margin: 3px 0 0;
            font-size: 9pt;
            color: #64748b;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 9pt;
            display: flex;
            justify-content: space-between;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 7px 10px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            font-size: 8pt;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .totals-box {
            margin-top: 20px;
            padding: 12px;
            background-color: #f0fdfa;
            border: 1px solid #ccfbf1;
            border-radius: 8px;
            font-size: 10pt;
            font-weight: bold;
            color: #115e59;
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            text-align: center;
            font-size: 8pt;
            color: #94a3b8;
        }
        .btn-print {
            background-color: #0d9488;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 15px;">
        <button class="btn-print" onclick="window.print()">Imprimir / Salvar em PDF</button>
    </div>

    <div class="header">
        <div>
            <h1>{{ $config->nome_fantasia }}</h1>
            <p>{{ $config->razao_social }} &bull; CNPJ: {{ $config->cnpj }} &bull; {{ $config->cidade }}/{{ $config->estado }}</p>
        </div>
        <div style="text-align: right;">
            <p><strong>Relatório Analítico</strong></p>
            <p>Emitido em {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="meta-box">
        <div>
            <strong>Fonte de Dados:</strong> {{ ucfirst(str_replace('_', ' ', $fonteDados)) }} &bull;
            <strong>Período:</strong> {{ date('d/m/Y', strtotime($dataInicio)) }} até {{ date('d/m/Y', strtotime($dataFim)) }}
        </div>
        <div>
            <strong>Total de Registros:</strong> {{ $resultado['totais']['quantidade'] }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($colunasSelecionadas as $cKey)
                    <th>{{ $colunasDisponiveis[$cKey]['label'] ?? $cKey }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($resultado['dados'] as $linha)
            <tr>
                @foreach($colunasSelecionadas as $cKey)
                    <td>{{ $linha[$cKey] ?? '-' }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(isset($resultado['totais']['valor']) && $resultado['totais']['valor'] > 0)
    <div class="totals-box">
        Totalização Financeira: R$ {{ number_format($resultado['totais']['valor'], 2, ',', '.') }}
    </div>
    @endif

    <div class="footer">
        {{ $config->nome_fantasia }} &bull; Relatório emitido eletronicamente via sistema de gestão clínica &bull; Confidencial
    </div>
</body>
</html>
