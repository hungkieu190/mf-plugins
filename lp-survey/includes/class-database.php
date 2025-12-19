<?php
/**
 * Database Management Class
 *
 * @package LP_Survey
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LP_Survey_Database Class
 */
class LP_Survey_Database
{

    /**
     * Get table name with WordPress prefix
     *
     * @param string $table Table name without prefix
     * @return string Full table name with prefix
     */
    public static function get_table_name($table)
    {
        global $wpdb;
        return $wpdb->prefix . 'lp_' . $table;
    }

    /**
     * Create plugin tables
     */
    public static function create_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table: lp_surveys
        $table_surveys = self::get_table_name('surveys');
        $sql_surveys = "CREATE TABLE IF NOT EXISTS {$table_surveys} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			type varchar(20) NOT NULL DEFAULT 'lesson',
			ref_id bigint(20) unsigned NOT NULL,
			title varchar(255) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'active',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY type (type),
			KEY ref_id (ref_id),
			KEY status (status)
		) {$charset_collate};";

        // Table: lp_survey_questions
        $table_questions = self::get_table_name('survey_questions');
        $sql_questions = "CREATE TABLE IF NOT EXISTS {$table_questions} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			survey_id bigint(20) unsigned NOT NULL,
			type varchar(20) NOT NULL DEFAULT 'rating',
			content text NOT NULL,
			question_order int(11) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY survey_id (survey_id),
			KEY type (type),
			KEY question_order (question_order)
		) {$charset_collate};";

        // Table: lp_survey_answers
        $table_answers = self::get_table_name('survey_answers');
        $sql_answers = "CREATE TABLE IF NOT EXISTS {$table_answers} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			survey_id bigint(20) unsigned NOT NULL,
			question_id bigint(20) unsigned NOT NULL,
			user_id bigint(20) unsigned NOT NULL,
			answer text NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY survey_id (survey_id),
			KEY question_id (question_id),
			KEY user_id (user_id)
		) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_surveys);
        dbDelta($sql_questions);
        dbDelta($sql_answers);

        // Insert default lesson survey if not exists
        self::create_default_surveys();
    }

    /**
     * Create default survey templates
     */
    private static function create_default_surveys()
    {
        global $wpdb;

        $table_surveys = self::get_table_name('surveys');
        $table_questions = self::get_table_name('survey_questions');

        // Check if default lesson survey exists
        $lesson_survey = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table_surveys} WHERE type = %s AND ref_id = %d",
                'lesson',
                0
            )
        );

        if (!$lesson_survey) {
            // Create default lesson survey
            $wpdb->insert(
                $table_surveys,
                array(
                    'type' => 'lesson',
                    'ref_id' => 0,
                    'title' => __('Default Lesson Survey', 'lp-survey'),
                    'status' => 'active',
                ),
                array('%s', '%d', '%s', '%s')
            );

            $lesson_survey_id = $wpdb->insert_id;

            // Insert default lesson questions
            $lesson_questions = array(
                array(
                    'type' => 'rating',
                    'content' => __('How easy was this lesson to understand?', 'lp-survey'),
                    'order' => 1,
                ),
                array(
                    'type' => 'rating',
                    'content' => __('Was the lesson duration appropriate?', 'lp-survey'),
                    'order' => 2,
                ),
                array(
                    'type' => 'text',
                    'content' => __('What part did you find difficult or unclear?', 'lp-survey'),
                    'order' => 3,
                ),
            );

            foreach ($lesson_questions as $question) {
                $wpdb->insert(
                    $table_questions,
                    array(
                        'survey_id' => $lesson_survey_id,
                        'type' => $question['type'],
                        'content' => $question['content'],
                        'question_order' => $question['order'],
                    ),
                    array('%d', '%s', '%s', '%d')
                );
            }
        }

        // Check if default course survey exists
        $course_survey = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table_surveys} WHERE type = %s AND ref_id = %d",
                'course',
                0
            )
        );

        if (!$course_survey) {
            // Create default course survey
            $wpdb->insert(
                $table_surveys,
                array(
                    'type' => 'course',
                    'ref_id' => 0,
                    'title' => __('Default Course Survey', 'lp-survey'),
                    'status' => 'active',
                ),
                array('%s', '%d', '%s', '%s')
            );

            $course_survey_id = $wpdb->insert_id;

            // Insert default course questions
            $course_questions = array(
                array(
                    'type' => 'rating',
                    'content' => __('Overall course rating', 'lp-survey'),
                    'order' => 1,
                ),
                array(
                    'type' => 'rating',
                    'content' => __('Did the course meet your expectations?', 'lp-survey'),
                    'order' => 2,
                ),
                array(
                    'type' => 'rating',
                    'content' => __('Would you recommend this course to others?', 'lp-survey'),
                    'order' => 3,
                ),
                array(
                    'type' => 'text',
                    'content' => __('Additional feedback for the instructor', 'lp-survey'),
                    'order' => 4,
                ),
            );

            foreach ($course_questions as $question) {
                $wpdb->insert(
                    $table_questions,
                    array(
                        'survey_id' => $course_survey_id,
                        'type' => $question['type'],
                        'content' => $question['content'],
                        'question_order' => $question['order'],
                    ),
                    array('%d', '%s', '%s', '%d')
                );
            }
        }
    }

    /**
     * Get survey by type and ref_id
     *
     * @param string $type Survey type (lesson|course)
     * @param int    $ref_id Reference ID
     * @return object|null Survey object or null
     */
    public static function get_survey($type, $ref_id)
    {
        global $wpdb;
        $table = self::get_table_name('surveys');

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE type = %s AND ref_id = %d AND status = 'active' LIMIT 1",
                $type,
                $ref_id
            )
        );
    }

    /**
     * Get survey questions
     *
     * @param int $survey_id Survey ID
     * @return array Questions
     */
    public static function get_questions($survey_id)
    {
        global $wpdb;
        $table = self::get_table_name('survey_questions');

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE survey_id = %d ORDER BY question_order ASC",
                $survey_id
            )
        );
    }

    /**
     * Check if user has answered survey
     *
     * @param int $survey_id Survey ID
     * @param int $user_id User ID
     * @return bool
     */
    public static function has_user_answered($survey_id, $user_id)
    {
        global $wpdb;
        $table = self::get_table_name('survey_answers');

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE survey_id = %d AND user_id = %d",
                $survey_id,
                $user_id
            )
        );

        return $count > 0;
    }

    /**
     * Save survey answer
     *
     * @param int    $survey_id Survey ID
     * @param int    $question_id Question ID
     * @param int    $user_id User ID
     * @param string $answer Answer content
     * @return int|false Insert ID or false on failure
     */
    public static function save_answer($survey_id, $question_id, $user_id, $answer)
    {
        global $wpdb;
        $table = self::get_table_name('survey_answers');

        $result = $wpdb->insert(
            $table,
            array(
                'survey_id' => $survey_id,
                'question_id' => $question_id,
                'user_id' => $user_id,
                'answer' => $answer,
            ),
            array('%d', '%d', '%d', '%s')
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get survey statistics
     *
     * @param int $survey_id Survey ID
     * @return array Statistics
     */
    public static function get_survey_stats($survey_id)
    {
        global $wpdb;
        $table_answers = self::get_table_name('survey_answers');

        // Total responses
        $total_users = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT user_id) FROM {$table_answers} WHERE survey_id = %d",
                $survey_id
            )
        );

        // Average rating for rating questions
        $avg_rating = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT AVG(CAST(answer AS DECIMAL(3,2))) 
				FROM {$table_answers} 
				WHERE survey_id = %d 
				AND answer REGEXP '^[0-9]+$'",
                $survey_id
            )
        );

        return array(
            'total_responses' => (int) $total_users,
            'average_rating' => $avg_rating ? round($avg_rating, 2) : 0,
        );
    }
}
