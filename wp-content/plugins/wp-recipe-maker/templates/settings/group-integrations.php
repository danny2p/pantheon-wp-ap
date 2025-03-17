<?php

$home_url = home_url();
$current_user_email = '?';
$current_user = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : false;
if ( $current_user ) {
	$current_user_email = $current_user->user_email;
}

$integrations = array(
	'id' => 'integrations',
	'icon' => 'plug',
	'name' => __( 'Integrations', 'wp-recipe-maker' ),
	'subGroups' => array(
		array(
			'name' => __( 'Access exclusive brands with Jupiter', 'wp-recipe-maker' ),
			'description' => 'Collaborate with top brands and give your subscribers access to product coupons through our exclusive recipe campaigns. We handle the legwork, securing partnerships with CPG brands so you can focus on creating great recipes. Available in the US and Canada only.',
			'documentation' => 'https://www.jupiter.co/creators',
			'settings' => array(
				array(
					'id' => 'integration_jupiter',
					'name' => __( 'Activate Jupiter', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
		array(
			'name' => __( 'Shoppable Recipes with Instacart', 'wp-recipe-maker' ),
			'description' => 'Make your recipes shoppable by adding an Instacart Shoppable Recipe button next to your ingredient list and monetize your content by signing up for the Instacart Tastemakers Affiliate Marketing Program. Available in the US only at the moment.',
			'documentation' => 'https://help.bootstrapped.ventures/article/323-shop-with-instacart-button',
			'settings' => array(
				array(
					'id' => 'integration_instacart_agree',
					'name' => __( 'Agree to Instacart Button terms', 'wp-recipe-maker' ),
					'description' => __( 'Enable to agree with the', 'wp-recipe-maker' ),
					'documentation' => 'https://docs.instacart.com/developer_platform_api/guide/terms_and_policies/developer_terms/',
					'documentation_text' => 'Instacart Developer Platform Terms and Conditions',
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
					'id' => 'integration_instacart_shopping_list',
					'name' => __( 'Shop Collections Shopping List', 'wp-recipe-maker' ),
					'description' => __( 'Show a button to shop the shopping list in the Recipe Collections feature.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
					'dependency' => array(
						array(
							'id' => 'integration_instacart_agree',
							'value' => true,
						),
						array(
							'id' => 'recipe_collections_shopping_list',
							'value' => true,
						),
					),
				),
				array(
					'id' => 'integration_instacart_affiliate_id',
					'name' => __( 'Impact.com Partner ID', 'wp-recipe-maker' ),
					'description' => __( 'Optional Impact.com Partner ID to monetize your Shoppable Recipe button. You agree to be bound by the Instacart Developer Platform Affiliate Marketing Terms and Conditions', 'wp-recipe-maker' ),
					'documentation' => 'https://docs.instacart.com/developer_platform_api/guide/concepts/launch_activities/conversions_and_payments/',
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
		array(
			'name' => __( 'Your own Recipe App with NAKKO Recipe to App', 'wp-recipe-maker' ),
			'description' => 'Provide your users with a dedicated app to increase traffic and foster loyalty. Our native apps, available for both iOS and Android, seamlessly integrate with your WP Recipe Maker backend, offering an outstanding mobile experience for your visitors. Additionally, an app store presence enhances your brand visibility and you can take advantage of the profitable monetization opportunities available within the app. Ensure your website is optimized and compatible with our services for converting your recipe site into a mobile app by performing a free preliminary compatibility scan.',
			'documentation' => 'https://recipetoapp.com',
			'settings' => array(
				array(
					'name' => '',
					'description' => __( 'Click the button to the right to request the free compatibility check.', 'wp-recipe-maker' ),
					'type' => 'button',
					'button' => __( 'Do the Compatibility Check', 'wp-recipe-maker' ),
					'link' => 'mailto:wprm@nakko.com?subject=RecipeToApp%20for%20' . esc_url( $home_url ) . '&body=Domain%3A%20' . esc_url( $home_url ) . '%0AMy%20email%20address%3A%20' . urlencode( $current_user_email ) . '%0A%0AI%20would%20love%20to%20learn%20more%20about%20the%20RecipeToApp%20solution!',
				),
			),
		),
	),
);
