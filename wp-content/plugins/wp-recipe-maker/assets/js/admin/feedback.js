function give_feedback(answer) {
	var data = {
		action: 'wprm_feedback',
		security: wprm_admin.nonce,
		answer: answer
	};

	jQuery.post(wprm_admin.ajax_url, data);
};

jQuery(document).ready(function($) {
	var feedback_notice = jQuery('.wprm-feedback-notice');

	if (feedback_notice.length > 0) {
		jQuery('#wprm-feedback-stop').on('click', function() {
			give_feedback('stop');
			feedback_notice.slideUp();
		});

		jQuery('#wprm-feedback-no').on('click', function() {
			give_feedback('no');
			var message = '<strong>How could we make it better?</strong><br/>';
			message += 'Please send any issues or suggestions you have to <a href="mailto:support@bootstrapped.ventures?subject=WP%20Recipe%20Maker%20feedback">support@bootstrapped.ventures</a> and we\'ll see what we can do!';
			feedback_notice.html(message);
		});

		jQuery('#wprm-feedback-yes').on('click', function() {
			give_feedback('yes');
			var message = '<strong>Happy to hear!</strong><br/>';
			message += 'It would be really helpful if you could leave us an honest review over at <a href="https://wordpress.org/support/plugin/wp-recipe-maker/reviews/#new-post" target="_blank">wordpress.org</a><br/>';
			message += 'Suggestions to make the plugin even better are also very welcome at <a href="mailto:support@bootstrapped.ventures?subject=WP%20Recipe%20Maker%20suggestions">support@bootstrapped.ventures</a>';
			feedback_notice.html(message);
		});
	}
});
