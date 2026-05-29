<?php
/**
 * Question Importer Class
 *
 * @package MF_Quiz_Importer_For_LearnPress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class MF_Question_Importer {
    
    /**
     * Import questions into a quiz
     *
     * @param int $quiz_id Target quiz ID
     * @param array $questions Array of question data
     * @return array Import results
     */
    public static function import_questions($quiz_id, $questions) {
        $results = array(
            'imported' => 0,
            'failed' => 0,
            'errors' => array()
        );
        
        // Verify quiz exists
        if (!get_post($quiz_id) || get_post_type($quiz_id) !== 'lp_quiz') {
            $results['errors'][] = __('Invalid quiz ID', 'mf-quiz-importer-lp');
            return $results;
        }
        
        foreach ($questions as $index => $question_data) {
            try {
                $question_id = MF_Quiz_Creator::create_question($question_data);
                
                if (!is_wp_error($question_id) && $question_id) {
                    // Add question to quiz using the same LearnPress 4-compatible path as full quiz imports.
                    $added = MF_Quiz_Creator::add_question_to_quiz($quiz_id, $question_id);

                    if (!$added) {
                        $results['failed']++;
                        $results['errors'][] = sprintf(
                            __('Failed to attach question at index %d to quiz.', 'mf-quiz-importer-lp'),
                            $index
                        );
                        continue;
                    }

                    $results['imported']++;
                } else {
                    $results['failed']++;
                    $message = is_wp_error($question_id) ? $question_id->get_error_message() : __('Unknown error', 'mf-quiz-importer-lp');
                    $results['errors'][] = sprintf(
                        __('Failed to create question at index %1$d: %2$s', 'mf-quiz-importer-lp'),
                        $index,
                        $message
                    );
                }
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = sprintf(
                    __('Error at question %d: %s', 'mf-quiz-importer-lp'),
                    $index,
                    $e->getMessage()
                );
            }
        }
        
        return $results;
    }
    
}
