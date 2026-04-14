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

function getContrastClass(color) {
	var ctx = document.createElement('canvas').getContext('2d');
	ctx.fillStyle = color;
	var hex = ctx.fillStyle;
	var r = parseInt(hex.slice(1, 3), 16);
	var g = parseInt(hex.slice(3, 5), 16);
	var b = parseInt(hex.slice(5, 7), 16);
	var luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
	return luminance > 0.5 ? 'wprm-progress-percentage-dark' : 'wprm-progress-percentage-light';
}

function update_progress_bar() {
	var percentage = ( 1.0 - ( posts.length / posts_total ) ) * 100;
	var isComplete = percentage >= 100;
	jQuery('#wprm-reports-progress-bar').css('width', percentage + '%').toggleClass('wprm-progress-complete', isComplete);

	var label = jQuery('#wprm-reports-progress-container .wprm-progress-percentage');
	label.text(isComplete ? '100%' : percentage.toFixed(1) + '%');
	label.removeClass('wprm-progress-percentage-light wprm-progress-percentage-dark wprm-progress-percentage-complete');

	if (isComplete) {
		label.addClass('wprm-progress-percentage-complete');
	} else if (percentage >= 50) {
		var color = getComputedStyle(document.documentElement).getPropertyValue('--wp-admin-theme-color').trim() || '#3858e9';
		label.addClass(getContrastClass(color));
	}
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
