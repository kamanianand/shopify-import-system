<?php

namespace App\Jobs;

use App\Models\Import;
use App\Models\ImportProduct;
use App\Models\ImportLog;
use App\Services\ShopifyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessCsvImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 3;

    protected Import $import;

    /**
     * Create a new job instance.
     */
    public function __construct(Import $import)
    {
        $this->import = $import;
    }

    /**
     * Execute the job.
     */
    public function handle(ShopifyService $shopifyService)
    {
        try {
            $this->import->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            $this->logInfo('Starting import processing', [
                'total_products' => $this->import->total_records
            ]);

            $products = $this->import->products()->where('status', 'pending')->get();

            foreach ($products as $product) {
                try {
                    $this->processProduct($product, $shopifyService);
                } catch (\Exception $e) {
                    $this->handleProductError($product, $e);
                }

                // Update progress
                $this->import->increment('processed_records');
            }

            $this->completeImport();
        } catch (\Exception $e) {
            $this->failImport($e);
            throw $e;
        }
    }

    protected function processProduct(ImportProduct $product, ShopifyService $shopifyService)
    {
        $this->logInfo("Processing product: {$product->handle}", [
            'product_id' => $product->id
        ]);

        $productData = $this->prepareProductData($product);

        try {
            // Check if product exists
            $existingProduct = $shopifyService->getProductByHandle($product->handle);

            if ($existingProduct) {
                // Update existing product
                $result = $shopifyService->updateProduct($existingProduct['id'], $productData);
                $shopifyId = $existingProduct['id'];
                $action = 'updated';
            } else {
                // Create new product
                $result = $shopifyService->createProduct($productData);
                $shopifyId = $result['id'] ?? null;
                $action = 'created';
            }

            // Update product record
            $product->update([
                'shopify_product_id' => $shopifyId,
                'status' => 'successful',
                'processed_at' => now(),
            ]);

            $this->import->increment('successful_records');

            $this->logInfo("Product {$action} successfully: {$product->handle}", [
                'shopify_id' => $shopifyId
            ]);

        } catch (\Exception $e) {
            throw new \Exception("Shopify API error: " . $e->getMessage());
        }
    }

    protected function prepareProductData(ImportProduct $product): array
    {
        return [
            'title' => $product->title,
            'body_html' => $product->body_html,
            'vendor' => $product->vendor,
            'product_type' => $product->product_type,
            'tags' => $product->tags,
            'published' => $product->published,
            'variant_sku' => $product->variant_sku,
            'variant_price' => $product->variant_price,
            'variant_compare_at_price' => $product->variant_compare_at_price,
            'variant_requires_shipping' => $product->variant_requires_shipping,
            'variant_taxable' => $product->variant_taxable,
            'variant_inventory_qty' => $product->variant_inventory_qty,
            'variant_inventory_policy' => $product->variant_inventory_policy,
            'variant_weight' => $product->variant_weight,
            'variant_weight_unit' => $product->variant_weight_unit,
            'image_src' => $product->image_src,
            'image_position' => $product->image_position,
            'image_alt_text' => $product->image_alt_text,
        ];
    }

    protected function handleProductError(ImportProduct $product, \Exception $e)
    {
        $product->update([
            'status' => 'failed',
            'error_message' => $e->getMessage(),
            'processed_at' => now(),
        ]);

        $this->import->increment('failed_records');

        $this->logError("Product failed: {$product->handle}", [
            'product_id' => $product->id,
            'error' => $e->getMessage()
        ]);
    }

    protected function completeImport()
    {
        $status = $this->import->failed_records > 0 ? 'partially_completed' : 'completed';

        $this->import->update([
            'status' => $status,
            'completed_at' => now(),
        ]);

        $this->logInfo('Import completed', [
            'successful' => $this->import->successful_records,
            'failed' => $this->import->failed_records
        ]);
    }

    protected function failImport(\Exception $e)
    {
        $this->import->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);

        $this->logError('Import failed', [
            'error' => $e->getMessage()
        ]);
    }

    protected function logInfo(string $message, array $context = [])
    {
        ImportLog::create([
            'import_id' => $this->import->id,
            'level' => 'info',
            'message' => $message,
            'context' => $context,
        ]);
        Log::info("Import {$this->import->id}: {$message}", $context);
    }

    protected function logError(string $message, array $context = [])
    {
        ImportLog::create([
            'import_id' => $this->import->id,
            'level' => 'error',
            'message' => $message,
            'context' => $context,
        ]);
        Log::error("Import {$this->import->id}: {$message}", $context);
    }
}
