<?php
/**
 * Admin page template.
 *
 * @package Bulk_Edit_LearnPress_Prices
 *
 * @var Bulk_Edit_LP_Price   $this
 * @var string               $page_title
 * @var string               $page_slug
 * @var string               $nonce_action
 * @var string               $nonce_name
 * @var LP_Price_List_Table|null $list_table
 * @var array                $current_filters
 * @var array                $filter_errors
 * @var array                $category_options
 * @var array                $instructor_options
 * @var array                $status_options
 * @var array                $bulk_actions
 */

defined( 'ABSPATH' ) || exit;

$current_filters = wp_parse_args(
	is_array( $current_filters ) ? $current_filters : array(),
	array(
		'course_type' => 'all',
		'category_id' => 0,
		'min_price'   => '',
		'max_price'   => '',
		'instructor'  => 0,
		'post_status' => 'publish',
	)
);

$reset_url = menu_page_url( $page_slug, false );
$bulk_value_labels = array(
	'set_regular_price'   => __( 'Regular price', 'bulk-edit-learnpress-prices' ),
	'set_sale_price'      => __( 'Sale price', 'bulk-edit-learnpress-prices' ),
	'schedule_sale_price' => __( 'Sale price', 'bulk-edit-learnpress-prices' ),
	'increase_percentage' => __( 'Percentage increase', 'bulk-edit-learnpress-prices' ),
	'decrease_percentage' => __( 'Percentage decrease', 'bulk-edit-learnpress-prices' ),
	'remove_sale_price'   => __( 'Value', 'bulk-edit-learnpress-prices' ),
);
?>

