<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Handle progress checks, coupon creation, and delivery orchestration.
 */
class LP_UPPC_Service
{
	private static $instance = null;

	/**
	 * Cache of last evaluated thresholds per request.
	 *
	 * @var array
	 */
	private $triggered = array();

	public static function instance(): self
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct()
	{
		add_action('learn-press/user-completed-lesson', [$this, 'handle_user_progress_event'], 50, 3);
		add_action('learn-press/user-course-finished', [$this, 'handle_course_completed'], 50, 3);
		add_action('learn-press/user-course/finished', [$this, 'handle_course_object_finished'], 50, 1);
	}

	public function handle_user_progress_event(int $item_id, int $course_id, int $user_id): void
	{
		$this->maybe_handle_progress($course_id, $user_id);
	}

	public function handle_course_completed(int $course_id, int $user_id, $user_item_id): void
	{
		$this->maybe_handle_progress($course_id, $user_id, true);
	}

	public function handle_course_object_finished($user_course_model): void
	{
		if (!$user_course_model || !method_exists($user_course_model, 'get_course_id')) {
			return;
		}

		$course_id = (int) $user_course_model->get_course_id();
		$user_id = (int) $user_course_model->get_user_id();

		if ($course_id && $user_id) {
			$this->maybe_handle_progress($course_id, $user_id, true);
		}
	}

	private function maybe_handle_progress(int $course_id, int $user_id, bool $force_completed = false): void
	{
		if (!$this->is_addon_enabled()) {
			return;
		}

		if ('yes' !== get_post_meta($course_id, '_lp_uppc_enable_course', true)) {
			return;
		}

		$rules = get_post_meta($course_id, '_lp_uppc_rules', true);
		if (empty($rules) || !is_array($rules)) {
			return;
		}

		$user = learn_press_get_user($user_id);
		if (!$user) {
			return;
		}

		$course_data = $user->get_course_data($course_id);
		if (!$course_data) {
			return;
		}

		$progress = $force_completed ? 100 : $this->round_progress($course_data->get_percent_completed_items());

		$cache_key = $course_id . '|' . $user_id;
		if (isset($this->triggered[$cache_key]) && $this->triggered[$cache_key] >= $progress) {
			return;
		}

		$sorted_rules = $this->sort_rules($rules);
		foreach ($sorted_rules as $rule) {
			$threshold = (int) ($rule['progress'] ?? 0);
			if ($progress < $threshold) {
				break;
			}

			if (!$this->should_trigger_rule($course_id, $user_id, $threshold)) {
				continue;
			}

			$this->triggered[$cache_key] = $progress;

			$this->process_rule($course_id, $user_id, $threshold, $rule);
		}
	}

	private function sort_rules(array $rules): array
	{
		usort(
			$rules,
			static function ($a, $b) {
				return ($a['progress'] ?? 0) <=> ($b['progress'] ?? 0);
			}
		);

		return $rules;
	}

	private function round_progress($progress): int
	{
		if (!is_numeric($progress)) {
			return 0;
		}

		return (int) floor((float) $progress);
	}

	private function should_trigger_rule(int $course_id, int $user_id, int $threshold): bool
	{
		$meta_key = $this->get_coupon_sent_key($course_id, $threshold);
		$sent = get_user_meta($user_id, $meta_key, true);

		if (empty($sent)) {
			return true;
		}

		$retake_allowed = 'yes' === LP_UPPC_Settings::get_setting('lp_uppc_retake_send_again', 'no');
		if (!$retake_allowed) {
			return false;
		}

		$last_sent_course_item_id = (int) $sent['user_item_id'] ?? 0;
		if (!$last_sent_course_item_id) {
			return false;
		}

		$user = learn_press_get_user($user_id);
		if (!$user) {
			return false;
		}

		$course_data = $user->get_course_data($course_id);
		if (!$course_data) {
			return false;
		}

		$current_user_item_id = (int) $course_data->get_user_item_id();

		return $current_user_item_id && $current_user_item_id !== $last_sent_course_item_id;
	}

	private function process_rule(int $course_id, int $user_id, int $threshold, array $rule): void
	{
		$coupon = $this->generate_coupon($course_id, $user_id, $threshold, $rule);
		if (!$coupon) {
			return;
		}

		$meta_key = $this->get_coupon_sent_key($course_id, $threshold);
		$meta = array(
			'user_item_id' => $this->get_user_course_item_id($user_id, $course_id),
			'coupon_id' => $coupon->get_id(),
			'timestamp' => time(),
		);
		update_user_meta($user_id, $meta_key, $meta);

		$this->log_coupon_event($course_id, $user_id, $threshold, $coupon);

		LP_UPPC_Email::instance()->queue_coupon_email($coupon, $course_id, $user_id, $threshold, $rule);
	}

