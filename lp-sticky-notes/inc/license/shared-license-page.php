<?php
/**
 * Shared Mamflow License Page
 * 
 * Centralized license management page for all Mamflow plugins.
 * Each plugin registers its own tab via the 'mamflow_license_tabs' filter.
 * 
 * @package Mamflow_License_System
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('mamflow_render_license_page')) {
    /**
     * Render the unified Mamflow license page with tabs
     */
    function mamflow_render_license_page()
    {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Get all registered tabs
        $tabs = apply_filters('mamflow_license_tabs', []);

        // Sort tabs by priority
        uasort($tabs, function ($a, $b) {
            $priority_a = isset($a['priority']) ? $a['priority'] : 10;
            $priority_b = isset($b['priority']) ? $b['priority'] : 10;
            return $priority_a - $priority_b;
        });

        // Get active tab
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';

        // If no tab specified or invalid tab, use first tab
        if (empty($active_tab) || !isset($tabs[$active_tab])) {
            $active_tab = !empty($tabs) ? array_key_first($tabs) : '';
        }

        ?>
        <div class="wrap mamflow-license-page">
            <div class="mamflow-page-header">
                <h1><?php esc_html_e('Mamflow License Management', 'mamflow'); ?></h1>
                <p><?php esc_html_e('Manage licenses for Mamflow plugins installed on this LearnPress site.', 'mamflow'); ?></p>
            </div>

            <?php if (!empty($tabs)): ?>
                <!-- Tab Navigation -->
                <h2 class="nav-tab-wrapper">
                    <?php foreach ($tabs as $tab_id => $tab_data): ?>
                        <?php
                        $tab_url = add_query_arg([
                            'page' => 'mamflow-license',
                            'tab' => $tab_id
                        ], admin_url('admin.php'));

                        $active_class = ($active_tab === $tab_id) ? 'nav-tab-active' : '';
                        ?>
                        <a href="<?php echo esc_url($tab_url); ?>" class="nav-tab <?php echo esc_attr($active_class); ?>">
                            <?php echo esc_html($tab_data['title']); ?>
                        </a>
                    <?php endforeach; ?>
                </h2>

                <!-- Tab Content -->
                <div class="mamflow-tab-content">
                    <?php
                    if (isset($tabs[$active_tab]) && isset($tabs[$active_tab]['callback'])) {
                        $callback = $tabs[$active_tab]['callback'];

                        if (is_callable($callback)) {
                            call_user_func($callback);
                        } else {
                            echo '<div class="notice notice-error"><p>Tab callback is not callable.</p></div>';
                        }
                    }
                    ?>
                </div>

            <?php else: ?>
                <div class="notice notice-warning">
                    <p><strong><?php esc_html_e('No Mamflow plugins registered.', 'mamflow'); ?></strong></p>
                    <p><?php esc_html_e('Install and activate a Mamflow plugin to manage its license here.', 'mamflow'); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <style>
            .mamflow-license-page {
                max-width: 1200px;
                color: #0f172a;
            }

            .mamflow-page-header {
                padding: 24px 0 16px;
            }

            .mamflow-page-header h1 {
                margin: 0;
                color: #0f172a;
                font-size: 24px;
                line-height: 1.25;
            }

            .mamflow-page-header p,
            .mamflow-license-heading p,
            .mamflow-license-section p,
            .mamflow-license-help p {
                color: #475569;
                font-size: 14px;
                line-height: 1.6;
            }

            .mamflow-page-header p {
                margin: 8px 0 0;
            }

            .mamflow-tab-content {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-top: none;
                padding: 24px;
                margin-top: -1px;
            }

            .mamflow-license-page .nav-tab-wrapper {
                border-bottom-color: #cbd5e1;
            }

            .mamflow-license-page .nav-tab {
                border-color: #cbd5e1;
                color: #475569;
                font-size: 14px;
            }

            .mamflow-license-page .nav-tab-active {
                border-bottom-color: #fff;
                color: #2563eb;
            }

            .mamflow-license-panel {
                max-width: 900px;
            }

            .mamflow-license-heading {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 24px;
                margin-bottom: 24px;
            }

            .mamflow-license-heading h2,
            .mamflow-license-section h3,
            .mamflow-license-help h3 {
                margin: 0;
                color: #0f172a;
            }

            .mamflow-license-heading h2 {
                font-size: 18px;
            }

            .mamflow-license-heading p {
                margin: 8px 0 0;
            }

            .mamflow-status-badge {
                display: inline-flex;
                align-items: center;
                min-height: 28px;
                padding: 0 10px;
                border: 1px solid #cbd5e1;
                border-radius: 4px;
                background: #fff;
                color: #475569;
                font-size: 12px;
                font-weight: 600;
                white-space: nowrap;
            }

            .mamflow-status-badge.is-success,
            .mamflow-status-text.is-success {
                color: #16a34a;
            }

            .mamflow-status-badge.is-warning {
                border-color: #f59e0b;
                color: #0f172a;
                background: #ffffff;
            }

            .mamflow-license-section,
            .mamflow-license-help {
                border: 1px solid #e2e8f0;
                border-radius: 4px;
                background: #fff;
                padding: 16px;
                margin-bottom: 16px;
            }

            .mamflow-license-section h3,
            .mamflow-license-help h3 {
                font-size: 16px;
            }

            .mamflow-license-table {
                margin-top: 16px;
                border-color: #e2e8f0;
            }

            .mamflow-license-table th {
                width: 180px;
                color: #475569;
                font-weight: 600;
            }

            .mamflow-license-table th,
            .mamflow-license-table td {
                padding: 12px;
                font-size: 14px;
            }

            .mamflow-license-actions {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
                margin-top: 16px;
            }

            .mamflow-license-form {
                display: grid;
                gap: 8px;
                max-width: 520px;
                margin-top: 16px;
            }

            .mamflow-license-form label {
                color: #475569;
                font-size: 13px;
                font-weight: 600;
            }

            .mamflow-license-form input {
                width: 100%;
                min-height: 36px;
                border-color: #cbd5e1;
            }

            .mamflow-license-page .button-primary {
                background: #2563eb;
                border-color: #2563eb;
                color: #fff;
            }

            .mamflow-license-page .button-primary:hover,
            .mamflow-license-page .button-primary:focus {
                background: #2563eb;
                border-color: #2563eb;
            }

            @media (max-width: 782px) {
                .mamflow-license-heading {
                    flex-direction: column;
                    gap: 12px;
                }

                .mamflow-tab-content {
                    padding: 16px;
                }
            }
        </style>
        <?php
    }
}

if (!function_exists('mamflow_register_license_menu')) {
    /**
     * Register the Mamflow License menu page
     * 
     * This should be called by the first Mamflow plugin that loads.
     * Subsequent plugins will just register their tabs.
     */
    function mamflow_register_license_menu()
    {
        // Check if page already exists
        global $submenu;
        $page_exists = false;

        if (isset($submenu['learn_press'])) {
            foreach ($submenu['learn_press'] as $item) {
                if ($item[2] === 'mamflow-license') {
                    $page_exists = true;
                    break;
                }
            }
        }

        // Only create if doesn't exist
        if (!$page_exists) {
            add_submenu_page(
                'learn_press',
                esc_html__('Mamflow License', 'mamflow'),
                esc_html__('Mamflow License', 'mamflow'),
                'manage_options',
                'mamflow-license',
                'mamflow_render_license_page'
            );
        }
    }
}
