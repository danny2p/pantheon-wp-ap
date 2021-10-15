<?php
$my_emissions_status = get_option( 'wprm_my_emissions_status', 'inactive' );

switch ( $my_emissions_status ) {
	case 'valid':
		$my_emissions_status_description = 'Your API key is active and authenticated.';
		break;
	case 'invalid':
		$my_emissions_status_description = 'Your API key was invalid. Please check with My Emission support.';
		break;
	case 'inactive':
		$my_emissions_status_description = 'No API key set. Save the API Key setting and press the button on the right.';
		break;
	default:
		$my_emissions_status_description = 'Unknown error. Please contact support.';
}

$integrations = array(
	'id' => 'integrations',
	'icon' => 'plug',
	'name' => __( 'Integrations', 'wp-recipe-maker' ),
	'subGroups' => array(
		array(
			'name' => 'My Emissions carbon footprint labels',
			'description' => 'Enable to display a carbon label for your recipes. You must first sign-up with My Emissions to launch this integration; note that WP Recipe Maker customers are entitled to a special reduced price for the labels.',
			'documentation' => 'https://myemissions.green/wp-recipe-maker?ref=wprm',
			'settings' => array(
				array(
					'id' => 'my_emissions_enable',
					'name' => __( 'Enable My Emissions Integration', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'my_emissions_api_key',
					'name' => __( 'Your My Emissions API Key', 'wp-recipe-maker' ),
					'description' => __( 'Click "Save Changes" in the top left before checking the API Key status.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
					'dependency' => array(
						'id' => 'my_emissions_enable',
						'value' => true,
					),
				),
				array(
					'name' => __( 'My Emissions API Key Status', 'wp-recipe-maker' ),
					'description' => $my_emissions_status_description,
					'type' => 'button',
					'button' => __( 'Check API Key Status', 'wp-recipe-maker' ),
					'link' => admin_url( 'admin.php?page=wprm_settings' ),
					'dependency' => array(
						'id' => 'my_emissions_enable',
						'value' => true,
					),
				),
				array(
					'id' => 'my_emissions_show_all',
					'name' => __( 'Show label for all recipes', 'wp-recipe-maker' ),
					'description' => 'By default, you can add a carbon label when managing individual recipes. Enable this setting to add a carbon label to all your recipes.',
					'type' => 'toggle',
					'default' => false,
					'dependency' => array(
						'id' => 'my_emissions_enable',
						'value' => true,
					),
				),
				array(
					'name' => __( 'Add Carbon Label to your recipe template', 'wp-recipe-maker' ),
					'documentation' => 'https://help.bootstrapped.ventures/article/283-my-emissions-carbon-footprint-label',
					'type' => 'button',
					'button' => __( 'Open the Template Editor', 'wp-recipe-maker' ),
					'link' => admin_url( 'admin.php?page=wprm_template_editor' ),
					'dependency' => array(
						'id' => 'my_emissions_enable',
						'value' => true,
					),
				),
			),
		),
		array(
			'name' => __( 'Other Integrations', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'integration_mediavine_ad',
					'name' => __( 'Add extra Mediavine ad unit', 'wp-recipe-maker' ),
					'description' => __( 'Enable to automatically output an extra ad unit after the recipe ingredients block.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
	),
);
