<?php
/**
 * Quiz Creator Class
 *
 * @package MF_Quiz_Importer_For_LearnPress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Quiz creator class for creating LearnPress quizzes
 */
class MF_Quiz_Creator {
    
    /**
     * Check if LearnPress classes are available
     *
     * @return bool
     */
    private static function check_learnpress() {
        return class_exists('LP_Quiz_CURD') && class_exists('LP_Question_CURD');
    }
    
    /**
     * Create a quiz
     *
     * @param array $quiz_data Quiz data
     * @return int|WP_Error Quiz ID or error
     */
    public static function create_quiz($quiz_data) {
        if (!self::check_learnpress()) {
            return new WP_Error('learnpress_not_found', __('LearnPress is not installed or activated.', 'mf-quiz-importer-lp'));
        }
        
        // Validate data
        $validation = MF_Quiz_Parser::validate_quiz_data($quiz_data);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Sanitize data
        $quiz_data = MF_Quiz_Parser::sanitize_quiz_data($quiz_data);
        
        // Get settings
        $settings = get_option('mf_quiz_importer_settings', array(
            'default_quiz_duration' => 60,
            'default_passing_grade' => 70,
            'default_retake_count' => 0,
            'auto_publish' => false,
        ));
        
        // Create quiz post
        $quiz_id = wp_insert_post(array(
            'post_title' => $quiz_data['title'],
            'post_content' => isset($quiz_data['description']) ? $quiz_data['description'] : '',
            'post_status' => $settings['auto_publish'] ? 'publish' : 'draft',
            'post_type' => LP_QUIZ_CPT,
            'post_author' => get_current_user_id(),
        ));
        
        if (is_wp_error($quiz_id) || !$quiz_id) {
            error_log('MF Quiz Importer: Failed to create quiz post');
            return new WP_Error('quiz_creation_failed', __('Failed to create quiz.', 'mf-quiz-importer-lp'));
        }
        
        // Set quiz meta
        self::set_quiz_meta($quiz_id, $quiz_data, $settings);
        
        // Create questions if provided
        if (isset($quiz_data['questions']) && is_array($quiz_data['questions'])) {
            $question_count = self::create_questions($quiz_id, $quiz_data['questions']);
            error_log("MF Quiz Importer: Created {$question_count} questions for quiz {$quiz_id}");
        }
        
        error_log("MF Quiz Importer: Successfully created quiz {$quiz_id}");
        return $quiz_id;
    }
    
    /**
     * Set quiz meta data
     *
     * @param int $quiz_id Quiz ID
     * @param array $quiz_data Quiz data
     * @param array $settings Plugin settings
     */
    private static function set_quiz_meta($quiz_id, $quiz_data, $settings) {
        // Duration - LearnPress stores as string like "10 minute", "1 hour", etc.
        $duration = isset($quiz_data['duration']) ? absint($quiz_data['duration']) : $settings['default_quiz_duration'];
        update_post_meta($quiz_id, '_lp_duration', $duration . ' minute');
        
        // Passing grade
        $passing_grade = isset($quiz_data['passing_grade']) ? absint($quiz_data['passing_grade']) : $settings['default_passing_grade'];
        update_post_meta($quiz_id, '_lp_passing_grade', $passing_grade);
        update_post_meta($quiz_id, '_lp_passing_grade_type', 'percentage');
        
        // Retake count (0 for unlimited in LearnPress 4.x, not -1)
        $retake_count = isset($quiz_data['retake_count']) ? absint($quiz_data['retake_count']) : $settings['default_retake_count'];
        update_post_meta($quiz_id, '_lp_retake_count', $retake_count);
        
        // Additional meta for LearnPress 4.x
        update_post_meta($quiz_id, '_lp_review', 'yes');
        update_post_meta($quiz_id, '_lp_show_correct_review', 'yes');
        update_post_meta($quiz_id, '_lp_negative_marking', 'no');
        update_post_meta($quiz_id, '_lp_instant_check', 'no');
        update_post_meta($quiz_id, '_lp_minus_skip_questions', 'no');
        update_post_meta($quiz_id, '_lp_pagination', 1);
        update_post_meta($quiz_id, '_lp_preview', 'no');
    }
    
