<?php
/**
 * Admin Settings Page Template
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

$tabs = $this->get_tabs();
$current_tab = $this->get_current_tab();
?>

<div class="wrap mf-lls-settings-wrap">
    <h1>
        <?php esc_html_e('LearnPress Live Studio Settings', 'lp-live-studio'); ?>
    </h1>

    <?php if (isset($_GET['updated']) && 'true' === $_GET['updated']): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php esc_html_e('Settings saved successfully.', 'lp-live-studio'); ?>
            </p>
        </div>
    <?php endif; ?>

    <nav class="nav-tab-wrapper">
        <?php foreach ($tabs as $tab_key => $tab_label): ?>
            <a href="<?php echo esc_url(add_query_arg(array('page' => 'mf-lls-settings', 'tab' => $tab_key), admin_url('admin.php'))); ?>"
                class="nav-tab <?php echo $current_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($tab_label); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="mf-lls-settings-content">
        <form method="post" action="">
            <?php wp_nonce_field('mf_lls_save_settings', 'mf_lls_settings_nonce'); ?>
            <input type="hidden" name="mf_lls_current_tab" value="<?php echo esc_attr($current_tab); ?>">

            <?php
            // Load tab content
            $tab_file = MF_LLS_PATH . '/includes/admin/views/settings-tab-' . $current_tab . '.php';
            if (file_exists($tab_file)) {
                include $tab_file;
            }
            ?>

            <p class="submit">
                <button type="submit" name="mf_lls_save_settings" class="button button-primary">
                    <?php esc_html_e('Save Settings', 'lp-live-studio'); ?>
                </button>
            </p>
        </form>
    </div>
</div>