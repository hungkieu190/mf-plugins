<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
  <h1>MF Quiz Importer for LearnPress</h1>
  <p>Upload CSV/XLSX file according to the sample format to import quiz & questions to LearnPress. CSV should use <code>UTF-8</code> encoding.</p>

  <div class="mfqi-download-section">
    <p><strong>Download Sample Files:</strong></p>
    <p>
      <a href="<?php echo esc_url(add_query_arg(['mfqi_action' => 'download_sample', 'type' => 'csv'], admin_url('admin.php?page=mfqi-import'))); ?>"
         class="button button-primary mfqi-download-btn">
        📥 Download CSV Sample
      </a>
    </p>
  </div>

  <form method="post" enctype="multipart/form-data" id="mfqi-import-form">
    <?php wp_nonce_field('mfqi_import_nonce'); ?>
    <input type="hidden" name="mfqi_action" value="import" />

    <!-- Loading overlay -->
    <div id="mfqi-loading-overlay" style="display: none;">
      <div class="mfqi-loading-content">
        <div class="mfqi-spinner"></div>
        <p id="mfqi-loading-text">Importing quiz data...</p>
        <p id="mfqi-loading-progress">Processing row 1...</p>
        <p id="mfqi-loading-time">Estimated time remaining: calculating...</p>
        <div class="mfqi-progress-bar">
          <div class="mfqi-progress-fill" id="mfqi-progress-fill"></div>
        </div>
        <p class="mfqi-loading-subtitle">Please do not close this page</p>
      </div>
    </div>

    <!-- Toast notifications -->
    <div id="mfqi-toast-container"></div>

    <table class="form-table" role="presentation">
      <tr>
        <th scope="row"><label for="mfqi_file">File (CSV/XLSX)</label></th>
        <td>
          <div class="mfqi-drag-drop-area" id="dragDropArea">
            <div class="mfqi-upload-zone">
              <div class="mfqi-upload-icon">📁</div>
              <div class="mfqi-upload-text">
                <strong>Drag and drop your CSV or XLSX file here</strong><br>
                <span>or</span>
              </div>
              <input type="file" id="mfqi_file" name="mfqi_file" class="mfqi-file-input" required />
              <button type="button" class="mfqi-browse-btn" id="browseBtn">Browse Files</button>
              <div class="mfqi-file-info" id="fileInfo" style="display: none;">
                <span class="mfqi-file-name" id="fileName"></span>
                <button type="button" class="mfqi-remove-file" id="removeFile">✕</button>
              </div>
            </div>
          </div>
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="delimiter">Delimiter for answers/correct columns</label></th>
        <td>
          <input type="text" id="delimiter" name="delimiter" value=";" size="2" />
          <p class="description">Example: <code>React; Vue; Angular</code></p>
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="dry_run">Dry run</label></th>
        <td><label><input type="checkbox" id="dry_run" name="dry_run" value="1" /> Test run (no database writes)</label></td>
      </tr>
      <tr>
        <th scope="row"><label for="debug_delay">Debug delay</label></th>
        <td><label><input type="checkbox" id="debug_delay" name="debug_delay" value="1" checked /> Add 3 second delay between rows (for debugging)</label></td>
      </tr>
    </table>

    <?php submit_button('Import Now', 'primary', 'mfqi-submit-btn'); ?>
  </form>

  <h2>CSV Format & Columns</h2>
  <p><strong>CSV Columns (required):</strong></p>
  <pre>course_id,section_name,quiz_title,question_type,question_text,answers,correct,mark</pre>

  <h3>Column Descriptions:</h3>
  <table class="wp-list-table widefat fixed striped">
    <thead>
      <tr>
        <th>Column</th>
        <th>Description</th>
        <th>Example</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><code>course_id</code></td>
        <td>Course ID to attach quiz to (0 for standalone quiz)</td>
        <td>123</td>
      </tr>
      <tr>
        <td><code>section_name</code></td>
        <td>Section name in course (leave empty for standalone)</td>
        <td>Chapter 1</td>
      </tr>
      <tr>
        <td><code>quiz_title</code></td>
        <td>Title of the quiz</td>
        <td>JavaScript Basics Quiz</td>
      </tr>
      <tr>
        <td><code>question_type</code></td>
        <td>Type: single, multiple, true_false, fill</td>
        <td>single</td>
      </tr>
      <tr>
        <td><code>question_text</code></td>
        <td>The question content</td>
        <td>What does JS stand for?</td>
      </tr>
      <tr>
        <td><code>answers</code></td>
        <td>Answer options separated by delimiter (;)</td>
        <td>Java Script; Just Script; JScript</td>
      </tr>
      <tr>
        <td><code>correct</code></td>
        <td>Correct answer(s) separated by delimiter (;)</td>
        <td>Java Script</td>
      </tr>
      <tr>
        <td><code>mark</code></td>
        <td>Points for this question</td>
        <td>1</td>
      </tr>
    </tbody>
  </table>
