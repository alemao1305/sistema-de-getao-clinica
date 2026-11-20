<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Declaração Fisioterapêutica - {{ $paciente->nome }}</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 20mm; font-size: 12pt; }
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #1e293b;
            line-height: 1.6;
            margin: 40px auto;
            max-width: 800px;
            padding: 30px;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }
        .header h1 {
            margin: 0;
            font-size: 24pt;
            color: #0f766e;
            font-family: Arial, sans-serif;
            letter-spacing: 1px;
        }
        .header p {
            margin: 4px 0;
            font-size: 10pt;
            color: #64748b;
        }
        .doc-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 50px 0 40px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-decoration: underline;
        }
        .body-text {
            text-align: justify;
            text-indent: 40px;
            margin-bottom: 30px;
            font-size: 13pt;
        }
        .footer-date {
            text-align: right;
            margin-top: 60px;
            font-size: 12pt;
        }
        .signature-box {
            margin-top: 90px;
            text-align: center;
        }
        .signature-line {
            display: inline-block;
            width: 320px;
            border-top: 1px solid #334155;
            padding-top: 8px;
            font-weight: bold;
            font-size: 11pt;
        }
        .clinic-footer {
            margin-top: 80px;
            border-top: 1px solid #cbd5e1;
            padding-top: 10px;
            text-align: center;
            font-size: 9pt;
            color: #94a3b8;
        }
        .btn-print {
            background-color: #0d9488;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right;">
        <button class="btn-print" onclick="window.print()">Imprimir / Salvar em PDF</button>
    </div>

    <!-- Header Timbrado -->
    <div class="header">
        <h1>{{ $config->nome_fantasia }}</h1>
        <p><strong>{{ $config->razao_social }}</strong> &bull; CNPJ: {{ $config->cnpj }} &bull; Registro: {{ $config->crefito_pj }}</p>
        <p>{{ $config->logradouro }}, {{ $config->numero }} {{ $config->bairro }} &bull; {{ $config->cidade }}/{{ $config->estado }}</p>
        <p>Telefone / WhatsApp: {{ $config->telefone }} &bull; {{ $config->email }}</p>
    </div>

    <!-- Título do Documento -->
    <div class="doc-title">
        @if($tipo === 'comparecimento')
            DECLARAÇÃO DE COMPARECIMENTO
        @elseif($tipo === 'alta')
            DECLARAÇÃO DE ALTA FISIOTERAPÊUTICA
        @else
            ATESTADO DE TRATAMENTO FISIOTERAPÊUTICO
        @endif
    </div>

    <!-- Corpo do Texto -->
    <div class="body-text">
        Declaro, para os devidos fins de direito e comprovação, que o(a) paciente <strong>{{ $paciente->nome }}</strong>, portador(a) do CPF nº <strong>{{ $paciente->cpf_formatado ?? 'não informado' }}</strong>, compareceu a esta clínica para assistência e tratamento fisioterapêutico especializado <strong>{{ $periodoTratamento }}</strong>.
    </div>

    @if($observacoes)
    <div class="body-text">
        {{ $observacoes }}
    </div>
    @endif

    <!-- Data por Extenso -->
    <div class="footer-date">
        {{ $config->cidade ?? 'São Paulo' }}, {{ now()->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y') }}.
    </div>

    <!-- Assinatura Profissional -->
    <div class="signature-box">
        <div class="signature-line">
            {{ $user->name }}<br>
            <span style="font-weight: normal; font-size: 10pt;">
                Fisioterapeuta - {{ $user->crefito ?? 'Responsável Técnico' }}
            </span>
        </div>
    </div>

    <!-- Rodapé -->
    <div class="clinic-footer">
        {{ $config->nome_fantasia }} &bull; Documento emitido eletronicamente via sistema de gestão clínica em {{ now()->format('d/m/Y H:i') }}.
    </div>
</body>
</html>
