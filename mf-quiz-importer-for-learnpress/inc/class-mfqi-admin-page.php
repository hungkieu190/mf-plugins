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
    }

    /**
     * Handle download requests early before any output
     */
    public static function handle_early_requests() {
        if (!empty($_GET['mfqi_action']) && $_GET['mfqi_action'] === 'download_sample') {
            self::download_sample_file();
        }
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
            $debugDelay = !empty($_POST['debug_delay']) ? (bool) $_POST['debug_delay'] : false;

            try {
                $importer = new MFQI_Importer([
                    'delimiter'   => $delimiter,
                    'supportXlsx' => false, // set true if you bundle PhpSpreadsheet
                    'dryRun'      => $dryRun,
                    'debugDelay'  => $debugDelay,
                ]);
                $result = $importer->import_from_upload($file);

                $notice  = '<div class="updated"><p><strong>✅ Import Successful!</strong><br>';
                $notice .= 'Created: ' . intval($result['created_questions']) . ' questions, ';
                $notice .= intval($result['created_quizzes']) . ' quizzes, ';
                $notice .= intval($result['attached_to_courses']) . ' attachments.<br>';
                $notice .= '<strong>Processed: ' . intval($result['processed_rows']) . '/' . intval($result['total_rows']) . ' rows</strong>';
                if (!empty($result['warnings'])) {
                    $notice .= '<br><em>(' . count($result['warnings']) . ' warnings shown in notifications)</em>';
                }
                if ($dryRun) {
                    $notice .= '<br><em>(Dry run: no actual data created)</em>';
                }
                if ($debugDelay) {
                    $actualSeconds = intval($result['actual_time']);
                    $actualMinutes = floor($actualSeconds / 60);
                    $actualRemainingSeconds = $actualSeconds % 60;

                    $timeText = 'Actual time: ';
                    if ($actualMinutes > 0) {
                        $timeText .= $actualMinutes . ' minute' . ($actualMinutes > 1 ? 's' : '');
                    }
                    if ($actualRemainingSeconds > 0) {
                        $timeText .= ($timeText !== 'Actual time: ' ? ' ' : '') . $actualRemainingSeconds . ' second' . ($actualRemainingSeconds > 1 ? 's' : '');
                    }

                    $notice .= '<br><em>(Debug delay: 3 seconds between rows - ' . $timeText . ')</em>';
                }
                $notice .= '</p></div>';

                // Show warnings as toast notifications if any
                if (!empty($result['warnings'])) {
                    $notice .= '<script>';
                    foreach ($result['warnings'] as $warning) {
                        $notice .= 'window.showMFQIToast && window.showMFQIToast("' . esc_js($warning) . '", "warning", 5000);';
                    }
                    $notice .= '</script>';
                }

                // Pass timing info to JavaScript for progress calculation
                if ($debugDelay) {
                    $notice .= '<script>';
                    $notice .= 'window.mfqiEstimatedTime = ' . intval($result['estimated_time']) . ';';
                    $notice .= 'window.mfqiActualTime = ' . intval($result['actual_time']) . ';';
                    $notice .= 'window.mfqiTotalRows = ' . intval($result['total_rows']) . ';';
                    $notice .= 'window.mfqiProcessedRows = ' . intval($result['processed_rows']) . ';';
                    $notice .= '</script>';
                }
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

                $notice = '<div class="error"><p><strong>❌ Import Failed:</strong><br>' . esc_html($errorMessage) . '</p></div>';

                // Show error as toast notification as well
                $notice .= '<script>';
                $notice .= 'window.showMFQIToast && window.showMFQIToast("' . esc_js($errorMessage) . '", "error", 8000);';
                $notice .= '</script>';
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
     * Render quiz importer page (blank page)
     */
    public static function render_quiz_importer_page() {
        if (!current_user_can('edit_lp_courses')) {
            wp_die('Insufficient permissions');
        }
        ?>
        <div class="wrap">
            <h1>Quiz Importer</h1>
            <p>This is a blank quiz importer page. Content will be added later.</p>
        </div>
        <?php
    }

}
