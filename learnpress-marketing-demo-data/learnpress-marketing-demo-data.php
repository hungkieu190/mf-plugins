<?php
/**
 * Plugin Name: LearnPress Marketing Demo Data
 * Description: One-click marketing course demo data generator for LearnPress.
 * Version: 4.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: LearnPress Demo Tools
 * Text Domain: learnpress-marketing-demo-data
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

final class LP_Marketing_Demo_Data {
	private const MARKER = '_lp_marketing_demo_data';
	private const VERSION = '4.0.0';
	private const SLUG = 'lp-marketing-demo-data';

	public static function boot(): void {
		add_action('admin_menu', array(__CLASS__, 'admin_menu'), 30);
		add_action('admin_init', array(__CLASS__, 'maybe_repair_existing_course_prices'));
		add_action('admin_post_lp_mdd_install', array(__CLASS__, 'handle_install'));
		add_action('admin_post_lp_mdd_remove', array(__CLASS__, 'handle_remove'));
		add_action('admin_notices', array(__CLASS__, 'dependency_notice'));
	}

	public static function admin_menu(): void {
		$parent = defined('LP_COURSE_CPT') ? 'learn_press' : 'tools.php';

		add_submenu_page(
			$parent,
			__('Marketing Demo Data', 'learnpress-marketing-demo-data'),
			__('Marketing Demo Data', 'learnpress-marketing-demo-data'),
			'manage_options',
			self::SLUG,
			array(__CLASS__, 'render_page')
		);
	}

	public static function dependency_notice(): void {
		if (self::learnpress_ready()) {
			return;
		}

		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		if (! $screen || ! str_contains((string) $screen->id, self::SLUG)) {
			return;
		}

		echo '<div class="notice notice-error"><p>' . esc_html__('LearnPress must be installed and active before generating demo data.', 'learnpress-marketing-demo-data') . '</p></div>';
	}

	public static function render_page(): void {
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have permission to access this page.', 'learnpress-marketing-demo-data'));
		}

		$learnpress_ready = self::learnpress_ready();
		if ($learnpress_ready) {
			self::repair_existing_course_prices(true);
		}

		$counts = self::get_counts();
		$install_attrs = $learnpress_ready ? array() : array('disabled' => 'disabled');
		?>
		<div class="wrap">
			<h1><?php esc_html_e('LearnPress Marketing Demo Data', 'learnpress-marketing-demo-data'); ?></h1>
			<p><?php esc_html_e('Create a complete marketing course catalog for testing LearnPress courses, lessons, quizzes, questions, users, enrollments, orders, categories, and tags.', 'learnpress-marketing-demo-data'); ?></p>

			<?php if (! empty($_GET['lp_mdd_message'])) : ?>
				<div class="notice notice-<?php echo esc_attr(sanitize_key((string) ($_GET['lp_mdd_type'] ?? 'success'))); ?> inline"><p><?php echo esc_html(rawurldecode((string) wp_unslash($_GET['lp_mdd_message']))); ?></p></div>
			<?php endif; ?>

			<?php if (! $learnpress_ready) : ?>
				<div class="notice notice-error inline"><p><?php esc_html_e('LearnPress is not active. Activate LearnPress first.', 'learnpress-marketing-demo-data'); ?></p></div>
			<?php endif; ?>

			<div class="notice notice-info inline" style="max-width: 760px;">
				<p>
					<strong><?php esc_html_e('Demo login note:', 'learnpress-marketing-demo-data'); ?></strong>
					<?php esc_html_e('Generated demo students and instructors use password', 'learnpress-marketing-demo-data'); ?>
					<code><?php echo esc_html(self::demo_password()); ?></code>.
					<?php esc_html_e('Example users:', 'learnpress-marketing-demo-data'); ?>
					<code>mdd_student_anna</code>,
					<code>mdd_student_ben</code>,
					<code>mdd_instructor_maya</code>.
				</p>
			</div>

			<table class="widefat striped" style="max-width: 760px; margin: 20px 0;">
				<tbody>
					<tr><th><?php esc_html_e('Demo courses', 'learnpress-marketing-demo-data'); ?></th><td><?php echo esc_html((string) $counts['courses']); ?></td></tr>
					<tr><th><?php esc_html_e('Demo lessons', 'learnpress-marketing-demo-data'); ?></th><td><?php echo esc_html((string) $counts['lessons']); ?></td></tr>
					<tr><th><?php esc_html_e('Demo quizzes', 'learnpress-marketing-demo-data'); ?></th><td><?php echo esc_html((string) $counts['quizzes']); ?></td></tr>
					<tr><th><?php esc_html_e('Demo questions', 'learnpress-marketing-demo-data'); ?></th><td><?php echo esc_html((string) $counts['questions']); ?></td></tr>
					<tr><th><?php esc_html_e('Demo users', 'learnpress-marketing-demo-data'); ?></th><td><?php echo esc_html((string) $counts['users']); ?></td></tr>
					<tr><th><?php esc_html_e('Demo orders', 'learnpress-marketing-demo-data'); ?></th><td><?php echo esc_html((string) $counts['orders']); ?></td></tr>
				</tbody>
			</table>

			<style>
				.lp-mdd-actions {
					display: flex;
					gap: 8px;
					align-items: center;
					flex-wrap: wrap;
				}
				.lp-mdd-actions.is-loading .button {
					cursor: wait;
					opacity: 0.75;
				}
				.lp-mdd-overlay {
					display: none;
					position: fixed;
					z-index: 100000;
					inset: 0;
					align-items: center;
					justify-content: center;
					background: rgba(17, 24, 39, 0.58);
					backdrop-filter: blur(2px);
				}
				.lp-mdd-overlay.is-visible {
					display: flex;
				}
				.lp-mdd-dialog {
					width: min(420px, calc(100vw - 40px));
					padding: 30px;
					border-radius: 10px;
					background: #fff;
					box-shadow: 0 24px 70px rgba(0, 0, 0, 0.24);
					text-align: center;
				}
				.lp-mdd-dialog-spinner {
					width: 46px;
					height: 46px;
					margin: 0 auto 18px;
					border: 4px solid #dcdcde;
					border-top-color: #2271b1;
					border-radius: 50%;
					animation: lp-mdd-spin 0.75s linear infinite;
				}
				.lp-mdd-dialog-title {
					margin: 0 0 8px;
					color: #1d2327;
					font-size: 18px;
					font-weight: 600;
					line-height: 1.35;
				}
				.lp-mdd-dialog-message {
					margin: 0;
					color: #646970;
					font-size: 14px;
					line-height: 1.5;
				}
				body.lp-mdd-overlay-open {
					overflow: hidden;
				}
				.lp-mdd-screen-reader-status {
					position: absolute;
					width: 1px;
					height: 1px;
					margin: -1px;
					padding: 0;
					overflow: hidden;
					clip: rect(0, 0, 0, 0);
					border: 0;
				}
				@keyframes lp-mdd-spin {
					to {
						transform: rotate(360deg);
					}
				}
			</style>
			<div class="lp-mdd-actions">
			<form class="lp-mdd-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block; margin-right: 8px;" data-loading-label="<?php esc_attr_e('Installing demo data...', 'learnpress-marketing-demo-data'); ?>">
				<input type="hidden" name="action" value="lp_mdd_install">
				<?php wp_nonce_field('lp_mdd_install'); ?>
				<?php submit_button(__('Install Demo Data', 'learnpress-marketing-demo-data'), 'primary', 'submit', false, $install_attrs); ?>
			</form>

			<form class="lp-mdd-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;" data-confirm="<?php esc_attr_e('Remove all LearnPress Marketing Demo Data?', 'learnpress-marketing-demo-data'); ?>" data-loading-label="<?php esc_attr_e('Removing demo data...', 'learnpress-marketing-demo-data'); ?>">
				<input type="hidden" name="action" value="lp_mdd_remove">
				<?php wp_nonce_field('lp_mdd_remove'); ?>
				<?php submit_button(__('Remove Demo Data', 'learnpress-marketing-demo-data'), 'delete', 'submit', false); ?>
			</form>
			</div>
			<div class="lp-mdd-overlay" aria-hidden="true" role="alert" aria-live="assertive">
				<div class="lp-mdd-dialog" role="document">
					<div class="lp-mdd-dialog-spinner" aria-hidden="true"></div>
					<p class="lp-mdd-dialog-title"><?php esc_html_e('Preparing demo data...', 'learnpress-marketing-demo-data'); ?></p>
					<p class="lp-mdd-dialog-message"><?php esc_html_e('Please keep this page open until the process finishes.', 'learnpress-marketing-demo-data'); ?></p>
					<span class="lp-mdd-screen-reader-status"><?php esc_html_e('Demo data process is running.', 'learnpress-marketing-demo-data'); ?></span>
				</div>
			</div>
			<script>
				(function() {
					var forms = document.querySelectorAll('.lp-mdd-form');
					var actions = document.querySelector('.lp-mdd-actions');
					var overlay = document.querySelector('.lp-mdd-overlay');
					var dialogTitle = overlay ? overlay.querySelector('.lp-mdd-dialog-title') : null;
					var defaultTitle = dialogTitle ? dialogTitle.textContent : '';

					forms.forEach(function(form) {
						form.addEventListener('submit', function(event) {
							var message = form.getAttribute('data-confirm');
							if (message && ! window.confirm(message)) {
								event.preventDefault();
								return;
							}

							if (actions) {
								actions.classList.add('is-loading');
							}

							forms.forEach(function(item) {
								item.querySelectorAll('input[type="submit"], button').forEach(function(button) {
									button.disabled = true;
								});
							});

							var submit = form.querySelector('input[type="submit"], button[type="submit"]');
							var loadingLabel = form.getAttribute('data-loading-label');
							if (submit && loadingLabel) {
								if (submit.tagName === 'INPUT') {
									submit.value = loadingLabel;
								} else {
									submit.textContent = loadingLabel;
								}
							}

							if (overlay) {
								if (dialogTitle) {
									dialogTitle.textContent = loadingLabel || defaultTitle;
								}
								overlay.classList.add('is-visible');
								overlay.setAttribute('aria-hidden', 'false');
								document.body.classList.add('lp-mdd-overlay-open');
							}
						});
					});
				})();
			</script>
		</div>
		<?php
	}

	public static function handle_install(): void {
		self::verify_request('lp_mdd_install');

		if (! self::learnpress_ready()) {
			self::redirect('LearnPress is not active.', 'error');
		}

		$result = self::install();
		self::redirect($result, 'success');
	}

	public static function handle_remove(): void {
		self::verify_request('lp_mdd_remove');
		self::remove();
		self::redirect('Demo data removed.', 'success');
	}

	public static function maybe_repair_existing_course_prices(): void {
		if (! current_user_can('manage_options') || ! self::learnpress_ready()) {
			return;
		}

		$option_key = 'lp_mdd_price_repair_' . self::VERSION;
		if (get_option($option_key)) {
			return;
		}

		self::repair_existing_course_prices();
		update_option($option_key, time(), false);
	}

	private static function verify_request(string $nonce): void {
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have permission to do this.', 'learnpress-marketing-demo-data'));
		}

		check_admin_referer($nonce);
	}

	private static function redirect(string $message, string $type): void {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => self::SLUG,
					'lp_mdd_message' => rawurlencode($message),
					'lp_mdd_type' => $type,
				),
				admin_url('admin.php')
			)
		);
		exit;
	}

	private static function learnpress_ready(): bool {
		return defined('LP_COURSE_CPT')
			&& defined('LP_LESSON_CPT')
			&& defined('LP_QUIZ_CPT')
			&& defined('LP_QUESTION_CPT')
			&& post_type_exists(LP_COURSE_CPT);
	}

	private static function install(): string {
		self::remove();

		$terms = self::create_terms();
		$users = self::create_users();
		$courses = self::course_blueprints();
		$created_courses = array();

		foreach ($courses as $index => $course) {
			$created_courses[] = self::create_course($course, $terms, $users['instructors'][$index % count($users['instructors'])]);
		}

		self::create_enrollments_and_orders($created_courses, $users['students']);
		$existing_orders_result = self::create_orders_for_existing_data();
		flush_rewrite_rules(false);

		return sprintf(
			'Demo data installed: %d courses, %d students, %d instructors. Demo user password: %s. %s',
			count($created_courses),
			count($users['students']),
			count($users['instructors']),
			self::demo_password(),
			$existing_orders_result
		);
	}

	private static function create_terms(): array {
		$tags = array(
			'beginner',
			'advanced',
			'certification',
			'google-ads',
			'facebook-ads',
			'seo-audit',
			'funnels',
			'copywriting',
			'analytics',
			'email-automation',
		);

		return array(
			'categories' => self::insert_course_category_tree(),
			'tags' => self::insert_terms($tags, LP_COURSE_TAXONOMY_TAG),
		);
	}

	private static function course_category_tree(): array {
		return array(
			'Digital Marketing' => array(
				'Launch Strategy' => array('Market Positioning', 'Funnel Planning'),
				'Growth Systems' => array('Acquisition Channels', 'Conversion Basics'),
			),
			'SEO' => array(
				'Content SEO' => array('Topic Clusters', 'On Page Optimization'),
				'Technical SEO' => array('Site Audits', 'Schema Markup'),
			),
			'Paid Ads' => array(
				'Search Ads' => array('Google Ads', 'Keyword Bidding'),
				'Social Ads' => array('Facebook Ads', 'Creative Testing'),
			),
			'Content Marketing' => array(
				'Editorial Strategy' => array('Content Calendar', 'Repurposing'),
				'Copywriting' => array('Landing Page Copy', 'Email Copy'),
			),
			'Email Marketing' => array(
				'Automation' => array('Welcome Sequences', 'Cart Recovery'),
				'List Growth' => array('Lead Magnets', 'Newsletter Strategy'),
			),
			'Marketing Analytics' => array(
				'Reporting' => array('Dashboard Design', 'Funnel Metrics'),
				'Experimentation' => array('A/B Testing', 'Attribution'),
			),
			'Social Media' => array(
				'Organic Social' => array('Short Form Content', 'Community Growth'),
				'Video Growth' => array('YouTube Strategy', 'Reels Workflow'),
			),
			'Growth Strategy' => array(
				'Revenue Growth' => array('Pricing Strategy', 'Lifecycle Marketing'),
				'Partnerships' => array('Affiliate Launch', 'Influencer Campaigns'),
			),
		);
	}

	private static function insert_course_category_tree(): array {
		$ids = array();

		foreach (self::course_category_tree() as $level_1 => $children) {
			$level_1_id = self::insert_term($level_1, LP_COURSE_CATEGORY_TAX);
			if (! $level_1_id) {
				continue;
			}

			$ids[$level_1] = $level_1_id;
			foreach ($children as $level_2 => $grandchildren) {
				$level_2_id = self::insert_term($level_2, LP_COURSE_CATEGORY_TAX, $level_1_id);
				if (! $level_2_id) {
					continue;
				}

				$ids[$level_2] = $level_2_id;
				foreach ($grandchildren as $level_3) {
					$level_3_id = self::insert_term($level_3, LP_COURSE_CATEGORY_TAX, $level_2_id);
					if ($level_3_id) {
						$ids[$level_3] = $level_3_id;
					}
				}
			}
		}

		return $ids;
	}

	private static function insert_terms(array $names, string $taxonomy): array {
		$ids = array();
		foreach ($names as $name) {
			$term_id = self::insert_term($name, $taxonomy);
			if ($term_id) {
				$ids[$name] = $term_id;
			}
		}
		return $ids;
	}

	private static function insert_term(string $name, string $taxonomy, int $parent = 0): int {
		$term = term_exists($name, $taxonomy, $parent);
		if (! $term) {
			$term = wp_insert_term(
				$name,
				$taxonomy,
				array(
					'slug' => sanitize_title($name),
					'parent' => $parent,
				)
			);
		}

		if (is_wp_error($term)) {
			return 0;
		}

		$term_id = (int) (is_array($term) ? $term['term_id'] : $term);
		update_term_meta($term_id, self::MARKER, self::VERSION);
		return $term_id;
	}

	private static function create_users(): array {
		$instructors = array(
			array('mdd_instructor_maya', 'Maya Growth', 'maya.growth@example.test'),
			array('mdd_instructor_daniel', 'Daniel Ads', 'daniel.ads@example.test'),
			array('mdd_instructor_linh', 'Linh Content', 'linh.content@example.test'),
		);
		$students = array(
			array('mdd_student_anna', 'Anna Nguyen', 'anna.nguyen@example.test'),
			array('mdd_student_ben', 'Ben Tran', 'ben.tran@example.test'),
			array('mdd_student_chloe', 'Chloe Pham', 'chloe.pham@example.test'),
			array('mdd_student_david', 'David Le', 'david.le@example.test'),
			array('mdd_student_emma', 'Emma Do', 'emma.do@example.test'),
			array('mdd_student_felix', 'Felix Hoang', 'felix.hoang@example.test'),
		);

		return array(
			'instructors' => array_map(static fn(array $user): int => self::create_user($user, defined('LP_TEACHER_ROLE') ? LP_TEACHER_ROLE : 'author'), $instructors),
			'students' => array_map(static fn(array $user): int => self::create_user($user, 'subscriber'), $students),
		);
	}

	private static function create_user(array $data, string $role): int {
		$user = get_user_by('login', $data[0]);
		if ($user) {
			wp_set_password(self::demo_password(), (int) $user->ID);
			update_user_meta($user->ID, self::MARKER, self::VERSION);
			return (int) $user->ID;
		}

		$user_id = wp_insert_user(
			array(
				'user_login' => $data[0],
				'user_pass' => self::demo_password(),
				'user_email' => $data[2],
				'display_name' => $data[1],
				'nickname' => $data[1],
				'role' => $role,
			)
		);

		if (! is_wp_error($user_id)) {
			update_user_meta((int) $user_id, self::MARKER, self::VERSION);
			return (int) $user_id;
		}

		return get_current_user_id();
	}

	private static function demo_password(): string {
		return 'demo123';
	}

	private static function course_blueprints(): array {
		$base = array(
			array(
				'title' => 'Digital Marketing Launchpad',
				'category' => 'Digital Marketing',
				'tags' => array('beginner', 'certification', 'funnels'),
				'price' => '49',
				'sale_price' => '29',
				'level' => 'beginner',
				'featured' => 'yes',
				'students' => 284,
				'description' => 'Build a practical digital marketing foundation across positioning, channels, funnels, and campaign planning.',
				'sections' => array(
					'Marketing Foundations' => array('Define your market and offer', 'Map the customer journey', 'Choose the right acquisition channels'),
					'Campaign Planning' => array('Create a launch calendar', 'Set campaign KPIs', 'Build a simple funnel checklist'),
					'Launch Review' => array('Final launch readiness quiz'),
				),
			),
			array(
				'title' => 'SEO Content Engine',
				'category' => 'Content SEO',
				'tags' => array('seo-audit', 'copywriting', 'analytics'),
				'price' => '79',
				'sale_price' => '',
				'level' => 'intermediate',
				'featured' => 'yes',
				'students' => 418,
				'description' => 'Plan topic clusters, audit ranking opportunities, and publish content that compounds organic traffic.',
				'sections' => array(
					'SEO Strategy' => array('Keyword intent research', 'Build topic clusters', 'Prioritize quick-win pages'),
					'Content Production' => array('Write SEO briefs', 'Optimize internal links', 'Refresh declining content'),
					'SEO Measurement' => array('Technical SEO and content quiz'),
				),
			),
			array(
				'title' => 'Performance Ads for Course Creators',
				'category' => 'Creative Testing',
				'tags' => array('google-ads', 'facebook-ads', 'advanced'),
				'price' => '129',
				'sale_price' => '89',
				'level' => 'advanced',
				'featured' => 'no',
				'students' => 193,
				'description' => 'Design paid acquisition campaigns for course funnels across search, social, retargeting, and conversion tracking.',
				'sections' => array(
					'Paid Channel Setup' => array('Campaign structure by intent', 'Audience and creative matrix', 'Tracking pixels and UTMs'),
					'Optimization' => array('Budget pacing and bid logic', 'Retargeting sequences', 'Landing page feedback loops'),
					'Ads Certification' => array('Paid media optimization quiz'),
				),
			),
			array(
				'title' => 'Email Automation Revenue System',
				'category' => 'Automation',
				'tags' => array('email-automation', 'funnels', 'copywriting'),
				'price' => '59',
				'sale_price' => '39',
				'level' => 'intermediate',
				'featured' => 'yes',
				'students' => 356,
				'description' => 'Create welcome, nurture, launch, cart recovery, and reactivation sequences for an online course business.',
				'sections' => array(
					'Email Strategy' => array('Segment your course audience', 'Write a welcome sequence', 'Design a lead magnet funnel'),
					'Automation Builds' => array('Cart recovery emails', 'Launch countdown campaign', 'Win-back automation'),
					'Email Review' => array('Automation strategy quiz'),
				),
			),
			array(
				'title' => 'Marketing Analytics Dashboard',
				'category' => 'Dashboard Design',
				'tags' => array('analytics', 'advanced', 'certification'),
				'price' => '99',
				'sale_price' => '',
				'level' => 'advanced',
				'featured' => 'no',
				'students' => 127,
				'description' => 'Measure funnel performance, channel ROI, cohort behavior, and campaign experiments with a simple analytics workflow.',
				'sections' => array(
					'Measurement Plan' => array('Choose funnel metrics', 'Create a KPI dictionary', 'Audit analytics events'),
					'Dashboard Build' => array('Source and medium reporting', 'Cohort retention views', 'Executive marketing dashboard'),
					'Analytics Review' => array('Analytics interpretation quiz'),
				),
			),
		);

		return self::expand_course_blueprints($base, 30);
	}

	private static function expand_course_blueprints(array $base, int $target_count): array {
		if (count($base) >= $target_count) {
			return array_slice($base, 0, $target_count);
		}

		$titles = array(
			'Social Media Content Sprint',
			'Conversion Copywriting Lab',
			'Google Ads Fundamentals',
			'Facebook Ads Creative Testing',
			'LinkedIn B2B Lead Generation',
			'Landing Page Optimization',
			'Marketing Funnel Strategy',
			'Course Launch Calendar',
			'YouTube Growth for Educators',
			'Webinar Funnel Blueprint',
			'Retargeting Campaign Workshop',
			'Email List Growth System',
			'SEO Technical Audit Basics',
			'Analytics for Course Funnels',
			'Brand Positioning for Courses',
			'Influencer Partnership Playbook',
			'Community-Led Growth',
			'Marketing Automation Essentials',
			'Content Repurposing Machine',
			'Paid Search Optimization',
			'Customer Research Interviews',
			'Offer Design and Pricing',
			'Affiliate Launch Strategy',
			'Organic Social Analytics',
			'Lifecycle Marketing Foundations',
		);

		$categories = array(
			'Digital Marketing',
			'Content SEO',
			'Google Ads',
			'Content Marketing',
			'Welcome Sequences',
			'Reporting',
			'Social Media',
			'Revenue Growth',
			'Funnel Planning',
			'Technical SEO',
			'Social Ads',
			'Copywriting',
			'Email Marketing',
			'Funnel Metrics',
			'Organic Social',
			'Pricing Strategy',
			'Launch Strategy',
			'Site Audits',
			'Facebook Ads',
			'Landing Page Copy',
			'Lead Magnets',
			'Marketing Analytics',
			'Community Growth',
			'Affiliate Launch',
		);
		$tag_sets = array(
			array('beginner', 'funnels', 'copywriting'),
			array('advanced', 'analytics', 'certification'),
			array('google-ads', 'facebook-ads', 'analytics'),
			array('seo-audit', 'copywriting', 'beginner'),
			array('email-automation', 'funnels', 'certification'),
		);
		$levels = array('beginner', 'intermediate', 'advanced');
		$prices = array(0, 29, 39, 49, 59, 79, 89, 99, 129, 149);

		foreach ($titles as $index => $title) {
			if (count($base) >= $target_count) {
				break;
			}

			$price = (float) $prices[$index % count($prices)];
			$sale_price = '';
			if ($price > 0 && 0 === $index % 3) {
				$sale_price = (string) max(9, $price - 20);
			}

			$base[] = array(
				'title' => $title,
				'category' => $categories[$index % count($categories)],
				'tags' => $tag_sets[$index % count($tag_sets)],
				'price' => self::format_price($price),
				'sale_price' => '' === $sale_price ? '' : self::format_price((float) $sale_price),
				'level' => $levels[$index % count($levels)],
				'featured' => 0 === $index % 5 ? 'yes' : 'no',
				'students' => 80 + ($index * 37),
				'description' => sprintf(
					'Practice a focused marketing workflow for %s with templates, checkpoints, and realistic campaign tasks.',
					strtolower($title)
				),
				'sections' => array(
					'Strategy' => array(
						sprintf('Plan the %s workflow', strtolower($title)),
						'Define success metrics',
						'Map audience and offer fit',
					),
					'Execution' => array(
						'Build the campaign checklist',
						'Create assets and tracking',
						'Review optimization opportunities',
					),
					'Validation' => array(
						sprintf('%s quiz', $title),
					),
				),
			);
		}

		return $base;
	}

	private static function create_course(array $data, array $terms, int $instructor_id): int {
		$course_id = wp_insert_post(
			array(
				'post_title' => $data['title'],
				'post_type' => LP_COURSE_CPT,
				'post_status' => 'publish',
				'post_author' => $instructor_id,
				'post_excerpt' => $data['description'],
				'post_content' => self::course_content($data),
			),
			true
		);

		if (is_wp_error($course_id)) {
			return 0;
		}

		update_post_meta($course_id, self::MARKER, self::VERSION);
		self::set_course_meta($course_id, $data);

		$cat_id = $terms['categories'][$data['category']] ?? 0;
		if ($cat_id) {
			wp_set_post_terms($course_id, array($cat_id), LP_COURSE_CATEGORY_TAX);
		}

		$tag_ids = array();
		foreach ($data['tags'] as $tag) {
			if (! empty($terms['tags'][$tag])) {
				$tag_ids[] = $terms['tags'][$tag];
			}
		}
		wp_set_post_terms($course_id, $tag_ids, LP_COURSE_TAXONOMY_TAG);

		$final_quiz_id = 0;
		$section_order = 1;
		$lesson_index = 0;
		foreach ($data['sections'] as $section_name => $items) {
			$section_id = self::create_section($course_id, (string) $section_name, $section_order++);
			$item_order = 1;
			foreach ($items as $item_title) {
				if (str_contains(strtolower((string) $item_title), 'quiz')) {
					$quiz_id = self::create_quiz((string) $item_title, $section_id, $item_order++);
					$final_quiz_id = $quiz_id ?: $final_quiz_id;
				} else {
					self::create_lesson((string) $item_title, $section_id, $item_order++, 0 === $lesson_index++);
				}
			}
		}

		if ($final_quiz_id) {
			update_post_meta($course_id, '_lp_final_quiz', (string) $final_quiz_id);
		}

		self::sync_demo_course_price_index($course_id, $data);
		return (int) $course_id;
	}

	private static function course_content(array $data): string {
		return sprintf(
			'<!-- wp:paragraph --><p>%s</p><!-- /wp:paragraph --><!-- wp:heading --><h2>What you will learn</h2><!-- /wp:heading --><!-- wp:list --><ul><li>Build a clear marketing strategy for a course business.</li><li>Set up campaigns, measurement, and optimization workflows.</li><li>Use repeatable templates for launch, nurture, and revenue growth.</li></ul><!-- /wp:list --><!-- wp:heading --><h2>Ideal for</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Course creators, marketers, education founders, and growth teams building a scalable online course funnel.</p><!-- /wp:paragraph -->',
			esc_html($data['description'])
		);
	}

	private static function set_course_meta(int $course_id, array $data): void {
		$metas = array(
			'_lp_duration' => '6 week',
			'_lp_block_expire_duration' => 'no',
			'_lp_block_finished' => 'no',
			'_lp_allow_course_repurchase' => 'no',
			'_lp_course_repurchase_option' => 'reset',
			'_lp_level' => $data['level'],
			'_lp_students' => (string) $data['students'],
			'_lp_max_students' => '0',
			'_lp_retake_count' => '3',
			'_lp_has_finish' => 'yes',
			'_lp_featured' => $data['featured'],
			'_lp_featured_review' => 'A practical marketing course with clear workflows, campaign examples, and measurable outcomes.',
			'_lp_course_result' => 'evaluate_lesson',
			'_lp_passing_condition' => '80',
			'_lp_no_required_enroll' => 'no',
			'_lp_requirements' => array('A website or landing page to improve', 'Basic familiarity with online marketing terms', 'Access to analytics or campaign data is helpful'),
			'_lp_target_audiences' => array('Course creators', 'Marketing managers', 'Education startup teams', 'Freelance growth consultants'),
			'_lp_key_features' => array('Marketing templates', 'Campaign checklists', 'Quizzes for validation', 'Realistic course funnel examples'),
			'_lp_faqs' => array(
				array('Do I need prior marketing experience?', 'The beginner courses start from fundamentals; advanced courses assume campaign experience.'),
				array('Can I use this for a real course launch?', 'Yes. The demo content is structured around realistic launch, funnel, and analytics workflows.'),
			),
			'_lp_sample_data' => 'yes',
		);

		foreach ($metas as $key => $value) {
			update_post_meta($course_id, $key, $value);
		}

		self::save_demo_course_price_meta($course_id, $data);
	}

	private static function save_demo_course_price_meta(int $course_id, array $data): array {
		$regular_price = isset($data['price']) && '' !== (string) $data['price'] ? max(0, (float) $data['price']) : 0.0;
		$sale_price = isset($data['sale_price']) && '' !== (string) $data['sale_price'] ? max(0, (float) $data['sale_price']) : null;

		if (0.0 === $regular_price || (null !== $sale_price && $sale_price >= $regular_price)) {
			$sale_price = null;
		}

		$active_price = null !== $sale_price ? $sale_price : $regular_price;
		$is_sale = null !== $sale_price && $sale_price > 0 && $sale_price < $regular_price;

		update_post_meta($course_id, '_lp_regular_price', self::format_price($regular_price));
		update_post_meta($course_id, '_lp_sale_price', null === $sale_price ? '' : self::format_price($sale_price));
		update_post_meta($course_id, '_lp_price', self::format_price($active_price));

		if ($is_sale) {
			update_post_meta($course_id, '_lp_course_is_sale', '1');
		} else {
			delete_post_meta($course_id, '_lp_course_is_sale');
		}

		return array(
			'regular_price' => $regular_price,
			'sale_price' => $sale_price,
			'active_price' => $active_price,
			'is_sale' => $is_sale ? 1 : 0,
		);
	}

	private static function sync_demo_course_price_index(int $course_id, array $data): void {
		global $wpdb;

		$price_data = self::save_demo_course_price_meta($course_id, $data);
		$table = self::table('courses');

		if (class_exists('\LearnPress\Models\CourseModel') && class_exists('\LearnPress\Models\CoursePostModel')) {
			try {
				$course = \LearnPress\Models\CourseModel::find($course_id, false);
				if ($course instanceof \LearnPress\Models\CourseModel) {
					if (! $course->meta_data instanceof stdClass) {
						$course->meta_data = new stdClass();
					}

					$course->meta_data->{\LearnPress\Models\CoursePostModel::META_KEY_REGULAR_PRICE} = (float) $price_data['regular_price'];
					$course->meta_data->{\LearnPress\Models\CoursePostModel::META_KEY_SALE_PRICE} = null === $price_data['sale_price'] ? '' : (float) $price_data['sale_price'];
					$course->meta_data->{\LearnPress\Models\CoursePostModel::META_KEY_PRICE} = (float) $price_data['active_price'];
					$course->price_to_sort = (float) $price_data['active_price'];
					$course->is_sale = (int) $price_data['is_sale'];
					$course->save(true);
					self::clear_course_price_cache($course_id);
					return;
				}
			} catch (Throwable $e) {
				// Fall through to direct table update. The course still has postmeta price values.
			}
		}

		$wpdb->update(
			$table,
			array(
				'price_to_sort' => $price_data['active_price'],
				'is_sale' => $price_data['is_sale'],
			),
			array('ID' => $course_id),
			array('%f', '%d'),
			array('%d')
		);
		self::clear_course_price_cache($course_id);
	}

	private static function clear_course_price_cache(int $course_id): void {
		if (class_exists('LP_Cache')) {
			LP_Cache::cache_load_first('clear', "{$course_id}/price");
		}

		if (class_exists('LP_Course_Cache')) {
			LP_Course_Cache::instance()->clear("{$course_id}/price");
		}

		clean_post_cache($course_id);
	}

	private static function repair_existing_course_prices(bool $force = false): void {
		$option_key = 'lp_mdd_price_repair_' . self::VERSION;
		if (! $force && get_option($option_key)) {
			return;
		}

		$blueprints_by_title = array();
		foreach (self::course_blueprints() as $blueprint) {
			$blueprints_by_title[(string) $blueprint['title']] = $blueprint;
		}

		if (! $blueprints_by_title) {
			return;
		}

		$courses = get_posts(
			array(
				'post_type' => LP_COURSE_CPT,
				'post_status' => 'any',
				'posts_per_page' => -1,
				'meta_key' => self::MARKER,
				'meta_value' => self::VERSION,
			)
		);

		foreach ($courses as $course) {
			if (! isset($blueprints_by_title[$course->post_title])) {
				continue;
			}

			self::sync_demo_course_price_index((int) $course->ID, $blueprints_by_title[$course->post_title]);
		}

		update_option($option_key, time(), false);
	}

	private static function format_price(float $price): string {
		if (0.0 === $price) {
			return '0';
		}

		return rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.');
	}

	private static function create_section(int $course_id, string $name, int $order): int {
		global $wpdb;
		$wpdb->insert(
			self::table('sections'),
			array(
				'section_name' => $name,
				'section_course_id' => $course_id,
				'section_order' => $order,
				'section_description' => 'Marketing demo section generated for LearnPress testing.',
			),
			array('%s', '%d', '%d', '%s')
		);
		return (int) $wpdb->insert_id;
	}

	private static function create_lesson(string $title, int $section_id, int $order, bool $preview): int {
		global $wpdb;
		$lesson_id = wp_insert_post(
			array(
				'post_title' => $title,
				'post_type' => LP_LESSON_CPT,
				'post_status' => 'publish',
				'post_content' => '<p>This lesson walks through a practical marketing task with examples, checklist items, and implementation notes for a course business.</p>',
			),
			true
		);

		if (is_wp_error($lesson_id)) {
			return 0;
		}

		update_post_meta($lesson_id, self::MARKER, self::VERSION);
		update_post_meta($lesson_id, '_lp_duration', '18 minute');
		update_post_meta($lesson_id, '_lp_preview', $preview ? 'yes' : 'no');
		update_post_meta($lesson_id, '_lp_sample_data', 'yes');

		$wpdb->insert(
			self::table('section_items'),
			array(
				'section_id' => $section_id,
				'item_id' => $lesson_id,
				'item_type' => LP_LESSON_CPT,
				'item_order' => $order,
			),
			array('%d', '%d', '%s', '%d')
		);

		return (int) $lesson_id;
	}

	private static function create_quiz(string $title, int $section_id, int $order): int {
		global $wpdb;
		$quiz_id = wp_insert_post(
			array(
				'post_title' => $title,
				'post_type' => LP_QUIZ_CPT,
				'post_status' => 'publish',
				'post_content' => '<p>Validate your understanding of the marketing workflow covered in this module.</p>',
			),
			true
		);

		if (is_wp_error($quiz_id)) {
			return 0;
		}

		foreach (
			array(
				self::MARKER => self::VERSION,
				'_lp_duration' => '15 minute',
				'_lp_passing_grade' => '80',
				'_lp_negative_marking' => 'no',
				'_lp_minus_skip_questions' => 'no',
				'_lp_instant_check' => 'yes',
				'_lp_retake_count' => '2',
				'_lp_pagination' => '1',
				'_lp_review' => 'yes',
				'_lp_show_correct_review' => 'yes',
				'_lp_sample_data' => 'yes',
			) as $key => $value
		) {
			update_post_meta($quiz_id, $key, $value);
		}

		$wpdb->insert(
			self::table('section_items'),
			array(
				'section_id' => $section_id,
				'item_id' => $quiz_id,
				'item_type' => LP_QUIZ_CPT,
				'item_order' => $order,
			),
			array('%d', '%d', '%s', '%d')
		);

		self::create_questions($quiz_id);
		return (int) $quiz_id;
	}

	private static function create_questions(int $quiz_id): void {
		$questions = array(
			array('Which metric best measures landing page conversion?', 'single_choice', array('Conversion rate' => 'yes', 'Impressions' => 'no', 'Bounce count only' => 'no')),
			array('A nurture email sequence can segment users by behavior.', 'true_or_false', array('True' => 'yes', 'False' => 'no')),
			array('Which items belong in a campaign tracking plan?', 'multi_choice', array('UTM naming rules' => 'yes', 'Primary KPI' => 'yes', 'Random color palette' => 'no', 'Conversion event definition' => 'yes')),
		);

		foreach ($questions as $index => $question) {
			self::create_question($quiz_id, $question[0], $question[1], $question[2], $index + 1);
		}
	}

	private static function create_question(int $quiz_id, string $title, string $type, array $answers, int $order): int {
		global $wpdb;
		$question_id = wp_insert_post(
			array(
				'post_title' => $title,
				'post_type' => LP_QUESTION_CPT,
				'post_status' => 'publish',
				'post_content' => '<p>Select the best answer based on the marketing concept.</p>',
			),
			true
		);

		if (is_wp_error($question_id)) {
			return 0;
		}

		update_post_meta($question_id, self::MARKER, self::VERSION);
		update_post_meta($question_id, '_lp_type', $type);
		update_post_meta($question_id, '_lp_mark', '1');
		update_post_meta($question_id, '_lp_explanation', 'Review the lesson checklist and campaign planning framework.');
		update_post_meta($question_id, '_lp_sample_data', 'yes');

		$wpdb->insert(
			self::table('quiz_questions'),
			array(
				'quiz_id' => $quiz_id,
				'question_id' => $question_id,
				'question_order' => $order,
			),
			array('%d', '%d', '%d')
		);

		$answer_order = 1;
		foreach ($answers as $answer => $is_true) {
			$wpdb->insert(
				self::table('question_answers'),
				array(
					'question_id' => $question_id,
					'title' => $answer,
					'value' => md5($question_id . $answer),
					'is_true' => $is_true,
					'order' => $answer_order++,
				),
				array('%d', '%s', '%s', '%s', '%d')
			);
		}

		return (int) $question_id;
	}

	private static function create_enrollments_and_orders(array $course_ids, array $student_ids): void {
		foreach ($student_ids as $student_index => $student_id) {
			foreach ($course_ids as $course_index => $course_id) {
				if (! $course_id || (($student_index + $course_index) % 2 !== 0 && $course_index > 1)) {
					continue;
				}

				$status = ($student_index + $course_index) % 3 === 0 ? LP_COURSE_FINISHED : LP_COURSE_ENROLLED;
				$graduation = LP_COURSE_FINISHED === $status ? LP_COURSE_GRADUATION_PASSED : LP_COURSE_GRADUATION_IN_PROGRESS;
				$order_id = self::create_order($student_id, $course_id, $status);
				$user_item_id = self::create_user_course($student_id, $course_id, $order_id, $status, $graduation);
				self::create_user_item_progress($user_item_id, $student_id, $course_id, $status);
			}
		}
	}

	private static function create_orders_for_existing_data(): string {
		$course_ids = get_posts(
			array(
				'post_type' => LP_COURSE_CPT,
				'post_status' => 'publish',
				'fields' => 'ids',
				'posts_per_page' => -1,
				'orderby' => 'ID',
				'order' => 'ASC',
			)
		);

		$user_ids = self::get_existing_student_user_ids();

		if (! $course_ids || ! $user_ids) {
			return 'No eligible users or published LearnPress courses found.';
		}

		$created = 0;
		$skipped = 0;

		foreach ($user_ids as $user_index => $user_id) {
			foreach ($course_ids as $course_index => $course_id) {
				$user_id = (int) $user_id;
				$course_id = (int) $course_id;

				if (self::user_has_course($user_id, $course_id)) {
					$skipped++;
					continue;
				}

				$status = ($user_index + $course_index) % 4 === 0 ? LP_COURSE_FINISHED : LP_COURSE_ENROLLED;
				$graduation = LP_COURSE_FINISHED === $status ? LP_COURSE_GRADUATION_PASSED : LP_COURSE_GRADUATION_IN_PROGRESS;
				$order_id = self::create_order($user_id, $course_id, $status);

				if (! $order_id) {
					$skipped++;
					continue;
				}

				$user_item_id = self::create_user_course($user_id, $course_id, $order_id, $status, $graduation);
				if ($user_item_id) {
					self::create_user_item_progress($user_item_id, $user_id, $course_id, $status);
					$created++;
				}
			}
		}

		return sprintf('Created %d orders/enrollments for existing users and courses. Skipped %d existing pairs.', $created, $skipped);
	}

	private static function get_existing_student_user_ids(): array {
		$users = get_users(
			array(
				'fields' => 'all',
				'orderby' => 'ID',
				'order' => 'ASC',
			)
		);

		$user_ids = array();
		foreach ($users as $user) {
			$roles = array_map('strval', (array) $user->roles);
			if (array_intersect($roles, array('administrator', defined('LP_TEACHER_ROLE') ? LP_TEACHER_ROLE : 'lp_teacher'))) {
				continue;
			}
			$user_ids[] = (int) $user->ID;
		}

		return $user_ids;
	}

	private static function user_has_course(int $user_id, int $course_id): bool {
		global $wpdb;

		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . self::table('user_items') . ' WHERE user_id = %d AND item_id = %d AND item_type = %s',
				$user_id,
				$course_id,
				LP_COURSE_CPT
			)
		);

		return $count > 0;
	}

	private static function create_order(int $user_id, int $course_id, string $course_status): int {
		$total = (float) get_post_meta($course_id, '_lp_price', true);
		$order_id = wp_insert_post(
			array(
				'post_type' => LP_ORDER_CPT,
				'post_status' => LP_ORDER_COMPLETED_DB,
				'post_title' => 'Order - ' . get_the_title($course_id),
				'post_author' => $user_id,
			),
			true
		);

		if (is_wp_error($order_id)) {
			return 0;
		}

		foreach (
			array(
				self::MARKER => self::VERSION,
				'_order_currency' => 'USD',
				'_prices_include_tax' => 'no',
				'_user_id' => (string) $user_id,
				'_order_subtotal' => (string) $total,
				'_order_total' => (string) $total,
				'_order_key' => 'lp_mdd_' . wp_generate_password(12, false, false),
				'_payment_method' => 'manual',
				'_payment_method_title' => 'Manual demo payment',
				'_order_version' => defined('LEARNPRESS_VERSION') ? LEARNPRESS_VERSION : '4',
				'_created_via' => defined('LP_ORDER_CREATED_VIA_MANUAL') ? LP_ORDER_CREATED_VIA_MANUAL : 'manual',
			) as $key => $value
		) {
			update_post_meta($order_id, $key, $value);
		}

		global $wpdb;
		$wpdb->insert(
			self::table('order_items'),
			array(
				'order_item_name' => get_the_title($course_id),
				'order_id' => $order_id,
				'item_id' => $course_id,
				'item_type' => LP_COURSE_CPT,
			),
			array('%s', '%d', '%d', '%s')
		);
		$item_id = (int) $wpdb->insert_id;
		foreach (array('_course_id' => $course_id, '_quantity' => 1, '_subtotal' => $total, '_total' => $total, '_status' => $course_status) as $key => $value) {
			$wpdb->insert(
				self::table('order_itemmeta'),
				array(
					'learnpress_order_item_id' => $item_id,
					'meta_key' => $key,
					'meta_value' => (string) $value,
				),
				array('%d', '%s', '%s')
			);
		}

		return (int) $order_id;
	}

	private static function create_user_course(int $user_id, int $course_id, int $order_id, string $status, string $graduation): int {
		global $wpdb;
		$wpdb->insert(
			self::table('user_items'),
			array(
				'user_id' => $user_id,
				'item_id' => $course_id,
				'start_time' => gmdate('Y-m-d H:i:s', strtotime('-20 days')),
				'end_time' => LP_COURSE_FINISHED === $status ? gmdate('Y-m-d H:i:s', strtotime('-2 days')) : null,
				'item_type' => LP_COURSE_CPT,
				'status' => $status,
				'graduation' => $graduation,
				'access_level' => 50,
				'ref_id' => $order_id,
				'ref_type' => LP_ORDER_CPT,
				'parent_id' => 0,
			),
			array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d')
		);
		return (int) $wpdb->insert_id;
	}

	private static function create_user_item_progress(int $parent_id, int $user_id, int $course_id, string $course_status): void {
		global $wpdb;
		$items = $wpdb->get_results($wpdb->prepare("SELECT item_id, item_type FROM " . self::table('section_items') . " si INNER JOIN " . self::table('sections') . " s ON s.section_id = si.section_id WHERE s.section_course_id = %d ORDER BY s.section_order, si.item_order", $course_id));
		$limit = LP_COURSE_FINISHED === $course_status ? count($items) : max(1, (int) floor(count($items) / 2));

		foreach (array_slice($items, 0, $limit) as $item) {
			$wpdb->insert(
				self::table('user_items'),
				array(
					'user_id' => $user_id,
					'item_id' => (int) $item->item_id,
					'start_time' => gmdate('Y-m-d H:i:s', strtotime('-14 days')),
					'end_time' => gmdate('Y-m-d H:i:s', strtotime('-3 days')),
					'item_type' => (string) $item->item_type,
					'status' => LP_ITEM_COMPLETED,
					'graduation' => LP_COURSE_GRADUATION_PASSED,
					'access_level' => 50,
					'ref_id' => $course_id,
					'ref_type' => LP_COURSE_CPT,
					'parent_id' => $parent_id,
				),
				array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d')
			);
		}
	}

	private static function refresh_course_json(int $course_id): void {
		if (! class_exists('\LearnPress\Models\CourseModel')) {
			return;
		}

		try {
			$course = \LearnPress\Models\CourseModel::find($course_id, false);
			if ($course instanceof \LearnPress\Models\CourseModel) {
				$course->save(true);
			}
		} catch (Throwable $e) {
			// The course remains usable through WordPress posts/meta if the fast-query table refresh fails.
		}
	}

	private static function remove(): void {
		if (! self::learnpress_ready()) {
			return;
		}

		if (! function_exists('wp_delete_user')) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		global $wpdb;

		$post_ids = get_posts(
			array(
				'post_type' => array(LP_COURSE_CPT, LP_LESSON_CPT, LP_QUIZ_CPT, LP_QUESTION_CPT, LP_ORDER_CPT),
				'post_status' => 'any',
				'fields' => 'ids',
				'posts_per_page' => -1,
				'meta_key' => self::MARKER,
				'meta_value' => self::VERSION,
			)
		);

		if ($post_ids) {
			$ids_sql = implode(',', array_map('absint', $post_ids));
			$order_item_ids = $wpdb->get_col("SELECT order_item_id FROM " . self::table('order_items') . " WHERE order_id IN ($ids_sql)");
			if ($order_item_ids) {
				$order_items_sql = implode(',', array_map('absint', $order_item_ids));
				$wpdb->query("DELETE FROM " . self::table('order_itemmeta') . " WHERE learnpress_order_item_id IN ($order_items_sql)");
			}
			$parent_user_item_ids = $wpdb->get_col("SELECT user_item_id FROM " . self::table('user_items') . " WHERE item_id IN ($ids_sql)");
			$parent_sql = $parent_user_item_ids ? implode(',', array_map('absint', $parent_user_item_ids)) : '0';
			$wpdb->query("DELETE FROM " . self::table('section_items') . " WHERE item_id IN ($ids_sql)");
			$wpdb->query("DELETE FROM " . self::table('quiz_questions') . " WHERE quiz_id IN ($ids_sql) OR question_id IN ($ids_sql)");
			$wpdb->query("DELETE qa FROM " . self::table('question_answers') . " qa WHERE qa.question_id IN ($ids_sql)");
			$wpdb->query("DELETE FROM " . self::table('courses') . " WHERE ID IN ($ids_sql)");
			$wpdb->query("DELETE FROM " . self::table('order_items') . " WHERE order_id IN ($ids_sql)");
			$wpdb->query("DELETE FROM " . self::table('user_items') . " WHERE ref_id IN ($ids_sql) OR item_id IN ($ids_sql) OR parent_id IN ($parent_sql)");
			foreach ($post_ids as $post_id) {
				wp_delete_post((int) $post_id, true);
			}
		}

		$course_ids = $post_ids ? implode(',', array_map('absint', $post_ids)) : '0';
		$wpdb->query("DELETE FROM " . self::table('sections') . " WHERE section_course_id IN ($course_ids)");

		$users = get_users(
			array(
				'meta_key' => self::MARKER,
				'meta_value' => self::VERSION,
				'fields' => 'ID',
			)
		);
		foreach ($users as $user_id) {
			wp_delete_user((int) $user_id);
		}

		foreach (array(LP_COURSE_CATEGORY_TAX, LP_COURSE_TAXONOMY_TAG) as $taxonomy) {
			$terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false, 'meta_key' => self::MARKER, 'meta_value' => self::VERSION));
			if (! is_wp_error($terms)) {
				foreach ($terms as $term) {
					wp_delete_term((int) $term->term_id, $taxonomy);
				}
			}
		}

		delete_option('lp_mdd_price_repair_' . self::VERSION);
	}

	private static function get_counts(): array {
		$post_count = static function (string $post_type): int {
			global $wpdb;

			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(DISTINCT p.ID)
					FROM {$wpdb->posts} p
					INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
					WHERE p.post_type = %s
					AND pm.meta_key = %s
					AND pm.meta_value = %s",
					$post_type,
					self::MARKER,
					self::VERSION
				)
			);
		};

		return array(
			'courses' => defined('LP_COURSE_CPT') ? $post_count(LP_COURSE_CPT) : 0,
			'lessons' => defined('LP_LESSON_CPT') ? $post_count(LP_LESSON_CPT) : 0,
			'quizzes' => defined('LP_QUIZ_CPT') ? $post_count(LP_QUIZ_CPT) : 0,
			'questions' => defined('LP_QUESTION_CPT') ? $post_count(LP_QUESTION_CPT) : 0,
			'orders' => defined('LP_ORDER_CPT') ? $post_count(LP_ORDER_CPT) : 0,
			'users' => count(get_users(array('meta_key' => self::MARKER, 'meta_value' => self::VERSION, 'fields' => 'ID'))),
		);
	}

	private static function table(string $suffix): string {
		global $wpdb;
		$property = 'learnpress_' . $suffix;
		if (isset($wpdb->{$property}) && is_string($wpdb->{$property})) {
			return $wpdb->{$property};
		}
		return $wpdb->prefix . 'learnpress_' . $suffix;
	}
}

add_action('plugins_loaded', array('LP_Marketing_Demo_Data', 'boot'));
