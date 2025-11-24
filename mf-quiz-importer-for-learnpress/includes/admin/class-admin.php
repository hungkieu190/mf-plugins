<?php
/**
 * Admin Class
 *
 * @package MF_Quiz_Importer_For_LearnPress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin functionality class
 */
class MF_Quiz_Importer_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_mf_quiz_importer_upload', array($this, 'handle_file_upload'));
        add_action('wp_ajax_mf_quiz_importer_process', array($this, 'handle_import_process'));
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('mf_quiz_importer_settings', 'mf_quiz_importer_settings', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_settings'),
        ));
    }
    
    /**
     * Sanitize settings
     *
     * @param array $settings Settings to sanitize
     * @return array Sanitized settings
     */
    public function sanitize_settings($settings) {
        $sanitized = array();
        
        if (isset($settings['default_quiz_duration'])) {
            $sanitized['default_quiz_duration'] = absint($settings['default_quiz_duration']);
        }
        
        if (isset($settings['default_passing_grade'])) {
            $sanitized['default_passing_grade'] = absint($settings['default_passing_grade']);
        }
        
        if (isset($settings['default_retake_count'])) {
            $sanitized['default_retake_count'] = absint($settings['default_retake_count']);
        }
        
        if (isset($settings['auto_publish'])) {
            $sanitized['auto_publish'] = (bool) $settings['auto_publish'];
        }
        
        return $sanitized;
    }
    
    /**
     * Handle file upload via AJAX
     */
    public function handle_file_upload() {
        check_ajax_referer('mf-quiz-importer-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mf-quiz-importer-lp')));
        }
        
        if (empty($_FILES['file'])) {
            wp_send_json_error(array('message' => __('No file uploaded.', 'mf-quiz-importer-lp')));
        }
        
        $file = $_FILES['file'];
        $allowed_types = array('text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/json');
        
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(array('message' => __('Invalid file type. Only CSV, Excel, and JSON files are allowed.', 'mf-quiz-importer-lp')));
        }
        
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/mf-quiz-importer/temp';
        
        if (!file_exists($plugin_upload_dir)) {
            wp_mkdir_p($plugin_upload_dir);
        }
        
        $filename = uniqid('quiz_import_') . '_' . sanitize_file_name($file['name']);
        $filepath = $plugin_upload_dir . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            wp_send_json_success(array(
                'message' => __('File uploaded successfully.', 'mf-quiz-importer-lp'),
                'file' => $filename,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to upload file.', 'mf-quiz-importer-lp')));
        }
    }
    
    /**
     * Handle import process via AJAX
     */
    public function handle_import_process() {
        check_ajax_referer('mf-quiz-importer-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mf-quiz-importer-lp')));
        }
        
        $filename = isset($_POST['file']) ? sanitize_text_field($_POST['file']) : '';
        $import_type = isset($_POST['import_type']) ? sanitize_text_field($_POST['import_type']) : 'quiz';
        $quiz_id = isset($_POST['quiz_id']) ? absint($_POST['quiz_id']) : null;
        
        if (empty($filename)) {
            wp_send_json_error(array('message' => __('No file specified.', 'mf-quiz-importer-lp')));
        }
        
        if ($import_type === 'questions' && empty($quiz_id)) {
            wp_send_json_error(array('message' => __('No target quiz specified.', 'mf-quiz-importer-lp')));
        }
        
        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['basedir'] . '/mf-quiz-importer/temp/' . $filename;
        
        if (!file_exists($filepath)) {
            wp_send_json_error(array('message' => __('File not found.', 'mf-quiz-importer-lp')));
        }
        
        // Process the import
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/admin/class-importer.php';
        $importer = new MF_Quiz_Importer();
        $result = $importer->import_from_file($filepath, $import_type, $quiz_id);
        
        // Clean up the temporary file
        unlink($filepath);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        $success_message = $import_type === 'quiz' 
            ? __('Quizzes imported successfully!', 'mf-quiz-importer-lp')
            : __('Questions imported successfully!', 'mf-quiz-importer-lp');
        
        $response = array(
            'message' => $success_message,
            'imported' => $result['imported'],
            'failed' => $result['failed'],
        );
        
        // Add errors if present
        if (isset($result['errors']) && !empty($result['errors'])) {
            $response['errors'] = $result['errors'];
        }
        
        wp_send_json_success($response);
    }
}

// Initialize admin class
new MF_Quiz_Importer_Admin();