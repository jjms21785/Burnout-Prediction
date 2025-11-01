@extends('layouts.app')

@section('title', 'Settings - Burnalytics')

@section('content')
<!-- Main Content Area -->
<main class="flex-1 overflow-y-auto p-3">
    @if(session('success'))
    <div class="mb-6 rounded-lg p-4 bg-green-100 border border-green-200">
        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 rounded-lg p-4 bg-red-100 border border-red-200">
        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
    </div>
    @endif

    <div class="rounded-xl shadow-sm p-6 bg-white border border-gray-200">
        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg bg-white">
            <div>
                <p class="text-sm font-medium text-neutral-800">Clear All Data</p>
                <p class="text-xs mt-1 text-gray-600">This will permanently delete all assessment records from the database. This will erase all data on the system.</p>
            </div>
            <form id="clearDataForm" method="POST" action="{{ route('admin.clear-all-data') }}" style="display: inline;">
                @csrf
                <button 
                    type="button"
                    onclick="confirmClearData()"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition text-white bg-red-500 hover:bg-red-600"
                >
                    Clear All Data
                </button>
            </form>
        </div>
    </div>
</main>

<script>
function confirmClearData() {
    if (confirm('WARNING: This action will permanently delete ALL a data. This cannot be undone. Are you absolutely sure?')) {
        const userInput = prompt('Last confirmation: Type \'Delete\' to remove all data');
        if (userInput === 'Delete') {
            document.getElementById('clearDataForm').submit();
        }
    }
}
</script>
@endsection
