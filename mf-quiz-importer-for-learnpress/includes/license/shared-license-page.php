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
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.'));
        }

        $tabs = apply_filters('mamflow_license_tabs', array());

        uasort(
            $tabs,
            function ($a, $b) {
                $priority_a = isset($a['priority']) ? $a['priority'] : 10;
                $priority_b = isset($b['priority']) ? $b['priority'] : 10;
                return $priority_a - $priority_b;
            }
        );

        $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification

        if (empty($active_tab) || !isset($tabs[$active_tab])) {
            $active_tab = !empty($tabs) ? array_key_first($tabs) : '';
        }
        ?>
        <div class="wrap mamflow-license-page">
            <h1>
                <?php esc_html_e('Mamflow License Management', 'mamflow'); ?>
            </h1>
            <p class="description">
                <?php esc_html_e('Manage licenses for all your Mamflow plugins in one place.', 'mamflow'); ?>
            </p>

            <?php if (!empty($tabs)): ?>
                <h2 class="nav-tab-wrapper">
                    <?php foreach ($tabs as $tab_id => $tab_data): ?>
                        <?php
                        $tab_url = add_query_arg(
                            array(
                                'page' => 'mamflow-license',
                                'tab' => $tab_id,
                            ),
                            admin_url('admin.php')
                        );
                        $active_class = ($active_tab === $tab_id) ? 'nav-tab-active' : '';
                        ?>
                        <a href="<?php echo esc_url($tab_url); ?>" class="nav-tab <?php echo esc_attr($active_class); ?>">
                            <?php echo esc_html($tab_data['title']); ?>
                        </a>
                    <?php endforeach; ?>
                </h2>

                <div class="mamflow-tab-content">
                    <?php
                    if (isset($tabs[$active_tab]) && isset($tabs[$active_tab]['callback'])) {
                        $callback = $tabs[$active_tab]['callback'];
                        if (is_callable($callback)) {
                            call_user_func($callback);
                        } else {
                            echo '<div class="notice notice-error"><p>' . esc_html__('Tab callback is not callable.', 'mamflow') . '</p></div>';
                        }
                    }
                    ?>
                </div>

            <?php else: ?>
                <div class="notice notice-warning">
                    <p><strong>
                            <?php esc_html_e('No Mamflow plugins registered.', 'mamflow'); ?>
                        </strong></p>
                    <p>
                        <?php esc_html_e('Install and activate Mamflow plugins to manage their licenses here.', 'mamflow'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <style>
            .mamflow-license-page {
                margin-top: 20px;
            }

            .mamflow-license-page .description {
                margin-bottom: 20px;
                font-size: 14px;
            }

            .mamflow-tab-content {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-top: none;
                padding: 20px;
                margin-top: -1px;
            }

            .mamflow-tab-content .card {
                border: none;
                box-shadow: none;
                background: transparent;
                padding: 0;
            }
        </style>
        <?php
    }
}

if (!function_exists('mamflow_register_license_menu')) {
    /**
     * Register the Mamflow License menu page
     *
     * Called by the first Mamflow plugin that loads.
     * Subsequent plugins just register their tabs.
     */
    function mamflow_register_license_menu()
    {
        global $submenu;
        $page_exists = false;

        if (isset($submenu['learn_press'])) {
            foreach ($submenu['learn_press'] as $item) {
                if ('mamflow-license' === $item[2]) {
                    $page_exists = true;
                    break;
                }
            }
        }

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
