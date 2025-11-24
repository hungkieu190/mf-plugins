/**
 * Admin JavaScript for Quiz Importer
 *
 * @package MF_Quiz_Importer_For_LearnPress
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        const QuizImporter = {
            init: function() {
                this.bindEvents();
                this.initDragDrop();
            },

            bindEvents: function() {
                // Quiz file input change
                $('#mf-quiz-file').on('change', this.handleQuizFileSelect);
                
                // Question file input change
                $('#mf-question-file').on('change', this.handleQuestionFileSelect);
                
                // Quiz form submit
                $('#mf-quiz-importer-form').on('submit', this.handleQuizFormSubmit);
                
                // Question form submit
                $('#mf-question-importer-form').on('submit', this.handleQuestionFormSubmit);
            },
            
            initDragDrop: function() {
                // Quiz upload area
                const $quizUpload = $('#mf-quiz-importer-form .mf-upload-area');
                const $quizLabel = $('#mf-quiz-importer-form .mf-upload-label');
                const $quizInput = $('#mf-quiz-file');
                
                if ($quizUpload.length) {
                    QuizImporter.setupDragDrop($quizUpload, $quizLabel, $quizInput, 'quiz');
                }
                
                // Question upload area
                const $questionUpload = $('#mf-question-importer-form .mf-upload-area');
                const $questionLabel = $('#mf-question-importer-form .mf-upload-label');
                const $questionInput = $('#mf-question-file');
                
                if ($questionUpload.length) {
                    QuizImporter.setupDragDrop($questionUpload, $questionLabel, $questionInput, 'question');
                }
            },
            
            setupDragDrop: function($area, $label, $input, type) {
                // Prevent default drag behaviors
                $label.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                });
                
                // Add highlight on drag over
                $label.on('dragover dragenter', function() {
                    $area.addClass('drag-over');
                });
                
                // Remove highlight on drag leave
                $label.on('dragleave dragend drop', function() {
                    $area.removeClass('drag-over');
                });
                
                // Handle dropped files
                $label.on('drop', function(e) {
                    const files = e.originalEvent.dataTransfer.files;
                    
                    if (files.length > 0) {
                        // Set the file to input
                        $input[0].files = files;
                        
                        // Trigger change event
                        $input.trigger('change');
                    }
                });
            },

            handleQuizFileSelect: function(e) {
                const file = e.target.files[0];
                
                if (file) {
                    // Validate file type
                    const validTypes = ['text/csv', 'application/json', 'application/vnd.ms-excel', 
                                       'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
                    const validExtensions = ['.csv', '.json', '.xls', '.xlsx'];
                    const fileName = file.name.toLowerCase();
                    const hasValidExtension = validExtensions.some(ext => fileName.endsWith(ext));
                    
                    if (!hasValidExtension && !validTypes.includes(file.type)) {
                        QuizImporter.showError(
                            'Invalid file type. Please upload CSV, JSON, or Excel (XLSX) files only.',
                            $('#mf-quiz-importer-form')
                        );
                        e.target.value = '';
                        return;
                    }
                    
                    // Validate file size (max 10MB)
                    const maxSize = 10 * 1024 * 1024; // 10MB
                    if (file.size > maxSize) {
                        QuizImporter.showError(
                            'File is too large. Maximum file size is 10MB.',
                            $('#mf-quiz-importer-form')
                        );
                        e.target.value = '';
                        return;
                    }
                    
                    $('#mf-file-name').text(file.name);
                    $('#mf-quiz-importer-form .mf-file-info').show();
                    $('#mf-quiz-importer-form .mf-upload-area').addClass('has-file');
                    $('#mf-import-btn').prop('disabled', false);
                    $('#mf-quiz-importer-form .mf-result').hide();
                } else {
                    $('#mf-quiz-importer-form .mf-file-info').hide();
                    $('#mf-quiz-importer-form .mf-upload-area').removeClass('has-file');
                    $('#mf-import-btn').prop('disabled', true);
                }
            },

            handleQuestionFileSelect: function(e) {
                const file = e.target.files[0];
                
                if (file) {
                    // Validate file type
                    const validTypes = ['text/csv', 'application/json', 'application/vnd.ms-excel', 
                                       'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
                    const validExtensions = ['.csv', '.json', '.xls', '.xlsx'];
                    const fileName = file.name.toLowerCase();
                    const hasValidExtension = validExtensions.some(ext => fileName.endsWith(ext));
                    
                    if (!hasValidExtension && !validTypes.includes(file.type)) {
                        QuizImporter.showError(
                            'Invalid file type. Please upload CSV, JSON, or Excel (XLSX) files only.',
                            $('#mf-question-importer-form')
                        );
                        e.target.value = '';
                        return;
                    }
                    
                    // Validate file size (max 10MB)
                    const maxSize = 10 * 1024 * 1024; // 10MB
                    if (file.size > maxSize) {
                        QuizImporter.showError(
                            'File is too large. Maximum file size is 10MB.',
                            $('#mf-question-importer-form')
                        );
                        e.target.value = '';
                        return;
                    }
                    
                    $('#mf-question-file-name').text(file.name);
                    $('#mf-question-importer-form .mf-file-info').show();
                    $('#mf-question-importer-form .mf-upload-area').addClass('has-file');
                    $('#mf-question-import-btn').prop('disabled', false);
                    $('#mf-question-importer-form .mf-result').hide();
                } else {
                    $('#mf-question-importer-form .mf-file-info').hide();
                    $('#mf-question-importer-form .mf-upload-area').removeClass('has-file');
                    $('#mf-question-import-btn').prop('disabled', true);
                }
            },

            handleQuizFormSubmit: function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $btn = $('#mf-import-btn');
                const file = $('#mf-quiz-file')[0].files[0];
                
                if (!file) {
                    QuizImporter.showError(mfQuizImporter.i18n.error);
                    return;
                }
                
                // Disable button and show loading
                $btn.prop('disabled', true).addClass('loading');
                $('#mf-quiz-importer-form .mf-result').hide();
                $('#mf-quiz-importer-form .mf-progress').show();
                $('#mf-quiz-importer-form .mf-progress-fill').css('width', '30%');
                
                // Upload file
                QuizImporter.uploadFile(file, 'quiz', $form);
            },

            handleQuestionFormSubmit: function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $btn = $('#mf-question-import-btn');
                const file = $('#mf-question-file')[0].files[0];
                const quizId = $('#target-quiz-id').val();
                
                if (!file) {
                    QuizImporter.showError(mfQuizImporter.i18n.error, $form);
                    return;
                }
                
                if (!quizId) {
                    QuizImporter.showError('Please select a target quiz', $form);
                    return;
                }
                
                // Disable button and show loading
                $btn.prop('disabled', true).addClass('loading');
                $('#mf-question-importer-form .mf-result').hide();
                $('#mf-question-importer-form .mf-progress').show();
                $('#mf-question-importer-form .mf-progress-fill').css('width', '30%');
                
                // Upload file
                QuizImporter.uploadFile(file, 'questions', $form, quizId);
            },

            uploadFile: function(file, importType, $form, quizId) {
                const formData = new FormData();
                formData.append('action', 'mf_quiz_importer_upload');
                formData.append('nonce', mfQuizImporter.nonce);
                formData.append('file', file);
                formData.append('import_type', importType);
                if (quizId) {
                    formData.append('quiz_id', quizId);
                }
                
                $.ajax({
                    url: mfQuizImporter.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $form.find('.mf-progress-fill').css('width', '60%');
                            QuizImporter.processImport(response.data.file, importType, $form, quizId);
                        } else {
                            QuizImporter.showError(response.data.message || mfQuizImporter.i18n.error, $form);
                        }
                    },
                    error: function() {
                        QuizImporter.showError(mfQuizImporter.i18n.error, $form);
                    }
                });
            },

            processImport: function(filename, importType, $form, quizId) {
                const data = {
                    action: 'mf_quiz_importer_process',
                    nonce: mfQuizImporter.nonce,
                    file: filename,
                    import_type: importType
                };
                
                if (quizId) {
                    data.quiz_id = quizId;
                }
                
                $.ajax({
                    url: mfQuizImporter.ajaxUrl,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        $form.find('.mf-progress-fill').css('width', '100%');
                        
                        setTimeout(function() {
                            $form.find('.mf-progress').hide();
                            
                            if (response.success) {
                                QuizImporter.showSuccess(response.data, importType, $form);
                            } else {
                                QuizImporter.showError(response.data.message || mfQuizImporter.i18n.error, $form);
                            }
                            
                            QuizImporter.resetForm(importType);
                        }, 500);
                    },
                    error: function() {
                        $form.find('.mf-progress').hide();
                        QuizImporter.showError(mfQuizImporter.i18n.error, $form);
                        QuizImporter.resetForm(importType);
                    }
                });
            },

            showSuccess: function(data, importType, $form) {
                let message = '';
                
                if (importType === 'quiz') {
                    message = `
                        <h3>${mfQuizImporter.i18n.success}</h3>
                        <ul>
                            <li><strong>Imported:</strong> ${data.imported} quiz(es)</li>
                            ${data.failed > 0 ? `<li><strong>Failed:</strong> ${data.failed} quiz(es)</li>` : ''}
                        </ul>
                    `;
                } else {
                    message = `
                        <h3>${mfQuizImporter.i18n.success}</h3>
                        <ul>
                            <li><strong>Imported:</strong> ${data.imported} question(s)</li>
                            ${data.failed > 0 ? `<li><strong>Failed:</strong> ${data.failed} question(s)</li>` : ''}
                        </ul>
                    `;
                }
                
                // Add error details if present
                if (data.errors && data.errors.length > 0) {
                    message += '<h4 style="margin-top: 20px; color: #d63638;">Error Details:</h4><ul style="color: #646970;">';
                    data.errors.forEach(function(error) {
                        message += `<li>${error}</li>`;
                    });
                    message += '</ul>';
                }
                
                const resultClass = data.failed > 0 ? 'error' : 'success';
                
                $form.find('.mf-result')
                    .removeClass('error success')
                    .addClass(resultClass)
                    .html(message)
                    .show();
            },

            showError: function(message, $form) {
                if (!$form) {
                    $form = $('.mf-quiz-importer-card form:visible');
                }
                
                $form.find('.mf-result')
                    .removeClass('success')
                    .addClass('error')
                    .html(`<h3>Error</h3><p>${message}</p>`)
                    .show();
            },

            resetForm: function(importType) {
                if (importType === 'quiz') {
                    $('#mf-import-btn').prop('disabled', false).removeClass('loading');
                    $('#mf-quiz-file').val('');
                    $('#mf-quiz-importer-form .mf-file-info').hide();
                    $('#mf-quiz-importer-form .mf-upload-area').removeClass('has-file');
                    $('#mf-quiz-importer-form .mf-progress-fill').css('width', '0');
                } else {
                    $('#mf-question-import-btn').prop('disabled', false).removeClass('loading');
                    $('#mf-question-file').val('');
                    $('#mf-question-importer-form .mf-file-info').hide();
                    $('#mf-question-importer-form .mf-upload-area').removeClass('has-file');
                    $('#mf-question-importer-form .mf-progress-fill').css('width', '0');
                }
            }
        };

        // Initialize
        QuizImporter.init();
        
        // Documentation Modal
        const DocModal = {
            init: function() {
                this.bindEvents();
            },
            
            bindEvents: function() {
                // View doc buttons
                $('.mf-view-doc').on('click', this.openModal);
                
                // Close modal
                $('.mf-doc-modal-close').on('click', this.closeModal);
                
                // Click outside to close
                $('#mf-doc-modal').on('click', function(e) {
                    if (e.target.id === 'mf-doc-modal') {
                        DocModal.closeModal();
                    }
                });
                
                // ESC key to close
                $(document).on('keydown', function(e) {
                    if (e.key === 'Escape' && $('#mf-doc-modal').is(':visible')) {
                        DocModal.closeModal();
                    }
                });
            },
            
            openModal: function(e) {
                e.preventDefault();
                const $btn = $(this);
                const docFile = $btn.data('doc');
                const docTitle = $btn.data('title');
                
                // Set title
                $('#mf-doc-modal-title span:last').text(docTitle);
                
                // Show modal
                $('#mf-doc-modal').fadeIn(300);
                $('body').css('overflow', 'hidden');
                
                // Load content
                DocModal.loadDoc(docFile);
            },
            
            closeModal: function() {
                $('#mf-doc-modal').fadeOut(300);
                $('body').css('overflow', '');
            },
            
            loadDoc: function(docFile) {
                const $body = $('#mf-doc-modal-body');
                
                // Show loading
                $body.html(`
                    <div class="mf-doc-modal-loading">
                        <span class="spinner is-active"></span>
                        <p>Loading documentation...</p>
                    </div>
                `);
                
                // Load file
                $.ajax({
                    url: mfQuizImporter.pluginUrl + docFile,
                    type: 'GET',
                    dataType: 'text',
                    success: function(content) {
                        // Convert markdown to HTML (basic conversion)
                        const html = DocModal.markdownToHtml(content);
                        $body.html(html);
                    },
                    error: function() {
                        $body.html(`
                            <div style="text-align: center; padding: 40px;">
                                <span class="dashicons dashicons-warning" style="font-size: 48px; color: #d63638;"></span>
                                <h3>Failed to load documentation</h3>
                                <p>Please try downloading the file instead.</p>
                            </div>
                        `);
                    }
                });
            },
            
            markdownToHtml: function(markdown) {
                let html = markdown;
                
                // Escape HTML first
                const escapeHtml = (text) => {
                    return text.replace(/&/g, '&amp;')
                              .replace(/</g, '&lt;')
                              .replace(/>/g, '&gt;');
                };
                
                // Process code blocks first (to preserve them)
                const codeBlocks = [];
                html = html.replace(/```([\s\S]*?)```/g, function(match, code) {
                    const index = codeBlocks.length;
                    codeBlocks.push('<pre><code>' + escapeHtml(code.trim()) + '</code></pre>');
                    return `___CODE_BLOCK_${index}___`;
                });
                
                // Escape remaining HTML
                html = escapeHtml(html);
                
                // Headers (must be in order from most specific to least)
                html = html.replace(/^#### (.*$)/gim, '<h4>$1</h4>');
                html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
                html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
                html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');
                
                // Horizontal rules
                html = html.replace(/^---$/gim, '<hr>');
                
                // Bold
                html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                
                // Italic
                html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
                
                // Inline code
                html = html.replace(/`([^`]+)`/g, '<code>$1</code>');
                
                // Links
                html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');
                
                // Images
                html = html.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1">');
                
                // Blockquotes
                html = html.replace(/^&gt; (.*$)/gim, '<blockquote>$1</blockquote>');
                
                // Lists - unordered
                const lines = html.split('\n');
                let inList = false;
                let listHtml = '';
                
                for (let i = 0; i < lines.length; i++) {
                    const line = lines[i];
                    
                    if (line.match(/^[\*\-] /)) {
                        if (!inList) {
                            listHtml += '<ul>';
                            inList = true;
                        }
                        listHtml += '<li>' + line.replace(/^[\*\-] /, '') + '</li>';
                    } else {
                        if (inList) {
                            listHtml += '</ul>';
                            inList = false;
                        }
                        listHtml += line + '\n';
                    }
                }
                
                if (inList) {
                    listHtml += '</ul>';
                }
                
                html = listHtml;
                
                // Paragraphs
                html = html.replace(/\n\n+/g, '</p><p>');
                html = '<p>' + html + '</p>';
                
                // Clean up
                html = html.replace(/<p><h/g, '<h');
                html = html.replace(/<\/h([1-6])><\/p>/g, '</h$1>');
                html = html.replace(/<p><ul>/g, '<ul>');
                html = html.replace(/<\/ul><\/p>/g, '</ul>');
                html = html.replace(/<p><hr><\/p>/g, '<hr>');
                html = html.replace(/<p><blockquote>/g, '<blockquote>');
                html = html.replace(/<\/blockquote><\/p>/g, '</blockquote>');
                html = html.replace(/<p>\s*<\/p>/g, '');
                html = html.replace(/<p><br><\/p>/g, '<br>');
                
                // Restore code blocks
                codeBlocks.forEach((block, index) => {
                    html = html.replace(`___CODE_BLOCK_${index}___`, block);
                });
                
                return html;
            }
        };
        
        // Initialize doc modal
        DocModal.init();
    });

})(jQuery);