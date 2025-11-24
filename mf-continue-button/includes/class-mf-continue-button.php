<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * Core class for MF-Continue-Button
 * - Tracks last visited item (lesson/quiz) per course for each user
 * - Renders "Continue Learning" button
 * - Auto-injects depending on settings
 */
class MF_Continue_Button {

    /** Meta key prefix for last item */
    const META_LAST_ITEM_PREFIX = '_mf_last_item_id_';
    const META_LAST_COURSE      = '_mf_last_course_id';

    public function __construct(){
        // 1) Track last item when user views a lesson/quiz page
        add_action('template_redirect', array($this, 'maybe_track_last_item'));

        // 2) Auto inject on course loop/cards (if enabled)
        add_action('learn-press/course-buttons',        array($this, 'print_button_in_loop'), 20);
        add_action('learn-press/after-course-buttons',  array($this, 'print_button_in_loop'), 20);

        // 3) Add to LP profile dashboard (if enabled)
        add_action('learn-press/profile-dashboard-summary', array($this, 'print_button_in_dashboard'));
    }

    /**
     * Track last visited lesson/quiz for logged-in user
     */
    public function maybe_track_last_item(){
        if ( ! is_user_logged_in() ) return;

        if ( is_singular('lp_lesson') || is_singular('lp_quiz') ) {
            $user_id = get_current_user_id();
            $item_id = get_queried_object_id();
            if ( ! $item_id ) return;

            $course_id = $this->get_course_id_by_item($item_id);
            if ( ! $course_id ) return;

            // Save last item per-course
            update_user_meta( $user_id, self::META_LAST_ITEM_PREFIX . $course_id, $item_id );
            // Also save global last course for quick access on dashboard
            update_user_meta( $user_id, self::META_LAST_COURSE, $course_id );
        }
    }

    /**
     * Try to print button inside LP course loop/cards
     */
    public function print_button_in_loop( $course = null ){
        $opts = function_exists('mf_cb_get_options') ? mf_cb_get_options() : array();
        if ( empty($opts['enable_loop']) ) return;

        if ( ! function_exists('learn_press_get_course') ) return;

        // Normalize course object
        if ( is_numeric($course) ) {
            $course_id = absint($course);
        } else if ( is_object($course) && method_exists($course, 'get_id') ) {
            $course_id = $course->get_id();
        } else {
            $course    = learn_press_get_course();
            $course_id = $course ? $course->get_id() : get_the_ID();
        }

        if ( ! $course_id ) return;

        echo self::render_button(array(
            'course_id' => $course_id,
            'label'     => $opts['label_continue'],
            'fallback'  => $opts['label_fallback'],
            'class'     => $opts['extra_class'],
            'echo'      => true,
        ));
    }

    /**
     * Print button in LP profile dashboard (global last course)
     */
    public function print_button_in_dashboard(){
        $opts = function_exists('mf_cb_get_options') ? mf_cb_get_options() : array();
        if ( empty($opts['enable_dashboard']) ) return;

        $user_id = get_current_user_id();
        if ( ! $user_id ) return;

        $course_id = (int) get_user_meta( $user_id, self::META_LAST_COURSE, true );
        if ( ! $course_id ) return;

        echo '<div class="mf-cb-dashboard">';
        echo '<h3 class="mf-cb-heading">Continue where you left off</h3>';
        echo self::render_button(array(
            'course_id' => $course_id,
            'label'     => $opts['label_continue'],
            'fallback'  => 'View Course',
            'echo'      => false,
            'class'     => 'is-dashboard ' . $opts['extra_class']
        ));
        echo '</div>';
    }