	private function generate_coupon(int $course_id, int $user_id, int $threshold, array $rule)
	{
		if (!class_exists('WC_Coupon') || !function_exists('wc_get_coupon_id_by_code')) {
			return null;
		}

		$discount_type = in_array($rule['coupon_type'] ?? '', array('percent', 'fixed'), true) ? $rule['coupon_type'] : 'percent';
		$amount = isset($rule['coupon_amount']) ? (float) $rule['coupon_amount'] : 0;
		$expiry_days = isset($rule['expiry_days']) ? (int) $rule['expiry_days'] : 0;

		if ($amount <= 0) {
			return null;
		}

		$code = $this->build_coupon_code($course_id, $user_id, $threshold);
		$coupon_id = wc_get_coupon_id_by_code($code);
		$coupon = $coupon_id ? new WC_Coupon($coupon_id) : new WC_Coupon();

		$coupon->set_code($code);
		$coupon->set_discount_type('percent' === $discount_type ? 'percent' : 'fixed_cart');
		$coupon->set_amount($amount);
		$coupon->set_usage_limit(1);
		$coupon->set_usage_limit_per_user(1);
		$coupon->set_individual_use(true);
		$coupon->set_email_restrictions(array_filter(array($this->get_user_email($user_id))));
		$coupon->set_free_shipping(false);
		$coupon->set_exclude_sale_items(false);

		if ($expiry_days > 0) {
			$coupon->set_date_expires(wc_string_to_datetime(gmdate('Y-m-d', time() + DAY_IN_SECONDS * $expiry_days)));
		} else {
			$coupon->set_date_expires(null);
		}

		$coupon->update_meta_data('lp_uppc_course_id', $course_id);
		$coupon->update_meta_data('lp_uppc_user_id', $user_id);
		$coupon->update_meta_data('lp_uppc_threshold', $threshold);

		$this->apply_coupon_targets($coupon, $rule, $course_id);

		try {
			$coupon->save();
		} catch (Exception $e) {
			return null;
		}

		return $coupon;
	}

	private function apply_coupon_targets(WC_Coupon $coupon, array $rule, int $current_course_id): void
	{
		switch ($this->normalize_apply_type($rule['applies_to'] ?? 'specific')) {
			case 'category':
				$categories = array_filter(array_map('absint', $rule['target_categories'] ?? array()));
				$coupon->set_product_categories($categories);
				$coupon->set_excluded_product_categories(array());
				$coupon->set_product_ids(array());
				$coupon->set_excluded_product_ids(array());
				break;

			case 'global':
				$coupon->set_product_categories(array());
				$coupon->set_product_ids(array());
				$coupon->set_excluded_product_categories(array());
				$excluded_courses = array_unique(
					array_filter(
						array_merge(
							array($current_course_id),
							array_map('absint', $rule['target_courses'] ?? array())
						)
					)
				);
				$coupon->set_excluded_product_ids($this->get_course_related_product_ids($excluded_courses));
				break;

			case 'specific':
			default:
				$course_ids = array_filter(array_map('absint', $rule['target_courses'] ?? array()));
				$product_ids = $this->get_course_related_product_ids($course_ids);
				$coupon->set_product_ids($product_ids);
				$coupon->set_product_categories(array());
				$coupon->set_excluded_product_categories(array());
				$coupon->set_excluded_product_ids(array());
				break;
		}
	}

	private function get_course_related_product_ids(array $course_ids): array
	{
		$product_ids = array();

		if (empty($course_ids)) {
			return $product_ids;
		}

		foreach ($course_ids as $course_id) {
			$product_id = LP_UPPC_Helper::get_course_product_id($course_id);
			if ($product_id) {
				$product_ids[] = $product_id;
			}
		}

		return array_filter(array_map('absint', array_unique($product_ids)));
	}

	private function get_coupon_sent_key(int $course_id, int $threshold): string
	{
		return '_lp_progress_coupon_sent_' . $course_id . '_' . $threshold;
	}

	private function get_user_course_item_id(int $user_id, int $course_id): int
	{
		$user = learn_press_get_user($user_id);
		if (!$user) {
			return 0;
		}

		$course_data = $user->get_course_data($course_id);
		if (!$course_data) {
			return 0;
		}

		return (int) $course_data->get_user_item_id();
	}

	private function build_coupon_code(int $course_id, int $user_id, int $threshold): string
	{
		$user = get_userdata($user_id);
		$prefix = 'LPUPPC';

		return sanitize_title(sprintf('%s-%d-%d-%d-%s', $prefix, $course_id, $threshold, $user_id, wp_generate_password(6, false)));
	}

	private function get_user_email(int $user_id): string
	{
		$user = get_userdata($user_id);
		if (!$user) {
			return '';
		}

		return $user->user_email;
	}

	private function normalize_apply_type($value): string
	{
		if (!in_array($value, array('specific', 'category', 'global'), true)) {
			return 'specific';
		}

		return $value;
	}

	private function is_addon_enabled(): bool
	{
		return 'yes' === LP_UPPC_Settings::get_setting('lp_uppc_enable', 'yes');
	}

	private function log_coupon_event(int $course_id, int $user_id, int $threshold, WC_Coupon $coupon): void
	{
		if ('yes' !== LP_UPPC_Settings::get_setting('lp_uppc_log_events', 'yes')) {
			return;
		}

		$logs = get_option('lp_uppc_logs', array());
		$product_id = LP_UPPC_Helper::get_course_product_id($course_id);
		$logs[] = array(
			'timestamp' => time(),
			'course_id' => $course_id,
			'user_id' => $user_id,
			'threshold' => $threshold,
			'coupon_id' => $coupon->get_id(),
			'coupon_txt' => $coupon->get_code(),
			'product_id' => $product_id,
		);

		update_option('lp_uppc_logs', $logs, false);
	}
}
