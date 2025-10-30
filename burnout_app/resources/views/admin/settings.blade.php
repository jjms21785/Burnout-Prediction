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

            @if($errors->any())
            <div class="mb-6 rounded-lg p-4 bg-red-100 border border-red-200">
                <ul class="text-sm text-red-800 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf

                <!-- General Settings -->
                <div class="rounded-xl shadow-sm p-6 mb-6 bg-white border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-neutral-800">General Settings</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-neutral-800">Site Name</label>
                            <input 
                                type="text" 
                                name="site_name" 
                                value="{{ old('site_name', $settings['site_name']) }}"
                                class="w-full px-4 py-2 text-sm rounded-lg border border-gray-200 bg-white text-neutral-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            >
                            <p class="text-xs mt-1 text-gray-500">The name of your application</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-neutral-800">Admin Email</label>
                            <input 
                                type="email" 
                                name="admin_email" 
                                value="{{ old('admin_email', $settings['admin_email']) }}"
                                class="w-full px-4 py-2 text-sm rounded-lg border border-gray-200 bg-white text-neutral-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            >
                            <p class="text-xs mt-1 text-gray-500">Primary contact email for admin notifications</p>
                        </div>
                    </div>
                </div>

                <!-- Display Settings -->
                <div class="rounded-xl shadow-sm p-6 mb-6 bg-white border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-neutral-800">Display Settings</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-neutral-800">Records Per Page</label>
                            <select 
                                name="records_per_page" 
                                class="w-full px-4 py-2 text-sm rounded-lg border border-gray-200 bg-white text-neutral-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            >
                                <option value="10" {{ old('records_per_page', $settings['records_per_page']) == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ old('records_per_page', $settings['records_per_page']) == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ old('records_per_page', $settings['records_per_page']) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ old('records_per_page', $settings['records_per_page']) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                            <p class="text-xs mt-1 text-gray-500">Number of records to display per page in data tables</p>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="rounded-xl shadow-sm p-6 mb-6 bg-white border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-neutral-800">Notification Settings</h3>
                    
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="enable_notifications" 
                                value="1"
                                {{ old('enable_notifications', $settings['enable_notifications']) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-indigo-500 focus:ring-indigo-500"
                            >
                            <span class="ml-3 text-sm font-medium text-neutral-800">Enable Email Notifications</span>
                        </label>
                        <p class="text-xs text-gray-500 ml-7">Receive email notifications for high-risk assessments and system updates</p>
                    </div>
                </div>

                <!-- Data Management Settings -->
                <div class="rounded-xl shadow-sm p-6 mb-6 bg-white border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-neutral-800">Data Management</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-neutral-800">Data Retention Period (days)</label>
                            <input 
                                type="number" 
                                name="data_retention_days" 
                                value="{{ old('data_retention_days', $settings['data_retention_days']) }}"
                                min="30"
                                max="3650"
                                class="w-full px-4 py-2 text-sm rounded-lg border border-gray-200 bg-white text-neutral-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            >
                            <p class="text-xs mt-1 text-gray-500">How long to keep assessment records (30-3650 days)</p>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="rounded-xl shadow-sm p-6 mb-6 bg-white border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-neutral-800">System Information</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <p class="text-xs font-medium mb-1 text-gray-500">Laravel Version</p>
                            <p class="text-sm font-semibold text-neutral-800">{{ app()->version() }}</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <p class="text-xs font-medium mb-1 text-gray-500">PHP Version</p>
                            <p class="text-sm font-semibold text-neutral-800">{{ phpversion() }}</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <p class="text-xs font-medium mb-1 text-gray-500">Environment</p>
                            <p class="text-sm font-semibold text-neutral-800">{{ app()->environment() }}</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <p class="text-xs font-medium mb-1 text-gray-500">Timezone</p>
                            <p class="text-sm font-semibold text-neutral-800">{{ config('app.timezone') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" onclick="window.location.reload()" class="px-5 py-2.5 text-sm font-medium rounded-lg transition text-neutral-800 bg-gray-100 border border-gray-200 hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium rounded-lg transition text-white bg-indigo-500 hover:bg-indigo-600">
                        Save Settings
                    </button>
                </div>
            </form>

            <!-- Danger Zone -->
            <div class="rounded-xl shadow-sm p-6 mt-6 bg-white border-2 border-red-200">
                <h3 class="text-lg font-semibold mb-2 text-red-600">Danger Zone</h3>
                <p class="text-sm mb-4 text-gray-600">Irreversible and destructive actions</p>
                
                <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg bg-red-50">
                    <div>
                        <p class="text-sm font-medium text-neutral-800">Clear All Assessment Data</p>
                        <p class="text-xs mt-1 text-gray-600">This will permanently delete all assessment records from the database</p>
                    </div>
                    <button 
                        type="button"
                        onclick="confirmClearData()"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition text-white bg-red-500 hover:bg-red-600"
                    >
                        Clear Data
                    </button>
                </div>
            </div>
</main>

<script>
function confirmClearData() {
    if (confirm('WARNING: This action will permanently delete ALL assessment data. This cannot be undone. Are you absolutely sure?')) {
        if (confirm('Last confirmation: You are about to delete all assessment records. Type YES in the prompt to continue.')) {
            const confirmation = prompt('Type YES to confirm:');
            if (confirmation === 'YES') {
                alert('Data clearing functionality would be implemented here with proper authorization.');
                {{-- In production, this would call a protected endpoint --}}
                {{-- window.location.href = "{{ route('admin.clear-data') }}"; --}}
            } else {
                alert('Confirmation failed. No data was deleted.');
            }
        }
    }
}
</script>
@endsection

