<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Email handler for coupon notifications.
 */
class LP_UPPC_Email
{
	private static $instance = null;

	const CRON_HOOK = 'lp_uppc_send_coupon_email';

	public static function instance(): self
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct()
	{
		add_action(self::CRON_HOOK, [$this, 'process_queue_item'], 10, 1);
	}

	public function queue_coupon_email(WC_Coupon $coupon, int $course_id, int $user_id, int $threshold, array $rule): void
	{
		LP_UPPC_Helper::debug_log('queue_coupon_email | Coupon: ' . $coupon->get_code() . ' | User: ' . $user_id);

		$payload = $this->build_payload_from_coupon($coupon, $course_id, $user_id, $threshold, $rule);
		if (empty($payload)) {
			LP_UPPC_Helper::debug_log('❌ Failed to build payload from coupon');
			return;
		}

		LP_UPPC_Helper::debug_log('✓ Payload built | Email: ' . $payload['user_email'] . ' | User name: ' . $payload['user_name']);

		$payload = apply_filters('lp_uppc_email_payload', $payload, $coupon, $rule);

		$delay = (int) apply_filters('lp_uppc_email_delay_seconds', 0, $payload);
		$delay = $delay < 0 ? 0 : $delay;
		$run_time = time() + $delay;

		LP_UPPC_Helper::debug_log('Scheduling email | Delay: ' . $delay . 's | Run time: ' . date('Y-m-d H:i:s', $run_time));

		$scheduled = wp_schedule_single_event($run_time, self::CRON_HOOK, array($payload));

		if (false === $scheduled || 0 === $delay) {
			LP_UPPC_Helper::debug_log('Sending email immediately (scheduled=' . var_export($scheduled, true) . ', delay=' . $delay . ')');
			$this->deliver_email_now($payload);
		} else {
			LP_UPPC_Helper::debug_log('✓ Email scheduled successfully for ' . date('Y-m-d H:i:s', $run_time));
		}
	}

	public function process_queue_item($payload): void
	{
		LP_UPPC_Helper::debug_log('process_queue_item | Processing scheduled email');

		if (empty($payload) || !is_array($payload)) {
			LP_UPPC_Helper::debug_log('❌ Invalid payload in process_queue_item');
			return;
		}

		$this->deliver_email_now($payload);
	}

	private function deliver_email_now(array $payload): void
	{
		LP_UPPC_Helper::debug_log('deliver_email_now | Composing email for: ' . ($payload['user_email'] ?? 'unknown'));

		$composed = $this->compose_email_from_payload($payload);
		if (empty($composed)) {
			LP_UPPC_Helper::debug_log('❌ Failed to compose email from payload');
			return;
		}

		LP_UPPC_Helper::debug_log('✓ Email composed | Subject: ' . $composed['subject']);
		LP_UPPC_Helper::debug_log('📤 Calling wp_mail | To: ' . $payload['user_email']);

		$result = wp_mail($payload['user_email'], $composed['subject'], $composed['message'], $composed['headers']);

		if ($result) {
			LP_UPPC_Helper::debug_log('✅ wp_mail returned TRUE - Email sent successfully!');
		} else {
			LP_UPPC_Helper::debug_log('❌ wp_mail returned FALSE - Email failed to send!');
			// Check for PHPMailer errors
			global $phpmailer;
			if (isset($phpmailer) && is_object($phpmailer)) {
				LP_UPPC_Helper::debug_log('PHPMailer Error: ' . $phpmailer->ErrorInfo);
			}
		}

		do_action('lp_uppc_email_sent', $payload, $composed);
	}

	public function build_preview_payload(int $course_id, int $user_id): ?array
	{
		$course_id = absint($course_id);
		if (!$course_id) {
			return null;
		}

		$user = get_userdata($user_id);
		if (!$user) {
			$admins = get_users(array('role__in' => array('administrator'), 'number' => 1));
			$user = $admins ? $admins[0] : wp_get_current_user();
		}

		if (!$user || !$user->exists()) {
			return null;
		}

		$rules = get_post_meta($course_id, '_lp_uppc_rules', true);
		$rules = is_array($rules) ? $rules : array();
		$preview_rule = reset($rules) ?: array(
			'progress' => 50,
			'coupon_type' => LP_UPPC_Settings::get_setting('lp_uppc_default_discount_type', 'percent'),
			'coupon_amount' => (float) LP_UPPC_Settings::get_setting('lp_uppc_default_discount_value', 10),
			'expiry_days' => (int) LP_UPPC_Settings::get_setting('lp_uppc_default_expiry_days', 30),
			'target_courses' => array(),
		);

		$threshold = isset($preview_rule['progress']) ? (int) $preview_rule['progress'] : 50;
		$target_course_id = $this->resolve_target_course_id($course_id, $preview_rule);
		$payload = array(
			'user_id' => (int) $user->ID,
			'user_email' => $user->user_email ?: get_option('admin_email'),
			'user_name' => $user->display_name ?: $user->user_login,
			'course_id' => $course_id,
			'course_title' => get_the_title($course_id),
			'target_course_id' => $target_course_id,
			'target_course_title' => $target_course_id ? get_the_title($target_course_id) : '',
			'threshold' => $threshold,
			'coupon_code' => 'PREVIEW-' . strtoupper(wp_generate_password(6, false)),
			'discount_display' => $this->format_discount_display($preview_rule),
			'expiry_display' => $this->format_expiry_display($preview_rule, null),
			'cta_url' => $this->resolve_cta_url($target_course_id ?: $course_id),
			'rule' => $preview_rule,
			'coupon_id' => 0,
		);

		if (empty($payload['user_email'])) {
			$payload['user_email'] = get_option('admin_email');
		}

		return $payload;
	}

