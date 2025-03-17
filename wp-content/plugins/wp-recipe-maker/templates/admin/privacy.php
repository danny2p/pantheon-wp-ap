<?php
/**
 * Template for the privacy policy.
 *
 * @link       http://bootstrapped.ventures
 * @since      2.5.2
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin
 */

?>
<?php esc_html_e( 'Bootstrapped Ventures, the developer of WP Recipe Maker, does not have access to any of the data collected by the plugin. This is all stored in your local database and not communicated back to us. Take note of the following topics for your own privacy policy.', 'wp-recipe-maker' ); ?>
<h2><?php esc_html_e( 'What personal data we collect and why we collect it', 'wp-recipe-maker' ); ?></h2>
<h3><?php esc_html_e( 'Comments', 'wp-recipe-maker' ); ?></h3>
<?php esc_html_e( 'When comment ratings are enabled we store the rating a user has given to a recipe along with the personal data WordPress core stores.', 'wp-recipe-maker' ); ?>
<h3><?php esc_html_e( 'Cookies', 'wp-recipe-maker' ); ?></h3>
<?php esc_html_e( 'When user ratings are enabled we store a WPRM_User_Voted_%recipe% cookie (with %recipe% the ID of the recipe) that contains the rating this user has given to a particular recipe. This cookie is used as (one of the) measures to prevent rating spam.', 'wp-recipe-maker' ); ?>
<h3><?php esc_html_e( 'IP Address', 'wp-recipe-maker' ); ?></h3>
<?php esc_html_e( 'When user ratings are enabled we store the IP address upon voting. This is used as (one of the) measures to prevent rating spam.', 'wp-recipe-maker' ); ?>
<h3><?php esc_html_e( 'Their own manually input information', 'wp-recipe-maker' ); ?></h3>
<?php esc_html_e( 'With the Recipe Submission feature personal data can be collected, depending on the fields that were added to the form. This can include the user email and name. When using the reCAPTCHA feature you will be agreeing to their terms of use and privacy policy.', 'wp-recipe-maker' ); ?>
<h2><?php esc_html_e( 'How long we retain your data', 'wp-recipe-maker' ); ?></h2>
<?php esc_html_e( 'Our cookies are stored for 30 days.', 'wp-recipe-maker' ); ?> <?php esc_html_e( 'User submitted data is stored indefinitely in the local database.', 'wp-recipe-maker' ); ?>