</div>

<style>
/* Drag and Drop Styles */
.mfqi-drag-drop-area {
  margin: 20px 0;
}

.mfqi-upload-zone {
  border: 2px dashed #ddd;
  border-radius: 8px;
  padding: 40px 20px;
  text-align: center;
  background: #fafafa;
  transition: all 0.3s ease;
  position: relative;
  cursor: pointer;
}

.mfqi-upload-zone:hover {
  border-color: #007cba;
  background: #f0f8ff;
}

.mfqi-upload-zone.drag-over {
  border-color: #007cba;
  background: #e7f3ff;
  transform: scale(1.02);
}

.mfqi-upload-icon {
  font-size: 48px;
  margin-bottom: 15px;
  opacity: 0.7;
}

.mfqi-upload-text {
  color: #666;
  margin-bottom: 20px;
}

.mfqi-upload-text strong {
  display: block;
  margin-bottom: 5px;
  color: #333;
}

.mfqi-upload-text span {
  color: #999;
  font-style: italic;
}

.mfqi-file-input {
  position: absolute;
  left: -9999px;
  visibility: hidden;
}

.mfqi-browse-btn {
  background: #007cba;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: background-color 0.3s ease;
}

.mfqi-browse-btn:hover {
  background: #005a87;
}

.mfqi-file-info {
  margin-top: 15px;
  padding: 10px;
  background: white;
  border-radius: 6px;
  border: 1px solid #ddd;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.mfqi-file-name {
  color: #333;
  font-weight: 500;
}

.mfqi-remove-file {
  background: #dc3545;
  color: white;
  border: none;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.mfqi-remove-file:hover {
   background: #c82333;
 }

/* Download Section Styles */
.mfqi-download-section {
   background: #f8f9fa;
   border: 1px solid #e9ecef;
   border-radius: 8px;
   padding: 20px;
   margin: 20px 0;
}

.mfqi-download-btn {
   margin-right: 10px;
   margin-bottom: 10px;
   font-size: 14px;
   padding: 8px 16px;
   text-decoration: none;
   display: inline-block;
}

.mfqi-download-btn:hover {
   text-decoration: none;
}

/* Sample Data Table Styles */
.wp-list-table {
   margin-top: 15px;
}

.wp-list-table th {
   background: #f8f9fa;
   font-weight: 600;
   color: #333;
}

.wp-list-table td {
   vertical-align: top;
   padding: 12px;
}

.wp-list-table code {
   background: #e9ecef;
   padding: 2px 6px;
   border-radius: 3px;
   font-size: 13px;
}

.wp-list-table tr:nth-child(even) {
   background: #f8f9fa;
}

/* Loading Overlay Styles */
#mfqi-loading-overlay {
   position: fixed;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
   background: rgba(0, 0, 0, 0.7);
   z-index: 9999;
   display: flex;
   align-items: center;
   justify-content: center;
}

.mfqi-loading-content {
   background: white;
   padding: 40px;
   border-radius: 12px;
   text-align: center;
   box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
   max-width: 400px;
   width: 90%;
}

.mfqi-spinner {
   width: 50px;
   height: 50px;
   border: 4px solid #f3f3f3;
   border-top: 4px solid #007cba;
   border-radius: 50%;
   animation: mfqi-spin 1s linear infinite;
   margin: 0 auto 20px;
}

