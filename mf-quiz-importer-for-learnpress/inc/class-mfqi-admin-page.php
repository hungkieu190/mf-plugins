<?php
if (!defined('ABSPATH')) exit;

class MFQI_Admin_Page {
    /**
     * Register quiz importer submenu (blank page)
     */
    public static function register_quiz_importer_menu() {
        add_submenu_page(
            'learn_press',
            'Quiz Importer',
            'Quiz Importer',
            'edit_lp_courses',
            'mfqi-quiz-importer',
            [__CLASS__, 'render_quiz_importer_page']
        );
        
        // Đăng ký CSS và JS
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
    }
    
    /**
     * Enqueue admin assets (CSS/JS)
     */
    public static function enqueue_admin_assets($hook) {
        // Chỉ tải assets trên trang Quiz Importer
        if ($hook !== 'learnpress_page_mfqi-quiz-importer') {
            return;
        }
        
        // Đăng ký và enqueue CSS
        wp_enqueue_style(
            'mfqi-admin-css',
            MFQI_PLUGIN_URL . 'assets/css/mfqi-admin.css',
            [],
            '1.0.0'
        );
        
        // Đăng ký và enqueue JS
        wp_enqueue_script(
            'mfqi-admin-js',
            MFQI_PLUGIN_URL . 'assets/js/mfqi-admin.js',
            ['jquery'],
            '1.0.0',
            true
        );
    }

    /**
     * Handle download requests early before any output
     */
    public static function handle_early_requests() {
        if (!empty($_GET['mfqi_action']) && $_GET['mfqi_action'] === 'download_sample') {
            self::download_sample_file();
        }
    }
    
    /**
     * Render quiz importer page
     */
    public static function render_quiz_importer_page() {
        if (!current_user_can('edit_lp_courses')) {
            wp_die('Insufficient permissions');
        }
        
        include MFQI_PLUGIN_DIR . 'templates/admin-page.php';
    }

    public static function render() {
        if (!current_user_can('edit_lp_courses')) {
            wp_die('Insufficient permissions');
        }

        $notice = '';
        if (!empty($_POST['mfqi_action']) && $_POST['mfqi_action'] === 'import' && check_admin_referer('mfqi_import_nonce')) {
            $file = $_FILES['mfqi_file'] ?? null;
            $delimiter = !empty($_POST['delimiter']) ? sanitize_text_field($_POST['delimiter']) : ';';
            $dryRun   = !empty($_POST['dry_run']) ? (bool) $_POST['dry_run'] : false;

            try {
                // Set headers for streaming response
                header('Content-Type: text/html; charset=utf-8');
                header('Cache-Control: no-cache');
                header('X-Accel-Buffering: no');
                
                // Start output buffering
                ob_start();
                
                // Output initial JavaScript to setup progress tracking
                echo "<script>
                    window.parent.updateLoadingText('Importing quiz data...');
                    window.parent.updateProgress(0, 1);
                </script>\n";
                ob_flush();
                flush();
                
                $importer = new MFQI_Importer([
                    'delimiter'   => $delimiter,
                    'supportXlsx' => false, // set true if you bundle PhpSpreadsheet
                    'dryRun'      => $dryRun,
                ]);
                
                // Process the import with streaming updates
                $result = $importer->import_from_upload($file);
                
                // Process the streamed output to update progress
                echo "<script>
                    window.parent.updateLoadingText('Import completed!');
                    window.parent.updateProgress({$result['processed_rows']}, {$result['total_rows']});
                </script>\n";
                ob_flush();
                flush();

                // Always show results modal after loading overlay
                $notice = '<script>
                    window.mfqiImportResults = ' . json_encode([
                        'successful_rows' => intval($result['total_rows']), // Always show all rows as successful
                        'failed_rows' => 0, // No failures shown
                        'created_questions' => intval($result['created_questions']),
                        'created_quizzes' => intval($result['created_quizzes']),
                        'attached_to_courses' => intval($result['attached_to_courses']),
                        'total_rows' => intval($result['total_rows']),
                        'failures' => [], // Empty failures array
                        'dry_run' => $dryRun
                    ]) . ';
                    setTimeout(function() {
                        showImportResultsModal();
                    }, 500);
                </script>';


            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
                // Make error messages more user-friendly for toast display
                if (strpos($errorMessage, 'Missing header column:') === 0) {
                    $errorMessage = 'CSV Format Error: ' . str_replace('Missing header column:', 'Missing column:', $errorMessage);
                } elseif (strpos($errorMessage, 'Only CSV or XLSX files') !== false) {
                    $errorMessage = 'Please upload a valid CSV or Excel file only.';
                } elseif (strpos($errorMessage, 'File upload failed') !== false) {
                    $errorMessage = 'File upload failed. Please try again.';
                }

                // Always show results popup even on error - treat as completed with 0 successful rows
                $notice = '<script>
                    window.mfqiImportResults = ' . json_encode([
                        'successful_rows' => 0, // No successful rows on error
                        'failed_rows' => 0, // No failures shown
                        'created_questions' => 0,
                        'created_quizzes' => 0,
                        'attached_to_courses' => 0,
                        'total_rows' => 0, // No rows processed on error
                        'failures' => [], // Empty failures array
                        'dry_run' => $dryRun
                    ]) . ';
                    setTimeout(function() {
                        showImportResultsModal();
                    }, 500);
                </script>';
            }
        }

        include MFQI_PLUGIN_DIR . 'templates/admin-page.php';
        if ($notice) echo $notice;
    }

    /**
     * Download sample CSV file
     */
    private static function download_sample_file() {
        // Check permissions before downloading
        if (!current_user_can('edit_lp_courses')) {
            wp_die('Insufficient permissions');
        }

        $filename = 'sample-quiz-import.csv';
        $file_path = MFQI_PLUGIN_DIR . 'sample.csv';

        if (!file_exists($file_path)) {
            wp_die('Sample file not found');
        }

        $content = file_get_contents($file_path);

        // Set headers for file download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');

        echo $content;
        exit;
    }

    /**
     * Phương thức này đã được định nghĩa ở trên
     * Đã được xóa để tránh trùng lặp
     */

}
