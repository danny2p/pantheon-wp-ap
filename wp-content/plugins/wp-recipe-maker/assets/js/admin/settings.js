jQuery(document).ready(function($) {
	jQuery('#wprm-activate-license').on('click', function(e) {
		e.preventDefault();

		let button = jQuery(this);
		button.prop('disabled', true);

		let container = button.parents('#wprm-activate-license-container');
		let input = container.find('input.wprm-license');

		let setting = input.attr('id');
		let license = input.val();

		let settings_to_update = {};
		settings_to_update[setting] = license;

		let data = {
            settings: settings_to_update,
		};

        return fetch(wprm_admin.endpoints.setting, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': wprm_admin.api_nonce,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(data),
        }).then(response => {
            if ( response.ok ) {
				container.html('<p>Thank you for saving your license. We will check the validity on page refresh.</p>');
			} else {
				container.html('<p>Something went wrong. Please try again later.</p>');
			}
        });
	});
});
