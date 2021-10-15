<?php
/**
 * Template for the recipe shortcode preview.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/modal
 */

?>
<?php
$image = $recipe->image( array( 100, 100 ) );
if ( $image ) :
?>
	<span contentEditable="false" style="display: inline-block; float: right; margin: 0 10px 10px 0;"><?php echo wp_kses_post( $image ); ?></span>
<?php endif; // Image. ?>

<span contentEditable="false" style="display: inline-block; margin-bottom: 10px;"><?php echo esc_html( strip_shortcodes( wp_strip_all_tags( $recipe->name() ) ) ); ?></span>
<span contentEditable="false" style="display: block; font-size: 12px; margin-bottom: 10px;"><?php echo esc_html( strip_shortcodes( wp_strip_all_tags( $recipe->summary() ) ) ); ?></span>

<span contentEditable="false" style="display: block; margin-bottom: 10px;">
	<?php
	$taxonomies = WPRM_Taxonomies::get_taxonomies();

	foreach ( $taxonomies as $taxonomy => $options ) :
		$key = substr( $taxonomy, 5 );
		$terms = $recipe->tags( $key );

		if ( count( $terms ) > 0 ) : ?>
		<span contentEditable="false" style="display: block; font-size: 12px;"><?php echo esc_html( WPRM_Template_Helper::label( $key . '_tags', $options['singular_name'] ) ); ?>: 
			<?php foreach ( $terms as $index => $term ) {
				if ( 0 !== $index ) {
					echo ', ';
				}
				echo esc_html( $term->name );
			} ?>
		</span>
	<?php endif; // Count.
	endforeach; // Taxonomies. ?>
</span>

<span contentEditable="false" style="display: block; margin-bottom: 10px;">
	<?php if ( $recipe->prep_time() ) : ?>
	<span contentEditable="false" style="display: block; font-size: 12px;">
		<?php echo esc_html( WPRM_Template_Helper::label( 'prep_time' ) ); ?>: 
		<?php echo esc_html( $recipe->prep_time() ); ?>m
	</span>
	<?php endif; // Prep time. ?>
	<?php if ( $recipe->cook_time() ) : ?>
	<span contentEditable="false" style="display: block; font-size: 12px;">
		<?php echo esc_html( WPRM_Template_Helper::label( 'cook_time' ) ); ?>: 
		<?php echo esc_html( $recipe->cook_time() ); ?>m
	</span>
	<?php endif; // Cook time. ?>
	<?php if ( $recipe->total_time() ) : ?>
	<span contentEditable="false" style="display: block; font-size: 12px;">
		<?php echo esc_html( WPRM_Template_Helper::label( 'total_time' ) ); ?>: 
		<?php echo esc_html( $recipe->total_time() ); ?>m
	</span>
	<?php endif; // Total time. ?>
	<?php if ( $recipe->custom_time() && $recipe->custom_time_label() ) : ?>
	<span contentEditable="false" style="display: block; font-size: 12px;">
		<?php echo esc_html( $recipe->custom_time_label() ); ?>: 
		<?php echo esc_html( $recipe->custom_time() ); ?>m
	</span>
	<?php endif; // Custom time. ?>
</span>

<span contentEditable="false" style="display: block; margin-bottom: 10px;">
	<?php if ( $recipe->servings() ) : ?>
	<span contentEditable="false" style="display: block; font-size: 12px;">
		<?php echo esc_html( WPRM_Template_Helper::label( 'servings' ) ); ?>: 
		<?php echo esc_html( $recipe->servings() . ' ' . $recipe->servings_unit() ); ?>
	</span>
	<?php endif; // Servings. ?>
	<?php if ( $recipe->author() ) : ?>
	<span contentEditable="false" style="display: block; font-size: 12px;">
		<?php echo esc_html( WPRM_Template_Helper::label( 'author' ) ); ?>: 
		<?php echo esc_html( $recipe->author() ); ?>
	</span>
	<?php endif; // Author. ?>
</span>

<?php
$equipment = $recipe->equipment();
if ( count( $equipment ) > 0 ) : ?>
<span contentEditable="false" style="display: block; margin-top: 10px; margin-bottom: 10px;">
	<?php foreach ( $equipment as $equipment ) : ?>
	<span contentEditable="false" style="display: block; font-size: 12px;">
		- 
		<?php echo esc_html( strip_shortcodes( wp_strip_all_tags( $equipment['name'] ) ) ); ?>
	</span>
	<?php endforeach; // equipment. ?>
