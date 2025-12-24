<?php
/**
 * Frontend Core Class
 *
 * @package LP_Survey
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LP_Survey_Frontend Class
 */
class LP_Survey_Frontend
{

    /**
     * The single instance of the class
     *
     * @var LP_Survey_Frontend
     */
    protected static $_instance = null;

    /**
     * Main instance
     *
     * @return LP_Survey_Frontend
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
        // Enqueue frontend assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

        // AJAX handlers
        add_action('wp_ajax_mf_lp_survey_submit', array($this, 'ajax_submit_survey'));
        add_action('wp_ajax_mf_lp_survey_skip', array($this, 'ajax_skip_survey'));
        add_action('wp_ajax_mf_lp_survey_get', array($this, 'ajax_get_survey'));

        // Initialize survey display
        LP_Survey_Display::instance();
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets()
    {
        // Only enqueue on LearnPress pages
        if (!$this->is_learnpress_page()) {
            return;
        }

        // CSS
        wp_enqueue_style(
            'lp-survey-frontend',
            LP_SURVEY_URL . 'assets/css/frontend.css',
            array(),
            LP_SURVEY_VERSION
        );

        // JS
        wp_enqueue_script(
            'lp-survey-frontend',
            LP_SURVEY_URL . 'assets/js/frontend.js',
            array('jquery'),
            LP_SURVEY_VERSION,
            true
        );

        // Prepare localize data
        $localize_data = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lp_survey_nonce'),
            'displayType' => LP_Survey_Helpers::get_display_type(),
            'canSkip' => LP_Survey_Helpers::can_skip_survey(),
        );

        // Check for pending survey from transient
        $user_id = get_current_user_id();

        if ($user_id) {
            $transient_key = 'lp_survey_show_for_user_' . $user_id;

            $pending_survey = get_transient($transient_key);

            if ($pending_survey) {

                // Delete transient immediately
                delete_transient($transient_key);

                // Add to localized data
                $localize_data['pendingSurvey'] = $pending_survey;

            } else {
            }
        } else {
        }


        // Localize script
        wp_localize_script(
            'lp-survey-frontend',
            'lpSurvey',
            $localize_data
        );
    }

    /**
     * Check if current page is a LearnPress page
     *
     * @return bool
     */
    private function is_learnpress_page()
    {
        if (!function_exists('learn_press_is_course')) {
            return false;
        }

        return learn_press_is_course() || is_singular('lp_lesson') || is_singular('lp_course');
    }

    /**
     * AJAX: Get survey data
     */
    public function ajax_get_survey()
    {

        // Check nonce but don't die on failure - just log it
        $nonce_check = check_ajax_referer('lp_survey_nonce', 'nonce', false);

        if (!$nonce_check) {
            wp_send_json_error(array('message' => 'Nonce verification failed'));
            return;
        }

        $survey_id = absint($_POST['survey_id'] ?? 0);
        $user_id = get_current_user_id();


        if (!$survey_id || !$user_id) {
            wp_send_json_error(array('message' => __('Invalid request', 'lp-survey')));
        }

        // Check if already answered
        $has_answered = LP_Survey_Database::has_user_answered($survey_id, $user_id);

        if ($has_answered) {
            wp_send_json_error(array('message' => __('You have already answered this survey', 'lp-survey')));
        }

        // Get questions
        $questions = LP_Survey_Database::get_questions($survey_id);

        if (empty($questions)) {
            wp_send_json_error(array('message' => __('No questions found', 'lp-survey')));
        }

        wp_send_json_success(array('questions' => $questions));
    }

    /**
     * AJAX: Submit survey
     */
    public function ajax_submit_survey()
    {
        check_ajax_referer('lp_survey_nonce', 'nonce');

        $survey_id = absint($_POST['survey_id'] ?? 0);
        $answers = $_POST['answers'] ?? array();
        $user_id = get_current_user_id();

        if (!$survey_id || !$user_id || empty($answers)) {
            wp_send_json_error(array('message' => __('Invalid request', 'lp-survey')));
        }

        // Check if already answered
        if (LP_Survey_Database::has_user_answered($survey_id, $user_id)) {
            wp_send_json_error(array('message' => __('You have already answered this survey', 'lp-survey')));
        }

        // Get questions to validate
        $questions = LP_Survey_Database::get_questions($survey_id);
        $question_types = array();
        foreach ($questions as $q) {
            $question_types[$q->id] = $q->type;
        }

        // Save answers
        foreach ($answers as $question_id => $answer) {
            $question_id = absint($question_id);
            $type = $question_types[$question_id] ?? 'text';
            $answer = LP_Survey_Helpers::sanitize_answer($answer, $type);

            // Skip empty text answers
            if ($type === 'text' && empty(trim($answer))) {
                continue;
            }

            LP_Survey_Database::save_answer($survey_id, $question_id, $user_id, $answer);
        }

        wp_send_json_success(array('message' => __('Thank you for your feedback!', 'lp-survey')));
    }

    /**
     * AJAX: Skip survey
     */
    public function ajax_skip_survey()
    {
        check_ajax_referer('lp_survey_nonce', 'nonce');

        // Just return success - survey can be skipped
        wp_send_json_success(array('message' => __('Survey skipped', 'lp-survey')));
    }
}
