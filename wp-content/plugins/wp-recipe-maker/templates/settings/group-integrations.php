<?php
$integrations = array(
	'id' => 'integrations',
	'icon' => 'plug',
	'name' => __( 'Integrations', 'wp-recipe-maker' ),
	'subGroups' => array(
		array(
			'name' => __( 'Shoppable Recipes with Instacart', 'wp-recipe-maker' ),
			'description' => 'Make your recipes shoppable by adding an Instacart Shoppable Recipe button next to your ingredient list and monetize your content by signing up for the Instacart Tastemakers Affiliate Marketing Program. Available in the US only at the moment.',
			'documentation' => 'https://help.bootstrapped.ventures/article/323-shop-with-instacart-button',
			'settings' => array(
				array(
					'id' => 'integration_instacart_agree',
					'name' => __( 'Agree to Instacart Button terms', 'wp-recipe-maker' ),
					'description' => __( 'Enable to agree to the applicable terms of use for the button. Click the following link for more information:', 'wp-recipe-maker' ),
					'documentation' => 'https://widgets.instacart.com/widget-terms.pdf',
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'integration_instacart',
					'name' => __( 'Automatically add Instacart Button', 'wp-recipe-maker' ),
					'description' => __( 'Enable to automatically output the Instacart Shoppable Recipe button after the ingredients section. Alternatively, add the Shoppable Recipe button in the Template Editor.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
					'dependency' => array(
						'id' => 'integration_instacart_agree',
						'value' => true,
					),
				),
				array(
					'id' => 'integration_instacart_affiliate_id',
					'name' => __( 'Instacart Tastemakers ID', 'wp-recipe-maker' ),
					'description' => __( 'Optional Tastemakers ID to monetize your Shoppable Recipe button. Terms apply.', 'wp-recipe-maker' ),
					'documentation' => 'https://www.instacart.com/tastemakers',
					'type' => 'text',
					'default' => '',
					'dependency' => array(
						'id' => 'integration_instacart_agree',
						'value' => true,
					),
				),
			),
		),
		array(
			'name' => __( 'SmartWithFood', 'wp-recipe-maker' ),
			'description' => 'SmartWithFood presents a widget that enables the automated translation of recipe ingredients into actual products within the Collect&Go platform, all via an embedded button. This feature seamlessly integrates the ingredients into the digital shopping basket, streamlining the user experience.',
			'documentation' => 'https://www.smartwithfood.com/solutions/shoppable-recipes',
			'settings' => array(
				array(
					'id' => 'integration_smartwithfood_token',
					'name' => __( 'SmartWithFood Token', 'wp-recipe-maker' ),
					'description' => __( 'The token provided by SmartWithFood. Required to make the button show up.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
				),
				array(
					'id' => 'integration_smartwithfood',
					'name' => __( 'Automatically add SmartWithFood Button', 'wp-recipe-maker' ),
					'description' => __( 'Enable to automatically output the Smart with Food button after the ingredients section. Alternatively, add the button in the Template Editor.', 'wp-recipe-maker' ),
					'documentation' => 'https://help.bootstrapped.ventures/article/332-smartwithfood-shoppable-recipes',
					'type' => 'toggle',
					'default' => false,
					'dependency' => array(
						'id' => 'integration_smartwithfood_token',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
		),
	),
);
