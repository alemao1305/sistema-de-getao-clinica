<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;
use App\Services\AuditService;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->get('busca');
        $categoria = $request->get('categoria');
        $filtroEstoque = $request->get('estoque'); // baixo, zerado, todos

        $query = Produto::query()
            ->when($busca, function ($q) use ($busca) {
                $q->where(function ($sub) use ($busca) {
                    $sub->where('nome', 'like', "%{$busca}%")
                        ->orWhere('sku', 'like', "%{$busca}%")
                        ->orWhere('codigo_barras', 'like', "%{$busca}%");
                });
            })
            ->when($categoria, fn($q) => $q->where('categoria', $categoria))
            ->when($filtroEstoque === 'baixo', fn($q) => $q->whereColumn('estoque_atual', '<=', 'estoque_minimo'))
            ->when($filtroEstoque === 'zerado', fn($q) => $q->where('estoque_atual', '<=', 0));

        $produtos = (clone $query)->orderBy('nome')->paginate(15)->withQueryString();

        // Métricas de Estoque
        $totalProdutos = Produto::count();
        $totalEstoqueBaixo = Produto::whereColumn('estoque_atual', '<=', 'estoque_minimo')->count();
        $valorTotalEstoqueCusto = Produto::where('ativo', true)->selectRaw('SUM(estoque_atual * preco_custo) as total')->value('total') ?? 0;
        $valorTotalEstoqueVenda = Produto::where('ativo', true)->selectRaw('SUM(estoque_atual * preco_venda) as total')->value('total') ?? 0;

        $categorias = Produto::distinct()->pluck('categoria')->filter()->values();

        return view('produtos.index', compact(
            'produtos',
            'totalProdutos',
            'totalEstoqueBaixo',
            'valorTotalEstoqueCusto',
            'valorTotalEstoqueVenda',
            'categorias',
            'busca',
            'categoria',
            'filtroEstoque'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', 'max:100'],
            'codigo_barras' => ['nullable', 'string', 'max:50'],
            'sku' => ['nullable', 'string', 'max:50'],
            'preco_custo' => ['required', 'numeric', 'min:0'],
            'preco_venda' => ['required', 'numeric', 'min:0'],
            'estoque_atual' => ['required', 'integer', 'min:0'],
            'estoque_minimo' => ['required', 'integer', 'min:0'],
            'unidade_medida' => ['required', 'string', 'max:10'],
            'descricao' => ['nullable', 'string'],
        ], [
            'nome.required' => 'O nome do produto é obrigatório.',
            'preco_venda.required' => 'Informe o preço de venda.',
        ]);

        $validated['ativo'] = true;
        $produto = Produto::create($validated);

        if ($produto->estoque_atual > 0) {
            $produto->movimentacoes()->create([
                'user_id' => auth()->id(),
                'tipo' => 'entrada',
                'quantidade' => $produto->estoque_atual,
                'saldo_anterior' => 0,
                'saldo_novo' => $produto->estoque_atual,
                'motivo' => 'Saldo de estoque inicial no cadastro do produto',
            ]);
        }

        AuditService::log('produtos.create', "Cadastrou o produto '{$produto->nome}' (Estoque: {$produto->estoque_atual} {$produto->unidade_medida})", $produto);

        return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', 'max:100'],
            'codigo_barras' => ['nullable', 'string', 'max:50'],
            'sku' => ['nullable', 'string', 'max:50'],
            'preco_custo' => ['required', 'numeric', 'min:0'],
            'preco_venda' => ['required', 'numeric', 'min:0'],
            'estoque_minimo' => ['required', 'integer', 'min:0'],
            'unidade_medida' => ['required', 'string', 'max:10'],
            'descricao' => ['nullable', 'string'],
            'ativo' => ['boolean'],
        ]);

        $validated['ativo'] = $request->boolean('ativo', true);

        $produto->update($validated);
        AuditService::log('produtos.update', "Atualizou os dados do produto '{$produto->nome}'", $produto);

        return back()->with('success', 'Produto atualizado com sucesso!');
    }

    public function ajustarEstoque(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);

        $validated = $request->validate([
            'tipo' => ['required', 'in:entrada,saida,ajuste'],
            'quantidade' => ['required', 'integer', 'min:1'],
            'motivo' => ['required', 'string', 'max:255'],
        ], [
            'quantidade.min' => 'A quantidade deve ser maior que zero.',
            'motivo.required' => 'Informe a justificativa do ajuste de estoque.',
        ]);

        if ($validated['tipo'] === 'saida' && $validated['quantidade'] > $produto->estoque_atual) {
            return back()->with('error', "Quantidade para saída ({$validated['quantidade']}) é superior ao estoque atual ({$produto->estoque_atual} {$produto->unidade_medida}).");
        }

        $produto->movimentarEstoque(
            $validated['tipo'],
            (int) $validated['quantidade'],
            $validated['motivo']
        );

        AuditService::log('produtos.ajuste_estoque', "Ajustou estoque do produto '{$produto->nome}' ({$validated['tipo']} de {$validated['quantidade']} {$produto->unidade_medida}): {$validated['motivo']}", $produto);

        return back()->with('success', "Estoque do produto '{$produto->nome}' atualizado com sucesso! Novo saldo: {$produto->estoque_atual} {$produto->unidade_medida}");
    }

    public function toggleStatus($id)
    {
        $produto = Produto::findOrFail($id);
        $produto->ativo = !$produto->ativo;
        $produto->save();

        $statusStr = $produto->ativo ? 'ativado' : 'inativado';
        AuditService::log('produtos.status', "Produto '{$produto->nome}' foi {$statusStr}", $produto);

        return back()->with('success', "Produto {$statusStr} com sucesso.");
    }
}
