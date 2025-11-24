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
                $question_id = self::create_question($question_data);
                
                if ($question_id) {
                    // Add question to quiz
                    self::add_question_to_quiz($quiz_id, $question_id);
                    $results['imported']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = sprintf(
                        __('Failed to create question at index %d', 'mf-quiz-importer-lp'),
                        $index
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
    
    /**
     * Create a single question
     *
     * @param array $data Question data
     * @return int|false Question ID or false on failure
     */
    private static function create_question($data) {
        // Validate required fields
        if (empty($data['title'])) {
            throw new Exception(__('Question title is required', 'mf-quiz-importer-lp'));
        }
        
        if (empty($data['type'])) {
            $data['type'] = 'single_choice';
        }
        
        // Map question type
        $question_type = self::map_question_type($data['type']);
        
        // Create question post
        $question_id = wp_insert_post(array(
            'post_title' => sanitize_text_field($data['title']),
            'post_content' => isset($data['content']) ? wp_kses_post($data['content']) : '',
            'post_type' => 'lp_question',
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ));
        
        if (is_wp_error($question_id)) {
            return false;
        }
        
        // Set question type
        update_post_meta($question_id, '_lp_type', $question_type);
        
        // Add explanation if provided
        if (!empty($data['explanation'])) {
            update_post_meta($question_id, '_lp_explanation', wp_kses_post($data['explanation']));
        }
        
        // Add answers
        if (!empty($data['answers']) && is_array($data['answers'])) {
            self::add_answers_to_question($question_id, $data['answers'], $question_type);
        }
        
        return $question_id;
    }
    
    /**
     * Map question type to LearnPress format
     *
     * @param string $type Question type
     * @return string LearnPress question type
     */
    private static function map_question_type($type) {
        $type_map = array(
            // True/False variations
            'true_or_false' => 'true_or_false',
            'true_false' => 'true_or_false',
            'truefalse' => 'true_or_false',
            'boolean' => 'true_or_false',
            'bool' => 'true_or_false',
            
            // Single Choice variations
            'single_choice' => 'single_choice',
            'single' => 'single_choice',
            'singlechoice' => 'single_choice',
            'radio' => 'single_choice',
            'one_choice' => 'single_choice',
            
            // Multiple Choice variations
            'multi_choice' => 'multi_choice',
            'multiple_choice' => 'multi_choice',
            'multiplechoice' => 'multi_choice',
            'multichoice' => 'multi_choice',
            'multiple' => 'multi_choice',
            'checkbox' => 'multi_choice',
            'many_choice' => 'multi_choice',
            
            // Fill in Blanks variations
            'fill_in_blanks' => 'fill_in_blanks',
            'fill_in_blank' => 'fill_in_blanks',
            'fillinblanks' => 'fill_in_blanks',
            'fill_blanks' => 'fill_in_blanks',
            'blanks' => 'fill_in_blanks'
        );
        
        $type = strtolower(trim($type));
        return isset($type_map[$type]) ? $type_map[$type] : 'single_choice';
    }
    
    /**
     * Add answers to a question
     *
     * @param int $question_id Question ID
     * @param array $answers Array of answers
     * @param string $question_type Question type
     */
    private static function add_answers_to_question($question_id, $answers, $question_type) {
        $answer_data = array();
        $correct_answers = array();
        
        foreach ($answers as $index => $answer) {
            $answer_text = is_array($answer) ? $answer['text'] : $answer;
            $is_correct = is_array($answer) && isset($answer['correct']) ? (bool)$answer['correct'] : false;
            
            $answer_id = 'answer_' . ($index + 1);
            $answer_data[$answer_id] = array(
                'text' => sanitize_text_field($answer_text),
                'value' => $answer_id
            );
            
            if ($is_correct) {
                $correct_answers[] = $answer_id;
            }
        }
        
        // Save answers
        update_post_meta($question_id, '_lp_answer_options', $answer_data);
        
        // Save correct answer(s)
        if ($question_type === 'true_or_false' || $question_type === 'single_choice') {
            update_post_meta($question_id, '_lp_answer', !empty($correct_answers) ? $correct_answers[0] : '');
        } else {
            update_post_meta($question_id, '_lp_answer', $correct_answers);
        }
    }
    
    /**
     * Add question to quiz
     *
     * @param int $quiz_id Quiz ID
     * @param int $question_id Question ID
     */
    private static function add_question_to_quiz($quiz_id, $question_id) {
        // Get existing questions
        $questions = get_post_meta($quiz_id, '_lp_questions', true);
        
        if (!is_array($questions)) {
            $questions = array();
        }
        
        // Add new question if not already exists
        if (!in_array($question_id, $questions)) {
            $questions[] = $question_id;
            update_post_meta($quiz_id, '_lp_questions', $questions);
        }
    }
}
