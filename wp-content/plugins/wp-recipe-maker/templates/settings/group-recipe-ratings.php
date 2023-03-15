<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$jetpack_warning = '';
if ( class_exists( 'Jetpack' ) && in_array( 'comments', Jetpack::get_active_modules(), true ) ) {
	$jetpack_warning = ' ' . __( 'Warning: comment ratings cannot work with the Jetpack Comments feature you have activated.', 'wp-recipe-maker' );
}

$recipe_ratings = array(
	'id' => 'recipeRatings',
	'icon' => 'star',
	'name' => __( 'Star Ratings', 'wp-recipe-maker' ),
	'subGroups' => array(
		array(
			'name' => __( 'Rating Feature', 'wp-recipe-maker' ),
			'description' => __( 'Choose what rating features to enable. The average recipe rating will combine the different methods of rating.', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'features_comment_ratings',
					'name' => __( 'Comment Ratings', 'wp-recipe-maker' ),
					'description' => __( 'Allow visitors to vote on your recipes when commenting.', 'wp-recipe-maker' ) . $jetpack_warning,
					'documentation' => 'https://help.bootstrapped.ventures/article/26-comment-ratings',
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'features_user_ratings',
					'name' => __( 'User Ratings', 'wp-recipe-maker' ),
					'required' => 'premium',
					'description' => __( 'Allow visitors to vote on your recipes by simply clicking on the stars.', 'wp-recipe-maker' ),
					'documentation' => 'https://help.bootstrapped.ventures/article/27-user-ratings',
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
		array(
			'name' => __( 'Appearance', 'wp-recipe-maker' ),
			'description' => __( 'How the rating details will be displayed in a recipe. The following placeholders can be used:', 'wp-recipe-maker' ) . ' %average%, %votes%, %user%',
			'settings' => array(
				array(
					'id' => 'rating_details_zero',
					'name' => __( 'No Ratings', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => __( 'No ratings yet', 'wp-recipe-maker' ),
				),
				array(
					'id' => 'rating_details_one',
					'name' => __( 'One Rating', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '%average% ' . __( 'from', 'wp-recipe-maker' ) . ' 1 ' . _n( 'vote', 'votes', 1, 'wp-recipe-maker' ),
				),
				array(
					'id' => 'rating_details_multiple',
					'name' => __( 'Multiple Ratings', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '%average% ' . __( 'from', 'wp-recipe-maker' ) . ' %votes% ' . _n( 'vote', 'votes', 2, 'wp-recipe-maker' ),
				),
				array(
					'id' => 'rating_details_user_voted',
					'name' => __( 'User Voted', 'wp-recipe-maker' ),
					'description' => __( 'This will show up where the %voted% placeholder is used, if the user has a user ratings vote for this recipe.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '(' . __( 'Your vote:', 'wp-recipe-maker' ) . ' %user%)',
				),
				array(
					'id' => 'rating_details_user_not_voted',
					'name' => __( 'User Not Voted', 'wp-recipe-maker' ),
					'description' => __( 'This will show up where the %not_voted% placeholder is used, if the user has no user ratings yet.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '(' . __( 'Click on the stars to vote!', 'wp-recipe-maker' ) . ')',
				),
			),
		),
		array(
			'name' => __( 'Comment Ratings', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'template_color_comment_rating',
					'name' => __( 'Stars Color', 'wp-recipe-maker' ),
					'description' => __( 'Color of the stars in the comment section, not in the recipe itself.', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#343434',
					'dependency' => array(
						'id' => 'features_custom_style',
						'value' => true,
					),
				),
				array(
					'id' => 'comment_rating_star_size',
					'name' => __( 'Star Size', 'wp-recipe-maker' ),
					'description' => __( 'Size of the stars in the comment section, not in the recipe itself.', 'wp-recipe-maker' ),
					'type' => 'number',
					'suffix' => 'px',
					'default' => '18',
					'dependency' => array(
						'id' => 'features_custom_style',
						'value' => true,
					),
				),
				array(
					'id' => 'comment_rating_position',
					'name' => __( 'Stars Position in Comments', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'above' => __( 'Above the comment', 'wp-recipe-maker' ),
						'below' => __( 'Below the comment', 'wp-recipe-maker' ),
					),
					'default' => 'above',
				),
				array(
					'id' => 'comment_rating_form_position',
					'name' => __( 'Stars Position in Comment Form', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'above' => __( 'Above the comment field', 'wp-recipe-maker' ),
						'below' => __( 'Below the comment field', 'wp-recipe-maker' ),
						'legacy' => __( 'Legacy mode', 'wp-recipe-maker' ),
					),
					'default' => 'above',
				),
				array(
					'id' => 'label_comment_rating',
					'name' => __( 'Comment Rating', 'wp-recipe-maker' ),
					'type' => 'text',
					'description' => __( 'Label used in the comment form.', 'wp-recipe-maker' ),
					'default' => __( 'Recipe Rating', 'wp-recipe-maker' ),
					'dependency' => array(
						'id' => 'recipe_template_mode',
						'value' => 'legacy',
						'type' => 'inverse',
					),
				),
			),
			'dependency' => array(
				'id' => 'features_comment_ratings',
				'value' => true,
			),
		),
		array(
			'name' => __( 'User Ratings', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'user_ratings_indicate_not_voted',
					'name' => __( 'Transparent Stars when not Voted', 'wp-recipe-maker' ),
					'description' => __( 'Make the stars transparent when the current user has not voted yet.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'user_ratings_spam_prevention',
					'name' => __( 'Spam Prevention Method', 'wp-recipe-maker' ),
					'description' => __( 'How to prevent spam ratings. Use "Anonymous ID" if you do not want to store IP addresses in the database.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'ip' => __( 'Check IP address', 'wp-recipe-maker' ),
						'uid' => __( 'Anonymous ID stored in cookie', 'wp-recipe-maker' ),
					),
					'default' => 'ip',
				),
				array(
					'id' => 'user_ratings_clear_cache',
					'name' => __( 'Clear Cache after Rating', 'wp-recipe-maker' ),
					'description' => __( 'Try to clear the site cache after a user rating. Makes sure the vote increases immediately after refreshing the page.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'user_ratings_thank_you_message',
					'name' => __( 'Thank You Message', 'wp-recipe-maker' ),
					'description' => __( 'Thank you message to show after clicking on the stars. Make empty to not show anything.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => __( 'Thank you for voting!', 'wp-recipe-maker' ),
				),
				array(
					'id' => 'features_comment_ratings',
					'name' => __( 'Force people to leave a comment', 'wp-recipe-maker' ),
					'description' => __( 'You need to enable the Comment Ratings feature to use this option.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
					'dependency' => array(
						'id' => 'features_comment_ratings',
						'value' => false,
					),
				),
				array(
					'id' => 'user_ratings_force_comment',
					'name' => __( 'Force visitors to leave a comment', 'wp-recipe-maker' ),
					'description' => __( 'Redirect visitors to the comment form instead of allowing them to vote by just clicking.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'never' => __( 'Never, allow any user rating', 'wp-recipe-maker' ),
						'1_star' => __( 'If they want to give 1 star', 'wp-recipe-maker' ),
						'2_star' => __( 'If they want to give 2 stars or less', 'wp-recipe-maker' ),
						'3_star' => __( 'If they want to give 3 stars or less', 'wp-recipe-maker' ),
						'4_star' => __( 'If they want to give 4 stars or less', 'wp-recipe-maker' ),
						'always' => __( 'Always redirect to the comment form', 'wp-recipe-maker' ),
					),
					'default' => 'never',
					'dependency' => array(
						'id' => 'features_comment_ratings',
						'value' => true,
					),
				),
				array(
					'id' => 'user_ratings_force_comment_scroll_to',
					'name' => __( 'HTML Element to scroll to', 'wp-recipe-maker' ),
					'description' => __( 'Optionally set a custom HTML element to scroll to. Can be useful when using lazy loading your comments, for example.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
					'dependency' => array(
						'id' => 'features_comment_ratings',
						'value' => true,
					),
				),
			),
			'dependency' => array(
				'id' => 'features_user_ratings',
				'value' => true,
			),
		),
	),
);