    /**
     * Create questions for a quiz
     *
     * @param int $quiz_id Quiz ID
     * @param array $questions Questions data
     * @return int Number of questions created
     */
    private static function create_questions($quiz_id, $questions) {
        $count = 0;
        
        foreach ($questions as $question_data) {
            $question_id = self::create_question($question_data);
            
            if (!is_wp_error($question_id) && $question_id) {
                $added = self::add_question_to_quiz($quiz_id, $question_id);
                if ($added) {
                    $count++;
                    error_log("MF Quiz Importer: Added question {$question_id} to quiz {$quiz_id}");
                } else {
                    error_log("MF Quiz Importer: Failed to add question {$question_id} to quiz {$quiz_id}");
                }
            } else {
                $error_msg = is_wp_error($question_id) ? $question_id->get_error_message() : 'Unknown error';
                error_log("MF Quiz Importer: Failed to create question - {$error_msg}");
            }
        }
        
        return $count;
    }
    
    /**
     * Create a single question
     *
     * @param array $question_data Question data
     * @return int|WP_Error Question ID or error
     */
    private static function create_question($question_data) {
        if (empty($question_data['title'])) {
            return new WP_Error('missing_question_title', __('Question title is required.', 'mf-quiz-importer-lp'));
        }
        
        // Sanitize data
        $question_data = MF_Quiz_Parser::sanitize_question_data($question_data);
        
        // Map question types to LearnPress format
        $type_map = array(
            'single_choice' => 'single_choice',
            'multiple_choice' => 'multi_choice',
            'multi_choice' => 'multi_choice',
            'true_or_false' => 'true_or_false',
            'true_false' => 'true_or_false',
        );
        
        $question_type = isset($question_data['type']) ? $question_data['type'] : 'true_or_false';
        $question_type = isset($type_map[$question_type]) ? $type_map[$question_type] : 'true_or_false';
        
        // Create question post
        $question_id = wp_insert_post(array(
            'post_title' => $question_data['title'],
            'post_content' => isset($question_data['content']) ? $question_data['content'] : '',
            'post_status' => 'publish',
            'post_type' => LP_QUESTION_CPT,
            'post_author' => get_current_user_id(),
        ));
        
        if (is_wp_error($question_id) || !$question_id) {
            return new WP_Error('question_creation_failed', __('Failed to create question.', 'mf-quiz-importer-lp'));
        }
        
        // Set question type
        update_post_meta($question_id, '_lp_type', $question_type);
        
        // Set question mark/point (default 1)
        $mark = isset($question_data['mark']) ? absint($question_data['mark']) : 1;
        update_post_meta($question_id, '_lp_mark', $mark);
        
        // Set question explanation (optional)
        if (isset($question_data['explanation'])) {
            update_post_meta($question_id, '_lp_explanation', $question_data['explanation']);
        }
        
        // Set question hint (optional)
        if (isset($question_data['hint'])) {
            update_post_meta($question_id, '_lp_hint', $question_data['hint']);
        }
        
        // Set question options/answers using LearnPress database table
        if (isset($question_data['answers']) && is_array($question_data['answers'])) {
            $result = self::set_question_answers($question_id, $question_data['answers'], $question_type);
            if (is_wp_error($result)) {
                error_log('MF Quiz Importer: Failed to set answers for question ' . $question_id . ' - ' . $result->get_error_message());
            }
        }
        
        return $question_id;
    }
    
