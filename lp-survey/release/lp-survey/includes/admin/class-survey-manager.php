<?php
/**
 * Survey Manager Class
 *
 * @package LP_Survey
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LP_Survey_Manager Class
 */
class LP_Survey_Manager
{

    /**
     * The single instance of the class
     *
     * @var LP_Survey_Manager
     */
    protected static $_instance = null;

    /**
     * Main instance
     *
     * @return LP_Survey_Manager
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        // Add admin menu
        add_action('admin_menu', array($this, 'mf_add_menu'), 101);

        // Enqueue assets
        add_action('admin_enqueue_scripts', array($this, 'mf_enqueue_assets'));

        // AJAX handlers
        add_action('wp_ajax_mf_lp_survey_add_question', array($this, 'mf_ajax_add_question'));
        add_action('wp_ajax_mf_lp_survey_edit_question', array($this, 'mf_ajax_edit_question'));
        add_action('wp_ajax_mf_lp_survey_delete_question', array($this, 'mf_ajax_delete_question'));
        add_action('wp_ajax_mf_lp_survey_reorder_questions', array($this, 'mf_ajax_reorder_questions'));
    }

    /**
     * Add admin menu
     */
    public function mf_add_menu()
    {
        add_submenu_page(
            'learn_press',
            __('Survey Questions', 'lp-survey'),
            __('Survey Questions', 'lp-survey'),
            'manage_options',
            'lp-survey-manager',
            array($this, 'mf_render_page')
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     */
    public function mf_enqueue_assets($hook)
    {
        // Only load on survey manager page
        if ($hook !== 'learnpress_page_lp-survey-manager') {
            return;
        }

        // jQuery UI for sortable
        wp_enqueue_script('jquery-ui-sortable');

        // CSS
        wp_enqueue_style(
            'lp-survey-manager',
            LP_SURVEY_URL . 'assets/css/survey-manager.css',
            array(),
            LP_SURVEY_VERSION
        );

        // JS
        wp_enqueue_script(
            'lp-survey-manager',
            LP_SURVEY_URL . 'assets/js/survey-manager.js',
            array('jquery', 'jquery-ui-sortable'),
            LP_SURVEY_VERSION,
            true
        );

        // Localize script
        wp_localize_script(
            'lp-survey-manager',
            'lpSurveyManager',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('lp_survey_manager'),
                'strings' => array(
                    'confirmDelete' => __('Are you sure you want to delete this question?', 'lp-survey'),
                    'error' => __('An error occurred. Please try again.', 'lp-survey'),
                    'success' => __('Changes saved successfully.', 'lp-survey'),
                ),
            )
        );
    }

    /**
     * Render page
     */
    public function mf_render_page()
    {
        include LP_SURVEY_PATH . 'templates/admin/survey-manager.php';
    }

    /**
     * AJAX: Add new question
     */
    public function mf_ajax_add_question()
    {
        // Check nonce
        check_ajax_referer('lp_survey_manager', 'nonce');

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'lp-survey')));
        }

        // Get data
        $survey_id = isset($_POST['survey_id']) ? intval($_POST['survey_id']) : 0;
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'rating';
        $content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : '';

        // Validate
        if (empty($survey_id) || empty($content)) {
            wp_send_json_error(array('message' => __('Missing required fields', 'lp-survey')));
        }

        // Insert question
        $question_id = LP_Survey_Database::mf_insert_question($survey_id, $type, $content);

        if ($question_id) {
            $question = LP_Survey_Database::mf_get_question($question_id);
            wp_send_json_success(array(
                'message' => __('Question added successfully', 'lp-survey'),
                'question' => $question,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to add question', 'lp-survey')));
        }
    }

    /**
     * AJAX: Edit question
     */
    public function mf_ajax_edit_question()
    {
        // Check nonce
        check_ajax_referer('lp_survey_manager', 'nonce');

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'lp-survey')));
        }

        // Get data
        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'rating';
        $content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : '';
        $order = isset($_POST['order']) ? intval($_POST['order']) : 1;

        // Validate
        if (empty($question_id) || empty($content)) {
            wp_send_json_error(array('message' => __('Missing required fields', 'lp-survey')));
        }

        // Update question
        $success = LP_Survey_Database::mf_update_question($question_id, $content, $type, $order);

        if ($success) {
            $question = LP_Survey_Database::mf_get_question($question_id);
            wp_send_json_success(array(
                'message' => __('Question updated successfully', 'lp-survey'),
                'question' => $question,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to update question', 'lp-survey')));
        }
    }

    /**
     * AJAX: Delete question
     */
    public function mf_ajax_delete_question()
    {
        // Check nonce
        check_ajax_referer('lp_survey_manager', 'nonce');

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'lp-survey')));
        }

        // Get data
        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;

        // Validate
        if (empty($question_id)) {
            wp_send_json_error(array('message' => __('Invalid question ID', 'lp-survey')));
        }

        // Delete question
        $success = LP_Survey_Database::mf_delete_question($question_id);

        if ($success) {
            wp_send_json_success(array(
                'message' => __('Question deleted successfully', 'lp-survey'),
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete question', 'lp-survey')));
        }
    }

    /**
     * AJAX: Reorder questions
     */
    public function mf_ajax_reorder_questions()
    {
        // Check nonce
        check_ajax_referer('lp_survey_manager', 'nonce');

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'lp-survey')));
        }

        // Get data
        $ordered_ids = isset($_POST['ordered_ids']) ? array_map('intval', $_POST['ordered_ids']) : array();

        // Validate
        if (empty($ordered_ids)) {
            wp_send_json_error(array('message' => __('No order data provided', 'lp-survey')));
        }

        // Reorder
        $success = LP_Survey_Database::mf_reorder_questions($ordered_ids);

        if ($success) {
            wp_send_json_success(array(
                'message' => __('Questions reordered successfully', 'lp-survey'),
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to reorder questions', 'lp-survey')));
        }
    }
}
