<?php
/**
 * Quiz Parser Class
 *
 * @package MF_Quiz_Importer_For_LearnPress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Quiz parser class for handling different file formats
 */
class MF_Quiz_Parser {
    
    /**
     * Parse CSV content
     *
     * @param string $content CSV content
     * @return array Parsed data
     */
    public static function parse_csv($content) {
        $lines = explode("\n", $content);
        $header = str_getcsv(array_shift($lines));
        $data = array();
        
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }
            
            $row = str_getcsv($line);
            if (count($row) === count($header)) {
                $data[] = array_combine($header, $row);
            }
        }
        
        return $data;
    }
    
    /**
     * Parse JSON content
     *
     * @param string $content JSON content
     * @return array|WP_Error Parsed data or error
     */
    public static function parse_json($content) {
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_parse_error', __('Invalid JSON format.', 'mf-quiz-importer-lp'));
        }
        
        return $data;
    }
    
    /**
     * Validate quiz data
     *
     * @param array $quiz_data Quiz data to validate
     * @return bool|WP_Error True if valid, WP_Error otherwise
     */
    public static function validate_quiz_data($quiz_data) {
        $errors = array();
        
        // Check required fields
        if (empty($quiz_data['title'])) {
            $errors[] = __('Quiz title is required.', 'mf-quiz-importer-lp');
        }
        
        // Validate duration
        if (isset($quiz_data['duration'])) {
            if (!is_numeric($quiz_data['duration'])) {
                $errors[] = sprintf(
                    __('Invalid duration "%s". Must be a number (minutes).', 'mf-quiz-importer-lp'),
                    $quiz_data['duration']
                );
            } elseif ($quiz_data['duration'] < 0) {
                $errors[] = __('Duration cannot be negative.', 'mf-quiz-importer-lp');
            }
        }
        
        // Validate passing grade
        if (isset($quiz_data['passing_grade'])) {
            if (!is_numeric($quiz_data['passing_grade'])) {
                $errors[] = sprintf(
                    __('Invalid passing grade "%s". Must be a number between 0-100.', 'mf-quiz-importer-lp'),
                    $quiz_data['passing_grade']
                );
            } elseif ($quiz_data['passing_grade'] < 0 || $quiz_data['passing_grade'] > 100) {
                $errors[] = sprintf(
                    __('Passing grade must be between 0 and 100 (got %s).', 'mf-quiz-importer-lp'),
                    $quiz_data['passing_grade']
                );
            }
        }
        
        // Validate retake count
        if (isset($quiz_data['retake_count'])) {
            if (!is_numeric($quiz_data['retake_count'])) {
                $errors[] = sprintf(
                    __('Invalid retake count "%s". Must be a number.', 'mf-quiz-importer-lp'),
                    $quiz_data['retake_count']
                );
            } elseif ($quiz_data['retake_count'] < 0) {
                $errors[] = __('Retake count cannot be negative.', 'mf-quiz-importer-lp');
            }
        }
        
        // Validate questions if present
        if (isset($quiz_data['questions']) && is_array($quiz_data['questions'])) {
            foreach ($quiz_data['questions'] as $index => $question) {
                $question_errors = self::validate_question_data($question, $index + 1);
                if (!empty($question_errors)) {
                    $errors = array_merge($errors, $question_errors);
                }
            }
        }
        
        if (!empty($errors)) {
            return new WP_Error('validation_failed', implode(' ', $errors));
        }
        
        return true;
    }
    
    /**
     * Validate question data
     *
     * @param array $question_data Question data to validate
     * @param int $question_number Question number for error messages
     * @return array Array of error messages
     */
    public static function validate_question_data($question_data, $question_number = null) {
        $errors = array();
        $prefix = $question_number ? sprintf(__('Question #%d: ', 'mf-quiz-importer-lp'), $question_number) : '';
        
        // Check required fields
        if (empty($question_data['title'])) {
            $errors[] = $prefix . __('Question title is required.', 'mf-quiz-importer-lp');
        }
        
        // Validate question type
        if (empty($question_data['type'])) {
            $errors[] = $prefix . __('Question type is required.', 'mf-quiz-importer-lp');
        } else {
            $valid_types = array('true_or_false', 'single_choice', 'multi_choice', 'fill_in_blanks');
            $type = strtolower(trim($question_data['type']));
            
            // Check if it's a valid type or variation
            $is_valid = false;
            foreach ($valid_types as $valid_type) {
                if (strpos($type, str_replace('_', '', $valid_type)) !== false || 
                    strpos($type, $valid_type) !== false) {
                    $is_valid = true;
                    break;
                }
            }
            
            if (!$is_valid) {
                $errors[] = $prefix . sprintf(
                    __('Invalid question type "%s". Valid types: true_or_false, single_choice, multi_choice, fill_in_blanks', 'mf-quiz-importer-lp'),
                    $question_data['type']
                );
            }
        }
        
        // Validate answers
        if (empty($question_data['answers']) || !is_array($question_data['answers'])) {
            $errors[] = $prefix . __('Question must have at least one answer.', 'mf-quiz-importer-lp');
        } else {
            $has_correct = false;
            foreach ($question_data['answers'] as $answer) {
                if (isset($answer['correct']) && $answer['correct']) {
                    $has_correct = true;
                    break;
                }
            }
            
            if (!$has_correct) {
                $errors[] = $prefix . __('Question must have at least one correct answer.', 'mf-quiz-importer-lp');
            }
        }
        
        return $errors;
    }
    
    /**
     * Sanitize quiz data
     *
     * @param array $quiz_data Quiz data to sanitize
     * @return array Sanitized data
     */
    public static function sanitize_quiz_data($quiz_data) {
        $sanitized = array();
        
        if (isset($quiz_data['title'])) {
            $sanitized['title'] = sanitize_text_field($quiz_data['title']);
        }
        
        if (isset($quiz_data['description'])) {
            $sanitized['description'] = wp_kses_post($quiz_data['description']);
        }
        
        if (isset($quiz_data['duration'])) {
            $sanitized['duration'] = absint($quiz_data['duration']);
        }
        
        if (isset($quiz_data['passing_grade'])) {
            $sanitized['passing_grade'] = absint($quiz_data['passing_grade']);
        }
        
        if (isset($quiz_data['retake_count'])) {
            $sanitized['retake_count'] = absint($quiz_data['retake_count']);
        }
        
        if (isset($quiz_data['questions']) && is_array($quiz_data['questions'])) {
            $sanitized['questions'] = array_map(array(__CLASS__, 'sanitize_question_data'), $quiz_data['questions']);
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize question data
     *
     * @param array $question_data Question data to sanitize
     * @return array Sanitized data
     */
    public static function sanitize_question_data($question_data) {
        $sanitized = array();
        
        if (isset($question_data['title'])) {
            $sanitized['title'] = sanitize_text_field($question_data['title']);
        }
        
        if (isset($question_data['content'])) {
            $sanitized['content'] = wp_kses_post($question_data['content']);
        }
        
        if (isset($question_data['type'])) {
            $sanitized['type'] = sanitize_text_field($question_data['type']);
        }
        
        if (isset($question_data['answers']) && is_array($question_data['answers'])) {
            $sanitized['answers'] = $question_data['answers'];
        }
        
        if (isset($question_data['correct_answer'])) {
            $sanitized['correct_answer'] = $question_data['correct_answer'];
        }
        
        return $sanitized;
    }
}