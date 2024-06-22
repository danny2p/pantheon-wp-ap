import '../../css/admin/reports.scss';

let action = false;
let args = {};
let posts = [];
let posts_total = 0;

function handle_posts() {
	var data = {
		action: 'wprm_' + action,
		security: wprm_admin.nonce,
		posts: JSON.stringify(posts),
		args: args,
    };

	jQuery.post(wprm_admin.ajax_url, data, function(out) {
		if (out.success) {
            posts = out.data.posts_left;
			update_progress_bar();

			if(posts.length > 0) {
				handle_posts();
			} else {
				window.location.search += '&wprm_report_finished=true';
			}
		} else {
			window.location = out.data.redirect;
		}
	}, 'json');
}

function update_progress_bar() {
	var percentage = ( 1.0 - ( posts.length / posts_total ) ) * 100;
	jQuery('#wprm-reports-progress-bar').css('width', percentage + '%');
};

jQuery(document).ready(function($) {
	// Import Process
	if(typeof window.wprm_reports !== 'undefined') {
		action = wprm_reports.action;
		args = wprm_reports.args;
		posts = wprm_reports.posts;
        posts_total = wprm_reports.posts.length;
		handle_posts();
	}
});
