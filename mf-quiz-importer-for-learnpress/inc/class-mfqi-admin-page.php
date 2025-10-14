<?php
if (!defined('ABSPATH')) exit;

class MFQI_Admin_Page {
    public static function register() {
        add_submenu_page(
            'learnpress',
            'Quiz Importer',
            'Quiz Importer',
            'manage_options',
            'mfqi-import',
            [__CLASS__, 'render']
        );
    }

    public static function render() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        $notice = '';
        if (!empty($_POST['mfqi_action']) && $_POST['mfqi_action'] === 'import' && check_admin_referer('mfqi_import_nonce')) {
            $file = $_FILES['mfqi_file'] ?? null;
            $delimiter = !empty($_POST['delimiter']) ? sanitize_text_field($_POST['delimiter']) : ';';
            $dryRun   = !empty($_POST['dry_run']) ? (bool) $_POST['dry_run'] : false;

            try {
                $importer = new MFQI_Importer([
                    'delimiter'   => $delimiter,
                    'supportXlsx' => false, // set true if you bundle PhpSpreadsheet
                    'dryRun'      => $dryRun,
                ]);
                $result = $importer->import_from_upload($file);

                $notice  = '<div class="updated"><p><strong>Imported:</strong> ' . intval($result['created_questions']) . ' questions, ';
                $notice .= intval($result['created_quizzes']) . ' quizzes, ';
                $notice .= intval($result['attached_to_courses']) . ' quiz-course attachments.';
                if (!empty($result['warnings'])) {
                    $notice .= '<br><strong>Warnings:</strong><br><ul style="margin-left:20px;">';
                    foreach ($result['warnings'] as $w) $notice .= '<li>' . esc_html($w) . '</li>';
                    $notice .= '</ul>';
                }
                if ($dryRun) {
                    $notice .= '<br><em>(Dry run: no actual data created)</em>';
                }
                $notice .= '</p></div>';
            } catch (Exception $e) {
                $notice = '<div class="error"><p><strong>Error:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
            }
        }

        include MFQI_PLUGIN_DIR . 'templates/admin-page.php';
        if ($notice) echo $notice;
    }
}
