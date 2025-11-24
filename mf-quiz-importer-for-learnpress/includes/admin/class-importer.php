<?php
/**
 * Importer Class
 *
 * @package MF_Quiz_Importer_For_LearnPress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Quiz importer class
 */
class MF_Quiz_Importer {
    
    /**
     * Import quizzes from file
     *
     * @param string $filepath Path to the import file
     * @param string $import_type Type of import: 'quiz' or 'questions'
     * @param int $quiz_id Target quiz ID (for questions import)
     * @return array|WP_Error Import results or error
     */
    public function import_from_file($filepath, $import_type = 'quiz', $quiz_id = null) {
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        
        switch (strtolower($extension)) {
            case 'csv':
                return $this->import_from_csv($filepath, $import_type, $quiz_id);
            case 'xlsx':
            case 'xls':
                return $this->import_from_excel($filepath, $import_type, $quiz_id);
            case 'json':
                return $this->import_from_json($filepath, $import_type, $quiz_id);
            default:
                return new WP_Error('invalid_file', __('Unsupported file format.', 'mf-quiz-importer-lp'));
        }
    }
    
    /**
     * Import from CSV file
     *
     * @param string $filepath Path to CSV file
     * @param string $import_type Type of import
     * @param int $quiz_id Target quiz ID
     * @return array|WP_Error Import results
     */
    private function import_from_csv($filepath, $import_type = 'quiz', $quiz_id = null) {
        if (!file_exists($filepath)) {
            return new WP_Error('file_not_found', __('File not found.', 'mf-quiz-importer-lp'));
        }
        
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            return new WP_Error('file_read_error', __('Could not read file.', 'mf-quiz-importer-lp'));
        }
        
        $imported = 0;
        $failed = 0;
        $header = fgetcsv($handle);
        
