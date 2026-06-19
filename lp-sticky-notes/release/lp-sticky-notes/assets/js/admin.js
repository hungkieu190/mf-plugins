/**
 * Admin JavaScript for Student Notes page
 *
 * @package LP_Sticky_Notes
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        $('.lp-combobox').each(function () {
            initCombobox($(this));
        });

        // Toggle full note view
        $('.view-full-note').on('click', function (e) {
            e.preventDefault();
            var noteId = $(this).data('note-id');
            var $fullContent = $('#note-content-' + noteId);

            if ($fullContent.is(':visible')) {
                $fullContent.hide();
                $(this).text($(this).data('show-text') || getI18n('viewFull', 'View full'));
            } else {
                // Hide all other expanded notes
                $('.full-note-content').hide();
                $('.view-full-note').text($('.view-full-note').first().data('show-text') || getI18n('viewFull', 'View full'));

                // Show this note
                $fullContent.show();
                $(this).text($(this).data('hide-text') || getI18n('hide', 'Hide'));
            }
        });

        // Store button text for toggling
        $('.view-full-note').each(function () {
            $(this).data('show-text', $(this).text());
            $(this).data('hide-text', getI18n('hide', 'Hide'));
        });

        $('#lp-note-search').on('input', function () {
            var query = $(this).val().toLowerCase();

            $('.lp-note-row').each(function () {
                var $row = $(this);
                var noteId = $row.find('.view-full-note').data('note-id');
                var $detailRow = $('#note-content-' + noteId);
                var matches = $row.text().toLowerCase().indexOf(query) !== -1;

                $row.toggle(matches);

                if (!matches) {
                    $detailRow.hide();
                    $row.find('.view-full-note').text($row.find('.view-full-note').data('show-text') || getI18n('viewFull', 'View full'));
                }
            });
        });

        $('.lp-notes-table th.sortable').on('click', function () {
            var $header = $(this);
            var columnIndex = $header.index();
            var sortType = $header.data('sort') || 'text';
            var direction = $header.hasClass('sorted-asc') ? 'desc' : 'asc';
            var $tbody = $header.closest('table').find('tbody');
            var rows = [];

            $('.lp-note-row').each(function () {
                var $row = $(this);
                var noteId = $row.find('.view-full-note').data('note-id');
                rows.push({
                    row: $row,
                    detail: $('#note-content-' + noteId),
                    value: getSortValue($row, columnIndex, sortType)
                });
            });

            rows.sort(function (a, b) {
                if (a.value < b.value) {
                    return direction === 'asc' ? -1 : 1;
                }

                if (a.value > b.value) {
                    return direction === 'asc' ? 1 : -1;
                }

                return 0;
            });

            $('.lp-notes-table th.sortable').removeClass('sorted-asc sorted-desc');
            $header.addClass(direction === 'asc' ? 'sorted-asc' : 'sorted-desc');

            $.each(rows, function (_, item) {
                $tbody.append(item.row);
                $tbody.append(item.detail);
            });
        });

        function getSortValue($row, columnIndex, sortType) {
            var $cell = $row.children('td').eq(columnIndex);
            var value = $cell.data('sort-value');

            if (typeof value === 'undefined') {
                value = $cell.text();
            }

            if (sortType === 'date') {
                return parseInt(value, 10) || 0;
            }

            return String(value).toLowerCase();
        }

        function initCombobox($combo) {
            var $input = $combo.find('.lp-combobox-input');
            var $hidden = $combo.find('input[type="hidden"]');
            var $options = $combo.find('.lp-combobox-options');
            var action = $combo.data('action');
            var emptyLabel = $combo.data('empty-label');
            var request = null;
            var debounceTimer = null;

            $input.on('focus', function () {
                openCombobox($combo);
            });

            $input.on('input', function () {
                var query = $input.val();

                if (query !== ($input.data('selected-label') || emptyLabel)) {
                    $hidden.val('0');
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    searchOptions(query);
                }, 240);
            });

            $input.on('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeCombobox($combo);
                    return;
                }

                if (event.key === 'Enter') {
                    var $firstOption = $options.find('.lp-combobox-option:visible').first();

                    if ($combo.hasClass('is-open') && $firstOption.length) {
                        event.preventDefault();
                        selectOption($combo, $firstOption);
                    }
                }
            });

            $combo.on('click', '.lp-combobox-option', function () {
                selectOption($combo, $(this));
            });

            $combo.find('.lp-combobox-clear').on('click', function () {
                var $emptyOption = $options.find('.lp-combobox-option[data-value="0"]').first();

                if ($emptyOption.length) {
                    selectOption($combo, $emptyOption);
                } else {
                    $hidden.val('0');
                    $input.val(emptyLabel).data('selected-label', emptyLabel);
                    closeCombobox($combo);
                }
            });

            function searchOptions(query) {
                if (!action) {
                    return;
                }

                openCombobox($combo);
                renderMessage(getI18n('searching', 'Searching...'));

                if (request) {
                    request.abort();
                }

                request = $.ajax({
                    url: getAjaxUrl(),
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        action: action,
                        nonce: getNonce(),
                        search: query
                    }
                }).done(function (response) {
                    if (!response || !response.success || !response.data || !response.data.length) {
                        renderMessage(getI18n('noResults', 'No matches found'));
                        return;
                    }

                    renderOptions(response.data);
                }).fail(function (_, status) {
                    if (status !== 'abort') {
                        renderMessage(getI18n('noResults', 'No matches found'));
                    }
                });
            }

            function renderOptions(items) {
                var html = '';

                html += optionTemplate({
                    id: 0,
                    label: emptyLabel
                });

                $.each(items, function (_, item) {
                    html += optionTemplate(item);
                });

                $options.html(html);
            }

            function optionTemplate(item) {
                return '<button type="button" class="lp-combobox-option" data-value="' +
                    escapeHtml(item.id) +
                    '" data-label="' +
                    escapeHtml(item.label) +
                    '">' +
                    escapeHtml(item.label) +
                    '</button>';
            }

            function renderMessage(message) {
                $options.html('<div class="lp-combobox-message">' + escapeHtml(message) + '</div>');
            }
        }

        function selectOption($combo, $option) {
            var label = $option.data('label');
            var value = $option.data('value');
            var $input = $combo.find('.lp-combobox-input');

            $combo.find('input[type="hidden"]').val(value);
            $input.val(label).data('selected-label', label);
            closeCombobox($combo);
        }

        function openCombobox($combo) {
            $('.lp-combobox').not($combo).removeClass('is-open').find('.lp-combobox-input').attr('aria-expanded', 'false');
            $combo.addClass('is-open');
            $combo.find('.lp-combobox-input').attr('aria-expanded', 'true');
        }

        function closeCombobox($combo) {
            $combo.removeClass('is-open');
            $combo.find('.lp-combobox-input').attr('aria-expanded', 'false');
        }

        $(document).on('mousedown', function (event) {
            if (!$(event.target).closest('.lp-combobox').length) {
                $('.lp-combobox').each(function () {
                    closeCombobox($(this));
                });
            }
        });

        function getAjaxUrl() {
            if (window.lpStickyNotesAdmin && window.lpStickyNotesAdmin.ajaxUrl) {
                return window.lpStickyNotesAdmin.ajaxUrl;
            }

            return typeof ajaxurl !== 'undefined' ? ajaxurl : '';
        }

        function getNonce() {
            return window.lpStickyNotesAdmin && window.lpStickyNotesAdmin.nonce ? window.lpStickyNotesAdmin.nonce : '';
        }

        function getI18n(key, fallback) {
            if (window.lpStickyNotesAdmin && window.lpStickyNotesAdmin.i18n && window.lpStickyNotesAdmin.i18n[key]) {
                return window.lpStickyNotesAdmin.i18n[key];
            }

            return fallback;
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    });

})(jQuery);
