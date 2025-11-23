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

function handleFileImport() {
    const form = document.getElementById('importForm');
    const fileInput = document.getElementById('importFileInput');
    
    if (!form || !fileInput) return;
    
    if (fileInput.files.length > 0) {
        const warningMessage = 'Are you sure you want to import this file?\n\nConsider Exporting or go Back Up the current data before adding new data.';
        
        if (confirm(warningMessage)) {
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
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                }
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    window.location.reload();
                }
                return null;
            })
            .then(data => {
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                window.location.reload();
            });
        } else {
            fileInput.value = '';
        }
    }
}

function exportData(format) {
    if (!filesConfig.exportRoute) {
        initializeFilesConfig();
    }
    
    const exportUrl = filesConfig.exportRoute + '?format=' + format;
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = '';
    link.style.display = 'none';
    document.body.appendChild(link);
    
    link.click();
    
    setTimeout(() => {
        if (link.parentElement) {
            document.body.removeChild(link);
        }
    }, 100);
}

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
    
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

function deleteFile(filename) {
    if (confirm('Are you sure you want to delete "' + filename + '"? This action cannot be undone.')) {
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

window.handleFileImport = handleFileImport;
window.exportData = exportData;
window.downloadFile = downloadFile;
window.deleteFile = deleteFile;
