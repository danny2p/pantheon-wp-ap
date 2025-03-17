/**
 * WP Recipe Maker admin notices functionality.
 * 
 * @since 9.8.1
 */
(function() {
    'use strict';
    
    document.addEventListener("DOMContentLoaded", function() {
        // Use MutationObserver to watch for dynamically added dismiss buttons
        var wprmNotices = document.querySelectorAll(".wprm-notice.is-dismissible");
        
        // Set up observer for each notice
        for (var i = 0; i < wprmNotices.length; i++) {
            observeNotice(wprmNotices[i]);
        }
        
        function observeNotice(notice) {
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes) {
                        mutation.addedNodes.forEach(function(node) {
                            // Check if the dismiss button was added
                            if (node.classList && node.classList.contains("notice-dismiss")) {
                                // Add our event listener to the dismiss button
                                node.addEventListener("click", function(e) {
                                    var noticeId = notice.getAttribute("data-notice-id");
                                    var userId = notice.getAttribute("data-user-id");
                                    
                                    if (noticeId) {
                                        // REST API call to dismiss notice
                                        var xhr = new XMLHttpRequest();
                                        xhr.open("DELETE", wprm_admin.endpoints.notices, true);
                                        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                                        xhr.send("id=" + encodeURIComponent(noticeId) + "&user_id=" + encodeURIComponent(userId));
                                    }
                                });
                                
                                // We found what we were looking for, disconnect observer
                                observer.disconnect();
                            }
                        });
                    }
                });
            });
            
            // Start observing the notice for added children
            observer.observe(notice, { childList: true });
        }
    });
})();