	public function compose_email_from_payload(array $payload): ?array
	{
		if (empty($payload['user_email'])) {
			return null;
		}

		$subject_template = LP_UPPC_Settings::get_setting('lp_uppc_email_subject', esc_html__('Congrats {user_name}! Unlock your exclusive coupon for {next_course_name}', 'lp-upsell-progress-coupon'));
		$heading_template = LP_UPPC_Settings::get_setting('lp_uppc_email_heading', esc_html__('You just unlocked a reward!', 'lp-upsell-progress-coupon'));
		$body_template = LP_UPPC_Settings::get_setting('lp_uppc_email_body', '');
		$header_template = LP_UPPC_Settings::get_setting('lp_uppc_email_header_html', '');
		$footer_template = LP_UPPC_Settings::get_setting('lp_uppc_email_footer_html', '');

		if (empty($body_template)) {
			$body_template = wp_kses_post(
				__('<p>Hi {user_name},</p><p>You just reached {progress}% of <strong>{course_name}</strong>! Here is a coupon worth <strong>{discount}</strong> for your next learning step:</p><p><code>{coupon_code}</code></p><p>Redeem before {expiry_date}.</p><p><a href="{cta_url}" class="button">Use coupon now</a></p>', 'lp-upsell-progress-coupon')
			);
		}

		$replacements = $this->build_replacements($payload);

		$subject = $this->apply_template_replacements($subject_template, $replacements);
		$subject = wp_strip_all_tags($subject);
		$heading = $this->apply_template_replacements($heading_template, $replacements);
		$body = $this->apply_template_replacements($body_template, $replacements);
		$header = $this->apply_template_replacements($header_template, $replacements);
		$footer = $this->apply_template_replacements($footer_template, $replacements);

		$message_parts = array();
		if (!empty($header)) {
			$message_parts[] = $header;
		}
		if (!empty($heading)) {
			$message_parts[] = '<h2>' . esc_html($heading) . '</h2>';
		}
		$message_parts[] = $body;
		if (!empty($footer)) {
			$message_parts[] = $footer;
		}

		$message = implode(PHP_EOL, $message_parts);
		$message = apply_filters('lp_uppc_email_message', $message, $payload);

		return array(
			'subject' => apply_filters('lp_uppc_email_subject', $subject, $payload),
			'message' => $message,
			'headers' => array('Content-Type: text/html; charset=UTF-8'),
		);
	}

	public function build_payload_from_coupon(WC_Coupon $coupon, int $course_id, int $user_id, int $threshold, array $rule): ?array
	{
		LP_UPPC_Helper::debug_log('build_payload_from_coupon | User: ' . $user_id . ' | Course: ' . $course_id);

		$user = get_userdata($user_id);
		if (!$user || !$user->user_email) {
			LP_UPPC_Helper::debug_log('❌ User not found or has no email | User ID: ' . $user_id);
			return null;
		}

		LP_UPPC_Helper::debug_log('✓ User found | Email: ' . $user->user_email . ' | Name: ' . ($user->display_name ?: $user->user_login));

		$target_course_id = $this->resolve_target_course_id($course_id, $rule);

		return array(
			'user_id' => $user_id,
			'user_email' => $user->user_email,
			'user_name' => $user->display_name ?: $user->user_login,
			'course_id' => $course_id,
			'course_title' => get_the_title($course_id),
			'target_course_id' => $target_course_id,
			'target_course_title' => $target_course_id ? get_the_title($target_course_id) : '',
			'threshold' => $threshold,
			'coupon_code' => $coupon->get_code(),
			'discount_display' => $this->format_discount_display($rule),
			'expiry_display' => $this->format_expiry_display($rule, $coupon),
			'cta_url' => $this->resolve_cta_url($target_course_id ?: $course_id),
			'rule' => $rule,
			'coupon_id' => $coupon->get_id(),
		);
	}

