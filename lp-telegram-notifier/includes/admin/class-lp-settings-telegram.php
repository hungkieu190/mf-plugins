<?php
/**
 * Telegram Settings Tab
 *
 * @package LP_Telegram_Notifier
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LP_Settings_Telegram
 *
 * Telegram settings tab for LearnPress
 */
class MF_LP_Settings_Telegram extends LP_Abstract_Settings_Page
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->id = 'telegram';
        $this->text = esc_html__('Telegram', 'lp-telegram-notifier');

        parent::__construct();


    }

    /**
     * Get settings fields
     *
     * @param string $section Section ID.
     * @param string $tab Tab ID.
     * @return array Settings fields.
     */
    public function get_settings($section = '', $tab = '')
    {
        return array(
            array(
                'title' => __('Telegram Notifications', 'lp-telegram-notifier'),
                'type' => 'title',
                'desc' => __('Configure Telegram notifications for course enrollments.', 'lp-telegram-notifier'),
            ),
            array(
                'title' => __('Enable Notifications', 'lp-telegram-notifier'),
                'id' => 'mf_lp_tg_enabled',
                'default' => 'no',
                'type' => 'yes-no',
            ),
            array(
                'title' => __('Bot Token', 'lp-telegram-notifier'),
                'id' => 'mf_lp_tg_bot_token',
                'default' => '',
                'type' => 'text',
                'desc' => sprintf(
                    /* translators: %s: Link to BotFather */
                    __('Enter your Telegram Bot Token. Get it from %s.', 'lp-telegram-notifier'),
                    '<a href="https://t.me/BotFather" target="_blank">@BotFather</a>'
                ),
                'placeholder' => '1234567890:ABCdefGHIjklMNOpqrsTUVwxyz',
                'class' => 'regular-text',
            ),
            array(
                'title' => __('Chat ID', 'lp-telegram-notifier'),
                'id' => 'mf_lp_tg_chat_id',
                'default' => '',
                'type' => 'text',
                'desc' => sprintf(
                    /* translators: %s: Link to get chat ID */
                    __('Enter your Telegram Chat ID. Get it from %s.', 'lp-telegram-notifier'),
                    '<a href="https://t.me/userinfobot" target="_blank">@userinfobot</a>'
                ),
                'placeholder' => '123456789',
                'class' => 'regular-text',
            ),
            array(
                'type' => 'sectionend',
            ),
        );
    }

    /**
     * Output settings page
     *
     * @param string $section Section ID.
     * @param array  $sections Sections array.
     */
    public function admin_page_settings($section = '', $sections = array())
    {
        // Check license status.
        $license_handler = LP_Telegram_Notifier::instance()->get_license_handler();
        $is_licensed = $license_handler->is_feature_enabled();

        // Show warning if license not active.
        if (!$is_licensed) {
            $this->mf_show_license_warning();
        }

        // Call parent method to render settings fields.
        parent::admin_page_settings($section, $sections);

        // Output test button JavaScript.
        $this->mf_output_test_button_html();

        // Disable fields if no license.
        if (!$is_licensed) {
            $this->mf_disable_fields_script();
        }
    }

    /**
     * Show license warning message
     */
    private function mf_show_license_warning()
    {
        ?>
        <div class="notice notice-warning" style="margin: 20px 0; padding: 15px; border-left: 4px solid #ffb900;">
            <h3 style="margin-top: 0;">
                ⚠️ <?php esc_html_e('License Required', 'lp-telegram-notifier'); ?>
            </h3>
            <p>
                <?php
                printf(
                    /* translators: %1$s: Opening link tag, %2$s: Closing link tag */
                    esc_html__('Please %1$sactivate your license%2$s to configure and use Telegram notifications. Settings are view-only until license is activated.', 'lp-telegram-notifier'),
                    '<a href="' . esc_url(admin_url('admin.php?page=mamflow-license&tab=telegram-notifier')) . '" class="button button-primary" style="margin-left: 10px;">',
                    '</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Output JavaScript to disable all input fields
     */
    private function mf_disable_fields_script()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                // Disable all Telegram settings fields
                $('input[name^="learn_press_mf_lp_tg"], select[name^="learn_press_mf_lp_tg"], textarea[name^="learn_press_mf_lp_tg"]').each(function () {
                    var $field = $(this);

                    $field.prop('disabled', true);
                    $field.css({
                        'opacity': '0.6',
                        'cursor': 'not-allowed',
                        'background-color': '#f5f5f5'
                    });

                    $field.attr('title', '<?php echo esc_js(__('License required to modify settings', 'lp-telegram-notifier')); ?>');
                });

                // Disable test connection button if it exists
                $('#mf-test-telegram-connection').prop('disabled', true).css({
                    'opacity': '0.6',
                    'cursor': 'not-allowed'
                });

                // Prevent form submission
                $('.lp-settings-page form').on('submit', function (e) {
                    if ($('input[name^="learn_press_mf_lp_tg"]:disabled').length > 0) {
                        e.preventDefault();
                        alert('<?php echo esc_js(__('Please activate your license before saving settings.', 'lp-telegram-notifier')); ?>');
                        return false;
                    }
                });
            });
        </script>
        <?php
    }

    /**
     * Output test connection button HTML
     */
    private function mf_output_test_button_html()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                // Add test button after Chat ID field
                var $chatIdRow = $('input[name="learn_press_mf_lp_tg_chat_id"]').closest('tr');
                var testButtonHtml = '<tr>' +
                    '<th scope="row"><?php echo esc_js(__('Test Connection', 'lp-telegram-notifier')); ?></th>' +
                    '<td>' +
                    '<button type="button" id="mf-test-telegram-connection" class="button button-secondary">' +
                    '<?php echo esc_js(__('Send Test Message', 'lp-telegram-notifier')); ?>' +
                    '</button>' +
                    '<p class="description"><?php echo esc_js(__('Click to send a test message to your Telegram chat.', 'lp-telegram-notifier')); ?></p>' +
                    '<div id="mf-telegram-test-result" style="margin-top: 10px;"></div>' +
                    '</td>' +
                    '</tr>';

                $chatIdRow.after(testButtonHtml);

                // Handle test button click
                $('#mf-test-telegram-connection').on('click', function (e) {
                    e.preventDefault();

                    var $button = $(this);
                    var $result = $('#mf-telegram-test-result');

                    // Get current values from form.
                    var botToken = $('input[name="learn_press_mf_lp_tg_bot_token"]').val();
                    var chatId = $('input[name="learn_press_mf_lp_tg_chat_id"]').val();

                    if (!botToken || !chatId) {
                        $result.html('<div class="notice notice-error"><p><?php echo esc_js(__('Please enter both Bot Token and Chat ID first.', 'lp-telegram-notifier')); ?></p></div>');
                        return;
                    }

                    // Disable button and show loading.
                    $button.prop('disabled', true).text('<?php echo esc_js(__('Sending...', 'lp-telegram-notifier')); ?>');
                    $result.html('<div class="notice notice-info"><p><?php echo esc_js(__('Sending test message...', 'lp-telegram-notifier')); ?></p></div>');

                    // Send AJAX request.
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'mf_test_telegram_connection',
                            bot_token: botToken,
                            chat_id: chatId,
                            nonce: '<?php echo esc_js(wp_create_nonce('mf_test_telegram')); ?>'
                        },
                        success: function (response) {
                            if (response.success) {
                                $result.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                            } else {
                                $result.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                            }
                        },
                        error: function () {
                            $result.html('<div class="notice notice-error"><p><?php echo esc_js(__('Request failed. Please try again.', 'lp-telegram-notifier')); ?></p></div>');
                        },
                        complete: function () {
                            $button.prop('disabled', false).text('<?php echo esc_js(__('Send Test Message', 'lp-telegram-notifier')); ?>');
                        }
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * AJAX handler for test connection
     */

}

return new MF_LP_Settings_Telegram();