</span>
<?php endif; // equipment. ?>

<?php
$ingredients = $recipe->ingredients();
if ( count( $ingredients ) > 0 ) : ?>
<span contentEditable="false" style="display: block; margin-top: 10px; margin-bottom: 10px;">
	<?php foreach ( $ingredients as $ingredient_group ) : ?>
	<span contentEditable="false" style="display: block; margin-bottom: 10px;">
		<?php if ( $ingredient_group['name'] ) : ?>
		<span contentEditable="false" style="display: block; font-size: 12px; font-weight: bold;"><?php echo esc_html( strip_shortcodes( wp_strip_all_tags( $ingredient_group['name'] ) ) ); ?></span>
		<?php endif; // Ingredient group name. ?>
		<?php foreach ( $ingredient_group['ingredients'] as $ingredient ) : ?>
		<span contentEditable="false" style="display: block; font-size: 12px;">
			- 
			<?php if ( $ingredient['amount'] ) : ?>
			<?php echo esc_html( strip_shortcodes( wp_strip_all_tags( $ingredient['amount'] ) ) ); ?>
			<?php endif; // Ingredient amount. ?>
			<?php if ( $ingredient['unit'] ) : ?>
			<?php echo esc_html( strip_shortcodes( wp_strip_all_tags( $ingredient['unit'] ) ) ); ?>
			<?php endif; // Ingredient unit. ?>
			<?php echo esc_html( strip_shortcodes( wp_strip_all_tags( $ingredient['name'] ) ) ); ?>
			<?php if ( $ingredient['notes'] ) : ?>
			<span contentEditable="false" style="padding-left: 5px; font-size: 10px;"><?php echo esc_html( strip_shortcodes( wp_strip_all_tags( $ingredient['notes'] ) ) ); ?></span>
			<?php endif; // Ingredient notes. ?>
		</span>
		<?php endforeach; // Ingredients. ?>
	</span>
	<?php endforeach; // Ingredient groups. ?>
</span>
<?php endif; // Ingredients. ?>

<?php
$instructions = $recipe->instructions();
if ( count( $instructions ) > 0 ) : ?>
<span contentEditable="false" style="display: block; margin-top: 10px; margin-bottom: 10px;">
	<?php foreach ( $instructions as $instruction_group ) : ?>
	<span contentEditable="false" style="display: block; margin-bottom: 10px;">
		<?php if ( $instruction_group['name'] ) : ?>
		<span contentEditable="false" style="display: block; font-size: 12px; font-weight: bold;"><?php echo esc_html( strip_shortcodes( wp_strip_all_tags( $instruction_group['name'] ) ) ); ?></span>
		<?php endif; // instruction group name. ?>
		<?php foreach ( $instruction_group['instructions'] as $index => $instruction ) : ?>
		<span contentEditable="false" style="display: block; font-size: 12px;">
			<?php echo esc_html( ($index + 1) . ') ' . strip_shortcodes( wp_strip_all_tags( $instruction['text'] ) ) ); ?>
		</span>
		<?php endforeach; // instructions. ?>
	</span>
	<?php endforeach; // instruction groups. ?>
</span>
<?php endif; // instructions. ?>

<span contentEditable="false" style="display: block; font-size: 12px; margin-bottom: 10px;"><?php echo esc_html( strip_shortcodes( wp_strip_all_tags( $recipe->notes() ) ) ); ?></span>

<?php
$nutrition = $recipe->nutrition();
if ( count( $nutrition ) > 0 ) : ?>
<span contentEditable="false" style="display: block; margin-top: 20px; font-size: 10px; margin-bottom: 10px;">
	<?php foreach ( $nutrition as $field => $value ) : ?>
		<?php if ( $value && 'serving_unit' !== $field ) : ?>
		<span contentEditable="false" style="padding-right: 5px;"><?php echo esc_html( $field . ' ' . $value ); ?></span>
		<?php endif; // nutrition field. ?>
	<?php endforeach; // nutrition. ?>
</span>
<?php endif; // nutrition. ?>

<span contentEditable="false" style="display: block; clear: both; height: 1px; line-height: 1px;">&nbsp;</span>
