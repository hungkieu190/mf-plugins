<?php
/**
 * Dashboard & Reports
 *
 * @package LP_Survey
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LP_Survey_Dashboard Class
 */
class LP_Survey_Dashboard
{

    /**
     * The single instance of the class
     *
     * @var LP_Survey_Dashboard
     */
    protected static $_instance = null;

    /**
     * Main instance
     *
     * @return LP_Survey_Dashboard
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
        // Export handler
        add_action('admin_post_mf_lp_survey_export', array($this, 'export_data'));
    }

    /**
     * Get overview statistics
     *
     * @return array
     */
    public static function get_overview_stats()
    {
        global $wpdb;

        $table_surveys = LP_Survey_Database::get_table_name('surveys');
        $table_answers = LP_Survey_Database::get_table_name('survey_answers');

        // Total surveys
        $total_surveys = $wpdb->get_var("SELECT COUNT(*) FROM {$table_surveys}");

        // Total responses
        $total_responses = $wpdb->get_var("SELECT COUNT(DISTINCT user_id, survey_id) FROM {$table_answers}");

        // Average rating
        $avg_rating = $wpdb->get_var(
            "SELECT AVG(CAST(answer AS DECIMAL(3,2))) 
			FROM {$table_answers} 
			WHERE answer REGEXP '^[0-9]+$'"
        );

        // Recent responses (last 30 days)
        $recent_responses = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_answers} WHERE created_at >= %s",
                date('Y-m-d H:i:s', strtotime('-30 days'))
            )
        );

        return array(
            'total_surveys' => (int) $total_surveys,
            'total_responses' => (int) $total_responses,
            'average_rating' => $avg_rating ? round($avg_rating, 2) : 0,
            'recent_responses' => (int) $recent_responses,
        );
    }

    /**
     * Get recent survey responses
     *
     * @param int $limit Number of responses
     * @return array
     */
    public static function get_recent_responses($limit = 10)
    {
        global $wpdb;

        $table_surveys = LP_Survey_Database::get_table_name('surveys');
        $table_answers = LP_Survey_Database::get_table_name('survey_answers');
        $table_questions = LP_Survey_Database::get_table_name('survey_questions');

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
					a.id,
					a.user_id,
					a.answer,
					a.created_at,
					q.content as question,
					q.type as question_type,
					s.type as survey_type,
					s.ref_id,
					s.title as survey_title
				FROM {$table_answers} a
				LEFT JOIN {$table_questions} q ON a.question_id = q.id
				LEFT JOIN {$table_surveys} s ON a.survey_id = s.id
				ORDER BY a.created_at DESC
				LIMIT %d",
                $limit
            )
        );

        return $results;
    }

    /**
     * Export survey data to CSV
     */
    public function export_data()
    {
        // Check nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'lp_survey_export')) {
            wp_die(__('Security check failed', 'lp-survey'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page', 'lp-survey'));
        }

        global $wpdb;

        $table_surveys = LP_Survey_Database::get_table_name('surveys');
        $table_answers = LP_Survey_Database::get_table_name('survey_answers');
        $table_questions = LP_Survey_Database::get_table_name('survey_questions');

        // Get all responses
        $results = $wpdb->get_results(
            "SELECT 
				a.id,
				a.user_id,
				a.answer,
				a.created_at,
				q.content as question,
				q.type as question_type,
				s.type as survey_type,
				s.ref_id,
				s.title as survey_title
			FROM {$table_answers} a
			LEFT JOIN {$table_questions} q ON a.question_id = q.id
			LEFT JOIN {$table_surveys} s ON a.survey_id = s.id
			ORDER BY a.created_at DESC"
        );

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=lp-survey-export-' . date('Y-m-d') . '.csv');

        // Output CSV
        $output = fopen('php://output', 'w');

        // Headers
        fputcsv($output, array(
            'ID',
            'User ID',
            'User Name',
            'Survey Type',
            'Survey Title',
            'Question',
            'Question Type',
            'Answer',
            'Date',
        ));

        // Data rows
        foreach ($results as $row) {
            $user = get_userdata($row->user_id);
            fputcsv($output, array(
                $row->id,
                $row->user_id,
                $user ? $user->display_name : 'N/A',
                $row->survey_type,
                $row->survey_title,
                $row->question,
                $row->question_type,
                $row->answer,
                $row->created_at,
            ));
        }

        fclose($output);
        exit;
    }

    /**
     * Render dashboard page
     */
    public static function render_dashboard_page()
    {
        include LP_SURVEY_PATH . 'templates/admin/dashboard.php';
    }
}