@keyframes mfqi-spin {
   0% { transform: rotate(0deg); }
   100% { transform: rotate(360deg); }
}

.mfqi-loading-text {
   font-size: 18px;
   font-weight: 600;
   color: #333;
   margin: 0 0 10px 0;
}

.mfqi-loading-subtitle {
    font-size: 14px;
    color: #666;
    margin: 0;
    font-style: italic;
}

.mfqi-loading-progress {
    font-size: 16px;
    color: #007cba;
    margin: 10px 0 5px 0;
    font-weight: 500;
}

.mfqi-loading-time {
    font-size: 14px;
    color: #666;
    margin: 0 0 15px 0;
    font-style: italic;
}

.mfqi-progress-bar {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    margin: 15px 0;
    overflow: hidden;
}

.mfqi-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #007cba 0%, #005a87 100%);
    border-radius: 4px;
    width: 0%;
    transition: width 0.3s ease;
}

/* Disabled form state */
.mfqi-form-disabled {
   opacity: 0.6;
   pointer-events: none;
}

.mfqi-form-disabled .mfqi-upload-zone {
   cursor: not-allowed !important;
 }

/* Toast Notification Styles */
#mfqi-toast-container {
   position: fixed;
   top: 20px;
   right: 20px;
   z-index: 10000;
   max-width: 400px;
}

.mfqi-toast {
   background: white;
   border-radius: 8px;
   box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
   margin-bottom: 10px;
   padding: 16px 20px;
   display: flex;
   align-items: flex-start;
   gap: 12px;
   animation: mfqi-toast-slide-in 0.3s ease-out;
   border-left: 4px solid;
   font-size: 14px;
   line-height: 1.4;
}

.mfqi-toast.success {
   border-left-color: #00a32a;
   background: #f8fff9;
}

.mfqi-toast.error {
   border-left-color: #d63638;
   background: #fff5f5;
}

.mfqi-toast.warning {
   border-left-color: #dba617;
   background: #fffbf0;
}

.mfqi-toast.info {
   border-left-color: #007cba;
   background: #f0f8ff;
}

.mfqi-toast-icon {
   flex-shrink: 0;
   margin-top: 2px;
}

.mfqi-toast-content {
   flex: 1;
}

.mfqi-toast-title {
   font-weight: 600;
   margin: 0 0 4px 0;
   color: #333;
}

.mfqi-toast-message {
   margin: 0;
   color: #666;
}

.mfqi-toast-close {
   background: none;
   border: none;
   cursor: pointer;
   padding: 0;
   width: 20px;
   height: 20px;
   display: flex;
   align-items: center;
   justify-content: center;
   border-radius: 50%;
   transition: background-color 0.2s;
   flex-shrink: 0;
}

.mfqi-toast-close:hover {
   background: rgba(0, 0, 0, 0.1);
}

@keyframes mfqi-toast-slide-in {
   from {
     transform: translateX(100%);
     opacity: 0;
   }
   to {
     transform: translateX(0);
     opacity: 1;
   }
}

