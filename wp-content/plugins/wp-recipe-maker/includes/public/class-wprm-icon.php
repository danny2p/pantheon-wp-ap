<?php
/**
 * Handle icons.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle icons.
 *
 * @since      3.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Icon {

	/**
	 * Get the icon.
	 *
	 * @since	3.3.0
	 * @param	mixed $keyword_or_url Keyword or URL for the icon.
	 * @param	mixed $color Color to return the icon in.
	 */
	public static function get( $keyword_or_url, $color = false ) {
		$icon = false;

		if ( ! $keyword_or_url ) {
			return $icon;
		}

		$sources = array_reverse( self::get_sources() ); // Reverse to give priority to child theme versions of icon.

		foreach ( $sources as $source ) {
			if ( file_exists( $source['dir'] . '/' . $keyword_or_url . '.svg' ) ) {
				// Use file_get_contents instead of include as include breaks when the svg file starts with <?xml.
				$icon = file_get_contents( $source['dir'] . '/' . $keyword_or_url . '.svg' );
				break;
			}
		}

		// No keyword match? Use as URL.
		if ( ! $icon ) {
			$icon = '<img src="' . esc_attr( $keyword_or_url ) . '" alt="" data-pin-nopin="true"/>';
		}

		if ( $color ) {
			$icon = preg_replace( '/#[0-9a-f]{3,6}/mi', $color, $icon );
		}

		return $icon;
	}
	
	/**
	 * Get all icons.
	 *
	 * @since	4.0.0
	 */
	public static function get_all() {
		$icons = array();
		$sources = self::get_sources();

		foreach ( $sources as $source ) {
			if ( file_exists( $source['dir'] ) && $handle = opendir( $source['dir'] ) ) {
				while ( false !== ( $file = readdir( $handle ) ) ) {
					preg_match( '/(.*?).svg$/', $file, $match );
					if ( isset( $match[1] ) ) {
						$id = $match[1];

						$icons[ $id ] = array(
							'id' => $id,
							'name' => ucwords( str_replace( '-', ' ', $id ) ),
							'url' => $source['url'] . $match[0],
						);
					}
				}
			}
		}

		return $icons;
	}

	/**
	 * Get all icon sources.
	 *
	 * @since	7.3.0
	 */
	public static function get_sources() {
		$sources = array(
			array(
				'dir' => WPRM_DIR . 'assets/icons',
				'url' => WPRM_URL . 'assets/icons/',
			),
		);

		// Load icons from parent theme.
		$theme_dir = get_template_directory();
		$sources[] = array(
			'dir' => $theme_dir . '/wprm-icons',
			'url' => get_template_directory_uri() . '/wprm-icons/',
		);

		// Load icons from child theme (if present).
		if ( get_stylesheet_directory() !== $theme_dir ) {
			$child_theme_dir = get_stylesheet_directory();			
			$sources[] = array(
				'dir' => $child_theme_dir . '/wprm-icons',
				'url' => get_stylesheet_directory_uri() . '/wprm-icons/',
			);
		}

		// Allow others to filter the icon sources.
		return apply_filters( 'wprm_icon_sources', $sources );
	}
}
