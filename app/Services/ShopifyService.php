<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    protected string $storeUrl;
    protected string $accessToken;
    protected string $apiVersion;

    public function __construct()
    {
        $this->storeUrl = config('shopify.store_url');
        $this->accessToken = config('shopify.access_token');
        $this->apiVersion = config('shopify.api_version', '2024-01');
    }

    public function createProduct(array $productData): array
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->accessToken,
            'Content-Type' => 'application/json',
        ])->post($this->getEndpoint('products'), [
            'product' => $this->formatProductData($productData)
        ]);

        if (!$response->successful()) {
            throw new \Exception('Shopify API Error: ' . $response->body());
        }

        return $response->json()['product'] ?? [];
    }

    public function updateProduct(string $productId, array $productData): array
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->accessToken,
            'Content-Type' => 'application/json',
        ])->put($this->getEndpoint("products/{$productId}"), [
            'product' => $this->formatProductData($productData)
        ]);

        if (!$response->successful()) {
            throw new \Exception('Shopify API Error: ' . $response->body());
        }

        return $response->json()['product'] ?? [];
    }

    public function getProductByHandle(string $handle): ?array
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->accessToken,
        ])->get($this->getEndpoint('products'), [
            'handle' => $handle,
            'limit' => 1
        ]);

        if (!$response->successful()) {
            throw new \Exception('Shopify API Error: ' . $response->body());
        }

        $products = $response->json()['products'] ?? [];
        return $products[0] ?? null;
    }

    protected function formatProductData(array $data): array
    {
        return [
            'title' => $data['title'] ?? '',
            'body_html' => $data['body_html'] ?? '',
            'vendor' => $data['vendor'] ?? '',
            'product_type' => $data['product_type'] ?? '',
            'tags' => $data['tags'] ?? '',
            'published' => $data['published'] ?? true,
            'variants' => [
                [
                    'sku' => $data['variant_sku'] ?? '',
                    'price' => (string)($data['variant_price'] ?? '0.00'),
                    'compare_at_price' => $data['variant_compare_at_price'] ? (string)$data['variant_compare_at_price'] : null,
                    'requires_shipping' => $data['variant_requires_shipping'] ?? true,
                    'taxable' => $data['variant_taxable'] ?? true,
                    'inventory_quantity' => $data['variant_inventory_qty'] ?? 0,
                    'inventory_policy' => $data['variant_inventory_policy'] ?? 'deny',
                    'fulfillment_service' => $data['variant_fulfillment_service'] ?? 'manual',
                    'weight' => (float)($data['variant_weight'] ?? 0),
                    'weight_unit' => $data['variant_weight_unit'] ?? 'kg',
                ]
            ]
        ];
    }

    protected function getEndpoint(string $path): string
    {
        return "https://{$this->storeUrl}/admin/api/{$this->apiVersion}/{$path}.json";
    }
}