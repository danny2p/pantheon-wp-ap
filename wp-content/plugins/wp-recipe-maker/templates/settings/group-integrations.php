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
			'name' => __( 'Shoppable Recipes with Walmart', 'wp-recipe-maker' ),
			'description' => 'Make your recipes shoppable with the largest retailer in North America by adding a Walmart Shoppable button powered by eMeals, which will be placed directly in line with your recipe instructions. Available in the US only.',
			'documentation' => 'https://support.emeals.com/portal/en/kb/articles/grocery-connect-shoppable-recipes-with-walmart',
			'settings' => array(
				array(
					'id' => 'emeals_walmart_button',
					'name' => __( 'Automatically add Shop Ingredients with Walmart Button', 'wp-recipe-maker' ),
					'description' => __( 'Enable to automatically output the Shop Ingredients with Walmart button after the ingredients section. Alternatively, add the button in the Template Editor.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
		array(
			'name' => __( 'Relevant In-Recipe Ads and Shoppability with Chicory', 'wp-recipe-maker' ),
			'description' => 'Monetize your recipe card with contextual, in-recipe ads from food advertisers that make sense for your site. Offer your audience a seamless shopping experience at 70+ integrated retailers, including Instacart, Walmart and Kroger. Join major food publishers like Food Network, The Pioneer Woman, and Delish in trusting our solution. Available in the U.S. only.',
			'documentation' => 'https://chicory.co/chicory-for-content-creators',
			'settings' => array(
				array(
					'name' => '',
					'description' => __( 'Click the button to the right to register directly with Chicory and set up revenue payment. Please note that enabling the Activate Chicory toggle below will not automatically set up payment. Note: If you work with Mediavine, you can enable Chicory directly through your Mediavine dashboard.', 'wp-recipe-maker' ),
					'type' => 'button',
					'button' => __( 'Sign Up with Chicory', 'wp-recipe-maker' ),
					'link' => 'https://chicoryapp.com/become-a-chicory-recipe-partner/?plugin=WP%20Recipe%20Maker',
				),
				array(
					'id' => 'integration_chicory_activate',
					'name' => __( 'Activate Chicory', 'wp-recipe-maker' ),
					'description' => __( 'Enable to activate Chicory on your site after registering by clicking the sign-up link above.', 'wp-recipe-maker' ),
					'documentation' => 'https://help.bootstrapped.ventures/article/341-chicory-integration',
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'integration_chicory_shoppable_button',
					'name' => __( 'Enable Chicory Shoppable Recipe Button', 'wp-recipe-maker' ),
					'description' => __( "Chicory's Shoppable Recipe Button will appear below your recipes' ingredient lists, allowing users to cart the ingredients for your recipes at 70+ retailers.", 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
					'dependency' => array(
						'id' => 'integration_chicory_activate',
						'value' => true,
					),
				),
				array(
					'id' => 'integration_chicory_premium_ads',
					'name' => __( 'Enable Chicory In-Recipe Ads', 'wp-recipe-maker' ),
					'description' => __( "Chicory's in-recipe ads will appear within and below your recipes' ingredient lists, allowing you to secure earnings from relevant grocery advertisers.", 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
					'dependency' => array(
						'id' => 'integration_chicory_activate',
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
