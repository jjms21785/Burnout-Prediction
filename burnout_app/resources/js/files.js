// Files Management JavaScript

let filesConfig = {
    exportRoute: '',
    downloadRoute: '',
    deleteRoute: '',
    csrfToken: ''
};

function initializeFilesConfig() {
    if (window.filesConfig) {
        filesConfig = {
            ...filesConfig,
            ...window.filesConfig
        };
    }
}

initializeFilesConfig();

// Show loading state
function showLoadingState(message = 'Loading Data, please wait...') {
    const tbody = document.getElementById('fileTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-3 py-8 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mb-2"></div>
                        <p class="text-xs text-gray-600">${message}</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

// Handle file import
function handleFileImport() {
    const form = document.getElementById('importForm');
    const fileInput = document.getElementById('importFileInput');
    
    if (!form || !fileInput) return;
    
    if (fileInput.files.length > 0) {
        // Show warning first
        const warningMessage = 'Are you sure you want to import this file?\n\nConsider Exporting or go Back Up the current data before adding new data.';
        
        if (confirm(warningMessage)) {
            // Show loading state
            showLoadingState('Loading Data, please wait...');
            
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                }
                // Fallback to redirect behavior
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    window.location.reload();
                }
                return null;
            })
            .then(data => {
                // Reload page regardless of success/error (loading will disappear)
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                // Reload page (loading will disappear)
                window.location.reload();
            });
        } else {
            fileInput.value = '';
        }
    }
}

// Export data in specified format
function exportData(format) {
    if (!filesConfig.exportRoute) {
        initializeFilesConfig();
    }
    
    // Create download link directly
    const exportUrl = filesConfig.exportRoute + '?format=' + format;
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = '';
    link.style.display = 'none';
    document.body.appendChild(link);
    
    // Trigger download
    link.click();
    
    // Clean up after download starts
    setTimeout(() => {
        if (link.parentElement) {
            document.body.removeChild(link);
        }
    }, 100);
}

// Download a specific file
function downloadFile(filename) {
    if (!filesConfig.downloadRoute) {
        initializeFilesConfig();
    }
    
    const downloadUrl = filesConfig.downloadRoute.replace(':filename', encodeURIComponent(filename));
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Refresh page after a short delay
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

// Delete a specific file
function deleteFile(filename) {
    if (confirm('Are you sure you want to delete "' + filename + '"? This action cannot be undone.')) {
        // Show loading state
        showLoadingState('Deleting File...');
        
        if (!filesConfig.deleteRoute || !filesConfig.csrfToken) {
            initializeFilesConfig();
        }
        
        const deleteUrl = filesConfig.deleteRoute.replace(':filename', encodeURIComponent(filename));
        const formData = new FormData();
        formData.append('_token', filesConfig.csrfToken);
        
        fetch(deleteUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.location.reload();
        });
    }
}

// Export functions to global scope for onclick handlers
window.handleFileImport = handleFileImport;
window.exportData = exportData;
window.downloadFile = downloadFile;
window.deleteFile = deleteFile;