	public function send_coupon_email(WC_Coupon $coupon, int $course_id, int $user_id, int $threshold, array $rule): void
	{
		$user = get_userdata($user_id);
		if (!$user || empty($user->user_email)) {
			return;
		}

		$subject_template = LP_UPPC_Settings::get_setting('lp_uppc_email_subject', esc_html__('Congrats {user_name}! Unlock your exclusive coupon for {next_course_name}', 'lp-upsell-progress-coupon'));
		$heading = LP_UPPC_Settings::get_setting('lp_uppc_email_heading', esc_html__('You just unlocked a reward!', 'lp-upsell-progress-coupon'));
		$body_template = LP_UPPC_Settings::get_setting('lp_uppc_email_body', '');

		if (empty($body_template)) {
			$body_template = wp_kses_post(
				__('<p>Hi {user_name},</p><p>You just reached {progress}% of <strong>{course_name}</strong>! Here is a coupon worth <strong>{discount}</strong> for your next learning step:</p><p><code>{coupon_code}</code></p><p>Redeem before {expiry_date}.</p><p><a href="{cta_url}" class="button">Use coupon now</a></p>', 'lp-upsell-progress-coupon')
			);
		}

		$target_course_id = $this->resolve_target_course_id($course_id, $rule);
		$next_course_name = $target_course_id ? get_the_title($target_course_id) : '';
		$discount_display = $this->format_discount_display($rule);
		$expiry_date = $this->format_expiry_date($coupon);
		$cta_url = $this->resolve_cta_url($target_course_id ?: $course_id);

		$replacements = array(
			'{user_name}' => $user->display_name ?: $user->user_login,
			'{course_name}' => get_the_title($course_id),
			'{next_course_name}' => $next_course_name ?: get_the_title($course_id),
			'{progress}' => absint($threshold),
			'{discount}' => $discount_display,
			'{coupon_code}' => $coupon->get_code(),
			'{expiry_date}' => $expiry_date,
			'{cta_url}' => esc_url($cta_url),
		);

		$subject = strtr($subject_template, $replacements);
		$body = strtr($body_template, $replacements);

		$message = '<h2>' . esc_html($heading) . '</h2>' . $body;

		$headers = array('Content-Type: text/html; charset=UTF-8');

		wp_mail($user->user_email, $subject, $message, $headers);
	}

	private function build_replacements(array $payload): array
	{
		$course_title = $payload['course_title'] ?? get_the_title($payload['course_id'] ?? 0);
		$target_course_title = $payload['target_course_title'] ?? '';
		if (!$target_course_title) {
			$target_course_title = $course_title;
		}

		$site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
		$site_url = home_url();

		return array(
			'{user_name}' => $payload['user_name'] ?? '',
			'{course_name}' => $course_title ?: '',
			'{next_course_name}' => $target_course_title ?: '',
			'{progress}' => isset($payload['threshold']) ? absint($payload['threshold']) : '',
			'{discount}' => $payload['discount_display'] ?? '',
			'{coupon_code}' => $payload['coupon_code'] ?? '',
			'{expiry_date}' => $payload['expiry_display'] ?? '',
			'{cta_url}' => isset($payload['cta_url']) ? esc_url($payload['cta_url']) : '',
			'{site_name}' => $site_name,
			'{site_url}' => esc_url($site_url),
		);
	}

	private function apply_template_replacements($template, array $replacements): string
	{
		$template = (string) $template;
		if (empty($template)) {
			return '';
		}

		return strtr($template, $replacements);
	}

	private function resolve_target_course_id(int $course_id, array $rule): int
	{
		$course_ids = array_map('absint', $rule['target_courses'] ?? array());
		$course_ids = array_filter($course_ids);

		if (!empty($course_ids)) {
			return (int) array_shift($course_ids);
		}

		return $course_id;
	}

	private function resolve_cta_url(int $course_id): string
	{
		$product_id = LP_UPPC_Helper::get_course_product_id($course_id);
		if ($product_id) {
			$permalink = get_permalink($product_id);
			if ($permalink) {
				return $permalink;
			}
		}

		$course_link = get_permalink($course_id);
		if ($course_link) {
			return $course_link;
		}

		return home_url();
	}

	private function format_discount_display(array $rule): string
	{
		$type = $rule['coupon_type'] ?? 'percent';
		$amount = isset($rule['coupon_amount']) ? (float) $rule['coupon_amount'] : 0;

		if ('percent' === $type) {
			return wc_format_decimal($amount) . '%';
		}

		return function_exists('wc_price') ? wc_price($amount) : number_format_i18n($amount, 2);
	}

	private function format_expiry_date(WC_Coupon $coupon): string
	{
		$expiry = $coupon->get_date_expires();

		if ($expiry) {
			return date_i18n(get_option('date_format'), $expiry->getTimestamp());
		}

		return esc_html__('No expiry', 'lp-upsell-progress-coupon');
	}

	private function format_expiry_display(array $rule, ?WC_Coupon $coupon): string
	{
		if ($coupon instanceof WC_Coupon) {
			$expiry = $coupon->get_date_expires();
			if ($expiry) {
				return date_i18n(get_option('date_format'), $expiry->getTimestamp());
			}
		}

		$days = isset($rule['expiry_days']) ? (int) $rule['expiry_days'] : 0;
		if ($days > 0) {
			$timestamp = current_time('timestamp') + ($days * DAY_IN_SECONDS);
			return date_i18n(get_option('date_format'), $timestamp);
		}

		return esc_html__('No expiry', 'lp-upsell-progress-coupon');
	}
}
