<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use League\Csv\Reader;

class CsvParserService
{
    protected array $requiredColumns = [
        'Handle', 'Title', 'Body HTML', 'Vendor', 'Product Type',
        'Tags', 'Published', 'Variant SKU', 'Variant Price'
    ];

    public function parse(string $filePath): array
    {
        try {
            $csv = Reader::createFromPath(storage_path('app/public/' . $filePath), 'r');
            $csv->setHeaderOffset(0);
            $csv->setDelimiter(',');

            $headers = $csv->getHeader();
            $this->validateHeaders($headers);

            $records = [];
            foreach ($csv as $index => $row) {
                $rowNumber = $index + 2;
                try {
                    $records[] = $this->validateRow($row, $rowNumber);
                } catch (\Exception $e) {
                    Log::warning("Invalid row {$rowNumber}: " . $e->getMessage());
                    throw $e;
                }
            }

            return $records;
        } catch (\Exception $e) {
            Log::error("CSV parsing error: " . $e->getMessage());
            throw new \RuntimeException('Failed to parse CSV file: ' . $e->getMessage());
        }
    }

    protected function validateHeaders(array $headers): void
    {
        $missing = array_diff($this->requiredColumns, $headers);
        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                'Missing required columns: ' . implode(', ', $missing)
            );
        }
    }

    protected function validateRow(array $row, int $rowNumber): array
    {
        // Required fields
        if (empty($row['Handle']) || empty($row['Title']) || empty($row['Variant Price'])) {
            throw new \InvalidArgumentException('Required fields missing');
        }

        return [
            'handle' => trim($row['Handle']),
            'title' => trim($row['Title']),
            'body_html' => $row['Body HTML'] ?? '',
            'vendor' => trim($row['Vendor'] ?? ''),
            'product_type' => trim($row['Product Type'] ?? ''),
            'tags' => trim($row['Tags'] ?? ''),
            'published' => $this->parseBoolean($row['Published'] ?? 'TRUE'),
            'variant_sku' => trim($row['Variant SKU'] ?? ''),
            'variant_price' => (float) $this->cleanPrice($row['Variant Price']),
            'variant_compare_at_price' => !empty($row['Variant Compare At Price']) ? 
                (float) $this->cleanPrice($row['Variant Compare At Price']) : null,
            'variant_requires_shipping' => $this->parseBoolean($row['Variant Requires Shipping'] ?? 'TRUE'),
            'variant_taxable' => $this->parseBoolean($row['Variant Taxable'] ?? 'TRUE'),
            'variant_inventory_tracker' => !empty($row['Variant Inventory Tracker']) ? trim($row['Variant Inventory Tracker']) : null,
            'variant_inventory_qty' => (int) ($row['Variant Inventory Qty'] ?? 0),
            'variant_inventory_policy' => trim($row['Variant Inventory Policy'] ?? 'deny'),
            'variant_fulfillment_service' => trim($row['Variant Fulfillment Service'] ?? 'manual'),
            'variant_weight' => (float) ($row['Variant Weight'] ?? 0),
            'variant_weight_unit' => trim($row['Variant Weight Unit'] ?? 'kg'),
            'image_src' => !empty($row['Image Src']) ? trim($row['Image Src']) : null,
            'image_position' => (int) ($row['Image Position'] ?? 1),
            'image_alt_text' => trim($row['Image Alt Text'] ?? ''),
            '_row_number' => $rowNumber
        ];
    }

    protected function parseBoolean(string $value): bool
    {
        return strtoupper(trim($value)) === 'TRUE';
    }

    protected function cleanPrice(string $value): string
    {
        // Remove currency symbols and extra spaces
        return preg_replace('/[^0-9.]/', '', trim($value));
    }
}