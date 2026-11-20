<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->get('busca');
        $userId = $request->get('user_id');
        $action = $request->get('action');
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');

        $logs = AuditLog::with('user')
            ->when($busca, fn($q) => $q->where('description', 'like', "%{$busca}%"))
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($action, fn($q) => $q->where('action', 'like', "%{$action}%"))
            ->when($dataInicio, fn($q) => $q->whereDate('created_at', '>=', $dataInicio))
            ->when($dataFim, fn($q) => $q->whereDate('created_at', '<=', $dataFim))
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $usuarios = User::orderBy('name')->get();

        return view('configuracoes.auditoria.index', compact('logs', 'usuarios', 'busca', 'userId', 'action', 'dataInicio', 'dataFim'));
    }
}
