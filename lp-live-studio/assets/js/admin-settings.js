/**
 * Admin Settings JavaScript
 *
 * @package lp-live-studio
 */

(function ($) {
    'use strict';

    const MFLLSAdmin = {
        /**
         * Initialize
         */
        init: function () {
            this.testConnection();
            this.connectGoogle();
            this.disconnectGoogle();
            this.sendTestEmail();
        },

        /**
         * Test platform connection
         */
        testConnection: function () {
            $(document).on('click', '.mf-lls-test-connection', function (e) {
                e.preventDefault();

                const $button = $(this);
                const $status = $button.siblings('.mf-lls-connection-status');
                const platform = $button.data('platform');

                $button.addClass('loading').prop('disabled', true);
                $status.removeClass('success error').addClass('loading').text('Testing connection');

                $.ajax({
                    url: mfLlsAdmin.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'mf_lls_test_connection',
                        platform: platform,
                        nonce: mfLlsAdmin.nonce
                    },
                    success: function (response) {
                        if (response.success) {
                            $status.removeClass('loading').addClass('success').text('✓ Connection successful');
                        } else {
                            $status.removeClass('loading').addClass('error').text('✗ ' + (response.data || 'Connection failed'));
                        }
                    },
                    error: function () {
                        $status.removeClass('loading').addClass('error').text('✗ Request failed');
                    },
                    complete: function () {
                        $button.removeClass('loading').prop('disabled', false);
                    }
                });
            });
        },

        /**
         * Connect Google account
         */
        connectGoogle: function () {
            $(document).on('click', '.mf-lls-connect-google', function (e) {
                e.preventDefault();

                const $button = $(this);
                $button.addClass('loading').prop('disabled', true).text('Connecting...');

                // Open OAuth popup
                const width = 600;
                const height = 700;
                const left = (screen.width / 2) - (width / 2);
                const top = (screen.height / 2) - (height / 2);

                const popup = window.open(
                    mfLlsAdmin.ajaxUrl + '?action=mf_lls_google_oauth_start&nonce=' + mfLlsAdmin.nonce,
                    'google_oauth',
                    `width=${width},height=${height},top=${top},left=${left}`
                );

                // Listen for OAuth callback
                const checkPopup = setInterval(function () {
                    if (popup.closed) {
                        clearInterval(checkPopup);
                        $button.removeClass('loading').prop('disabled', false).text('Connect Google Account');
                        // Reload page to show updated status
                        location.reload();
                    }
                }, 500);
            });
        },

        /**
         * Disconnect Google account
         */
        disconnectGoogle: function () {
            $(document).on('click', '.mf-lls-disconnect-google', function (e) {
                e.preventDefault();

                if (!confirm('Are you sure you want to disconnect your Google account?')) {
                    return;
                }

                const $button = $(this);
                $button.addClass('loading').prop('disabled', true);

                $.ajax({
                    url: mfLlsAdmin.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'mf_lls_google_disconnect',
                        nonce: mfLlsAdmin.nonce
                    },
                    success: function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Failed to disconnect: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function () {
                        alert('Request failed');
                    },
                    complete: function () {
                        $button.removeClass('loading').prop('disabled', false);
                    }
                });
            });
        },

        /**
         * Send test email
         */
        sendTestEmail: function () {
            $(document).on('click', '.mf-lls-send-test-email', function (e) {
                e.preventDefault();

                const $button = $(this);
                $button.addClass('loading').prop('disabled', true).text('Sending...');

                $.ajax({
                    url: mfLlsAdmin.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'mf_lls_send_test_email',
                        nonce: mfLlsAdmin.nonce
                    },
                    success: function (response) {
                        if (response.success) {
                            alert('Test email sent successfully! Check your inbox.');
                        } else {
                            alert('Failed to send test email: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function () {
                        alert('Request failed');
                    },
                    complete: function () {
                        $button.removeClass('loading').prop('disabled', false).text('Send Test Email');
                    }
                });
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        MFLLSAdmin.init();
    });

})(jQuery);
