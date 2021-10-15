<?php
/**
 * New place for storing the nutrition daily values.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$custom_fields = array();
$nutrition_fields = WPRM_Nutrition::get_fields();

foreach ( $nutrition_fields as $nutrient => $options ) {
	$setting_value = WPRM_Settings::get( 'nutrition_label_custom_daily_values_' . $nutrient );

	if ( false !== $setting_value ) {
		$setting_value = floatval( $setting_value );

		if ( ! isset( $options['daily'] ) || floatval( $options['daily'] ) !== $setting_value ) {
			$custom_fields[ $nutrient ] = array(
				'daily' => $setting_value,
			);
		}
	}
}

update_option( 'wprm_user_nutrition_fields', $custom_fields );