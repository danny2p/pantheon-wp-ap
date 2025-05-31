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
					'description' => __( 'Allow visitors to vote after clicking on the stars in the recipe card.', 'wp-recipe-maker' ),
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
					'id' => 'rating_stars_style',
					'name' => __( 'Star Icon Style', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'pointy' => __( 'Pointy Stars', 'wp-recipe-maker' ),
						'rounded' => __( 'Rounded Stars', 'wp-recipe-maker' ),
					),
					'default' => 'rounded',
				),
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
				),
				array(
					'id' => 'comment_rating_star_padding',
					'name' => __( 'Star Padding', 'wp-recipe-maker' ),
					'description' => __( 'Padding of the stars in the comment section. Increase when experiencing tap target issues.', 'wp-recipe-maker' ),
					'type' => 'number',
					'suffix' => 'px',
					'default' => '3',
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
			'description' => __( 'The User Ratings feature allows visitors to vote on your recipes after clicking on the stars inside of the recipe card.', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'user_ratings_type',
					'name' => __( 'User Ratings Mode', 'wp-recipe-maker' ),
					'description' => __( 'What type of user ratings to use.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'modal' => __( 'Open a modal when clicking on the stars', 'wp-recipe-maker' ),
						'scroll' => __( 'Jump to the comments section when clicking on the stars, open modal if comments are not available', 'wp-recipe-maker' ),
					),
					'default' => 'modal',
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
						array(
							'id' => 'features_comment_ratings',
							'value' => true,
						),
						array(
							'id' => 'user_ratings_type',
							'value' => 'scroll',
						),
					),
				),
				array(
					'id' => 'user_ratings_force_comment_scroll_to_smooth',
					'name' => __( 'Use Smooth Scroll', 'wp-recipe-maker' ),
					'description' => __( 'Use smooth scrolling when jumping to the comment form.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
					'dependency' => array(
						array(
							'id' => 'features_comment_ratings',
							'value' => true,
						),
						array(
							'id' => 'user_ratings_type',
							'value' => 'scroll',
						),
					),
				),
				array(
					'id' => 'user_ratings_indicate_not_voted',
					'name' => __( 'Transparent Stars when not Voted', 'wp-recipe-maker' ),
					'description' => __( 'Make the stars transparent when the current user has not voted yet.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'user_ratings_automatically_approve',
					'name' => __( 'Automatically Approve Ratings', 'wp-recipe-maker' ),
					'description' => __( 'Automatically approve comments and ratings given through the modal.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'user_ratings_automatically_approve_type',
					'name' => __( 'Which Ratings to Automatically Approve', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'all' => __( 'All ratings, with or without comment text', 'wp-recipe-maker' ),
						'5_star' => __( 'Only 5 star ratings without comment text', 'wp-recipe-maker' ),
						'4_star' => __( 'Only 4+ star ratings without comment text', 'wp-recipe-maker' ),
						'3_star' => __( 'Only 3+ star ratings without comment text', 'wp-recipe-maker' ),
						'2_star' => __( 'Only 2+ star ratings without comment text', 'wp-recipe-maker' ),
						'1_star' => __( 'Any rating without comment text', 'wp-recipe-maker' ),
					),
					'default' => 'all',
					'dependency' => array(
						'id' => 'user_ratings_automatically_approve',
						'value' => true,
					),
				),
				array(
					'id' => 'user_ratings_open_url_parameter',
					'name' => __( 'Open Modal through URL Parameter', 'wp-recipe-maker' ),
					'description' => __( 'Immediately open rating modal for first recipe on the page if this specific URL parameter is set. For example:', 'wp-recipe-maker' ) . ' https://demo.wprecipemaker.com/amazing-vegetable-pizza/?rate',
					'type' => 'text',
					'default' => 'rate',
				),
			),
			'dependency' => array(
				'id' => 'features_user_ratings',
				'value' => true,
			),
		),
		array(
			'name' => __( 'User Ratings Modal', 'wp-recipe-maker' ),
			'description' => __( 'Settings related to the modal that pops up when a visitors clicks on the stars.', 'wp-recipe-maker' ) . ' ' . __( 'For colors and fonts, check out the Appearance > Custom Style section.', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'user_ratings_modal_title',
					'name' => __( 'Modal Title', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => __( 'Rate This Recipe', 'wp-recipe-maker' ),
				),
				array(
					'id' => 'user_ratings_thank_you_title',
					'name' => __( 'Modal Title After Voting', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => __( 'Thank You!', 'wp-recipe-maker' ),
				),
				array(
					'id' => 'user_ratings_modal_star_color',
					'name' => __( 'Stars Color', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#FFD700',
				),
				array(
					'id' => 'user_ratings_modal_star_size',
					'name' => __( 'Star Size', 'wp-recipe-maker' ),
					'type' => 'number',
					'suffix' => 'px',
					'default' => '28',
				),
				array(
					'id' => 'user_ratings_modal_star_padding',
					'name' => __( 'Star Padding', 'wp-recipe-maker' ),
					'type' => 'number',
					'suffix' => 'px',
					'default' => '3',
				),
				array(
					'id' => 'user_ratings_text_above_comment',
					'name' => __( 'Text above input fields', 'wp-recipe-maker' ),
					'description' => __( 'Optional text to show above the input fields.', 'wp-recipe-maker' ),
					'type' => 'richTextarea',
					'default' => '',
				),
				array(
					'id' => 'user_ratings_modal_comment_placeholder',
					'name' => __( 'Comment Placeholder', 'wp-recipe-maker' ),
					'type' => 'textarea',
					'default' => __( 'Share your thoughts! What did you like about this recipe?', 'wp-recipe-maker' ),
				),
				array(
					'id' => 'user_ratings_modal_name_placeholder',
					'name' => __( 'Name Field Placeholder', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
				),
				array(
					'id' => 'user_ratings_modal_email_placeholder',
					'name' => __( 'Email Field Placeholder', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
				),
				array(
					'id' => 'user_ratings_modal_submit_comment_button',
					'name' => __( 'Submit Button', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => __( 'Rate and Review Recipe', 'wp-recipe-maker' ),
				),
				array(
					'id' => 'user_ratings_modal_submit_no_comment_button',
					'name' => __( 'Submit Button without Comment', 'wp-recipe-maker' ),
					'description' => __( 'Text to show if no comment was written (yet).', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => __( 'Rate Recipe', 'wp-recipe-maker' ),
					'dependency' => array(
						array(
							'id' => 'user_ratings_require_comment',
							'value' => false,
						),
					),
				),
				array(
					'id' => 'user_ratings_thank_you_message_with_comment',
					'name' => __( 'Thank You Message', 'wp-recipe-maker' ),
					'description' => __( 'Thank you message to show after voting with a comment. Make empty to not show anything.', 'wp-recipe-maker' ),
					'type' => 'richTextarea',
					'default' => __( 'Thank you for voting!', 'wp-recipe-maker' ),
				),
				array(
					'id' => 'user_ratings_problem_message',
					'name' => __( 'Problem Message', 'wp-recipe-maker' ),
					'description' => __( 'Message to show when there was a problem with rating the recipe. Make empty to not show anything.', 'wp-recipe-maker' ),
					'type' => 'richTextarea',
					'default' => __( 'There was a problem rating this recipe. Please try again later.', 'wp-recipe-maker' ),
				),
			),
			'dependency' => array(
				array(
					'id' => 'features_user_ratings',
					'value' => true,
				),
			),
		),
		array(
			'name' => __( 'User Ratings Requirements', 'wp-recipe-maker' ),
			'description' => __( 'For the strongest trust signal, and to have ratings show up as reviews in the recipe metadata, we recommend requiring a comment text and visitor details for each comment.', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'user_ratings_require_comment',
					'name' => __( 'Require Comment Text', 'wp-recipe-maker' ),
					'description' => __( 'Whether comment text is required to leave a rating', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'user_ratings_require_name',
					'name' => __( 'Require Visitor Name', 'wp-recipe-maker' ),
					'description' => __( 'Whether the name of the visitor is required to leave a rating', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'user_ratings_require_email',
					'name' => __( 'Require Visitor Email', 'wp-recipe-maker' ),
					'description' => __( 'Whether the email of the visitor is required to leave a rating', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'user_ratings_comments_closed',
					'name' => __( 'When Comments are Closed', 'wp-recipe-maker' ),
					'description' => __( 'What to do when the comments for the parent post of the recipe are closed.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'allow' => __( 'Visitors can still vote and comment through the modal', 'wp-recipe-maker' ),
						'close' => __( 'Do not allow voting and commenting through the User Rating Modal either', 'wp-recipe-maker' ),
					),
					'default' => 'allow',
				),
			),
			'dependency' => array(
				array(
					'id' => 'features_user_ratings',
					'value' => true,
				),
			),
		),
		array(
			'name' => __( 'User Ratings Comment Suggestions', 'wp-recipe-maker' ),
			'description' => __( 'Make it easier for visitors to leave a comment by giving them suggestions.', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'user_ratings_comment_suggestions_enabled',
					'name' => __( 'Enable Comment Suggestions', 'wp-recipe-maker' ),
					'description' => __( 'When to show comment suggestions.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'never' => __( 'Never', 'wp-recipe-maker' ),
						'5_star' => __( 'If they want to give 5 stars', 'wp-recipe-maker' ),
						'4_star' => __( 'If they want to give 4 stars or more', 'wp-recipe-maker' ),
						'3_star' => __( 'If they want to give 3 stars or more', 'wp-recipe-maker' ),
						'2_star' => __( 'If they want to give 2 stars or more', 'wp-recipe-maker' ),
						'always' => __( 'Always', 'wp-recipe-maker' ),
					),
					'default' => 'never',
				),
				array(
					'id' => 'user_ratings_comment_suggestion_text_before',
					'name' => __( 'Text before suggestions', 'wp-recipe-maker' ),
					'description' => __( 'Text to display before the list of suggestions.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => __( 'Let us know what you thought of this recipe:', 'wp-recipe-maker' ),
					'dependency' => array(
						array(
							'id' => 'user_ratings_comment_suggestions_enabled',
							'value' => 'never',
							'type' => 'inverse',
						),
					),
				),
				array(
					'id' => 'user_ratings_comment_suggestion_1',
					'name' => __( 'Comment Suggestion 1', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => __( 'This worked exactly as written, thanks!', 'wp-recipe-maker' ),
					'dependency' => array(
						array(
							'id' => 'user_ratings_comment_suggestions_enabled',
							'value' => 'never',
							'type' => 'inverse',
						),
					),
				),
				array(
					'id' => 'user_ratings_comment_suggestion_2',
					'name' => __( 'Comment Suggestion 2', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => __( 'My family loved this!', 'wp-recipe-maker' ),
					'dependency' => array(
						array(
							'id' => 'user_ratings_comment_suggestions_enabled',
							'value' => 'never',
							'type' => 'inverse',
						),
					),
				),
				array(
					'id' => 'user_ratings_comment_suggestion_3',
					'name' => __( 'Comment Suggestion 3', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => __( 'Thank you for sharing this recipe', 'wp-recipe-maker' ),
					'dependency' => array(
						array(
							'id' => 'user_ratings_comment_suggestions_enabled',
							'value' => 'never',
							'type' => 'inverse',
						),
					),
				),
				array(
					'id' => 'user_ratings_comment_suggestion_4',
					'name' => __( 'Comment Suggestion 4', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
					'dependency' => array(
						array(
							'id' => 'user_ratings_comment_suggestions_enabled',
							'value' => 'never',
							'type' => 'inverse',
						),
					),
				),
				array(
					'id' => 'user_ratings_comment_suggestion_5',
					'name' => __( 'Comment Suggestion 5', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
					'dependency' => array(
						array(
							'id' => 'user_ratings_comment_suggestions_enabled',
							'value' => 'never',
							'type' => 'inverse',
						),
					),
				),
				array(
					'id' => 'user_ratings_comment_suggestion_6',
					'name' => __( 'Comment Suggestion 6', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
					'dependency' => array(
						array(
							'id' => 'user_ratings_comment_suggestions_enabled',
							'value' => 'never',
							'type' => 'inverse',
						),
					),
				),
				array(
					'id' => 'user_ratings_comment_suggestion_text_after',
					'name' => __( 'Text after suggestions', 'wp-recipe-maker' ),
					'description' => __( 'Text to display after the list of suggestions, before the comment field.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => __( 'Or write in your own words:', 'wp-recipe-maker' ),
					'dependency' => array(
						array(
							'id' => 'user_ratings_comment_suggestions_enabled',
							'value' => 'never',
							'type' => 'inverse',
						),
					),
				),
			),
			'dependency' => array(
				array(
					'id' => 'features_user_ratings',
					'value' => true,
				),
			),
		),
		array(
			'name' => __( 'User Ratings without Comment Text', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'user_ratings_no_comment_display',
					'name' => __( 'Display', 'wp-recipe-maker' ),
					'description' => __( 'How to display ratings without comment text.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'comment' => __( 'Between regular comments', 'wp-recipe-maker' ),
						'summary' => __( 'Show as aggregate summary, showing details in modal', 'wp-recipe-maker' ),
					),
					'default' => 'summary',
				),
				array(
					'id' => 'user_ratings_summary_position',
					'name' => __( 'Summary Position', 'wp-recipe-maker' ),
					'description' => __( 'Where to the display the recipe ratings summary.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'above_form' => __( 'Above the comment input form', 'wp-recipe-maker' ),
						'below_form' => __( 'Below the comment input form', 'wp-recipe-maker' ),
					),
					'default' => 'above_form',
					'dependency' => array(
						array(
							'id' => 'user_ratings_no_comment_display',
							'value' => 'summary',
						),
					),
				),
				array(
					'id' => 'user_ratings_summary_star_color',
					'name' => __( 'Star Color', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#343434',
					'dependency' => array(
						array(
							'id' => 'user_ratings_no_comment_display',
							'value' => 'summary',
						),
					),
				),
				array(
					'id' => 'user_ratings_summary_star_size',
					'name' => __( 'Star Size', 'wp-recipe-maker' ),
					'type' => 'number',
					'suffix' => 'px',
					'default' => '18',
					'dependency' => array(
						array(
							'id' => 'user_ratings_no_comment_display',
							'value' => 'summary',
						),
					),
				),
				array(
					'id' => 'user_ratings_summary_star_padding',
					'name' => __( 'Star Padding', 'wp-recipe-maker' ),
					'type' => 'number',
					'suffix' => 'px',
					'default' => '0',
					'dependency' => array(
						array(
							'id' => 'user_ratings_no_comment_display',
							'value' => 'summary',
						),
					),
				),
				array(
					'id' => 'user_ratings_summary_modal_title',
					'name' => __( 'Summary Modal Title', 'wp-recipe-maker' ),
					'description' => __( 'Title for the modal that shows the rating details.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => __( 'Recipe Ratings without Comment', 'wp-recipe-maker' ),
					'dependency' => array(
						array(
							'id' => 'user_ratings_no_comment_display',
							'value' => 'summary',
						),
					),
				),
			),
			'dependency' => array(
				array(
					'id' => 'features_user_ratings',
					'value' => true,
				),
			),
		),
	),
);
