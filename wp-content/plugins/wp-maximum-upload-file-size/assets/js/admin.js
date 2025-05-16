(function ($) {
    "use strict";

    $(document).ready(function () {
        console.log("Admin script loaded");
        // Hide admin notice
        $('#hideWmufsNotice').on('click', function () {
            $.ajax({
                url: wmufs_admin_notice_ajax_object.wmufs_admin_notice_ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wmufs_admin_notice_ajax_object_save',
                    data: 1,
                    _ajax_nonce: wmufs_admin_notice_ajax_object.nonce
                },
                success: function (response) {
                    if (response.success === true) {
                        $('.hideWmufsNotice').hide('fast');
                    }
                }
            });
        });

        // Function to initialize sidebar functionality
        function initializeSidebar() {
            // Example: Re bind click events for sidebar toggle
            // Replace with your actual sidebar initialization logic
            $('.wmufs-sidebar-toggle').off('click').on('click', function () {
                $('.wmufs-sidebar').toggleClass('active');
            });
        }

        // Handle tab clicks
        $('.max-uploader-tab-link').on('click', function (e) {
            e.preventDefault();
            const tabId = $(this).data('tab');

            // Update active tab styling
            $('.max-uploader-tab-link').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');

            // Show/hide tab content
            $('.max-uploader-tab-content').hide();
            $('#max-uploader-tab-' + tabId).show();

            // Update URL without a reload
            const newUrl = new URL(window.location.href);
            newUrl.searchParams.set('tab', tabId);
            history.pushState({ tab: tabId }, '', newUrl);

            // Re-initialize sidebar for the new tab
            initializeSidebar();
        });

        // Handle browser back/forward navigation
        $(window).on('popstate', function (event) {
            const state = event.originalEvent.state;
            const tabId = state && state.tab ? state.tab : (new URLSearchParams(window.location.search).get('tab') || 'general');

            // Update active tab styling
            $('.wmufs-tab-link').removeClass('nav-tab-active');
            $('.max-uploader-tab-link[data-tab="' + tabId + '"]').addClass('nav-tab-active');

            // Show/hide tab content
            $('.wmufs-tab-content').hide();
            $('#max-uploader-tab-' + tabId).show();

            // Re-initialize sidebar for the new tab
            initializeSidebar();
        });

        // Initialize active tab
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab') || 'general';
        $('.max-uploader-tab-link').removeClass('nav-tab-active');
        $('.max-uploader-tab-link[data-tab="' + initialTab + '"]').addClass('nav-tab-active');
        $('.max-uploader-tab-content').hide();
        $('#max-uploader-tab-' + initialTab).show();

        // Initialize sidebar on page load
        initializeSidebar();
    });
})(jQuery);
