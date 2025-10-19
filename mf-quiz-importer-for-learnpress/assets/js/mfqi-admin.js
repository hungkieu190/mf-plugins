/**
 * MF Quiz Importer Admin JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Process tracking elements
    const loadingOverlay = document.getElementById('mfqi-loading-overlay');
    const loadingText = document.getElementById('mfqi-loading-text');
    const loadingProgress = document.getElementById('mfqi-loading-progress');
    
    // Basic progress update function
    window.updateProgress = function(current, total) {
        if (!loadingProgress) return;
        
        // Calculate percentage
        const percentage = total > 0 ? Math.round((current / total) * 100) : 0;
        
        // Update progress text
        loadingProgress.textContent = `Processing row ${current} of ${total} (${percentage}%)`;
        
        // Update progress bar if exists
        const progressFill = document.getElementById('mfqi-progress-fill');
        if (progressFill) {
            progressFill.style.width = percentage + '%';
        }
    };
    
    // Update loading text
    window.updateLoadingText = function(text) {
        if (loadingText) loadingText.textContent = text;
    };
    
    
    // Handle form submission
    document.addEventListener('submit', function(e) {
        if (e.target.id === 'mfqi-import-form') {
            const fileInput = document.getElementById('mfqi_file');
            if (!fileInput || !fileInput.files.length) {
                e.preventDefault();
                alert('Please select a file to import');
                return false;
            }

            if (loadingOverlay) {
                loadingOverlay.style.display = 'flex';
            }
        }
    });
});