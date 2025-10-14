<?php
if (!defined('ABSPATH')) exit;

class MFQI_Importer {
    protected $delimiter = ';';
    protected $supportXlsx = false;
    protected $dryRun = false;

    public function __construct($args = []) {
        if (!empty($args['delimiter']))   $this->delimiter   = $args['delimiter'];
        if (!empty($args['supportXlsx'])) $this->supportXlsx = (bool)$args['supportXlsx'];
        if (isset($args['dryRun']))       $this->dryRun      = (bool)$args['dryRun'];
    }

    /**
     * Import from uploaded file (CSV or XLSX)
     * @param array $file
     * @return array
     * @throws Exception
     */
    public function import_from_upload($file) {
        // --- Basic validations ---
        if (empty($file) || empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed');
        }
        $name = $file['name'];
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'xlsx'])) {
            throw new Exception('Only CSV or XLSX files are supported');
        }
        if ($ext === 'xlsx' && !$this->supportXlsx) {
            throw new Exception('XLSX requires PhpSpreadsheet. Enable $supportXlsx');
        }

        // --- Parse rows ---
        $rows = ($ext === 'csv') ? $this->parse_csv($file['tmp_name']) : $this->parse_xlsx($file['tmp_name']);

        // Expect header columns
        $required = ['course_id','section_name','quiz_title','question_type','question_text','answers','correct','mark'];
        $header   = array_map('trim', array_map('strval', array_shift($rows)));
        foreach ($required as $req) {
            if (!in_array($req, $header, true)) {
                throw new Exception("Missing header column: {$req}");
            }
        }

        $colIndex = array_flip($header);
        $quizMap  = []; // quiz_title => quiz_id
        $result = [
            'created_quizzes'       => 0,
            'created_questions'     => 0,
            'attached_to_courses'   => 0,
            'warnings'              => [],
        ];

        foreach ($rows as $i => $line) {
            if (empty(array_filter($line, fn($v)=>trim((string)$v) !== ''))) continue; // skip empty

            // --- Extract values ---
            $course_id    = intval($line[$colIndex['course_id']] ?? 0);
            $section_name = trim((string)($line[$colIndex['section_name']] ?? ''));
            $quiz_title   = trim((string)($line[$colIndex['quiz_title']] ?? ''));
            $q_type       = strtolower(trim((string)($line[$colIndex['question_type']] ?? 'single')));
            $q_text       = trim((string)($line[$colIndex['question_text']] ?? ''));
            $answers_str  = (string)($line[$colIndex['answers']] ?? '');
            $correct_str  = (string)($line[$colIndex['correct']] ?? '');
            $mark         = floatval($line[$colIndex['mark']] ?? 1);

            if (!$quiz_title || !$q_text) {
                $result['warnings'][] = "Row ".($i+2).": Missing quiz_title or question_text";
                continue;
            }

            // --- Ensure quiz exists ---
            if (!isset($quizMap[$quiz_title])) {
                $quiz_id = $this->ensure_quiz($quiz_title);
                $quizMap[$quiz_title] = $quiz_id;
                $result['created_quizzes']++;
            } else {
                $quiz_id = $quizMap[$quiz_title];
            }

            // --- Create question ---
            $answers = array_values(array_filter(array_map('trim', explode($this->delimiter, $answers_str)), fn($x)=>$x!==''));
            $correct = array_values(array_filter(array_map('trim', explode($this->delimiter, $correct_str)), fn($x)=>$x!==''));
            $question_id = $this->create_question($q_text, $q_type, $answers, $correct, $mark);
            if ($question_id) {
                $result['created_questions']++;
                $this->attach_question_to_quiz($quiz_id, $question_id);
            } else {
                $result['warnings'][] = "Row ".($i+2).": Failed to create question";
            }

            // --- Attach quiz to course/section if course_id provided ---
            if ($course_id > 0) {
                $attached = $this->attach_quiz_to_course($course_id, $quiz_id, $section_name ?: 'Imported Section');
                if ($attached) $result['attached_to_courses']++;
            }
        }

        return $result;
    }

    /** CSV parser */
    protected function parse_csv($path) {
        // Use fgetcsv with default comma delimiter for CSV file,
        // inner answer options are split later by $this->delimiter (default ';').
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle, 0)) !== false) {
                $rows[] = array_map('trim', $data);
            }
            fclose($handle);
        }
        return $rows;
    }

    /** XLSX parser (requires PhpSpreadsheet) */
    protected function parse_xlsx($path) {
        if (!$this->supportXlsx) throw new Exception('XLSX not enabled');
        // Example skeleton if you enable PhpSpreadsheet:
        // $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        // $spreadsheet = $reader->load($path);
        // $sheet = $spreadsheet->getActiveSheet();
        // $rows = [];
        // foreach ($sheet->toArray(null, true, true, true) as $row) { $rows[] = array_values($row); }
        // return $rows;
        return [];
    }

    /** Ensure quiz by title, return quiz_id */
    protected function ensure_quiz($title) {
        // Check existing
        $existing = get_page_by_title($title, OBJECT, 'lp_quiz');
        if ($existing) return $existing->ID;

        if ($this->dryRun) return 999999; // simulate

        // Create quiz post
        $quiz_id = wp_insert_post([
            'post_type'   => 'lp_quiz',
            'post_title'  => $title,
            'post_status' => 'publish'
        ]);
        return $quiz_id;
    }

    /**
     * Create a LearnPress question
     * @param string $text
     * @param string $type single|multiple|true_false|fill
     * @param array $answers
     * @param array $correct
     * @param float $mark
     * @return int|false
     */
    protected function create_question($text, $type, $answers, $correct, $mark) {
        if ($this->dryRun) return 999998; // simulate

        // Create question post
        $post_id = wp_insert_post([
            'post_type'   => 'lp_question',
            'post_title'  => wp_strip_all_tags(wp_trim_words($text, 8, '…')),
            'post_content'=> $text,
            'post_status' => 'publish'
        ]);

        if (is_wp_error($post_id) || !$post_id) return false;

        // --- Minimal meta (change to fit LP version if needed)
        // Question type
        update_post_meta($post_id, '_lp_type', $this->normalize_type($type));
        // Question mark/point
        update_post_meta($post_id, '_lp_mark', floatval($mark));

        // Answers meta (structure may vary by LP version)
        $normalized_type = $this->normalize_type($type);
        switch ($normalized_type) {
            case 'single_choice':
            case 'multi_choice':
                // Build answers array
                $answers_meta = [];
                foreach ($answers as $idx => $ans) {
                    $is_true = in_array($ans, $correct, true);
                    $answers_meta[] = [
                        'text'    => $ans,
                        'value'   => 'ans_' . ($idx+1),
                        'is_true' => $is_true ? 'yes' : 'no',
                    ];
                }
                // TODO: adjust meta keys for your LP version:
                update_post_meta($post_id, '_lp_answers', $answers_meta);
                break;

            case 'true_or_false':
                $truth = (strtolower($correct[0] ?? '') === 'true') ? 'true' : 'false';
                update_post_meta($post_id, '_lp_true_or_false', $truth);
                break;

            case 'fill_in_blanks':
                // Allow multiple synonyms as correct answers
                $valids = !empty($correct) ? $correct : $answers;
                update_post_meta($post_id, '_lp_fill_in_blanks', $valids);
                break;
        }

        return $post_id;
    }

    /** Attach question to quiz (preserve order) */
    protected function attach_question_to_quiz($quiz_id, $question_id) {
        if ($this->dryRun) return true;

        // Preferred: use LP internal APIs if available (pseudo):
        // if (function_exists('learn_press_add_question_to_quiz')) {
        //     return learn_press_add_question_to_quiz($quiz_id, $question_id);
        // }

        // Fallback: push to stored list
        $questions = get_post_meta($quiz_id, '_lp_questions', true);
        if (!is_array($questions)) $questions = [];
        if (!in_array($question_id, $questions, true)) {
            $questions[] = $question_id;
            update_post_meta($quiz_id, '_lp_questions', $questions);
        }
        return true;
    }

    /** Attach quiz to a course, create/find section */
    protected function attach_quiz_to_course($course_id, $quiz_id, $section_name) {
        if ($this->dryRun) return true;

        // Sections stored in meta: _lp_course_sections (structure differs by LP version)
        $sections = get_post_meta($course_id, '_lp_course_sections', true);
        if (!is_array($sections)) $sections = [];

        // Find or create section
        $section_id = null;
        foreach ($sections as $sid => $sec) {
            if (!empty($sec['title']) && $sec['title'] === $section_name) {
                $section_id = $sid;
                break;
            }
        }
        if (!$section_id) {
            // Create new section structure
            $section_id = wp_generate_uuid4();
            $sections[$section_id] = [
                'title'   => $section_name,
                'items'   => [],
                'order'   => count($sections),
                'section_id' => $section_id,
            ];
        }

        // Add quiz as an item in section
        $item_key = 'quiz_' . $quiz_id;
        if (!in_array($item_key, $sections[$section_id]['items'], true)) {
            $sections[$section_id]['items'][] = $item_key;
        }

        // Save
        update_post_meta($course_id, '_lp_course_sections', $sections);

        // Also ensure LP sense of curriculum items (compat):
        $curriculum = get_post_meta($course_id, '_lp_curriculum_items', true);
        if (!is_array($curriculum)) $curriculum = [];
        if (!in_array($quiz_id, $curriculum, true)) {
            $curriculum[] = $quiz_id;
            update_post_meta($course_id, '_lp_curriculum_items', $curriculum);
        }

        // Link post type to course if LP requires taxonomy/relationship
        wp_set_object_terms($quiz_id, (int)$course_id, 'course-item', true);

        return true;
    }

    /** Normalize question type to LearnPress expected values (best-effort) */
    protected function normalize_type($type) {
        $t = strtolower(trim($type));
        switch ($t) {
            case 'single':
            case 'single_choice':
            case 'single-choice':
                return 'single_choice';
            case 'multiple':
            case 'multi':
            case 'multi_choice':
            case 'multiple_choice':
                return 'multi_choice';
            case 'true_false':
            case 'true-false':
            case 'tf':
            case 'trueorfalse':
                return 'true_or_false';
            case 'fill':
            case 'fill_in_blank':
            case 'fill_in_blanks':
            case 'fib':
                return 'fill_in_blanks';
            default:
                return 'single_choice';
        }
    }
}
