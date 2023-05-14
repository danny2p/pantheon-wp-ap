<?php
/**
 * Template to be used for the rating field in the comment form.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/public
 */

$hide_form = '';
$displaying_in_admin = is_admin() && ! wp_doing_ajax();

if ( ! $displaying_in_admin && false === WPRM_Template_Shortcodes::get_current_recipe_id() ) {
	$hide_form = ' style="display: none"';
}

$size = intval( WPRM_Settings::get( 'comment_rating_star_size' ) );
$size = 0 < $size ? $size : 16;

$padding = intval( WPRM_Settings::get( 'comment_rating_star_padding' ) );
$padding = 0 < $padding ? $padding : 0;

$stars_width = 5 * $size + 10 * $padding;
$stars_height = $size + 2 * $padding;
$input_size = $size + 2 * $padding;

if ( is_rtl() ) {
	$first_input_style = ' style="margin-right: -' . ( $size + $padding ) . 'px !important; width: ' . $input_size . 'px !important; height: ' . $input_size . 'px !important;"';
} else {
	$first_input_style = ' style="margin-left: -' . ( $size + $padding ) . 'px !important; width: ' . $input_size . 'px !important; height: ' . $input_size . 'px !important;"';
}

$input_style = ' style="width: ' . $input_size . 'px !important; height: ' . $input_size . 'px !important;"';
$span_style = ' style="width: ' . $stars_width . 'px !important; height: ' . $stars_height . 'px !important;"';

// Add onclick on non-AMP pages only.
$onclick = ' onclick="WPRecipeMaker.rating.onClick(this)"';
if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
	$onclick = '';
}

// Uses random ID instead of fixed to prevent duplicate ID issues when form is on the page multiple times (happens with wpDiscuz).
$label_id = $displaying_in_admin ? false : 'wprm-comment-rating-' . rand();

// Name for the input element.
$name = $displaying_in_admin && isset( $comment_id ) && $comment_id ? 'wprm-comment-rating-' . $comment_id : 'wprm-comment-rating';

// Currently selected rating.
$selected = isset( $rating ) && $rating ? $rating : 0;
?>
<div class="comment-form-wprm-rating"<?php echo $hide_form; ?>>
	<?php
	if ( $label_id ) {
		echo '<label for="' . esc_attr( $label_id ) .'">' . WPRM_Template_Helper::label( 'comment_rating' ) . '</label>';
	}
	?>
	<span class="wprm-rating-stars">
		<fieldset class="wprm-comment-ratings-container" data-original-rating="<?php echo $selected; ?>" data-current-rating="<?php echo $selected; ?>">
			<legend><?php echo WPRM_Template_Helper::label( 'comment_rating' ); ?></legend>
			<?php
			$labels = array(
				0 => __( "Don't rate this recipe", 'wp-recipe-maker' ),
				1 => __( 'Rate this recipe 1 out of 5 stars', 'wp-recipe-maker' ),
				2 => __( 'Rate this recipe 2 out of 5 stars', 'wp-recipe-maker' ),
				3 => __( 'Rate this recipe 3 out of 5 stars', 'wp-recipe-maker' ),
				4 => __( 'Rate this recipe 4 out of 5 stars', 'wp-recipe-maker' ),
				5 => __( 'Rate this recipe 5 out of 5 stars', 'wp-recipe-maker' ),
			);

			$rating_icons = array();

			for ( $i = 0; $i <= 5; $i++ ) {
				// Reverse order for RTL.
				$star = is_rtl() ? 5 - $i : $i;

				ob_start();
				include( WPRM_DIR . 'assets/icons/rating/stars-' . $star . '.svg' );
				$svg = ob_get_contents();
				ob_end_clean();

				// Add padding.
				if ( $padding ) {
					$ratio = 120 / ( $size * 5 );
					$viewbox_padding = $padding * $ratio;

					if ( is_numeric( $viewbox_padding ) ) {
						$viewbox_width = 120 + (5 * 2 * $viewbox_padding);
						$viewbox_height = 24 + (2 * $viewbox_padding);

						$svg = str_replace( 'viewBox="0 0 120 24"', 'viewBox="0 0 ' . $viewbox_width . ' ' . $viewbox_height . '"', $svg );
						$svg = str_replace( 'width="80px"', 'width="' . ( $viewbox_width / 6 * 4 ) . 'px"', $svg );

						$svg = str_replace( 'x="96"', 'x="' . ( 9 * $viewbox_padding + 4 * 24 ) . '"', $svg );
						$svg = str_replace( 'x="72"', 'x="' . ( 7 * $viewbox_padding + 3 * 24 ) . '"', $svg );
						$svg = str_replace( 'x="48"', 'x="' . ( 5 * $viewbox_padding + 2 * 24 ) . '"', $svg );
						$svg = str_replace( 'x="24"', 'x="' . ( 3 * $viewbox_padding + 1 * 24 ) . '"', $svg );
						$svg = str_replace( 'x="0"', 'x="' . ( 1 * $viewbox_padding + 0 * 24 ) . '"', $svg );

						$svg = str_replace( 'y="0"', 'y="' . $viewbox_padding . '"', $svg );
					}
				}

				// Replace color when using custom style.
				if ( WPRM_Settings::get( 'features_custom_style' ) ) {
					$svg = str_replace( '#343434', WPRM_Settings::get( 'template_color_comment_rating' ), $svg );
				}

				// Output HTML.
				echo '<input aria-label="' . esc_attr( $labels[ $star ] ) . '" name="' . esc_attr( $name ) .'" value="' . $star . '" type="radio"' . $onclick;
				echo 5 === $star && $label_id ? ' id="' . esc_attr( $label_id ) . '"' : '';
				echo 0 === $star ? $first_input_style : $input_style;
				echo $selected === $star ? ' checked="checked"' : '';
				echo '>';
				echo '<span aria-hidden="true"' . $span_style . '>' . apply_filters( 'wprm_rating_stars_svg', $svg, $star ) . '</span>';

				if ( ( is_rtl() && 0 !== $star ) || ( ! is_rtl() && 5 !== $star ) ) {
					echo '<br>';
				}
			}
			?>
		</fieldset>
	</span>
</div>
