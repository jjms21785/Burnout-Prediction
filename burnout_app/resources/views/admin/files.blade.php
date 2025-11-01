@extends('layouts.app')

@section('title', 'Files - Burnalytics')

@section('content')
<!-- Main Content Area -->
<main class="flex-1 overflow-y-auto p-3">
    @if(session('error'))
    <div class="mb-4 rounded-lg p-3 bg-red-100 border border-red-200">
        <p class="text-xs font-medium text-red-800">{{ session('error') }}</p>
    </div>
    @endif

    <!-- Hidden Import File Input -->
    <form id="importForm" action="{{ route('admin.import-data') }}" method="POST" enctype="multipart/form-data" class="hidden">
        @csrf
        <input type="file" id="importFileInput" name="file" accept=".csv,.xlsx,.xls" onchange="handleFileImport()">
    </form>

    <!-- File Manager Section -->
    <div class="rounded-xl px-4 shadow-sm bg-white border border-gray-200">
        <div class="py-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Import, export, and manage your data files</h3>
                </div>
                <div class="flex gap-2">
                    <!-- Import Button -->
                    <button onclick="document.getElementById('importFileInput').click()" class="flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition text-white bg-indigo-500 hover:bg-indigo-600">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Import
                    </button>

                    <!-- Export Button with Dropdown -->
                    <div class="relative">
                        <button id="exportBtn" onclick="toggleExportMenu()" class="flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition text-neutral-800 bg-gray-200 hover:bg-gray-300">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                            Export
                            <svg class="w-3.5 h-3.5 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="exportMenu" class="hidden absolute right-0 mt-1 w-40 bg-white border border-gray-200 rounded-md shadow-lg z-10">
                            <button onclick="exportData('csv')" class="block w-full text-left px-3 py-2 hover:bg-gray-100 text-neutral-800 border-b border-gray-200">
                                <span class="font-medium text-xs">CSV Format</span>
                                <p class="text-[10px] text-gray-500">Comma-separated values</p>
                            </button>
                            <button onclick="exportData('xlsx')" class="block w-full text-left px-3 py-2 hover:bg-gray-100 text-neutral-800">
                                <span class="font-medium text-xs">Excel Format</span>
                                <p class="text-[10px] text-gray-500">Microsoft Excel file</p>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border border-gray-300">
                        <th class="px-3 py-2 text-left text-xs border-r border-gray-300 font-semibold text-neutral-800">File Name</th>
                        <th class="px-3 py-2 text-left text-xs border-r border-gray-300 font-semibold text-neutral-800">Type</th>
                        <th class="px-3 py-2 text-left text-xs border-r border-gray-300 font-semibold text-neutral-800">Size</th>
                        <th class="px-3 py-2 text-left text-xs border-r border-gray-300 font-semibold text-neutral-800">Date Uploaded</th>
                        <th class="px-3 py-2 text-left text-xs border-r border-gray-300 font-semibold text-neutral-800">Actions</th>
                    </tr>
                </thead>
                <tbody id="fileTableBody">
                    @forelse($files as $file)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-3 py-2">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-xs text-neutral-800 font-medium">{{ $file['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-2">
                            @if(strtolower($file['extension']) === 'csv')
                                <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 rounded text-[10px] font-medium">CSV</span>
                            @elseif(in_array(strtolower($file['extension']), ['xlsx', 'xls']))
                                <span class="px-1.5 py-0.5 bg-green-100 text-green-800 rounded text-[10px] font-medium">EXCEL</span>
                            @else
                                <span class="px-1.5 py-0.5 bg-gray-100 text-gray-800 rounded text-[10px] font-medium">{{ strtoupper($file['extension']) }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-xs text-gray-600">
                            {{ number_format($file['size'] / 1024, 2) }} KB
                        </td>
                        <td class="px-3 py-2 text-xs text-gray-600">
                            {{ date('M d, Y H:i', $file['date']) }}
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2">
                                <button onclick="downloadFile('{{ $file['name'] }}')" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium underline transition">
                                    Download
                                </button>
                                <button onclick="deleteFile('{{ $file['name'] }}')" class="text-red-600 hover:text-red-800 text-xs font-medium transition">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-3 py-8 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-xs font-medium text-gray-900">No files</h3>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Pass configuration to JavaScript - must be before vite script -->
<script>
    (function() {
        window.filesConfig = {
            exportRoute: '{{ route("admin.export-data") }}',
            downloadRoute: '{{ route("admin.download-file", ":filename") }}',
            deleteRoute: '{{ route("admin.delete-file", ":filename") }}',
            csrfToken: '{{ csrf_token() }}'
        };
    })();
</script>
@vite('resources/js/files.js')
@endsection
