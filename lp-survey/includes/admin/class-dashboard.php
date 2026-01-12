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
        // Reset handler
        add_action('admin_post_mf_lp_survey_reset', array($this, 'reset_data'));
    }

    /**
     * Reset all survey answers
     */
    public function reset_data()
    {
        // Check nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'lp_survey_reset')) {
            wp_die(__('Security check failed', 'lp-survey'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page', 'lp-survey'));
        }

        global $wpdb;
        $table_answers = LP_Survey_Database::get_table_name('survey_answers');

        // Clear all answers
        $wpdb->query("TRUNCATE TABLE {$table_answers}");

        // Redirect back to dashboard
        wp_safe_redirect(admin_url('admin.php?page=lp-survey-dashboard&reset=success'));
        exit;
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
     * Get recent survey responses with pagination
     *
     * @param int $limit Number of responses
     * @param int $offset Offset for pagination
     * @return array
     */
    public static function get_recent_responses($limit = 10, $offset = 0, $type = '', $ref_id = 0)
    {
        global $wpdb;

        $table_surveys = LP_Survey_Database::get_table_name('surveys');
        $table_answers = LP_Survey_Database::get_table_name('survey_answers');
        $table_questions = LP_Survey_Database::get_table_name('survey_questions');

        $where_clauses = array();
        $params = array();

        if (!empty($type)) {
            $where_clauses[] = "s.type = %s";
            $params[] = $type;
        }

        if (!empty($ref_id)) {
            $where_clauses[] = "a.ref_id = %d";
            $params[] = $ref_id;
        }

        $where = !empty($where_clauses) ? " WHERE " . implode(' AND ', $where_clauses) : "";

        // Add limit and offset
        $params[] = $limit;
        $params[] = $offset;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
					a.id,
					a.user_id,
					a.answer,
					a.ref_id as item_ref_id,
					a.created_at,
					q.content as question,
					q.type as question_type,
					s.type as survey_type,
					s.title as survey_title
				FROM {$table_answers} a
				LEFT JOIN {$table_questions} q ON a.question_id = q.id
				LEFT JOIN {$table_surveys} s ON a.survey_id = s.id
				{$where}
				ORDER BY a.created_at DESC
				LIMIT %d OFFSET %d",
                ...$params
            )
        );

        return $results;
    }

    /**
     * Get total number of survey responses
     *
     * @param string $type Optional filter by type
     * @param int $ref_id Optional filter by item reference ID
     * @return int
     */
    public static function get_total_responses_count($type = '', $ref_id = 0)
    {
        global $wpdb;
        $table_answers = LP_Survey_Database::get_table_name('survey_answers');
        $table_surveys = LP_Survey_Database::get_table_name('surveys');

        $where_clauses = array();
        $params = array();

        if (!empty($type)) {
            $where_clauses[] = "s.type = %s";
            $params[] = $type;
        }

        if (!empty($ref_id)) {
            $where_clauses[] = "a.ref_id = %d";
            $params[] = $ref_id;
        }

        $where = !empty($where_clauses) ? " WHERE " . implode(' AND ', $where_clauses) : "";

        if (!empty($where)) {
            return (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) 
					FROM {$table_answers} a 
					JOIN {$table_surveys} s ON a.survey_id = s.id 
					{$where}",
                    ...$params
                )
            );
        }

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_answers}");
    }

    /**
     * Get grouped statistics for items (Lessons/Courses)
     *
     * @param string $type Survey type (lesson/course)
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function get_grouped_items_stats($type, $limit = 20, $offset = 0)
    {
        global $wpdb;
        $table_answers = LP_Survey_Database::get_table_name('survey_answers');
        $table_surveys = LP_Survey_Database::get_table_name('surveys');

        $query = $wpdb->prepare(
            "SELECT 
				a.ref_id,
				COUNT(DISTINCT a.user_id, a.survey_id) as total_responses,
				AVG(CASE WHEN q_type.type = 'rating' THEN CAST(a.answer AS DECIMAL(10,2)) ELSE NULL END) as avg_rating
			FROM {$table_answers} a
			JOIN {$table_surveys} s ON a.survey_id = s.id
			LEFT JOIN " . LP_Survey_Database::get_table_name('survey_questions') . " q_type ON a.question_id = q_type.id
			WHERE s.type = %s
			GROUP BY a.ref_id
			ORDER BY total_responses DESC
			LIMIT %d OFFSET %d",
            $type,
            $limit,
            $offset
        );

        return $wpdb->get_results($query);
    }

    /**
     * Get total count of unique items that have responses
     *
     * @param string $type Survey type
     * @return int
     */
    public static function get_total_grouped_items_count($type)
    {
        global $wpdb;
        $table_answers = LP_Survey_Database::get_table_name('survey_answers');
        $table_surveys = LP_Survey_Database::get_table_name('surveys');

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT a.ref_id) 
				FROM {$table_answers} a 
				JOIN {$table_surveys} s ON a.survey_id = s.id 
				WHERE s.type = %s",
                $type
            )
        );
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

        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
        $item_id = isset($_GET['item_id']) ? absint($_GET['item_id']) : 0;
        $where_clauses = array();
        $params = array();

        if (!empty($type)) {
            $where_clauses[] = "s.type = %s";
            $params[] = $type;
        }

        if (!empty($item_id)) {
            $where_clauses[] = "a.ref_id = %d";
            $params[] = $item_id;
        }

        $where = !empty($where_clauses) ? " WHERE " . implode(' AND ', $where_clauses) : "";

        // Get results
        $query = "SELECT 
				a.id,
				a.user_id,
				a.answer,
				a.ref_id as item_ref_id,
				a.created_at,
				q.content as question,
				q.type as question_type,
				s.type as survey_type,
				s.title as survey_title
			FROM {$table_answers} a
			LEFT JOIN {$table_questions} q ON a.question_id = q.id
			LEFT JOIN {$table_surveys} s ON a.survey_id = s.id
			{$where}
			ORDER BY a.created_at DESC";

        if (!empty($params)) {
            $results = $wpdb->get_results($wpdb->prepare($query, ...$params));
        } else {
            $results = $wpdb->get_results($query);
        }

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
            'Item Title',
            'Survey Title',
            'Question',
            'Question Type',
            'Answer',
            'Date',
        ));

        // Data rows
        foreach ($results as $row) {
            $user = get_userdata($row->user_id);
            $item_title = '';
            if ($row->survey_type === 'lesson') {
                $item_title = LP_Survey_Helpers::get_lesson_title($row->item_ref_id);
            } elseif ($row->survey_type === 'course') {
                $item_title = LP_Survey_Helpers::get_course_title($row->item_ref_id);
            }

            fputcsv($output, array(
                $row->id,
                $row->user_id,
                $user ? $user->display_name : 'N/A',
                $row->survey_type,
                $item_title,
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
