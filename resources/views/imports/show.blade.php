@extends('layouts.app')

@section('title', 'Import Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $import->file_name }}</h2>
                <div class="mt-2 flex items-center gap-4 text-sm text-gray-600">
                    <span>Total: {{ $import->total_records }}</span>
                    <span>Processed: {{ $import->processed_records }}</span>
                    <span class="text-green-600">Successful: {{ $import->successful_records }}</span>
                    <span class="text-red-600">Failed: {{ $import->failed_records }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $import->status_badge }}">
                    {{ ucfirst($import->status) }}
                </span>
                @if($import->failed_records > 0)
                    <form action="{{ route('imports.retry', $import) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                            <i class="fas fa-redo mr-1"></i> Retry Failed
                        </button>
                    </form>
                @endif
                <form action="{{ route('imports.destroy', $import) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
        
        <div class="mt-4">
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $import->progress }}%"></div>
            </div>
            <span class="text-xs text-gray-600">{{ $import->progress }}% Complete</span>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Products</h3>
            <span class="text-sm text-gray-600">{{ $products->total() }} records</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Handle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shopify ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($products as $product)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $product->handle }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $product->title }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($product->variant_price, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->status_badge }}">
                                {{ ucfirst($product->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $product->shopify_product_id ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-red-600 max-w-xs">
                            {{ $product->error_message ? Str::limit($product->error_message, 50) : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Logs -->
    @if($logs->isNotEmpty())
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Logs</h3>
        </div>
        <div class="p-6 max-h-96 overflow-y-auto">
            @foreach($logs as $log)
            <div class="mb-2 text-sm">
                <span class="text-gray-500">{{ $log->created_at->format('H:i:s') }}</span>
                <span class="px-2 py-0.5 text-xs rounded-full {{ $log->level === 'error' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ $log->level }}
                </span>
                <span class="text-gray-700">{{ $log->message }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection