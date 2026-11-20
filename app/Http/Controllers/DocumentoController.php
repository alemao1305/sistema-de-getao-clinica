<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\DocumentoAnexo;
use App\Models\Paciente;
use App\Services\AuditService;

class DocumentoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['required', 'string'],
            'arquivo' => ['required', 'file', 'max:15360'], // max 15MB
            'observacoes' => ['nullable', 'string'],
        ], [
            'arquivo.required' => 'Selecione um arquivo para envio.',
            'arquivo.max' => 'O arquivo não pode exceder 15MB.',
        ]);

        $file = $request->file('arquivo');
        $paciente = Paciente::findOrFail($request->paciente_id);

        $path = $file->store("documentos_pacientes/{$paciente->id}", 'local');

        $doc = DocumentoAnexo::create([
            'paciente_id' => $paciente->id,
            'user_id' => Auth::id(),
            'prontuario_id' => $request->prontuario_id ?? null,
            'titulo' => $request->titulo,
            'tipo_documento' => $request->tipo_documento,
            'arquivo_path' => $path,
            'nome_original' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'tamanho_bytes' => $file->getSize(),
            'observacoes' => $request->observacoes ?? null,
        ]);

        AuditService::log('documentos.upload', "Anexou o documento '{$doc->titulo}' ao paciente {$paciente->nome}", $doc);

        return back()->with('success', 'Documento anexado com sucesso e protegido com acesso seguro.');
    }

    public function download($id)
    {
        $doc = DocumentoAnexo::with('paciente')->findOrFail($id);

        if (!Storage::disk('local')->exists($doc->arquivo_path)) {
            abort(404, 'Arquivo não encontrado no armazenamento seguro.');
        }

        AuditService::log('documentos.download', "Fez download do arquivo '{$doc->titulo}' do paciente {$doc->paciente->nome}", $doc);

        return Storage::disk('local')->download($doc->arquivo_path, $doc->nome_original);
    }

    public function destroy($id)
    {
        $doc = DocumentoAnexo::with('paciente')->findOrFail($id);
        $titulo = $doc->titulo;
        $doc->delete();

        AuditService::log('documentos.delete', "Removeu o anexo '{$titulo}' do paciente {$doc->paciente->nome}");

        return back()->with('success', 'Documento removido com sucesso.');
    }
}