    /**
     * Set question answers using LearnPress database table
     *
     * @param int $question_id Question ID
     * @param array $answers Answers data
     * @param string $question_type Question type
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    private static function set_question_answers($question_id, $answers, $question_type) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'learnpress_question_answers';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return new WP_Error('table_not_found', __('LearnPress question answers table not found.', 'mf-quiz-importer-lp'));
        }
        
        $order = 1;
        foreach ($answers as $index => $answer) {
            $answer_text = '';
            $is_correct = false;
            
            if (is_array($answer)) {
                $answer_text = isset($answer['text']) ? $answer['text'] : '';
                $is_correct = isset($answer['correct']) && $answer['correct'];
            } else {
                $answer_text = $answer;
            }
            
            // Generate unique value for answer
            if (function_exists('learn_press_random_value')) {
                $answer_value = learn_press_random_value();
            } else {
                $answer_value = wp_generate_password(32, false);
            }
            
            // Insert answer into database
            $inserted = $wpdb->insert(
                $table_name,
                array(
                    'question_id' => $question_id,
                    'title' => $answer_text,
                    'value' => $answer_value,
                    'is_true' => $is_correct ? 'yes' : '',
                    'order' => $order++,
                ),
                array('%d', '%s', '%s', '%s', '%d')
            );
            
            if ($inserted === false) {
                error_log('MF Quiz Importer: Failed to insert answer - ' . $wpdb->last_error);
                return new WP_Error('answer_insert_failed', __('Failed to insert answer.', 'mf-quiz-importer-lp'));
            }
        }
        
        return true;
    }
    
    /**
     * Add question to quiz using LearnPress API
     *
     * @param int $quiz_id Quiz ID
     * @param int $question_id Question ID
     * @return bool|int False on failure, insert ID on success
     */
    private static function add_question_to_quiz($quiz_id, $question_id) {
        // Use LearnPress CURD to add question
        if (class_exists('LP_Quiz_CURD')) {
            $quiz_curd = new LP_Quiz_CURD();
            $result = $quiz_curd->add_question($quiz_id, $question_id);
            
            if ($result) {
                return $result;
            } else {
                error_log("MF Quiz Importer: LP_Quiz_CURD::add_question returned false for quiz {$quiz_id}, question {$question_id}");
            }
        }
        
        // Fallback: Direct database insert
        global $wpdb;
        $table_name = $wpdb->prefix . 'learnpress_quiz_questions';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            error_log("MF Quiz Importer: Table {$table_name} does not exist");
            return false;
        }
        
        // Check if question already exists in quiz
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT quiz_question_id FROM $table_name WHERE quiz_id = %d AND question_id = %d",
            $quiz_id,
            $question_id
        ));
        
        if ($exists) {
            error_log("MF Quiz Importer: Question {$question_id} already exists in quiz {$quiz_id}");
            return false;
        }
        
        // Get max order
        $max_order = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(question_order) FROM $table_name WHERE quiz_id = %d",
            $quiz_id
        ));
        
        $question_order = $max_order ? $max_order + 1 : 1;
        
        // Insert question
        $inserted = $wpdb->insert(
            $table_name,
            array(
                'quiz_id' => $quiz_id,
                'question_id' => $question_id,
                'question_order' => $question_order,
            ),
            array('%d', '%d', '%d')
        );
        
        if ($inserted === false) {
            error_log("MF Quiz Importer: Failed to insert question {$question_id} into quiz {$quiz_id} - " . $wpdb->last_error);
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Delete quiz and its questions
     *
     * @param int $quiz_id Quiz ID
     * @return bool Success status
     */
    public static function delete_quiz($quiz_id) {
        // Get questions
        $questions = get_post_meta($quiz_id, '_lp_questions', true);
        
        // Delete questions
        if (is_array($questions)) {
            foreach ($questions as $question_id) {
                wp_delete_post($question_id, true);
            }
        }
        
        // Delete quiz
        $result = wp_delete_post($quiz_id, true);
        
        return !is_wp_error($result) && $result !== false;
    }
}