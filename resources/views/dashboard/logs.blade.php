@extends('layouts.app')

@section('title', 'System Logs')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">System Logs</h2>
        <div class="flex gap-3">
            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
            <button onclick="window.location.reload()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form action="{{ route('dashboard.logs') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Level</label>
                <select name="level" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Levels</option>
                    <option value="info" {{ request('level') == 'info' ? 'selected' : '' }}>Info</option>
                    <option value="error" {{ request('level') == 'error' ? 'selected' : '' }}>Error</option>
                    <option value="warning" {{ request('level') == 'warning' ? 'selected' : '' }}>Warning</option>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search logs..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="{{ route('dashboard.logs') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 text-sm ml-2">
                    <i class="fas fa-times mr-2"></i> Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Recent Logs</h3>
            <span class="text-sm text-gray-600">{{ $logs->total() }} records</span>
        </div>

        @if($logs->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-inbox text-4xl mb-4"></i>
                <p class="text-lg">No logs found</p>
                <p class="text-sm">Logs will appear here when imports are processed</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Import</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Context</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $log->level === 'error' ? 'bg-red-100 text-red-800' : 
                                       ($log->level === 'warning' ? 'bg-yellow-100 text-yellow-800' : 
                                        'bg-blue-100 text-blue-800') }}">
                                    {{ ucfirst($log->level) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($log->import)
                                    <a href="{{ route('imports.show', $log->import_id) }}" class="text-blue-600 hover:text-blue-900">
                                        #{{ $log->import_id }} - {{ Str::limit($log->import->file_name, 20) }}
                                    </a>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 max-w-xs">
                                {{ Str::limit($log->message, 100) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                                @if($log->context)
                                    <button onclick="toggleContext({{ $log->id }})" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <div id="context-{{ $log->id }}" class="hidden mt-2 p-2 bg-gray-100 rounded text-xs overflow-x-auto">
                                        <pre>{{ json_encode($log->context, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Pagination -->
        <div class="px-6 py-4 border-t">
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h4 class="text-sm font-medium text-gray-600">Total Logs</h4>
            <p class="text-2xl font-semibold text-gray-900">{{ $logs->total() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h4 class="text-sm font-medium text-gray-600">Info</h4>
            <p class="text-2xl font-semibold text-blue-600">{{ $logs->where('level', 'info')->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h4 class="text-sm font-medium text-gray-600">Warnings</h4>
            <p class="text-2xl font-semibold text-yellow-600">{{ $logs->where('level', 'warning')->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h4 class="text-sm font-medium text-gray-600">Errors</h4>
            <p class="text-2xl font-semibold text-red-600">{{ $logs->where('level', 'error')->count() }}</p>
        </div>
    </div>

    <!-- Export & Clear Actions -->
    <div class="flex justify-end gap-3 mt-6">
        <button onclick="exportLogs()" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
            <i class="fas fa-download mr-2"></i> Export Logs
        </button>
        <form action="{{ route('dashboard.logs.clear') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to clear all logs?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">
                <i class="fas fa-trash mr-2"></i> Clear All Logs
            </button>
        </form>
    </div>
</div>

<script>
function toggleContext(id) {
    const element = document.getElementById('context-' + id);
    if (element.classList.contains('hidden')) {
        element.classList.remove('hidden');
    } else {
        element.classList.add('hidden');
    }
}

function exportLogs() {
    window.location.href = "{{ route('dashboard.logs.export') }}";
}

// Auto-refresh logs every 30 seconds if there are recent logs
setInterval(function() {
    if (document.querySelector('tbody tr')) {
        window.location.reload();
    }
}, 30000);
</script>
@endsection