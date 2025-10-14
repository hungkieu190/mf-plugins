<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
  <h1>MF Quiz Importer for LearnPress</h1>
  <p>Upload CSV/XLSX file according to the sample format to import quiz & questions to LearnPress. CSV should use <code>UTF-8</code> encoding.</p>

  <form method="post" enctype="multipart/form-data">
    <?php wp_nonce_field('mfqi_import_nonce'); ?>
    <input type="hidden" name="mfqi_action" value="import" />

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
    </table>

    <?php submit_button('Import Now'); ?>
  </form>

  <h2>CSV Columns</h2>
  <pre>course_id,section_name,quiz_title,question_type,question_text,answers,correct,mark</pre>
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