<div class="wrap belpcp-admin-page">
	<h1><?php echo esc_html( $page_title ); ?></h1>

	<?php $this->render_admin_page_notices(); ?>

	<p>
		<?php esc_html_e( 'Review LearnPress courses and prepare bulk price updates from one admin screen.', 'bulk-edit-learnpress-prices' ); ?>
	</p>

	<?php if ( ! empty( $filter_errors ) ) : ?>
		<div class="notice notice-error inline">
			<?php foreach ( $filter_errors as $filter_error ) : ?>
				<p><?php echo esc_html( $filter_error ); ?></p>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<form method="get" class="belpcp-filter-form" data-filter-form>
		<input type="hidden" name="page" value="<?php echo esc_attr( $page_slug ); ?>" />

		<div class="belpcp-filter-bar">
			<div class="belpcp-filter-grid">
				<div class="belpcp-filter-field">
					<label for="belpcp-course-type"><?php esc_html_e( 'Type', 'bulk-edit-learnpress-prices' ); ?></label>
					<select id="belpcp-course-type" name="course_type">
						<option value="all" <?php selected( $current_filters['course_type'], 'all' ); ?>><?php esc_html_e( 'All courses', 'bulk-edit-learnpress-prices' ); ?></option>
						<option value="paid" <?php selected( $current_filters['course_type'], 'paid' ); ?>><?php esc_html_e( 'Paid courses', 'bulk-edit-learnpress-prices' ); ?></option>
						<option value="free" <?php selected( $current_filters['course_type'], 'free' ); ?>><?php esc_html_e( 'Free courses', 'bulk-edit-learnpress-prices' ); ?></option>
					</select>
				</div>

				<div class="belpcp-filter-field">
					<label for="belpcp-category"><?php esc_html_e( 'Category', 'bulk-edit-learnpress-prices' ); ?></label>
					<select id="belpcp-category" name="category_id" <?php disabled( empty( $category_options ) ); ?>>
						<option value="0"><?php esc_html_e( 'All categories', 'bulk-edit-learnpress-prices' ); ?></option>
						<?php foreach ( $category_options as $category_id => $category_name ) : ?>
							<option value="<?php echo esc_attr( absint( $category_id ) ); ?>" <?php selected( absint( $current_filters['category_id'] ), absint( $category_id ) ); ?>>
								<?php echo esc_html( $category_name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="belpcp-filter-field belpcp-filter-field--price">
					<label for="belpcp-min-price"><?php esc_html_e( 'Min price', 'bulk-edit-learnpress-prices' ); ?></label>
					<input
						type="number"
						id="belpcp-min-price"
						name="min_price"
						value="<?php echo esc_attr( $current_filters['min_price'] ); ?>"
						min="0"
						step="any"
						inputmode="decimal"
					/>
				</div>

				<div class="belpcp-filter-field belpcp-filter-field--price">
					<label for="belpcp-max-price"><?php esc_html_e( 'Max price', 'bulk-edit-learnpress-prices' ); ?></label>
					<input
						type="number"
						id="belpcp-max-price"
						name="max_price"
						value="<?php echo esc_attr( $current_filters['max_price'] ); ?>"
						min="0"
						step="any"
						inputmode="decimal"
					/>
				</div>

				<div class="belpcp-filter-field">
					<label for="belpcp-instructor"><?php esc_html_e( 'Instructor', 'bulk-edit-learnpress-prices' ); ?></label>
					<select id="belpcp-instructor" name="instructor" <?php disabled( empty( $instructor_options ) ); ?>>
						<option value="0"><?php esc_html_e( 'All instructors', 'bulk-edit-learnpress-prices' ); ?></option>
						<?php foreach ( $instructor_options as $instructor_id => $instructor_name ) : ?>
							<option value="<?php echo esc_attr( absint( $instructor_id ) ); ?>" <?php selected( absint( $current_filters['instructor'] ), absint( $instructor_id ) ); ?>>
								<?php echo esc_html( $instructor_name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="belpcp-filter-field">
					<label for="belpcp-post-status"><?php esc_html_e( 'Status', 'bulk-edit-learnpress-prices' ); ?></label>
					<select id="belpcp-post-status" name="post_status">
						<option value="any" <?php selected( $current_filters['post_status'], 'any' ); ?>><?php esc_html_e( 'Any status', 'bulk-edit-learnpress-prices' ); ?></option>
						<?php foreach ( $status_options as $status_key => $status_label ) : ?>
							<option value="<?php echo esc_attr( $status_key ); ?>" <?php selected( $current_filters['post_status'], $status_key ); ?>>
								<?php echo esc_html( $status_label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div class="belpcp-filter-actions">
				<?php submit_button( __( 'Apply Filters', 'bulk-edit-learnpress-prices' ), 'secondary', 'filter_action', false ); ?>

				<?php if ( $reset_url ) : ?>
					<a class="button" href="<?php echo esc_url( $reset_url ); ?>"><?php esc_html_e( 'Reset', 'bulk-edit-learnpress-prices' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</form>

	<div class="belpcp-course-load-message notice notice-error inline" data-course-load-errors hidden>
		<p data-course-load-error-text><?php esc_html_e( 'Unable to load courses. Refresh the page and try again.', 'bulk-edit-learnpress-prices' ); ?></p>
	</div>

	<form method="post" class="belpcp-bulk-form">
		<?php wp_nonce_field( $nonce_action, $nonce_name ); ?>

		<div class="belpcp-table-actionbar">
			<div>
				<strong><?php esc_html_e( 'Selected courses', 'bulk-edit-learnpress-prices' ); ?></strong>
				<span>
					<span class="belpcp-selected-count" data-selected-count>0</span>
					<?php esc_html_e( 'selected', 'bulk-edit-learnpress-prices' ); ?>
				</span>
				<p class="belpcp-table-actionbar__hint" data-selection-hint>
					<?php esc_html_e( 'Select at least one course below to get started.', 'bulk-edit-learnpress-prices' ); ?>
				</p>
			</div>
			<button type="button" class="button button-primary" data-open-bulk-action disabled>
				<?php esc_html_e( 'Bulk price action', 'bulk-edit-learnpress-prices' ); ?>
			</button>
		</div>

		<div class="belpcp-course-table-region" data-course-table-region aria-live="polite" aria-busy="false">
			<?php if ( $list_table ) : ?>
				<?php $list_table->display(); ?>
			<?php else : ?>
				<div class="notice notice-error inline">
					<p><?php esc_html_e( 'The course table could not be loaded. Refresh the page and try again.', 'bulk-edit-learnpress-prices' ); ?></p>
				</div>
			<?php endif; ?>
		</div>

		<div
			class="belpcp-modal belpcp-modal--bulk-action"
			role="dialog"
			aria-modal="true"
			aria-labelledby="belpcp-bulk-action-title"
			aria-describedby="belpcp-bulk-action-description"
			data-bulk-action-modal
			hidden
		>
			<div class="belpcp-modal__backdrop" data-close-bulk-action></div>
			<div class="belpcp-modal__dialog belpcp-modal__dialog--bulk-action" role="document" tabindex="-1">
				<div class="belpcp-modal__header">
					<h2 id="belpcp-bulk-action-title"><?php esc_html_e( 'Bulk price action', 'bulk-edit-learnpress-prices' ); ?></h2>
					<button type="button" class="button-link belpcp-modal__close" data-close-bulk-action aria-label="<?php echo esc_attr__( 'Close bulk price action dialog', 'bulk-edit-learnpress-prices' ); ?>">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="belpcp-modal__body">
					<div
						class="belpcp-bulk-panel"
						data-preview-ready="false"
						data-error-no-courses="<?php echo esc_attr__( 'Select at least one course before previewing changes.', 'bulk-edit-learnpress-prices' ); ?>"
						data-error-missing-value="<?php echo esc_attr__( 'Enter a value before previewing changes.', 'bulk-edit-learnpress-prices' ); ?>"
						data-error-invalid-value="<?php echo esc_attr__( 'Enter a value of 0 or greater.', 'bulk-edit-learnpress-prices' ); ?>"
						data-error-missing-schedule="<?php echo esc_attr__( 'Enter both sale start and sale end dates.', 'bulk-edit-learnpress-prices' ); ?>"
						data-error-invalid-schedule="<?php echo esc_attr__( 'Sale start date must be before sale end date.', 'bulk-edit-learnpress-prices' ); ?>"
						data-error-invalid-decrease="<?php echo esc_attr__( 'Decrease percentage cannot be greater than 100.', 'bulk-edit-learnpress-prices' ); ?>"
						data-error-too-many-courses="<?php echo esc_attr( sprintf(
							/* translators: %d: maximum selected course count. */
							__( 'Select %d courses or fewer per update request.', 'bulk-edit-learnpress-prices' ),
							BELPCP_MAX_SELECTED_COURSES
						) ); ?>"
						data-summary-ready="<?php echo esc_attr__( 'courses ready for confirmation.', 'bulk-edit-learnpress-prices' ); ?>"
						data-after-remove-sale="<?php echo esc_attr__( 'Sale price will be removed', 'bulk-edit-learnpress-prices' ); ?>"
						data-empty-value="<?php echo esc_attr__( 'None', 'bulk-edit-learnpress-prices' ); ?>"
					>
						<div class="belpcp-bulk-panel__header">
							<h3><?php esc_html_e( 'Pricing action', 'bulk-edit-learnpress-prices' ); ?></h3>
							<p>
								<span class="belpcp-selected-count" data-selected-count>0</span>
								<?php esc_html_e( 'courses selected', 'bulk-edit-learnpress-prices' ); ?>
							</p>
						</div>

						<div class="belpcp-bulk-panel__controls">
							<div class="belpcp-bulk-field">
								<label for="belpcp-bulk-action"><?php esc_html_e( 'Action', 'bulk-edit-learnpress-prices' ); ?></label>
								<select id="belpcp-bulk-action" name="bulk_action" data-bulk-action>
									<?php foreach ( $bulk_actions as $action_key => $action_label ) : ?>
										<option
											value="<?php echo esc_attr( $action_key ); ?>"
											data-value-label="<?php echo esc_attr( isset( $bulk_value_labels[ $action_key ] ) ? $bulk_value_labels[ $action_key ] : $bulk_value_labels['remove_sale_price'] ); ?>"
										>
											<?php echo esc_html( $action_label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="belpcp-bulk-field" data-bulk-value-field>
								<label for="belpcp-bulk-value" data-bulk-value-label><?php esc_html_e( 'Value', 'bulk-edit-learnpress-prices' ); ?></label>
								<input
									type="number"
									id="belpcp-bulk-value"
									name="bulk_value"
									min="0"
									step="any"
									inputmode="decimal"
									data-bulk-value
								/>
							</div>

							<div class="belpcp-bulk-field belpcp-bulk-field--schedule" data-sale-schedule-field hidden>
								<label for="belpcp-sale-start"><?php esc_html_e( 'Sale start', 'bulk-edit-learnpress-prices' ); ?></label>
								<input
									type="datetime-local"
									id="belpcp-sale-start"
									name="sale_start"
									step="1"
									data-sale-start
								/>
							</div>

							<div class="belpcp-bulk-field belpcp-bulk-field--schedule" data-sale-schedule-field hidden>
								<label for="belpcp-sale-end"><?php esc_html_e( 'Sale end', 'bulk-edit-learnpress-prices' ); ?></label>
								<input
									type="datetime-local"
									id="belpcp-sale-end"
									name="sale_end"
									step="1"
									data-sale-end
								/>
							</div>
						</div>

						<div class="belpcp-bulk-actions">
							<p class="belpcp-bulk-actions__hint">
								<?php esc_html_e( 'Preview the selected course changes first. Apply Changes becomes available only after a valid preview is generated.', 'bulk-edit-learnpress-prices' ); ?>
							</p>
							<div class="belpcp-bulk-actions__buttons">
								<button type="button" class="button button-secondary" data-preview-button disabled>
									<?php esc_html_e( 'Preview Changes', 'bulk-edit-learnpress-prices' ); ?>
								</button>
								<button type="button" class="button button-primary" data-apply-button disabled>
									<?php esc_html_e( 'Apply Changes', 'bulk-edit-learnpress-prices' ); ?>
								</button>
							</div>
						</div>

						<div class="belpcp-bulk-message notice notice-error inline" data-bulk-errors hidden>
							<p data-bulk-error-text><?php esc_html_e( 'Select at least one course before previewing changes.', 'bulk-edit-learnpress-prices' ); ?></p>
						</div>

						<div class="belpcp-bulk-preview" data-preview-summary hidden>
							<h3><?php esc_html_e( 'Preview summary', 'bulk-edit-learnpress-prices' ); ?></h3>
							<p data-preview-summary-text><?php esc_html_e( 'Preview results will appear here before any course prices are updated.', 'bulk-edit-learnpress-prices' ); ?></p>
							<div class="belpcp-preview-table-wrap" data-preview-table-wrap hidden>
								<table class="widefat striped belpcp-preview-table">
									<thead>
										<tr>
											<th scope="col"><?php esc_html_e( 'Course', 'bulk-edit-learnpress-prices' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Current regular', 'bulk-edit-learnpress-prices' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Current sale', 'bulk-edit-learnpress-prices' ); ?></th>
											<th scope="col"><?php esc_html_e( 'After preview', 'bulk-edit-learnpress-prices' ); ?></th>
										</tr>
									</thead>
									<tbody data-preview-rows></tbody>
								</table>
							</div>
						</div>

						<div class="belpcp-bulk-report notice inline" data-update-report hidden>
							<h3><?php esc_html_e( 'Update report', 'bulk-edit-learnpress-prices' ); ?></h3>
							<p data-update-report-text><?php esc_html_e( 'Final update results will appear here after changes are applied.', 'bulk-edit-learnpress-prices' ); ?></p>
							<div class="belpcp-preview-table-wrap" data-update-report-table-wrap hidden>
								<table class="widefat striped belpcp-preview-table belpcp-report-table">
									<thead>
										<tr>
											<th scope="col"><?php esc_html_e( 'Course', 'bulk-edit-learnpress-prices' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Previous regular', 'bulk-edit-learnpress-prices' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Previous sale', 'bulk-edit-learnpress-prices' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Current price', 'bulk-edit-learnpress-prices' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Result', 'bulk-edit-learnpress-prices' ); ?></th>
										</tr>
									</thead>
									<tbody data-update-report-rows></tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div
			class="belpcp-modal"
			role="dialog"
			aria-modal="true"
			aria-labelledby="belpcp-confirm-title"
			aria-describedby="belpcp-confirm-description"
			data-confirm-modal
			hidden
		>
			<div class="belpcp-modal__backdrop" data-close-modal></div>
			<div class="belpcp-modal__dialog" role="document" tabindex="-1">
				<div class="belpcp-modal__header">
					<h2 id="belpcp-confirm-title"><?php esc_html_e( 'Confirm bulk price update', 'bulk-edit-learnpress-prices' ); ?></h2>
					<button type="button" class="button-link belpcp-modal__close" data-close-modal aria-label="<?php echo esc_attr__( 'Close confirmation dialog', 'bulk-edit-learnpress-prices' ); ?>">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="belpcp-modal__body">
					<p id="belpcp-confirm-description">
						<?php esc_html_e( 'Review the selected action and preview details before applying changes.', 'bulk-edit-learnpress-prices' ); ?>
					</p>

					<dl class="belpcp-modal-summary">
						<div>
							<dt><?php esc_html_e( 'Selected courses', 'bulk-edit-learnpress-prices' ); ?></dt>
							<dd data-modal-course-count>0</dd>
						</div>
						<div>
							<dt><?php esc_html_e( 'Action', 'bulk-edit-learnpress-prices' ); ?></dt>
							<dd data-modal-action>--</dd>
						</div>
						<div>
							<dt><?php esc_html_e( 'Value', 'bulk-edit-learnpress-prices' ); ?></dt>
							<dd data-modal-value>--</dd>
						</div>
					</dl>

					<div class="notice notice-warning inline belpcp-modal-warning">
						<p><?php esc_html_e( 'This will update LearnPress course price metadata after confirmation. Make sure the preview matches your intent.', 'bulk-edit-learnpress-prices' ); ?></p>
					</div>

					<div class="belpcp-modal-preview">
						<h3><?php esc_html_e( 'Preview details', 'bulk-edit-learnpress-prices' ); ?></h3>
						<div class="belpcp-preview-table-wrap">
							<table class="widefat striped belpcp-preview-table">
								<thead>
									<tr>
										<th scope="col"><?php esc_html_e( 'Course', 'bulk-edit-learnpress-prices' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Current regular', 'bulk-edit-learnpress-prices' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Current sale', 'bulk-edit-learnpress-prices' ); ?></th>
										<th scope="col"><?php esc_html_e( 'After preview', 'bulk-edit-learnpress-prices' ); ?></th>
									</tr>
								</thead>
								<tbody data-modal-preview-rows></tbody>
							</table>
						</div>
					</div>
				</div>

				<div class="belpcp-modal__footer">
					<button type="button" class="button button-primary" data-confirm-apply>
						<?php esc_html_e( 'Confirm Apply', 'bulk-edit-learnpress-prices' ); ?>
					</button>
					<button type="button" class="button" data-close-modal>
						<?php esc_html_e( 'Cancel', 'bulk-edit-learnpress-prices' ); ?>
					</button>
				</div>
			</div>
		</div>

		<div
			class="belpcp-modal"
			role="dialog"
			aria-modal="true"
			aria-labelledby="belpcp-history-title"
			aria-describedby="belpcp-history-description"
			data-history-modal
			hidden
		>
			<div class="belpcp-modal__backdrop" data-close-history-modal></div>
			<div class="belpcp-modal__dialog belpcp-modal__dialog--history" role="document" tabindex="-1">
				<div class="belpcp-modal__header">
					<h2 id="belpcp-history-title"><?php esc_html_e( 'Price change history', 'bulk-edit-learnpress-prices' ); ?></h2>
					<button type="button" class="button-link belpcp-modal__close" data-close-history-modal aria-label="<?php echo esc_attr__( 'Close price history dialog', 'bulk-edit-learnpress-prices' ); ?>">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="belpcp-modal__body">
					<p id="belpcp-history-description" data-history-description>
						<?php esc_html_e( 'Review recorded price changes for this course.', 'bulk-edit-learnpress-prices' ); ?>
					</p>

					<div class="belpcp-bulk-message notice notice-error inline" data-history-errors hidden>
						<p data-history-error-text><?php esc_html_e( 'Unable to load price history. Refresh the page and try again.', 'bulk-edit-learnpress-prices' ); ?></p>
					</div>

					<p class="belpcp-history-empty" data-history-empty hidden>
						<?php esc_html_e( 'No price changes have been recorded for this course yet.', 'bulk-edit-learnpress-prices' ); ?>
					</p>

					<div class="belpcp-preview-table-wrap" data-history-table-wrap hidden>
						<table class="widefat striped belpcp-preview-table belpcp-history-table">
							<thead>
								<tr>
									<th scope="col"><?php esc_html_e( 'Date', 'bulk-edit-learnpress-prices' ); ?></th>
									<th scope="col"><?php esc_html_e( 'User', 'bulk-edit-learnpress-prices' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Action', 'bulk-edit-learnpress-prices' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Regular price', 'bulk-edit-learnpress-prices' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Sale price', 'bulk-edit-learnpress-prices' ); ?></th>
								</tr>
							</thead>
							<tbody data-history-rows></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