@keyframes mfqi-toast-slide-out {
   from {
     transform: translateX(0);
     opacity: 1;
   }
   to {
     transform: translateX(100%);
     opacity: 0;
   }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
   // Import form handling with loading state
   const importForm = document.getElementById('mfqi-import-form');
   const submitBtn = document.getElementById('mfqi-submit-btn');
   const loadingOverlay = document.getElementById('mfqi-loading-overlay');
   const loadingText = document.getElementById('mfqi-loading-text');

   if (importForm && submitBtn) {
     importForm.addEventListener('submit', function(e) {
       // Change button text immediately
       submitBtn.value = 'Importing...';
       submitBtn.disabled = true;

       // Show loading state
       showLoading('Importing quiz data...');

       // Disable form to prevent double submission
       importForm.classList.add('mfqi-form-disabled');

       // Simulate initial progress updates
       setTimeout(() => {
         updateLoadingText('Reading CSV file...');
         updateLoadingProgress(0, window.mfqiTotalRows || 1, 'Preparing data', window.mfqiEstimatedTime || 0);
       }, 500);

       setTimeout(() => {
         updateLoadingText('Validating data...');
         updateLoadingProgress(0, window.mfqiTotalRows || 1, 'Checking course IDs', window.mfqiEstimatedTime || 0);
       }, 1000);
     });
   }

   function showLoading(text) {
     if (loadingText) loadingText.textContent = text;
     if (loadingOverlay) loadingOverlay.style.display = 'flex';

     // Show estimated time if available
     const timeElement = document.getElementById('mfqi-loading-time');
     if (timeElement) {
       if (window.mfqiActualTime) {
         // Show actual time if import is complete
         const minutes = Math.floor(window.mfqiActualTime / 60);
         const seconds = window.mfqiActualTime % 60;
         let timeText = 'Completed in: ';
         if (minutes > 0) {
           timeText += minutes + ' minute' + (minutes > 1 ? 's' : '');
         }
         if (seconds > 0) {
           timeText += (minutes > 0 ? ' ' : '') + seconds + ' second' + (seconds > 1 ? 's' : '');
         }
         timeElement.textContent = timeText;
       } else if (window.mfqiEstimatedTime) {
         // Show estimated time if still processing
         const minutes = Math.floor(window.mfqiEstimatedTime / 60);
         const seconds = window.mfqiEstimatedTime % 60;
         let timeText = 'Estimated time: ';
         if (minutes > 0) {
           timeText += minutes + ' minute' + (minutes > 1 ? 's' : '');
         }
         if (seconds > 0) {
           timeText += (minutes > 0 ? ' ' : '') + seconds + ' second' + (seconds > 1 ? 's' : '');
         }
         timeElement.textContent = timeText;
       }
     }
   }

   function updateLoadingText(text) {
     if (loadingText) loadingText.textContent = text;
   }

   function updateLoadingProgress(currentRow, totalRows, rowInfo = '', estimatedTime = 0) {
     const progressElement = document.getElementById('mfqi-loading-progress');
     const progressFill = document.getElementById('mfqi-progress-fill');
     const timeElement = document.getElementById('mfqi-loading-time');

     if (progressElement) {
       const percent = totalRows > 0 ? (currentRow / totalRows) * 100 : 0;
       progressElement.textContent = `Processing row ${currentRow}/${totalRows}${rowInfo ? ' - ' + rowInfo : ''}`;
     }

     if (progressFill) {
       progressFill.style.width = Math.min(percent, 100) + '%';
     }

     if (timeElement && estimatedTime > 0) {
       const remainingRows = totalRows - currentRow;
       const remainingSeconds = remainingRows * 3; // 3 seconds per row
       const remainingMinutes = Math.floor(remainingSeconds / 60);
       const remainingSecs = remainingSeconds % 60;

       let timeText = 'About ';
       if (remainingMinutes > 0) {
         timeText += remainingMinutes + ' minute' + (remainingMinutes > 1 ? 's' : '');
       }
       if (remainingSecs > 0) {
         timeText += (timeText !== 'About ' ? ' ' : '') + remainingSecs + ' second' + (remainingSecs > 1 ? 's' : '');
       }
       timeText += ' remaining';

       timeElement.textContent = timeText;
     }
   }

   function hideLoading() {
     if (loadingOverlay) loadingOverlay.style.display = 'none';
     if (importForm) importForm.classList.remove('mfqi-form-disabled');
     if (submitBtn) {
       submitBtn.value = 'Import Now';
       submitBtn.disabled = false;
     }

     // Reset progress bar
     const progressFill = document.getElementById('mfqi-progress-fill');
     if (progressFill) {
       progressFill.style.width = '0%';
     }
   }

   // Auto-hide loading if there's an error (fallback)
   window.addEventListener('beforeunload', function() {
     hideLoading();
   });

   // Check if we're returning from a form submission and hide loading
   if (loadingOverlay && loadingOverlay.style.display !== 'none') {
     // Check if there are any success/error messages on the page
     const hasMessages = document.querySelector('.updated, .error, .notice');
     if (hasMessages) {
       hideLoading();
     }
   }

   // Toast notification system
   const toastContainer = document.getElementById('mfqi-toast-container');

   // Show toast messages for PHP errors/notices
   showToastFromPHP();

   // Drag and drop functionality
   const dragDropArea = document.getElementById('dragDropArea');
  const fileInput = document.getElementById('mfqi_file');
  const browseBtn = document.getElementById('browseBtn');
  const fileInfo = document.getElementById('fileInfo');
  const fileName = document.getElementById('fileName');
  const removeFile = document.getElementById('removeFile');

  // Allowed file types
  const allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
  const allowedExtensions = ['.csv', '.xlsx'];

  // Browse button click
  browseBtn.addEventListener('click', function() {
    fileInput.click();
  });

  // File input change
  fileInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      handleFile(file);
    }
  });

  // Drag and drop events
  dragDropArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    dragDropArea.classList.add('drag-over');
  });

  dragDropArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    dragDropArea.classList.remove('drag-over');
  });

  dragDropArea.addEventListener('drop', function(e) {
    e.preventDefault();
    dragDropArea.classList.remove('drag-over');

    const files = e.dataTransfer.files;
    if (files.length > 0) {
      const file = files[0];
      if (validateFile(file)) {
        handleFile(file);
      } else {
        alert('Please select a valid CSV or XLSX file.');
      }
    }
  });

  // Remove file
  removeFile.addEventListener('click', function() {
    fileInput.value = '';
    fileInfo.style.display = 'none';
    dragDropArea.classList.remove('has-file');
  });

  // Validate file
  function validateFile(file) {
    // Check file type
    if (!allowedTypes.includes(file.type)) {
      // Check file extension as fallback
      const fileName = file.name.toLowerCase();
      return allowedExtensions.some(ext => fileName.endsWith(ext));
    }
    return true;
  }

  // Handle selected file
  function handleFile(file) {
    if (!validateFile(file)) {
      alert('Please select a valid CSV or XLSX file.');
      return;
    }

    // Create new DataTransfer for file input
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    fileInput.files = dataTransfer.files;

    // Show file info
    fileName.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
    fileInfo.style.display = 'flex';
    dragDropArea.classList.add('has-file');
  }

  // Format file size
  function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  // Click on upload zone to browse files
  dragDropArea.addEventListener('click', function(e) {
    if (e.target === dragDropArea || e.target.classList.contains('mfqi-upload-zone')) {
      browseBtn.click();
    }
  });

  // Toast notification functions
  function showToastFromPHP() {
    // Check for PHP error/success messages and convert to toasts
    const notices = document.querySelectorAll('.updated, .error, .notice');
    notices.forEach(notice => {
      const message = notice.textContent.trim();
      if (message) {
        const type = notice.classList.contains('error') ? 'error' : 'success';
        showToast(message, type, 8000); // Show for 8 seconds
        // Hide the original notice after a short delay
        setTimeout(() => {
          notice.style.display = 'none';
        }, 1000);
      }
    });
  }

  function showToast(message, type = 'info', duration = 5000) {
    if (!toastContainer) return;

    const toast = document.createElement('div');
    toast.className = `mfqi-toast ${type}`;

    const iconMap = {
      success: '✓',
      error: '⚠',
      warning: '!',
      info: 'ℹ'
    };

    toast.innerHTML = `
      <div class="mfqi-toast-icon">${iconMap[type] || 'ℹ'}</div>
      <div class="mfqi-toast-content">
        <div class="mfqi-toast-message">${escapeHtml(message)}</div>
      </div>
      <button class="mfqi-toast-close" onclick="this.parentElement.remove()">&times;</button>
    `;

    toastContainer.appendChild(toast);

    // Auto remove after duration
    setTimeout(() => {
      if (toast.parentElement) {
        toast.style.animation = 'mfqi-toast-slide-out 0.3s ease-in';
        setTimeout(() => toast.remove(), 300);
      }
    }, duration);

    return toast;
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // Global function for showing toasts (can be called from anywhere)
  window.showMFQIToast = showToast;
});
</script>
