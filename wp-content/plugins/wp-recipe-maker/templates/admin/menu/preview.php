<?php
/**
 * Template for the preview page.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu
 */

?>

<div class="wrap wprm-preview" style="max-width: 500px;">
	<h1><?php echo esc_html_e( 'WP Recipe Maker Plugin Preview', 'wp-recipe-maker' ); ?></h1>
	<p>
		Hi there!
	</p>
	<p>
		Welcome to a live preview of the WP Recipe Maker plugin! We created an example recipe for you and added it to a regular WordPress post. This can give you an idea of how the plugin works and what it looks like.
	</p>
	<p>
		Things you can <strong>check out in this preview</strong>:
	</p>
	<ul style="list-style: disc; margin-left: 23px;">
		<li>Have a <a href="<?php echo get_permalink( $preview_post_id ); ?>">look at the example post</a> to see what a recipe could look like on your site</li>
		<li>Check out the <a href="<?php echo admin_url( 'admin.php?page=wprm_manage' ); ?>">WP Recipe Maker > Manage page</a>, where you'll find an overview of all recipes and can edit them</li>
	</ul>
	<br/>
	<p>
		If you want to learn even more, check out <a href="https://demo.wprecipemaker.com">our demo site</a> or <a href="https://bootstrapped.ventures/wp-recipe-maker/">WP Recipe Maker homepage</a>. But our best recommendation is to just install WP Recipe Maker on your site and play around with it. There is a ton to discover!
	</p>
</div>
