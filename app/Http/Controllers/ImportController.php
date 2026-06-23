<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\ImportProduct;
use App\Services\CsvParserService;
use App\Jobs\ProcessCsvImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function index()
    {
        $imports = Import::withCount(['products'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('imports.index', compact('imports'));
    }

    public function create()
    {
        return view('imports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        try {
            $file = $request->file('csv_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('imports', $fileName, 'public');

            // Parse CSV
            $parser = app(CsvParserService::class);
            $products = $parser->parse($path);

            // Create import record
            $import = Import::create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'total_records' => count($products),
                'status' => 'pending'
            ]);

            // Save products
            foreach ($products as $productData) {
                $rowNumber = $productData['_row_number'] ?? 0;
                unset($productData['_row_number']);
                
                ImportProduct::create([
                    'import_id' => $import->id,
                    ...$productData,
                    'status' => 'pending'
                ]);
            }

            // Dispatch job for background processing
            ProcessCsvImportJob::dispatch($import);

            return redirect()->route('imports.show', $import)
                ->with('success', 'CSV file uploaded successfully! Processing started in the background.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process CSV: ' . $e->getMessage());
        }
    }

    public function show(Import $import)
    {
        $products = $import->products()->paginate(20);
        $logs = $import->logs()->latest()->limit(50)->get();
        
        return view('imports.show', compact('import', 'products', 'logs'));
    }

    public function retry(Import $import)
    {
        $failedProducts = $import->products()->where('status', 'failed')->get();
        
        if ($failedProducts->isEmpty()) {
            return back()->with('info', 'No failed products to retry.');
        }

        // Reset failed products to pending
        $failedProducts->each(function ($product) {
            $product->update([
                'status' => 'pending',
                'error_message' => null,
                'processed_at' => null
            ]);
        });

        $import->update([
            'status' => 'pending',
            'failed_records' => 0,
            'processed_records' => 0,
            'successful_records' => 0,
            'completed_at' => null
        ]);

        ProcessCsvImportJob::dispatch($import);

        return back()->with('success', 'Retry started for failed products.');
    }

    public function destroy(Import $import)
    {
        // Delete file
        if (Storage::disk('public')->exists($import->file_path)) {
            Storage::disk('public')->delete($import->file_path);
        }

        $import->delete();

        return redirect()->route('imports.index')
            ->with('success', 'Import deleted successfully.');
    }
}
