<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CepController extends Controller
{
    /**
     * Consulta endereço por CEP com suporte a ViaCEP e fallback para BrasilAPI.
     */
    public function consultar(string $cep): JsonResponse
    {
        $cleanCep = preg_replace('/\D/', '', $cep);

        if (strlen($cleanCep) !== 8) {
            return response()->json(['erro' => 'CEP inválido'], 422);
        }

        // 1. Tenta ViaCEP
        try {
            $response = Http::timeout(4)->get("https://viacep.com.br/ws/{$cleanCep}/json/");
            if ($response->successful()) {
                $data = $response->json();
                if (empty($data['erro'])) {
                    return response()->json([
                        'logradouro' => $data['logradouro'] ?? '',
                        'bairro' => $data['bairro'] ?? '',
                        'cidade' => $data['localidade'] ?? '',
                        'estado' => $data['uf'] ?? '',
                        'complemento' => $data['complemento'] ?? '',
                    ]);
                }
                return response()->json(['erro' => 'CEP não encontrado'], 404);
            }
        } catch (\Throwable $e) {
            Log::warning("Falha na consulta ao ViaCEP para o CEP {$cleanCep}: " . $e->getMessage());
        }

        // 2. Fallback: BrasilAPI
        try {
            $response = Http::timeout(4)->get("https://brasilapi.com.br/api/cep/v1/{$cleanCep}");
            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'logradouro' => $data['street'] ?? '',
                    'bairro' => $data['neighborhood'] ?? '',
                    'cidade' => $data['city'] ?? '',
                    'estado' => $data['state'] ?? '',
                    'complemento' => '',
                ]);
            }
            if ($response->status() === 404) {
                return response()->json(['erro' => 'CEP não encontrado'], 404);
            }
        } catch (\Throwable $e) {
            Log::warning("Falha no fallback BrasilAPI para o CEP {$cleanCep}: " . $e->getMessage());
        }

        return response()->json(['erro' => 'Não foi possível consultar o CEP no momento.'], 503);
    }
}
