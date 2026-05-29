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

        if (!$this->is_license_active()) {
            wp_send_json_error(array('message' => __('This feature requires an active license. Please activate your license to import quizzes.', 'mf-quiz-importer-lp')));
        }
        
        if (empty($_FILES['file'])) {
            wp_send_json_error(array('message' => __('No file uploaded.', 'mf-quiz-importer-lp')));
        }
        
        $file = $_FILES['file'];

        $validation = $this->validate_uploaded_file($file);
        if (is_wp_error($validation)) {
            wp_send_json_error(array('message' => $validation->get_error_message()));
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

        if (!$this->is_license_active()) {
            wp_send_json_error(array('message' => __('This feature requires an active license. Please activate your license to import quizzes.', 'mf-quiz-importer-lp')));
        }
        
        $filename = isset($_POST['file']) ? sanitize_file_name(wp_unslash($_POST['file'])) : '';
        $import_type = isset($_POST['import_type']) ? sanitize_text_field(wp_unslash($_POST['import_type'])) : 'quiz';
        $quiz_id = isset($_POST['quiz_id']) ? absint($_POST['quiz_id']) : null;
        
        if (empty($filename)) {
            wp_send_json_error(array('message' => __('No file specified.', 'mf-quiz-importer-lp')));
        }
        
        if ($import_type === 'questions' && empty($quiz_id)) {
            wp_send_json_error(array('message' => __('No target quiz specified.', 'mf-quiz-importer-lp')));
        }
        
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = trailingslashit($upload_dir['basedir']) . 'mf-quiz-importer/temp';
        $filepath = trailingslashit($plugin_upload_dir) . $filename;
        $real_upload_dir = realpath($plugin_upload_dir);
        $real_filepath = realpath($filepath);
        $normalized_upload_dir = $real_upload_dir ? trailingslashit(wp_normalize_path($real_upload_dir)) : '';
        $normalized_filepath = $real_filepath ? wp_normalize_path($real_filepath) : '';

        if (!$real_upload_dir || !$real_filepath || strpos($normalized_filepath, $normalized_upload_dir) !== 0 || !file_exists($real_filepath)) {
            wp_send_json_error(array('message' => __('File not found.', 'mf-quiz-importer-lp')));
        }
        
        // Process the import
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/admin/class-importer.php';
        $importer = new MF_Quiz_Importer();
        $result = $importer->import_from_file($real_filepath, $import_type, $quiz_id);
        
        // Clean up the temporary file
        unlink($real_filepath);
        
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

    /**
     * Check if the paid import feature is enabled by license.
     *
     * @return bool
     */
    private function is_license_active() {
        if (!class_exists('MF_Quiz_Importer_For_LearnPress')) {
            return false;
        }

        $license_handler = MF_Quiz_Importer_For_LearnPress::instance()->get_license_handler();

        return $license_handler && $license_handler->is_feature_enabled();
    }

    /**
     * Validate uploaded import file by extension first, MIME second.
     *
     * @param array $file Uploaded file array.
     * @return true|WP_Error
     */
    private function validate_uploaded_file($file) {
        $filename = isset($file['name']) ? $file['name'] : '';
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime_type = isset($file['type']) ? strtolower($file['type']) : '';

        $allowed_extensions = array('csv', 'xlsx', 'json');
        if (!in_array($extension, $allowed_extensions, true)) {
            if ($extension === 'xls') {
                return new WP_Error('xls_not_supported', __('Legacy XLS files are not supported. Please save the file as XLSX or CSV and try again.', 'mf-quiz-importer-lp'));
            }

            return new WP_Error('invalid_file_extension', __('Invalid file extension. Supported formats: CSV, XLSX, JSON.', 'mf-quiz-importer-lp'));
        }

        $allowed_mimes = array(
            'csv' => array('text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel', 'application/octet-stream', ''),
            'json' => array('application/json', 'text/plain', 'application/octet-stream', ''),
            'xlsx' => array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/octet-stream', 'application/zip', ''),
        );

        if (!in_array($mime_type, $allowed_mimes[$extension], true)) {
            return new WP_Error('invalid_file_type', __('Invalid file type. Supported formats: CSV, XLSX, JSON.', 'mf-quiz-importer-lp'));
        }

        return true;
    }
}

// Initialize admin class
new MF_Quiz_Importer_Admin();
