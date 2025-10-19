<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
  <h1>MF Quiz Importer for LearnPress</h1>
  <p>Upload CSV/XLSX file according to the sample format to import quiz & questions to LearnPress. CSV should use <code>UTF-8</code> encoding.</p>

  <div class="mfqi-download-section">
    <p><strong>Download Sample Files:</strong></p>
    <p>
      <a href="<?php echo esc_url(add_query_arg(['mfqi_action' => 'download_sample', 'type' => 'csv'], admin_url('admin.php?page=mfqi-quiz-importer'))); ?>"
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

    <!-- Import Results Modal -->
    <div id="mfqi-results-modal" style="display: none;">
      <div class="mfqi-modal-overlay">
        <div class="mfqi-modal-content">
          <div class="mfqi-modal-header">
            <h2>Import Results</h2>
          </div>
          <div class="mfqi-modal-body">
            <div class="mfqi-results-summary">
              <div class="mfqi-result-item success">
                <span class="mfqi-result-icon">✅</span>
                <span class="mfqi-result-text">Successful: <span id="successful-count">0</span> rows</span>
              </div>
            </div>

            <div class="mfqi-results-details">
              <ul id="mfqi-results-details-list">
                <li id="created-questions">Questions created: 0</li>
                <li id="created-quizzes">Quizzes created: 0</li>
                <li id="attached-courses">Attached to courses: 0</li>
                <li id="total-processed">Total processed: 0/0 rows</li>
              </ul>
            </div>

            <div id="mfqi-failures-section" style="display: none;">
              <h4 style="color: #d63638; margin: 15px 0 10px 0;">Failures:</h4>
              <div class="mfqi-failures-list" id="mfqi-failures-list"></div>
            </div>

            <div id="mfqi-dry-run-notice" style="display: none;">
              <p style="color: #666; font-style: italic; margin-top: 15px;">Note: This was a dry run - no actual data was created.</p>
            </div>
          </div>
          <div class="mfqi-modal-footer">
            <button class="button button-primary" onclick="closeImportResultsModal()">Close</button>
          </div>
        </div>
      </div>
    </div>



    <table class="form-table" role="presentation">
      <tr>
        <th scope="row"><label for="mfqi_file">File (CSV/XLSX)</label></th>
        <td>
          <input type="file" id="mfqi_file" name="mfqi_file" class="regular-text" required accept=".csv,.xlsx" />
          <p class="description">Upload a CSV or XLSX file containing quiz data.</p>
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

/* Modal Styles */
#mfqi-results-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10001;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mfqi-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
}

.mfqi-modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    animation: mfqi-modal-slide-in 0.3s ease-out;
}

@keyframes mfqi-modal-slide-in {
    from {
        transform: scale(0.9);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.mfqi-modal-header {
    padding: 20px 30px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.mfqi-modal-header h2 {
    margin: 0;
    color: #333;
    font-size: 24px;
    font-weight: 600;
}

.mfqi-modal-body {
    padding: 20px 30px;
}

.mfqi-results-summary {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.mfqi-result-item {
    flex: 1;
    padding: 15px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.mfqi-result-item.success {
    background: #f8fff9;
    border: 1px solid #00a32a;
}

.mfqi-result-icon {
    font-size: 20px;
}

.mfqi-result-text {
    font-weight: 600;
    color: #333;
}

.mfqi-results-details ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.mfqi-results-details li {
    padding: 5px 0;
    color: #666;
}

.mfqi-failures-list {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 10px;
    background: #f8f9fa;
}

.mfqi-failures-list div {
    padding: 5px 0;
    color: #d63638;
    font-size: 14px;
    border-bottom: 1px solid #e9ecef;
}

.mfqi-failures-list div:last-child {
    border-bottom: none;
}

.mfqi-modal-footer {
    padding: 20px 30px;
    border-top: 1px solid #e9ecef;
    text-align: right;
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


   // Drag and drop functionality - REMOVED
   // const dragDropArea = document.getElementById('dragDropArea');
   // const fileInput = document.getElementById('mfqi_file');
   // const browseBtn = document.getElementById('browseBtn');
   // const fileInfo = document.getElementById('fileInfo');
   // const fileName = document.getElementById('fileName');
   // const removeFile = document.getElementById('removeFile');



  // Import results modal functions
  function showImportResultsModal() {
    const modal = document.getElementById('mfqi-results-modal');
    if (!modal || !window.mfqiImportResults) return;

    const results = window.mfqiImportResults;

    // Update summary counts
    document.getElementById('successful-count').textContent = results.successful_rows;

    // Update details
    document.getElementById('created-questions').textContent = 'Questions created: ' + results.created_questions;
    document.getElementById('created-quizzes').textContent = 'Quizzes created: ' + results.created_quizzes;
    document.getElementById('attached-courses').textContent = 'Attached to courses: ' + results.attached_to_courses;
    document.getElementById('total-processed').textContent = 'Total processed: ' + results.successful_rows + '/' + results.total_rows + ' rows';

    // Show failures if any
    const failuresSection = document.getElementById('mfqi-failures-section');
    const failuresList = document.getElementById('mfqi-failures-list');
    if (results.failures && results.failures.length > 0) {
      failuresList.innerHTML = results.failures.map(failure => '<div>' + escapeHtml(failure) + '</div>').join('');
      failuresSection.style.display = 'block';
    } else {
      failuresSection.style.display = 'none';
    }

    // Show dry run notice if applicable
    const dryRunNotice = document.getElementById('mfqi-dry-run-notice');
    if (results.dry_run) {
      dryRunNotice.style.display = 'block';
    } else {
      dryRunNotice.style.display = 'none';
    }

    // Hide loading overlay and show results modal
    const loadingOverlay = document.getElementById('mfqi-loading-overlay');
    if (loadingOverlay) {
      loadingOverlay.style.display = 'none';
    }

    // Show modal
    modal.style.display = 'flex';
  }

  function closeImportResultsModal() {
    const modal = document.getElementById('mfqi-results-modal');
    if (modal) {
      modal.style.display = 'none';
    }

    // Reset form
    const importForm = document.getElementById('mfqi-import-form');
    const submitBtn = document.getElementById('mfqi-submit-btn');
    if (importForm) importForm.classList.remove('mfqi-form-disabled');
    if (submitBtn) {
      submitBtn.value = 'Import Now';
      submitBtn.disabled = false;
    }
  }
});
</script>