    /**
     * Public renderer for the button (supports shortcode and direct echo)
     * $args: course_id, label, fallback, class, echo
     */
    public static function render_button( $args = array() ){
        wp_enqueue_style('mf-cb-style');

        $defaults = array(
            'course_id' => 0,
            'label'     => 'Continue Learning →',
            'fallback'  => 'Start Learning',
            'class'     => '',
            'echo'      => false,
        );
        $args = wp_parse_args( $args, $defaults );

        if ( ! $args['course_id'] ) {
            // Try to infer from loop/single
            if ( function_exists('learn_press_get_course') ) {
                $course = learn_press_get_course();
                $args['course_id'] = $course ? $course->get_id() : 0;
            } else {
                $args['course_id'] = get_the_ID();
            }
        }

        $btn_html = self::build_button_html( (int)$args['course_id'], $args['label'], $args['fallback'], $args['class'] );

        if ( $args['echo'] ) { echo $btn_html; return ''; }
        return $btn_html;
    }

    /**
     * Build actual button HTML
     */
    private static function build_button_html( $course_id, $label, $fallback, $extra_class ){
        if ( ! $course_id ) return '';

        $opts = function_exists('mf_cb_get_options') ? mf_cb_get_options() : array();

        $url   = '';
        $text  = $label;
        $class = 'mf-continue-btn';

        // Respect only_logged_in option
        if ( ! is_user_logged_in() ) {
            if ( ! empty($opts['only_logged_in']) ) {
                return ''; // Hide completely if set to only logged-in
            }
            // Else show fallback to course page
            $url  = get_permalink($course_id);
            $text = esc_html($fallback);
            return sprintf('<a class="%s %s" href="%s">%s</a>', esc_attr($class), esc_attr($extra_class), esc_url($url), esc_html($text));
        }

        $user_id  = get_current_user_id();
        $last_id  = get_user_meta( $user_id, self::META_LAST_ITEM_PREFIX . $course_id, true );

        if ( $last_id ) {
            $item_url = self::get_item_url( $last_id, $course_id );
            if ( $item_url ) $url = $item_url;
        }

        if ( ! $url ) {
            // Fallback to first item or course permalink
            $first_item_id = self::get_first_item_id( $course_id );
            $url = $first_item_id ? self::get_item_url( $first_item_id, $course_id ) : get_permalink($course_id);
            $text = esc_html($fallback);
        }

        return sprintf('<a class="%s %s" href="%s">%s</a>',
            esc_attr($class),
            esc_attr($extra_class),
            esc_url($url),
            esc_html($text)
        );
    }

    /**
     * Resolve course id by item id. Use LP helper if available, fallback meta.
     */
    private function get_course_id_by_item( $item_id ){
        if ( function_exists('learn_press_get_course_id_by_item') ) {
            $cid = learn_press_get_course_id_by_item( $item_id );
            if ( $cid ) return $cid;
        }
        if ( function_exists('learn_press_get_item_course_id') ) {
            $cid = learn_press_get_item_course_id( $item_id );
            if ( $cid ) return $cid;
        }
        // Fallback: try post meta key used by LP
        $cid = (int) get_post_meta( $item_id, '_lp_course', true );
        return $cid ?: 0;
    }

    /**
     * Build item URL using LearnPress helper if available
     */
    private static function get_item_url( $item_id, $course_id ){
        if ( function_exists('learn_press_get_item_url') ) {
            return learn_press_get_item_url( $item_id, $course_id );
        }
        // Fallback to course permalink
        return get_permalink( $course_id );
    }

    /**
     * Get first curriculum item ID in a course (lesson/quiz)
     */
    private static function get_first_item_id( $course_id ){
        if ( ! function_exists('learn_press_get_course') ) return 0;
        $course = learn_press_get_course( $course_id );
        if ( ! $course ) return 0;

        // Try to grab first item from curriculum
        $sections = $course->get_curriculum_items();
        if ( empty($sections) ) return 0;

        if ( is_array($sections) ) {
            foreach( $sections as $it ){
                if ( is_object($it) && method_exists($it, 'get_id') ) {
                    $id = (int) $it->get_id();
                } else {
                    $id = is_numeric($it) ? (int)$it : 0;
                }
                if ( $id ) return $id;
            }
        }
        return 0;
    }
}

// Bootstrap
new MF_Continue_Button();
