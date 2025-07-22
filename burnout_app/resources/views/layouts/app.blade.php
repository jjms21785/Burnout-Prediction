<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Burnalytix - Academic Burnout Predictor')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-b from-green-50 to-white">
    @if(request()->routeIs('admin.*'))
    <!-- Sidebar for admin pages only -->
    <div class="flex min-h-screen">
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col py-8 px-4 space-y-6 items-center">
            <div class="mb-8 text-center w-full">
                <h2 class="text-2xl font-bold text-green-700">Burnalytix</h2>
                <p class="text-sm text-green-600">@yield('subtitle', 'Academic Burnout Predictor')</p>
            </div>
            <nav class="flex flex-col space-y-4 mt-16 w-full items-center">
                <a href="{{ route('admin.dashboard') }}" id="sidebarDashboardBtn" data-section="dashboard" class="sidebar-btn w-full text-center px-4 py-2 rounded-lg font-medium border-2 border-green-200 text-green-700 bg-white hover:bg-green-50 hover:border-green-400 focus:outline-none focus:bg-green-100 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 relative overflow-hidden block shadow-sm">
                    Dashboard
                </a>
                <a href="#" id="sidebarDataMonitoringBtn" data-section="data-monitoring" class="sidebar-btn w-full text-center px-4 py-2 rounded-lg font-medium border-2 border-green-200 text-green-700 bg-white hover:bg-green-50 hover:border-green-400 focus:outline-none focus:bg-green-100 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 relative overflow-hidden block shadow-sm">
                    Data Monitoring
                </a>
                <button id="importExportBtn" class="sidebar-btn w-full text-center px-4 py-2 rounded-lg font-medium border-2 border-green-200 text-green-700 bg-white hover:bg-green-50 hover:border-green-400 focus:outline-none focus:bg-green-100 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 relative overflow-hidden block shadow-sm ">
                    Import/Export
                </button>
                <a href="{{ route('assessment.index') }}" class="sidebar-btn w-full text-center px-4 py-2 rounded-lg font-medium border-2 border-green-200 text-green-700 bg-white hover:bg-green-50 hover:border-green-400 focus:outline-none focus:bg-green-100 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 relative overflow-hidden block shadow-sm">
                    Predict
                </a>
                <button class="sidebar-btn w-full text-center px-4 py-2 rounded-lg font-medium border-2 border-green-200 text-green-700 bg-white hover:bg-green-50 hover:border-green-400 focus:outline-none focus:bg-green-100 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 relative overflow-hidden block shadow-sm">
                    Settings
                </button>
            </nav>
        </aside>
        <div class="flex-1 flex flex-col min-h-screen">
            <!-- Header removed for admin pages -->
            <!-- Main Content -->
            <main class="flex-1">
                <div id="adminSectionContainer">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <!-- Import/Export Modal -->
    <div id="importExportModal" class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm" style="background: transparent; display:none;">
        <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-8 relative flex flex-col items-center justify-center">
            <button id="closeImportExportModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
            <h2 class="text-2xl font-bold text-black-700 mb-6 text-center w-full">Import/Export Data</h2>
            <div class="flex flex-col items-center w-full gap-8">
                <!-- Import Data -->
                <div class="flex flex-col items-center w-full">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Import Data</h3>
                    <form action="{{ route('admin.import') }}" method="POST" enctype="multipart/form-data" id="importForm" class="flex flex-col items-center w-full">
                        @csrf
                        <input type="file" name="import_file" accept=".csv,.xlsx" class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-200 mb-4 text-center" />
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors w-40 mx-auto">Upload</button>
                    </form>
                </div>
                <!-- Export Data -->
                <div class="flex flex-col items-center w-full">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Export Data</h3>
                    <button type="button" class="bg-green-600 text-white px-4 py-2 rounded-lg font-medium w-40 mx-auto" disabled>Download</button>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- No sidebar for non-admin pages -->
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Burnalytix</h1>
                        <p class="text-sm text-green-600">@yield('subtitle', 'Academic Burnout Predictor')</p>
                    </div>
                </div>
                <nav class="flex items-center space-x-8">
                    <a href="#" class="text-gray-600 hover:text-green-600 transition-all duration-200 transform hover:scale-105">Home</a>
                    <a href="#" class="text-gray-600 hover:text-green-600 transition-all duration-200 transform hover:scale-105">About</a>
                    @if(request()->routeIs('home') || request()->routeIs('assessment.*'))
                        <a href="{{ route('admin.dashboard') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg font-medium shadow hover:bg-green-700 transition-all duration-200 transform hover:scale-105">Dashboard</a>
                    @endif
                </nav>
            </div>
        </div>
    </header>
    <!-- Main Content -->
    <main>
        @yield('content')
    </main>
    @endif
    <style>
.ripple {
    position: absolute;
    border-radius: 50%;
    transform: scale(0);
    animation: ripple 0.6s linear;
    background-color: rgba(16, 185, 129, 0.3); /* green-500 with opacity */
    pointer-events: none;
    z-index: 10;
}
@keyframes ripple {
    to {
        transform: scale(2.5);
        opacity: 0;
    }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar ripple effect for engagement
    function createRipple(event) {
        const button = event.currentTarget;
        const circle = document.createElement('span');
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;
        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${event.clientX - button.getBoundingClientRect().left - radius}px`;
        circle.style.top = `${event.clientY - button.getBoundingClientRect().top - radius}px`;
        circle.classList.add('ripple');
        const ripple = button.getElementsByClassName('ripple')[0];
        if (ripple) {
            ripple.remove();
        }
        button.appendChild(circle);
    }
    document.querySelectorAll('.sidebar-btn').forEach(btn => {
        btn.addEventListener('click', createRipple);
    });

    const importExportBtn = document.getElementById('importExportBtn');
    const importExportModal = document.getElementById('importExportModal');
    const closeImportExportModal = document.getElementById('closeImportExportModal');
    if(importExportBtn && importExportModal && closeImportExportModal) {
        importExportBtn.addEventListener('click', function() {
            importExportModal.style.display = 'flex';
        });
        closeImportExportModal.addEventListener('click', function() {
            importExportModal.style.display = 'none';
        });
        importExportModal.addEventListener('click', function(e) {
            if(e.target === importExportModal) {
                importExportModal.style.display = 'none';
            }
        });
    }
});
</script>
</body>
</html>