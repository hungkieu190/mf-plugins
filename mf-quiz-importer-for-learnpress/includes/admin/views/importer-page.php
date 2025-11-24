<?php
/**
 * Admin Importer Page View
 *
 * @package MF_Quiz_Importer_For_LearnPress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'import-quiz';
// Backward compatibility
if ($current_tab === 'import') {
    $current_tab = 'import-quiz';
}

// Force enqueue CSS if not already loaded
if (!wp_style_is('mf-quiz-importer-admin', 'enqueued')) {
    wp_enqueue_style(
        'mf-quiz-importer-admin',
        MF_QUIZ_IMPORTER_PLUGIN_URL . 'assets/css/admin.css',
        array(),
        MF_QUIZ_IMPORTER_VERSION
    );
}
?>

<div class="wrap mf-quiz-importer-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Tabs Navigation -->
    <nav class="nav-tab-wrapper wp-clearfix">
        <a href="<?php echo esc_url(admin_url('admin.php?page=mf-quiz-importer&tab=import-quiz')); ?>" 
           class="nav-tab <?php echo ($current_tab === 'import-quiz' || $current_tab === 'import') ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-welcome-learn-more"></span>
            <?php _e('Import Quizzes', 'mf-quiz-importer-lp'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=mf-quiz-importer&tab=import-questions')); ?>" 
           class="nav-tab <?php echo $current_tab === 'import-questions' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-editor-help"></span>
            <?php _e('Import Questions', 'mf-quiz-importer-lp'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=mf-quiz-importer&tab=documentation')); ?>" 
           class="nav-tab <?php echo $current_tab === 'documentation' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-book"></span>
            <?php _e('Documentation', 'mf-quiz-importer-lp'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=mf-quiz-importer&tab=settings')); ?>" 
           class="nav-tab <?php echo $current_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-admin-settings"></span>
            <?php _e('Settings', 'mf-quiz-importer-lp'); ?>
        </a>
    </nav>
    
    <div class="mf-quiz-importer-container">
        
        <?php if ($current_tab === 'import-quiz') : ?>
            <!-- Import Quiz Tab Content -->
            <div class="mf-quiz-importer-card">
                <h2><?php _e('Import Quizzes', 'mf-quiz-importer-lp'); ?></h2>
            <p><?php _e('Upload a CSV, Excel, or JSON file to import complete quizzes with questions into LearnPress.', 'mf-quiz-importer-lp'); ?></p>
            
            <form id="mf-quiz-importer-form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="import_type" value="quiz">
                <div class="mf-upload-area">
                    <input type="file" id="mf-quiz-file" name="file" accept=".csv,.xlsx,.xls,.json" required>
                    <label for="mf-quiz-file" class="mf-upload-label">
                        <span class="dashicons dashicons-upload"></span>
                        <span class="mf-upload-text"><?php _e('Drag & Drop or Click to Upload', 'mf-quiz-importer-lp'); ?></span>
                        <span class="mf-upload-hint"><?php _e('Supported formats: CSV, Excel (XLSX/XLS), JSON', 'mf-quiz-importer-lp'); ?></span>
                    </label>
                </div>
                
                <div class="mf-file-info" style="display: none;">
                    <p>
                        <strong><?php _e('Selected file:', 'mf-quiz-importer-lp'); ?></strong><br>
                        <span id="mf-file-name"></span>
                    </p>
                </div>
                
                <div class="mf-import-actions">
                    <button type="submit" class="button button-primary button-large" id="mf-import-btn">
                        <?php _e('Import Quizzes', 'mf-quiz-importer-lp'); ?>
                    </button>
                </div>
                
                <div class="mf-progress" style="display: none;">
                    <div class="mf-progress-bar">
                        <div class="mf-progress-fill"></div>
                    </div>
                    <p class="mf-progress-text"><?php _e('Processing...', 'mf-quiz-importer-lp'); ?></p>
                </div>
                
                <div class="mf-result" style="display: none;"></div>
            </form>
        </div>
        
        <div class="mf-quiz-importer-card">
            <h2><?php _e('File Format Guide', 'mf-quiz-importer-lp'); ?></h2>
            
            <h3><?php _e('CSV Format Options', 'mf-quiz-importer-lp'); ?></h3>
            
            <h4><?php _e('Option 1: Simple Quiz (No Questions)', 'mf-quiz-importer-lp'); ?></h4>
            <p><?php _e('For importing quiz metadata only:', 'mf-quiz-importer-lp'); ?></p>
            <ul>
                <li><code>title</code> - <?php _e('Quiz title (required)', 'mf-quiz-importer-lp'); ?></li>
                <li><code>description</code> - <?php _e('Quiz description', 'mf-quiz-importer-lp'); ?></li>
                <li><code>duration</code> - <?php _e('Quiz duration in minutes', 'mf-quiz-importer-lp'); ?></li>
                <li><code>passing_grade</code> - <?php _e('Passing grade percentage', 'mf-quiz-importer-lp'); ?></li>
                <li><code>retake_count</code> - <?php _e('Number of retakes allowed', 'mf-quiz-importer-lp'); ?></li>
            </ul>
            
            <h4><?php _e('Option 2: Quiz with Questions', 'mf-quiz-importer-lp'); ?></h4>
            <p><?php _e('For importing complete quizzes with questions:', 'mf-quiz-importer-lp'); ?></p>
            <ul>
                <li><code>quiz_title</code> - <?php _e('Quiz title (required)', 'mf-quiz-importer-lp'); ?></li>
                <li><code>quiz_description</code> - <?php _e('Quiz description', 'mf-quiz-importer-lp'); ?></li>
                <li><code>duration</code> - <?php _e('Quiz duration in minutes', 'mf-quiz-importer-lp'); ?></li>
                <li><code>passing_grade</code> - <?php _e('Passing grade percentage', 'mf-quiz-importer-lp'); ?></li>
                <li><code>retake_count</code> - <?php _e('Number of retakes allowed', 'mf-quiz-importer-lp'); ?></li>
                <li><code>question_title</code> - <?php _e('Question title', 'mf-quiz-importer-lp'); ?></li>
                <li><code>question_content</code> - <?php _e('Question content', 'mf-quiz-importer-lp'); ?></li>
                <li><code>question_type</code> - <?php _e('Question type (see below)', 'mf-quiz-importer-lp'); ?></li>
                <li><code>answer_1, answer_2, ...</code> - <?php _e('Answer options', 'mf-quiz-importer-lp'); ?></li>
                <li><code>correct_answers</code> - <?php _e('Correct answer numbers (e.g., "1" or "1;3" for multiple)', 'mf-quiz-importer-lp'); ?></li>
                <li><code>explanation</code> - <?php _e('Answer explanation (optional)', 'mf-quiz-importer-lp'); ?></li>
            </ul>
            <pre><code>quiz_title,quiz_description,duration,passing_grade,retake_count,question_title,question_content,question_type,answer_1,answer_2,answer_3,correct_answers,explanation
"Math Quiz","Basic math",30,70,0,"What is 2+2?","Addition",single_choice,3,4,5,2,"2+2=4"
"Math Quiz","Basic math",30,70,0,"Is 10>5?","Compare",true_or_false,True,False,,1,"10 is greater"
"Math Quiz","Basic math",30,70,0,"Even numbers","Select all",multi_choice,2,3,4,1;3,"2 and 4 are even"</code></pre>
            <p class="description">
                <strong><?php _e('Note:', 'mf-quiz-importer-lp'); ?></strong> 
                <?php _e('Multiple rows with the same quiz_title will be grouped into one quiz. Use semicolon (;) to separate multiple correct answers.', 'mf-quiz-importer-lp'); ?>
            </p>
            
            <h3><?php _e('JSON Format', 'mf-quiz-importer-lp'); ?></h3>
            <p><?php _e('Your JSON file should be an array of quiz objects with questions:', 'mf-quiz-importer-lp'); ?></p>
            <pre><code>[
  {
    "title": "Sample Quiz",
    "description": "Quiz description",
    "duration": 60,
    "passing_grade": 70,
    "retake_count": 0,
    "questions": [
      {
        "title": "What is 2+2?",
        "content": "Simple math question",
        "type": "single_choice",
        "answers": [
          {"text": "3", "correct": false},
          {"text": "4", "correct": true},
          {"text": "5", "correct": false}
        ],
        "explanation": "2+2 equals 4"
      },
      {
        "title": "Is Earth round?",
        "content": "Geography question",
        "type": "true_or_false",
        "answers": [
          {"text": "True", "correct": true},
          {"text": "False", "correct": false}
        ],
        "explanation": "Earth is spherical"
      }
    ]
  }
]</code></pre>
            
            <h4><?php _e('Question Types (LearnPress Compatible)', 'mf-quiz-importer-lp'); ?></h4>
            <ul>
                <li><code>true_or_false</code> - <?php _e('True/False questions', 'mf-quiz-importer-lp'); ?></li>
                <li><code>single_choice</code> - <?php _e('Single choice (one correct answer)', 'mf-quiz-importer-lp'); ?></li>
                <li><code>multi_choice</code> - <?php _e('Multiple choice (multiple correct answers)', 'mf-quiz-importer-lp'); ?></li>
                <li><code>fill_in_blanks</code> - <?php _e('Fill in the blanks', 'mf-quiz-importer-lp'); ?></li>
            </ul>
            <p class="description">
                <?php _e('Note: You can also use variations like "multiple_choice", "checkbox", "boolean", etc. They will be automatically mapped to the correct LearnPress type.', 'mf-quiz-importer-lp'); ?>
            </p>
            
            <h3><?php _e('Download Sample Files', 'mf-quiz-importer-lp'); ?></h3>
            <p><?php _e('Download example files to see the correct format:', 'mf-quiz-importer-lp'); ?></p>
            <p>
                <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'samples/sample-quiz-complete.json'); ?>" class="button" download>
                    <span class="dashicons dashicons-media-code"></span>
                    <?php _e('Complete Quiz JSON', 'mf-quiz-importer-lp'); ?>
                </a>
                <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'samples/sample-quiz-with-questions.csv'); ?>" class="button" download>
                    <span class="dashicons dashicons-media-spreadsheet"></span>
                    <?php _e('Quiz with Questions CSV', 'mf-quiz-importer-lp'); ?>
                </a>
                <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'samples/sample-quiz.csv'); ?>" class="button" download>
                    <span class="dashicons dashicons-media-spreadsheet"></span>
                    <?php _e('Simple Quiz CSV', 'mf-quiz-importer-lp'); ?>
                </a>
                <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'samples/sample-quiz.json'); ?>" class="button" download>
                    <span class="dashicons dashicons-media-code"></span>
                    <?php _e('Quiz JSON (Original)', 'mf-quiz-importer-lp'); ?>
                </a>
            </p>
        </div>
        
        <?php elseif ($current_tab === 'import-questions') : ?>
            <!-- Import Questions Tab Content -->
            <div class="mf-quiz-importer-card">
                <h2><?php _e('Import Questions', 'mf-quiz-importer-lp'); ?></h2>
                <p><?php _e('Upload a file to import questions into an existing quiz.', 'mf-quiz-importer-lp'); ?></p>
            
                <form id="mf-question-importer-form" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="import_type" value="questions">
                    
                    <!-- Quiz Selection -->
                    <div class="mf-form-field">
                        <label for="target-quiz-id">
                            <strong><?php _e('Select Target Quiz:', 'mf-quiz-importer-lp'); ?></strong>
                        </label>
                        <select id="target-quiz-id" name="target_quiz_id" class="regular-text" required>
                            <option value=""><?php _e('-- Select a Quiz --', 'mf-quiz-importer-lp'); ?></option>
                            <?php
                            $quizzes = get_posts(array(
                                'post_type' => 'lp_quiz',
                                'posts_per_page' => -1,
                                'orderby' => 'title',
                                'order' => 'ASC',
                                'post_status' => array('publish', 'draft', 'pending')
                            ));
                            foreach ($quizzes as $quiz) {
                                printf(
                                    '<option value="%d">%s (ID: %d)</option>',
                                    $quiz->ID,
                                    esc_html($quiz->post_title),
                                    $quiz->ID
                                );
                            }
                            ?>
                        </select>
                        <p class="description"><?php _e('Choose the quiz where questions will be imported.', 'mf-quiz-importer-lp'); ?></p>
                    </div>
                    
                    <div class="mf-upload-area">
                        <input type="file" id="mf-question-file" name="file" accept=".csv,.xlsx,.xls,.json" required>
                        <label for="mf-question-file" class="mf-upload-label">
                            <span class="dashicons dashicons-upload"></span>
                            <span class="mf-upload-text"><?php _e('Drag & Drop or Click to Upload', 'mf-quiz-importer-lp'); ?></span>
                            <span class="mf-upload-hint"><?php _e('Supported formats: CSV, Excel (XLSX/XLS), JSON', 'mf-quiz-importer-lp'); ?></span>
                        </label>
                    </div>
                    
                    <div class="mf-file-info" style="display: none;">
                        <p>
                            <strong><?php _e('Selected file:', 'mf-quiz-importer-lp'); ?></strong><br>
                            <span id="mf-question-file-name"></span>
                        </p>
                    </div>
                    
                    <div class="mf-import-actions">
                        <button type="submit" class="button button-primary button-large" id="mf-question-import-btn">
                            <?php _e('Import Questions', 'mf-quiz-importer-lp'); ?>
                        </button>
                    </div>
                    
                    <div class="mf-progress" style="display: none;">
                        <div class="mf-progress-bar">
                            <div class="mf-progress-fill"></div>
                        </div>
                        <p class="mf-progress-text"><?php _e('Processing...', 'mf-quiz-importer-lp'); ?></p>
                    </div>
                    
                    <div class="mf-result" style="display: none;"></div>
                </form>
            </div>
            
            <div class="mf-quiz-importer-card">
                <h2><?php _e('Questions File Format', 'mf-quiz-importer-lp'); ?></h2>
                
                <h3><?php _e('CSV Format', 'mf-quiz-importer-lp'); ?></h3>
                <p><?php _e('Your CSV file should have the following columns:', 'mf-quiz-importer-lp'); ?></p>
                <ul>
                    <li><code>title</code> - <?php _e('Question title (required)', 'mf-quiz-importer-lp'); ?></li>
                    <li><code>content</code> - <?php _e('Question content/description', 'mf-quiz-importer-lp'); ?></li>
                    <li><code>type</code> - <?php _e('Question type (see below)', 'mf-quiz-importer-lp'); ?></li>
                    <li><code>answer_1, answer_2, answer_3, ...</code> - <?php _e('Answer options', 'mf-quiz-importer-lp'); ?></li>
                    <li><code>correct_answers</code> - <?php _e('Correct answer numbers separated by semicolon (e.g., "1" or "1;3")', 'mf-quiz-importer-lp'); ?></li>
                    <li><code>explanation</code> - <?php _e('Answer explanation (optional)', 'mf-quiz-importer-lp'); ?></li>
                </ul>
                <pre><code>title,content,type,answer_1,answer_2,answer_3,answer_4,correct_answers,explanation
"What is 2+2?","Simple math",single_choice,3,4,5,6,2,"2+2 equals 4"
"Is Earth round?","Geography",true_or_false,True,False,,,1,"Earth is spherical"
"Select even","Choose all",multi_choice,2,3,4,5,1;3,"2 and 4 are even"</code></pre>
                <p class="description">
                    <strong><?php _e('Note:', 'mf-quiz-importer-lp'); ?></strong> 
                    <?php _e('Use semicolon (;) to separate multiple correct answer numbers. Answer numbers start from 1.', 'mf-quiz-importer-lp'); ?>
                </p>
                
                <h3><?php _e('JSON Format', 'mf-quiz-importer-lp'); ?></h3>
                <p><?php _e('Your JSON file should be an array of question objects:', 'mf-quiz-importer-lp'); ?></p>
                <pre><code>[
  {
    "title": "What is 2+2?",
    "content": "Simple math question",
    "type": "single_choice",
    "answers": [
      {"text": "3", "correct": false},
      {"text": "4", "correct": true},
      {"text": "5", "correct": false}
    ],
    "explanation": "2+2 equals 4"
  },
  {
    "title": "Is Earth round?",
    "content": "Geography question",
    "type": "true_or_false",
    "answers": [
      {"text": "True", "correct": true},
      {"text": "False", "correct": false}
    ],
    "explanation": "Earth is spherical"
  },
  {
    "title": "Select all prime numbers",
    "content": "Choose all that apply",
    "type": "multi_choice",
    "answers": [
      {"text": "2", "correct": true},
      {"text": "3", "correct": true},
      {"text": "4", "correct": false},
      {"text": "5", "correct": true}
    ],
    "explanation": "Prime numbers are 2, 3, and 5"
  }
]</code></pre>
                
                <h4><?php _e('Question Types (LearnPress Compatible)', 'mf-quiz-importer-lp'); ?></h4>
                <ul>
                    <li><code>true_or_false</code> - <?php _e('True/False questions', 'mf-quiz-importer-lp'); ?></li>
                    <li><code>single_choice</code> - <?php _e('Single choice (one correct answer)', 'mf-quiz-importer-lp'); ?></li>
                    <li><code>multi_choice</code> - <?php _e('Multiple choice (multiple correct answers)', 'mf-quiz-importer-lp'); ?></li>
                    <li><code>fill_in_blanks</code> - <?php _e('Fill in the blanks', 'mf-quiz-importer-lp'); ?></li>
                </ul>
                <p class="description">
                    <?php _e('Note: You can also use variations like "multiple_choice", "checkbox", "boolean", etc. They will be automatically mapped to the correct LearnPress type.', 'mf-quiz-importer-lp'); ?>
                </p>
                
                <h3><?php _e('Download Sample Files', 'mf-quiz-importer-lp'); ?></h3>
                <p><?php _e('Download example files to see the correct format:', 'mf-quiz-importer-lp'); ?></p>
                <p>
                    <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'samples/sample-questions.json'); ?>" class="button" download>
                        <span class="dashicons dashicons-media-code"></span>
                        <?php _e('Questions JSON', 'mf-quiz-importer-lp'); ?>
                    </a>
                    <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'samples/sample-questions.csv'); ?>" class="button" download>
                        <span class="dashicons dashicons-media-spreadsheet"></span>
                        <?php _e('Questions CSV', 'mf-quiz-importer-lp'); ?>
                    </a>
                </p>
            </div>
        
        <?php elseif ($current_tab === 'documentation') : ?>
            <!-- Documentation Tab Content -->
            <div class="mf-quiz-importer-card">
                <h2>
                    <span class="dashicons dashicons-book"></span>
                    <?php _e('Documentation & Resources', 'mf-quiz-importer-lp'); ?>
                </h2>
                <p><?php _e('Complete guides and references to help you get the most out of Quiz Importer for LearnPress.', 'mf-quiz-importer-lp'); ?></p>
            </div>

            <!-- Quick Start -->
            <div class="mf-quiz-importer-card mf-doc-card">
                <h3>
                    <span class="dashicons dashicons-flag"></span>
                    <?php _e('Quick Start Guide', 'mf-quiz-importer-lp'); ?>
                </h3>
                <p><?php _e('Get started in 3 simple steps with examples and pro tips.', 'mf-quiz-importer-lp'); ?></p>
                <div class="mf-doc-actions">
                    <button type="button" class="button button-primary mf-view-doc" data-doc="QUICK-START.md" data-title="Quick Start Guide">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php _e('View Guide', 'mf-quiz-importer-lp'); ?>
                    </button>
                    <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'QUICK-START.md'); ?>" class="button button-secondary" download>
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Download', 'mf-quiz-importer-lp'); ?>
                    </a>
                </div>
                <div class="mf-doc-meta">
                    <span class="dashicons dashicons-clock"></span>
                    <?php _e('5 min read', 'mf-quiz-importer-lp'); ?> • 
                    <span class="dashicons dashicons-star-filled"></span>
                    <?php _e('Recommended for beginners', 'mf-quiz-importer-lp'); ?>
                </div>
            </div>

            <!-- Main Guides -->
            <div class="mf-quiz-importer-card mf-doc-card">
                <h3>
                    <span class="dashicons dashicons-book-alt"></span>
                    <?php _e('Complete Import Guide', 'mf-quiz-importer-lp'); ?>
                </h3>
                <p><?php _e('Comprehensive instructions for importing quizzes and questions with all file formats.', 'mf-quiz-importer-lp'); ?></p>
                <div class="mf-doc-actions">
                    <button type="button" class="button button-primary mf-view-doc" data-doc="IMPORT-GUIDE.md" data-title="Complete Import Guide">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php _e('View Guide', 'mf-quiz-importer-lp'); ?>
                    </button>
                    <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'IMPORT-GUIDE.md'); ?>" class="button button-secondary" download>
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Download', 'mf-quiz-importer-lp'); ?>
                    </a>
                </div>
                <div class="mf-doc-meta">
                    <span class="dashicons dashicons-clock"></span>
                    <?php _e('15 min read', 'mf-quiz-importer-lp'); ?> • 
                    <?php _e('Covers JSON, CSV formats', 'mf-quiz-importer-lp'); ?>
                </div>
            </div>

            <!-- Question Types -->
            <div class="mf-quiz-importer-card mf-doc-card">
                <h3>
                    <span class="dashicons dashicons-editor-help"></span>
                    <?php _e('Question Types Reference', 'mf-quiz-importer-lp'); ?>
                </h3>
                <p><?php _e('Complete reference for all LearnPress question types with examples and variations.', 'mf-quiz-importer-lp'); ?></p>
                <div class="mf-doc-actions">
                    <button type="button" class="button button-primary mf-view-doc" data-doc="QUESTION-TYPES.md" data-title="Question Types Reference">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php _e('View Reference', 'mf-quiz-importer-lp'); ?>
                    </button>
                    <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'QUESTION-TYPES.md'); ?>" class="button button-secondary" download>
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Download', 'mf-quiz-importer-lp'); ?>
                    </a>
                </div>
                <div class="mf-doc-meta">
                    <span class="dashicons dashicons-clock"></span>
                    <?php _e('10 min read', 'mf-quiz-importer-lp'); ?> • 
                    <?php _e('4 types, 20+ variations', 'mf-quiz-importer-lp'); ?>
                </div>
            </div>

            <!-- Features -->
            <div class="mf-quiz-importer-card mf-doc-card">
                <h3>
                    <span class="dashicons dashicons-star-filled"></span>
                    <?php _e('Features Overview', 'mf-quiz-importer-lp'); ?>
                </h3>
                <p><?php _e('Explore all features, capabilities, and technical details of the plugin.', 'mf-quiz-importer-lp'); ?></p>
                <div class="mf-doc-actions">
                    <button type="button" class="button button-primary mf-view-doc" data-doc="FEATURES.md" data-title="Features Overview">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php _e('View Features', 'mf-quiz-importer-lp'); ?>
                    </button>
                    <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'FEATURES.md'); ?>" class="button button-secondary" download>
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Download', 'mf-quiz-importer-lp'); ?>
                    </a>
                </div>
                <div class="mf-doc-meta">
                    <span class="dashicons dashicons-clock"></span>
                    <?php _e('8 min read', 'mf-quiz-importer-lp'); ?> • 
                    <?php _e('Complete feature list', 'mf-quiz-importer-lp'); ?>
                </div>
            </div>

            <!-- Technical Docs -->
            <div class="mf-quiz-importer-card mf-doc-card">
                <h3>
                    <span class="dashicons dashicons-admin-plugins"></span>
                    <?php _e('LearnPress Integration', 'mf-quiz-importer-lp'); ?>
                </h3>
                <p><?php _e('Technical documentation about LearnPress integration and compatibility.', 'mf-quiz-importer-lp'); ?></p>
                <div class="mf-doc-actions">
                    <button type="button" class="button button-primary mf-view-doc" data-doc="LEARNPRESS-INTEGRATION.md" data-title="LearnPress Integration">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php _e('View Guide', 'mf-quiz-importer-lp'); ?>
                    </button>
                    <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'LEARNPRESS-INTEGRATION.md'); ?>" class="button button-secondary" download>
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Download', 'mf-quiz-importer-lp'); ?>
                    </a>
                </div>
                <div class="mf-doc-meta">
                    <span class="dashicons dashicons-clock"></span>
                    <?php _e('12 min read', 'mf-quiz-importer-lp'); ?> • 
                    <?php _e('For developers', 'mf-quiz-importer-lp'); ?>
                </div>
            </div>

            <!-- Sample Files -->
            <div class="mf-quiz-importer-card">
                <h3>
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Sample Files', 'mf-quiz-importer-lp'); ?>
                </h3>
                <p><?php _e('Download example files to get started quickly:', 'mf-quiz-importer-lp'); ?></p>
                
                <h4><?php _e('Quiz Import Samples', 'mf-quiz-importer-lp'); ?></h4>
                <p>
                    <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'samples/sample-quiz-complete.json'); ?>" class="button" download>
                        <span class="dashicons dashicons-media-code"></span>
                        <?php _e('Complete Quiz JSON', 'mf-quiz-importer-lp'); ?>
                    </a>
                    <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'samples/sample-quiz-with-questions.csv'); ?>" class="button" download>
                        <span class="dashicons dashicons-media-spreadsheet"></span>
                        <?php _e('Quiz with Questions CSV', 'mf-quiz-importer-lp'); ?>
                    </a>
                    <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'samples/sample-quiz.csv'); ?>" class="button" download>
                        <span class="dashicons dashicons-media-spreadsheet"></span>
                        <?php _e('Simple Quiz CSV', 'mf-quiz-importer-lp'); ?>
                    </a>
                </p>

                <h4><?php _e('Question Import Samples', 'mf-quiz-importer-lp'); ?></h4>
                <p>
                    <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'samples/sample-questions.json'); ?>" class="button" download>
                        <span class="dashicons dashicons-media-code"></span>
                        <?php _e('Questions JSON', 'mf-quiz-importer-lp'); ?>
                    </a>
                    <a href="<?php echo esc_url(MF_QUIZ_IMPORTER_PLUGIN_URL . 'samples/sample-questions.csv'); ?>" class="button" download>
                        <span class="dashicons dashicons-media-spreadsheet"></span>
                        <?php _e('Questions CSV', 'mf-quiz-importer-lp'); ?>
                    </a>
                </p>
            </div>

            <!-- Support -->
            <div class="mf-quiz-importer-card mf-support-card">
                <h3>
                    <span class="dashicons dashicons-sos"></span>
                    <?php _e('Need Help?', 'mf-quiz-importer-lp'); ?>
                </h3>
                <p><?php _e('If you need assistance or have questions:', 'mf-quiz-importer-lp'); ?></p>
                <ul>
                    <li>
                        <span class="dashicons dashicons-book"></span>
                        <?php _e('Check the documentation above', 'mf-quiz-importer-lp'); ?>
                    </li>
                    <li>
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Try the sample files', 'mf-quiz-importer-lp'); ?>
                    </li>
                    <li>
                        <span class="dashicons dashicons-admin-site"></span>
                        <?php printf(
                            __('Visit our website: %s', 'mf-quiz-importer-lp'),
                            '<a href="https://mamflow.com" target="_blank">mamflow.com</a>'
                        ); ?>
                    </li>
                </ul>
            </div>
        
        <?php elseif ($current_tab === 'settings') : ?>
            <!-- Settings Tab Content -->
            <div class="mf-quiz-importer-card">
                <h2><?php _e('Default Settings', 'mf-quiz-importer-lp'); ?></h2>
                <p><?php _e('Configure default settings for imported quizzes. These values will be used when the import file does not specify them.', 'mf-quiz-importer-lp'); ?></p>
            
            <?php
            $settings = get_option('mf_quiz_importer_settings', array(
                'default_quiz_duration' => 60,
                'default_passing_grade' => 70,
                'default_retake_count' => 0,
                'auto_publish' => false,
            ));
            ?>
            
            <form method="post" action="options.php">
                <?php settings_fields('mf_quiz_importer_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="default_quiz_duration"><?php _e('Default Quiz Duration (minutes)', 'mf-quiz-importer-lp'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="default_quiz_duration" name="mf_quiz_importer_settings[default_quiz_duration]" value="<?php echo esc_attr($settings['default_quiz_duration']); ?>" min="1" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="default_passing_grade"><?php _e('Default Passing Grade (%)', 'mf-quiz-importer-lp'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="default_passing_grade" name="mf_quiz_importer_settings[default_passing_grade]" value="<?php echo esc_attr($settings['default_passing_grade']); ?>" min="0" max="100" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="default_retake_count"><?php _e('Default Retake Count', 'mf-quiz-importer-lp'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="default_retake_count" name="mf_quiz_importer_settings[default_retake_count]" value="<?php echo esc_attr($settings['default_retake_count']); ?>" min="0" class="regular-text">
                            <p class="description"><?php _e('0 = unlimited retakes', 'mf-quiz-importer-lp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="auto_publish"><?php _e('Auto Publish', 'mf-quiz-importer-lp'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="auto_publish" name="mf_quiz_importer_settings[auto_publish]" value="1" <?php checked($settings['auto_publish'], true); ?>>
                                <?php _e('Automatically publish imported quizzes', 'mf-quiz-importer-lp'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Save Settings', 'mf-quiz-importer-lp')); ?>
            </form>
        </div>
        
        <?php endif; ?>
        
    </div>
</div>

<!-- Documentation Modal -->
<div id="mf-doc-modal" class="mf-doc-modal">
    <div class="mf-doc-modal-content">
        <div class="mf-doc-modal-header">
            <h2 id="mf-doc-modal-title">
                <span class="dashicons dashicons-media-document"></span>
                <span></span>
            </h2>
            <button class="mf-doc-modal-close">&times;</button>
        </div>
        <div class="mf-doc-modal-body" id="mf-doc-modal-body">
            <div class="mf-doc-modal-loading">
                <span class="spinner is-active"></span>
                <p><?php _e('Loading documentation...', 'mf-quiz-importer-lp'); ?></p>
            </div>
        </div>
    </div>
</div>