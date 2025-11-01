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

// Close export menu
document.addEventListener('DOMContentLoaded', function() {
    initializeFilesConfig();
    document.addEventListener('click', function(event) {
        const exportBtn = document.getElementById('exportBtn');
        const exportMenu = document.getElementById('exportMenu');
        
        if (exportBtn && exportMenu && !exportBtn.contains(event.target) && !exportMenu.contains(event.target)) {
            exportMenu.classList.add('hidden');
        }
    });
});

// Toggle export menu 
function toggleExportMenu() {
    const menu = document.getElementById('exportMenu');
    if (menu) {
        menu.classList.toggle('hidden');
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
            const formData = new FormData(form);
            
            fetch(form.action, {
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
    
    const link = document.createElement('a');
    link.href = filesConfig.exportRoute + '?format=' + format;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Refresh page after a short delay to allow download to start
    setTimeout(() => {
        window.location.reload();
    }, 500);
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
window.toggleExportMenu = toggleExportMenu;
window.handleFileImport = handleFileImport;
window.exportData = exportData;
window.downloadFile = downloadFile;
window.deleteFile = deleteFile;

