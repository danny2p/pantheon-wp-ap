<?php
/**
 * Template for the addons page.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu
 */

?>

<div class="wrap wprm-addons">
	<h1><?php echo esc_html_e( 'Upgrade WP Recipe Maker', 'wp-recipe-maker' ); ?></h1>
	<div class="wprm-addons-bundle-container">
		<h2>Premium Bundle</h2>
		<?php if ( WPRM_Addons::is_active( 'premium' ) ) : ?>
		<p>You already have these features!</p>
		<?php else : ?>
		<ul>
			<li>Use <strong>ingredient links</strong> for linking to products or other recipes</li>
			<li><strong>Adjustable servings</strong> make it easy for your visitors</li>
			<li>Display all nutrition data in a <strong>nutrition label</strong></li>
			<li><strong>User Ratings</strong> allow visitors to vote without commenting</li>
			<li>Add a mobile-friendly <strong>kitchen timer</strong> to your recipes</li>
			<li>More <strong>Premium templates</strong> for a unique recipe template</li>
			<li>Display a <strong>Call to Action</strong> in your recipes</li>
			<li>Create custom <strong>recipe taxonomies</strong> like price level, difficulty, ...</li>
			<li>Use <strong>checkboxes</strong> for your ingredients and instructions</li>
			<li>...and more!</li>
		</ul>
		<div class="wprm-addons-button-container">
			<a class="button button-primary" href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">Learn More</a>
		</div>
		<?php endif; // Premium active. ?>
	</div>

	<div class="wprm-addons-bundle-container">
		<h2>Pro Bundle</h2>
		<?php if ( WPRM_Addons::is_active( 'pro' ) ) : ?>
		<p>You already have these features!</p>
		<?php else : ?>
		<ul>
			<li><strong>All Premium Features</strong></li>
			<li>Integration with a <strong>Nutrition API</strong> for automatic nutrition facts</li>
			<li>Create <strong>custom nutrition ingredients</strong> for your calculations</li>
			<li>Define and calculate a <strong>second unit system</strong> for your ingredients</li>
		</ul>
		<div class="wprm-addons-button-container">
			<a class="button button-primary" href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">Learn More</a>
		</div>
		<?php endif; // Pro Bundle active. ?>
	</div>

	<div class="wprm-addons-bundle-container">
		<h2>Elite Bundle</h2>
		<ul>
			<li><strong>All Premium and Pro Features</strong></li>
			<li>Your visitors can generate <strong>shopping lists</strong> and favourites with Recipe Collections</li>
			<li>Use Saved Recipe Collections to create <strong>Meal Plans</strong></li>
			<li>Have your <strong>visitors submit recipes</strong> through your website</li>
		</ul>
		<div class="wprm-addons-button-container">
			<a class="button button-primary" href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">Learn More</a>
		</div>
	</div>
</div>
