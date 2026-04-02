<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Renderiza a página de escolha de filtros
     */
    public function index()
    {
        return Inertia::render('Reports/Index', [
            'suppliers' => Supplier::select('id', 'company_name')->orderBy('company_name')->get()
        ]);
    }

    /**
     * Gera o PDF de Produtos
     */
    public function products(Request $request)
    {
        $type = $request->query('type', 'sintetico');
        
        // O toArray() resolve as imagens e fornecedores imediatamente
        $products = Product::with(['supplier', 'images'])->get()->toArray();

        $data = [
            'products' => $products,
            'type'     => $type,
            'title'    => 'Relatório de Produtos - ' . strtoupper($type),
            'date'     => now()->format('d/m/Y H:i')
        ];

        $pdf = Pdf::loadView('reports.products', $data);
        
        $pdf->setPaper('a4', $type === 'analitico' ? 'landscape' : 'portrait');
        
        // Importante: isRemoteEnabled deve ser true para aceitar base64 e links
        $pdf->getDomPDF()->set_option("isRemoteEnabled", true);
        $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
        $pdf->getDomPDF()->set_option("chroot", public_path());

        return $pdf->stream('relatorio.pdf');
    }

    /**
     * Gera o PDF de Clientes com campos dinâmicos
     */
    public function clients(Request $request)
    {
        $fields = $request->query('fields', []);
        $documentType = $request->query('document_type');
        $status = $request->query('status');

        $query = Client::with(['user', 'addresses', 'sales'])
            ->withSum('sales as total_purchases', 'total_amount');

        if ($documentType) {
            $query->where('document_type', $documentType);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status == '1');
        }

        $clients = $query->get();

        $data = [
            'clients' => $clients,
            'fields'  => $fields,
            'title'   => 'Relatório de Clientes',
            'date'    => now()->format('d/m/Y H:i')
        ];

        $pdf = Pdf::loadView('reports.clients', $data);
        
        // Se tiver muitos campos, usa paisagem
        $paperOrientation = count($fields) > 5 ? 'landscape' : 'portrait';
        $pdf->setPaper('a4', $paperOrientation);
        
        $pdf->getDomPDF()->set_option("isRemoteEnabled", true);
        $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
        $pdf->getDomPDF()->set_option("chroot", public_path());

        return $pdf->stream('relatorio_clientes.pdf');
    }
}