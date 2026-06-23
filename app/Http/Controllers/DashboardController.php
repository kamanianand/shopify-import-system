<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\ImportLog;
use App\Models\ImportProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $totalImports = Import::count();
        $pendingImports = Import::where('status', 'pending')->count();
        $processingImports = Import::where('status', 'processing')->count();
        $completedImports = Import::where('status', 'completed')->count();
        $failedImports = Import::where('status', 'failed')->count();

        $totalProducts = ImportProduct::count();
        $successfulProducts = ImportProduct::where('status', 'successful')->count();
        $failedProducts = ImportProduct::where('status', 'failed')->count();
        $pendingProducts = ImportProduct::where('status', 'pending')->count();

        $recentImports = Import::withCount(['products'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentProducts = ImportProduct::with('import')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'totalImports',
            'pendingImports',
            'processingImports',
            'completedImports',
            'failedImports',
            'totalProducts',
            'successfulProducts',
            'failedProducts',
            'pendingProducts',
            'recentImports',
            'recentProducts'
        ));
    }


}
