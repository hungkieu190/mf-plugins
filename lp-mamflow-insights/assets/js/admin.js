jQuery(document).ready(function ($) {
    // Initialize Select2 for course selector
    if ($('.mf-select2-course').length) {
        $('.mf-select2-course').select2({
            placeholder: 'Search or select a course...',
            allowClear: true,
            width: 'resolve'
        });
    }

    if ($('#engagementChart').length && window.mf_insights_data) {
        const ctx = document.getElementById('engagementChart').getContext('2d');
        const data = window.mf_insights_data;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Completion', 'Avg Progress', 'Drop-off', 'Quiz Pass'],
                datasets: [{
                    label: '% Value',
                    data: [data.completion, data.progress, data.dropoff, data.pass],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(255, 193, 7, 0.7)'
                    ],
                    borderColor: [
                        '#28a745',
                        '#36a2eb',
                        '#dc3545',
                        '#ffc107'
                    ],
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function (value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.raw + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // Lesson Completion Funnel Chart
    if ($('#lessonFunnelChart').length && window.mf_lessons_data) {
        const ctx = document.getElementById('lessonFunnelChart').getContext('2d');
        const data = window.mf_lessons_data;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Completion Rate %',
                    data: data.rates,
                    borderColor: '#3182ce',
                    backgroundColor: 'rgba(49, 130, 206, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3182ce',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function (value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.raw + '% Completion';
                            }
                        }
                    }
                }
            }
        });
    }

    // CSV Export handler
    $(document).on('click', '.mf-export-btn', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const courseId = $btn.data('course-id');
        const exportType = $btn.data('export-type');

        if (!courseId || !exportType) {
            alert('Missing export parameters');
            return;
        }

        $btn.prop('disabled', true).addClass('updating-message');

        $.ajax({
            url: mf_insights.ajax_url,
            type: 'POST',
            data: {
                action: 'mf_insights_export_csv',
                nonce: mf_insights.nonce,
                course_id: courseId,
                export_type: exportType
            },
            success: function (response) {
                if (response.success) {
                    // Create blob and trigger download
                    const blob = new Blob([response.data.content], { type: 'text/csv;charset=utf-8;' });
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = response.data.filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                } else {
                    alert(response.data.message || 'Export failed');
                }
            },
            error: function () {
                alert('Export request failed');
            },
            complete: function () {
                $btn.prop('disabled', false).removeClass('updating-message');
            }
        });
    });
});
