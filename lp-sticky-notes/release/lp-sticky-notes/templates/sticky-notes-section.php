<?php
/**
 * Template for sticky notes sidebar on lesson page
 *
 * @package LP_Sticky_Notes
 */

defined('ABSPATH') || exit();
?>

<!-- Sticky Notes Toggle Button -->
<button type="button" class="lp-sticky-notes-toggle" id="lp-sticky-notes-toggle"
	title="<?php esc_attr_e('Toggle Sticky Notes', 'lp-sticky-notes'); ?>">
	<svg viewBox="-0.5 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" width="20" height="20">
		<g id="SVGRepo_bgCarrier" stroke-width="0"></g>
		<g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
		<g id="SVGRepo_iconCarrier">
			<path
				d="M18.6375 9.04176L13.3875 14.2418C13.3075 14.3218 13.1876 14.3718 13.0676 14.3718H10.1075V11.3118C10.1075 11.1918 10.1575 11.0818 10.2375 11.0018L15.4376 5.84176"
				stroke="currentColor" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
			<path
				d="M18.7076 21.6618V21.6618C18.7076 21.9018 18.5176 22.0918 18.2776 22.0918H2.84756C2.60756 22.0918 2.41754 21.9018 2.41754 21.6618V6.23176C2.41754 5.99176 2.60756 5.80176 2.84756 5.80176H12.4875"
				stroke="currentColor" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
			<path d="M18.3863 2.90824L16.859 4.43558L20.0551 7.63167L21.5824 6.10433L18.3863 2.90824Z"
				stroke="currentColor" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
		</g>
	</svg>
</button>