        if ($import_type === 'questions') {
            $questions = array();
            while (($row = fgetcsv($handle)) !== false) {
                $question_data = array_combine($header, $row);
                $questions[] = $this->parse_question_from_csv($question_data);
            }
            fclose($handle);
            
            $result = MF_Question_Importer::import_questions($quiz_id, $questions);
            return $result;
        } else {
            // Check if CSV has quiz with questions format
            $has_questions = in_array('question_title', $header);
            
            if ($has_questions) {
                // Group rows by quiz
                $quizzes = array();
                while (($row = fgetcsv($handle)) !== false) {
                    $data = array_combine($header, $row);
                    $quiz_title = $data['quiz_title'];
                    
                    if (!isset($quizzes[$quiz_title])) {
                        $quizzes[$quiz_title] = array(
                            'title' => $data['quiz_title'],
                            'description' => isset($data['quiz_description']) ? $data['quiz_description'] : '',
                            'duration' => isset($data['duration']) ? $data['duration'] : 60,
                            'passing_grade' => isset($data['passing_grade']) ? $data['passing_grade'] : 70,
                            'retake_count' => isset($data['retake_count']) ? $data['retake_count'] : 0,
                            'questions' => array()
                        );
                    }
                    
                    // Add question to quiz
                    if (!empty($data['question_title'])) {
                        $quizzes[$quiz_title]['questions'][] = $this->parse_question_from_csv($data, 'question_');
                    }
                }
                fclose($handle);
                
                // Create quizzes with questions
                foreach ($quizzes as $quiz_data) {
                    if ($this->create_quiz($quiz_data)) {
                        $imported++;
                    } else {
                        $failed++;
                    }
                }
            } else {
                // Simple quiz CSV without questions
                while (($row = fgetcsv($handle)) !== false) {
                    $quiz_data = array_combine($header, $row);
                    
                    if ($this->create_quiz($quiz_data)) {
                        $imported++;
                    } else {
                        $failed++;
                    }
                }
                fclose($handle);
            }
            
            return array(
                'imported' => $imported,
                'failed' => $failed,
            );
        }
    }
    
    /**
     * Import from Excel file
     *
     * @param string $filepath Path to Excel file
     * @param string $import_type Type of import
     * @param int $quiz_id Target quiz ID
     * @return array|WP_Error Import results
     */
    private function import_from_excel($filepath, $import_type = 'quiz', $quiz_id = null) {
        // Check if Excel parsing is supported
        $requirements = MF_Excel_Parser::check_requirements();
        if (is_wp_error($requirements)) {
            return $requirements;
        }
        
        // Parse Excel file
        $excel_data = MF_Excel_Parser::parse($filepath);
        if (is_wp_error($excel_data)) {
            return $excel_data;
        }
        
        // Convert to CSV-like format
        $csv_data = MF_Excel_Parser::to_csv_format($excel_data);
        
        if (empty($csv_data)) {
            return new WP_Error('empty_file', __('Excel file is empty or invalid.', 'mf-quiz-importer-lp'));
        }
        
        $imported = 0;
        $failed = 0;
        
        if ($import_type === 'questions') {
            $questions = array();
            foreach ($csv_data as $row) {
                $questions[] = $this->parse_question_from_csv($row);
            }
            
            $result = MF_Question_Importer::import_questions($quiz_id, $questions);
            return $result;
        } else {
            // Check if Excel has quiz with questions format
            $first_row = reset($csv_data);
            $has_questions = isset($first_row['question_title']);
            
            if ($has_questions) {
                // Group rows by quiz
                $quizzes = array();
                foreach ($csv_data as $data) {
                    $quiz_title = $data['quiz_title'];
                    
                    if (!isset($quizzes[$quiz_title])) {
                        $quizzes[$quiz_title] = array(
                            'title' => $data['quiz_title'],
                            'description' => isset($data['quiz_description']) ? $data['quiz_description'] : '',
                            'duration' => isset($data['duration']) ? $data['duration'] : 60,
                            'passing_grade' => isset($data['passing_grade']) ? $data['passing_grade'] : 70,
                            'retake_count' => isset($data['retake_count']) ? $data['retake_count'] : 0,
                            'questions' => array()
                        );
                    }
                    
                    // Add question to quiz
                    if (!empty($data['question_title'])) {
                        $quizzes[$quiz_title]['questions'][] = $this->parse_question_from_csv($data, 'question_');
                    }
                }
                
                // Create quizzes with questions
                foreach ($quizzes as $quiz_data) {
                    if ($this->create_quiz($quiz_data)) {
                        $imported++;
                    } else {
                        $failed++;
                    }
                }
            } else {
                // Simple quiz Excel without questions
                foreach ($csv_data as $quiz_data) {
                    if ($this->create_quiz($quiz_data)) {
                        $imported++;
                    } else {
                        $failed++;
                    }
                }
            }
            
            return array(
                'imported' => $imported,
                'failed' => $failed,
            );
        }
    }
    
    /**
     * Parse question data from CSV row
     *
     * @param array $row CSV row data
     * @param string $prefix Column prefix (e.g., 'question_' for quiz CSV)
     * @return array Parsed question data
     */
    private function parse_question_from_csv($row, $prefix = '') {
        $question = array(
            'title' => isset($row[$prefix . 'title']) ? $row[$prefix . 'title'] : '',
            'content' => isset($row[$prefix . 'content']) ? $row[$prefix . 'content'] : '',
            'type' => isset($row[$prefix . 'type']) ? $row[$prefix . 'type'] : 'single_choice',
            'explanation' => isset($row['explanation']) ? $row['explanation'] : '',
            'answers' => array()
        );
        
        // Parse answers
        $answer_index = 1;
        while (isset($row['answer_' . $answer_index]) && !empty($row['answer_' . $answer_index])) {
            $question['answers'][] = array(
                'text' => $row['answer_' . $answer_index],
                'correct' => false
            );
            $answer_index++;
        }
        
        // Parse correct answers
        if (isset($row['correct_answers']) && !empty($row['correct_answers'])) {
            $correct_indices = array_map('trim', explode(';', $row['correct_answers']));
            foreach ($correct_indices as $index) {
                $index = (int)$index - 1; // Convert to 0-based index
                if (isset($question['answers'][$index])) {
                    $question['answers'][$index]['correct'] = true;
                }
            }
        }
        
        return $question;
    }
    
    /**
     * Import from JSON file
     *
     * @param string $filepath Path to JSON file
     * @param string $import_type Type of import
     * @param int $quiz_id Target quiz ID
     * @return array|WP_Error Import results
     */
    private function import_from_json($filepath, $import_type = 'quiz', $quiz_id = null) {
        if (!file_exists($filepath)) {
            return new WP_Error('file_not_found', __('File not found.', 'mf-quiz-importer-lp'));
        }
        
        $json_content = file_get_contents($filepath);
        $data = json_decode($json_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_msg = sprintf(
                __('Invalid JSON format: %s', 'mf-quiz-importer-lp'),
                json_last_error_msg()
            );
            return new WP_Error('json_parse_error', $error_msg);
        }
        
        if (!is_array($data)) {
            return new WP_Error('invalid_format', __('JSON file must contain an array of quizzes or questions.', 'mf-quiz-importer-lp'));
        }
        
        if (empty($data)) {
            return new WP_Error('empty_file', __('JSON file is empty.', 'mf-quiz-importer-lp'));
        }
        
        if ($import_type === 'questions') {
            $result = MF_Question_Importer::import_questions($quiz_id, $data);
            return $result;
        } else {
            $imported = 0;
            $failed = 0;
            $errors = array();
            
            foreach ($data as $index => $quiz_data) {
                $result = $this->create_quiz($quiz_data);
                if ($result === true) {
                    $imported++;
                } else {
                    $failed++;
                    if (is_wp_error($result)) {
                        $errors[] = sprintf(
                            __('Quiz #%d (%s): %s', 'mf-quiz-importer-lp'),
                            $index + 1,
                            isset($quiz_data['title']) ? $quiz_data['title'] : 'Untitled',
                            $result->get_error_message()
                        );
                    }
                }
            }
            
            return array(
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
            );
        }
    }
    
    /**
     * Create a quiz from data
     *
     * @param array $quiz_data Quiz data
     * @return bool|WP_Error Success status or error
     */
    private function create_quiz($quiz_data) {
        if (empty($quiz_data['title'])) {
            return new WP_Error('missing_title', __('Quiz title is required.', 'mf-quiz-importer-lp'));
        }
        
        // Validate quiz data
        $validation = MF_Quiz_Parser::validate_quiz_data($quiz_data);
        if (is_wp_error($validation)) {
            error_log('MF Quiz Importer: Validation failed - ' . $validation->get_error_message());
            return $validation;
        }
        
        // Use MF_Quiz_Creator to create quiz
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/class-quiz-creator.php';
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/class-quiz-parser.php';
        
        $quiz_id = MF_Quiz_Creator::create_quiz($quiz_data);
        
        if (is_wp_error($quiz_id)) {
            error_log('MF Quiz Importer: Failed to create quiz - ' . $quiz_id->get_error_message());
            return $quiz_id;
        }
        
        return true;
    }
}