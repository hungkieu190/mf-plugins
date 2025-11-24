<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Register addon settings inside LearnPress > Settings.
 */
class LP_UPPC_Settings extends LP_Abstract_Settings_Page
{
	private static $instance = null;

	public static function instance(): self
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function map_wp_editor_field_template(string $path): string
	{
		if ('wp-editor.php' !== basename($path)) {
			return $path;
		}

		$wysiwyg_template = LP_UPPC_INC_PATH . 'admin/meta-box/fields/wp-editor.php';
		if (file_exists($wysiwyg_template)) {
			return $wysiwyg_template;
		}

		return $path;
	}

	private function __construct()
	{
		$this->id = 'upsell-progress';
		$this->text = esc_html__('Upsell Progress', 'lp-upsell-progress-coupon');

		add_filter('learn-press/admin/settings-tabs-array', [$this, 'register_tab']);
		add_filter('learn-press/settings/' . $this->id . '/sections', [$this, 'get_sections']);
		add_filter('learnpress/meta-box/field-custom', [$this, 'map_wp_editor_field_template']);

		parent::__construct();
	}

	public function register_tab(array $tabs): array
	{
		$tabs[$this->id] = $this;

		return $tabs;
	}

	public function get_sections(): array
	{
		return [
			'general' => esc_html__('General', 'lp-upsell-progress-coupon'),
			'email' => esc_html__('Email', 'lp-upsell-progress-coupon'),
		];
	}

	public function get_settings($section = '', $tab = '')
	{
		if ('' === $section) {
			$section = 'general';
		}

		if ('general' === $section) {
			return $this->general_settings();
		}

		if ('email' === $section) {
			return $this->email_settings();
		}

		return [];
	}

	private function general_settings(): array
	{
		return [
			['type' => 'title', 'title' => esc_html__('Progress Coupon Rules', 'lp-upsell-progress-coupon')],
			[
				'id' => 'lp_uppc_enable',
				'title' => esc_html__('Enable addon', 'lp-upsell-progress-coupon'),
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => esc_html__('Automatically watch course progress and send coupons.', 'lp-upsell-progress-coupon'),
			],
			[
				'id' => 'lp_uppc_retake_send_again',
				'title' => esc_html__('Send again on course retake', 'lp-upsell-progress-coupon'),
				'type' => 'checkbox',
				'default' => 'no',
				'desc' => esc_html__('Allow coupon rules to trigger again when the learner retakes the course.', 'lp-upsell-progress-coupon'),
			],
			[
				'id' => 'lp_uppc_log_events',
				'title' => esc_html__('Log coupon events', 'lp-upsell-progress-coupon'),
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => esc_html__('Store coupon delivery history for auditing.', 'lp-upsell-progress-coupon'),
			],
			['type' => 'sectionend'],
		];
	}

	private function email_settings(): array
	{
		$nonce = wp_create_nonce('lp_uppc_preview_email');

		return [
			['type' => 'title', 'title' => esc_html__('Email Templates', 'lp-upsell-progress-coupon')],
			[
				'id' => 'lp_uppc_email_subject',
				'title' => esc_html__('Email subject', 'lp-upsell-progress-coupon'),
				'type' => 'text',
				'default' => esc_html__('Congrats {user_name}! Unlock your exclusive coupon for {next_course_name}', 'lp-upsell-progress-coupon'),
			],
			[
				'id' => 'lp_uppc_email_heading',
				'title' => esc_html__('Email heading', 'lp-upsell-progress-coupon'),
				'type' => 'text',
				'default' => esc_html__('You just unlocked a reward!', 'lp-upsell-progress-coupon'),
			],
			[
				'id' => 'lp_uppc_email_body',
				'title' => esc_html__('Email content', 'lp-upsell-progress-coupon'),
				'type' => 'wp-editor',
				'default' => wp_kses_post(
					__('<p>Hi {user_name},</p><p>You just reached {progress}% of <strong>{course_name}</strong>! Here is a coupon worth <strong>{discount}</strong> for your next learning step:</p><p><code>{coupon_code}</code></p><p>Redeem before {expiry_date}.</p><p><a href="{cta_url}" class="button">Use coupon now</a></p>', 'lp-upsell-progress-coupon')
				),
				'args' => [
					'textarea_rows' => 12,
				],
				'desc' => sprintf(
					'<button type="button" class="button lp-uppc-preview-email" data-nonce="%s" data-course-input="#lp_uppc_email_preview_course">%s</button>',
					esc_attr($nonce),
					esc_html__('Preview email', 'lp-upsell-progress-coupon')
				),
			],
			[
				'id' => 'lp_uppc_email_header_html',
				'title' => esc_html__('Email header HTML', 'lp-upsell-progress-coupon'),
				'type' => 'wp-editor',
				'default' => wp_kses_post(__('<p style="font-size:16px;">{site_name}</p>', 'lp-upsell-progress-coupon')),
				'args' => [
					'textarea_rows' => 4,
				],
			],
			[
				'id' => 'lp_uppc_email_footer_html',
				'title' => esc_html__('Email footer HTML', 'lp-upsell-progress-coupon'),
				'type' => 'wp-editor',
				'default' => wp_kses_post(__('<p style="font-size:12px;color:#666;">{site_name} &mdash; {site_url}</p>', 'lp-upsell-progress-coupon')),
				'args' => [
					'textarea_rows' => 4,
				],
			],
			[
				'id' => 'lp_uppc_email_preview_course',
				'title' => esc_html__('Preview course ID', 'lp-upsell-progress-coupon'),
				'type' => 'number',
				'css' => 'width:100px;',
				'default' => '',
				'desc' => esc_html__('Used when previewing the email template.', 'lp-upsell-progress-coupon'),
			],
			['type' => 'sectionend'],
		];
	}

	public static function get_setting(string $key, $default = '')
	{
		return LP_Settings::get_option($key, $default);
	}
}