<!-- Sticky Notes Sidebar -->
<div class="lp-sticky-notes-sidebar" id="lp-sticky-notes-sidebar">
	<div class="lp-sticky-notes-header">
		<h3 class="lp-sticky-notes-title">
			<svg viewBox="-0.5 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" width="20" height="20">
				<g id="SVGRepo_bgCarrier" stroke-width="0"></g>
				<g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
				<g id="SVGRepo_iconCarrier">
					<path
						d="M18.6375 9.04176L13.3875 14.2418C13.3075 14.3218 13.1876 14.3718 13.0676 14.3718H10.1075V11.3118C10.1075 11.1918 10.1575 11.0818 10.2375 11.0018L15.4376 5.84176"
						stroke="currentColor" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round">
					</path>
					<path
						d="M18.7076 21.6618V21.6618C18.7076 21.9018 18.5176 22.0918 18.2776 22.0918H2.84756C2.60756 22.0918 2.41754 21.9018 2.41754 21.6618V6.23176C2.41754 5.99176 2.60756 5.80176 2.84756 5.80176H12.4875"
						stroke="currentColor" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round">
					</path>
					<path d="M18.3863 2.90824L16.859 4.43558L20.0551 7.63167L21.5824 6.10433L18.3863 2.90824Z"
						stroke="currentColor" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round">
					</path>
				</g>
			</svg>
			<?php esc_html_e('Sticky Notes', 'lp-sticky-notes'); ?>
		</h3>
		<div class="lp-sticky-notes-header-actions">
			<button type="button" class="lp-btn-view-all-notes" id="lp-btn-view-all-notes"
				title="<?php esc_attr_e('View All Notes', 'lp-sticky-notes'); ?>">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path
						d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4"
						stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
				<?php esc_html_e('View All', 'lp-sticky-notes'); ?>
			</button>
			<button type="button" class="lp-sticky-notes-close" id="lp-sticky-notes-close"
				title="<?php esc_attr_e('Close', 'lp-sticky-notes'); ?>">
				&times;
			</button>
		</div>
	</div>

	<div class="lp-sticky-notes-content">
		<div class="lp-sticky-notes-info">
			<p><?php esc_html_e('You can add text notes or highlight content and add notes to specific text.', 'lp-sticky-notes'); ?>
			</p>
			<p class="lp-highlight-instruction">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M12 2L13.09 8.26L20 9L13.09 9.74L12 16L10.91 9.74L4 9L10.91 8.26L12 2Z"
						stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
				<?php esc_html_e('To create a highlight note: Select text in the lesson content, then click "Add Note" button that appears.', 'lp-sticky-notes'); ?>
			</p>
		</div>

		<!-- Add Note Button -->
		<div class="lp-sticky-notes-actions">
			<button type="button" class="lp-btn-add-note" id="lp-btn-add-text-note">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"
						stroke-linejoin="round" />
				</svg>
				<?php esc_html_e('Add Note', 'lp-sticky-notes'); ?>
			</button>
		</div>

		<!-- Add/Edit Note Form -->
		<div class="lp-note-form-wrapper" style="display: none;">
			<form class="lp-note-form" id="lp-note-form">
				<input type="hidden" name="note_id" id="lp-note-id" value="">
				<input type="hidden" name="note_type" id="lp-note-type" value="text">
				<input type="hidden" name="highlight_text" id="lp-highlight-text" value="">
				<input type="hidden" name="position" id="lp-note-position" value="">

				<div class="lp-note-highlight-preview" id="lp-highlight-preview" style="display: none;" tabindex="-1">
					<strong><?php esc_html_e('Highlighted text:', 'lp-sticky-notes'); ?></strong>
					<div class="lp-highlight-text"></div>
				</div>

				<div class="lp-form-group">
					<label for="lp-note-content"><?php esc_html_e('Note Content:', 'lp-sticky-notes'); ?></label>
					<textarea name="content" id="lp-note-content" rows="4"
						placeholder="<?php esc_attr_e('Enter your note here...', 'lp-sticky-notes'); ?>"
						required></textarea>
				</div>

				<div class="lp-form-actions">
					<button type="submit" class="lp-btn lp-btn-primary">
						<?php esc_html_e('Save Note', 'lp-sticky-notes'); ?>
					</button>
					<button type="button" class="lp-btn lp-btn-secondary" id="lp-btn-cancel-note">
						<?php esc_html_e('Cancel', 'lp-sticky-notes'); ?>
					</button>
				</div>
			</form>
		</div>

		<!-- Notes List -->
		<div class="lp-sticky-notes-list" id="lp-sticky-notes-list">
			<?php if (empty($notes)): ?>
				<div class="lp-no-notes">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
							stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
							opacity="0.5" />
					</svg>
					<p><?php esc_html_e('No notes yet. Start taking notes to remember important points!', 'lp-sticky-notes'); ?>
					</p>
				</div>
			<?php else: ?>
				<?php foreach ($notes as $note): ?>
					<div class="lp-note-item" data-note-id="<?php echo esc_attr($note->id); ?>"
						data-note-type="<?php echo esc_attr($note->note_type); ?>">
						<div class="lp-note-header">
							<span class="lp-note-type-badge lp-note-type-<?php echo esc_attr($note->note_type); ?>">
								<?php echo $note->note_type === 'highlight' ? esc_html__('Highlight', 'lp-sticky-notes') : esc_html__('Text', 'lp-sticky-notes'); ?>
							</span>
							<span class="lp-note-date">
								<?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($note->created_at))); ?>
							</span>
						</div>

						<?php if ($note->note_type === 'highlight' && !empty($note->highlight_text)): ?>
							<div class="lp-note-highlight">
								<strong><?php esc_html_e('Highlighted:', 'lp-sticky-notes'); ?></strong>
								<div class="lp-highlight-text"><?php echo wp_kses_post($note->highlight_text); ?></div>
							</div>
						<?php endif; ?>

						<div class="lp-note-content">
							<?php echo wp_kses_post(wpautop($note->content)); ?>
						</div>

						<div class="lp-note-actions">
							<button type="button" class="lp-btn-edit-note" data-note-id="<?php echo esc_attr($note->id); ?>">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor"
										stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
									<path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2"
										stroke-linecap="round" stroke-linejoin="round" />
								</svg>
								<?php esc_html_e('Edit', 'lp-sticky-notes'); ?>
							</button>
							<button type="button" class="lp-btn-delete-note" data-note-id="<?php echo esc_attr($note->id); ?>">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path
										d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"
										stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
								<?php esc_html_e('Delete', 'lp-sticky-notes'); ?>
							</button>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>

	<!-- All Notes Modal -->
	<div class="lp-all-notes-modal" id="lp-all-notes-modal" style="display: none;">
		<div class="lp-all-notes-modal-overlay"></div>
		<div class="lp-all-notes-modal-content">
			<div class="lp-all-notes-modal-header">
				<h3>
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4"
							stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
					<?php esc_html_e('All Notes', 'lp-sticky-notes'); ?>
				</h3>
				<button type="button" class="lp-all-notes-modal-close" id="lp-all-notes-modal-close">
					&times;
				</button>
			</div>
			<div class="lp-all-notes-modal-body" id="lp-all-notes-modal-body">
				<!-- Notes will be loaded here via JavaScript -->
				<div class="lp-loading">
					<svg width="40" height="40" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"
							opacity="0.25" />
						<path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="2" fill="none"
							stroke-linecap="round" />
					</svg>
					<p><?php esc_html_e('Loading notes...', 'lp-sticky-notes'); ?></p>
				</div>
			</div>
		</div>
	</div>