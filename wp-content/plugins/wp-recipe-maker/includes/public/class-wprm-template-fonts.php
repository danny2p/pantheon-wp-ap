<?php
/**
 * Responsible for the recipe template fonts.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the recipe template fonts.
 *
 * @since      10.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Template_Fonts {
	/**
	 * Fonts that have already been loaded.
	 *
	 * @since    10.0.0
	 * @access   private
	 * @var      array    $loaded_fonts    Array containing all fonts that have been loaded.
	 */
	private static $loaded_fonts = array();

	/**
	 * Register actions and filters.
	 *
	 * @since    10.0.0
	 */
	public static function init() {
		add_action( 'admin_head', array( __CLASS__, 'template_editor_fonts' ) );
	}

	/**
	 * Load all fonts in template editor.
	 *
	 * @since    10.0.0
	 */
	public static function template_editor_fonts() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;

		if ( $screen && 'wp-recipe-maker_page_wprm_template_editor' === $screen->id ) {
			echo '<style type="text/css">' . self::get_css_for_all_fonts() . '</style>';
		}
	}

	/**
	 * Get the CSS for the fonts used in the template.
	 *
	 * @since    10.0.0
	 * @param    array    $template    Current template.
	 */
	public static function get_css_for_fonts_in_template( $template ) {
		$css = '';

		// Get custom fonts used in template.
		$fonts = isset( $template['fonts'] ) ? $template['fonts'] : array();
		$fonts = array_unique( $fonts );

		foreach ( $fonts as $font ) {
			if ( ! in_array( $font, self::$loaded_fonts ) ) {
				$css .= self::get_css_for_font( $font );
				self::$loaded_fonts[] = $font;
			}
		}

		if ( $css ) {
			$css .= ' ';
		}

		return $css;
	}

	/**
	 * Get the CSS for the given font.
	 *
	 * @since    10.0.0
	 * @param    string    $font    Font to get the CSS for.
	 */
	public static function get_css_for_font( $font ) {
		$available_fonts = self::get_available_fonts();

		return isset( $available_fonts[ $font ] ) ? $available_fonts[ $font ] : '';
	}
	
	/**
	 * Get the CSS for all available fonts.
	 *
	 * @since    10.0.0
	 */
	public static function get_css_for_all_fonts() {
		$css = '';

		$available_fonts = self::get_available_fonts();

		foreach ( $available_fonts as $font => $css_font ) {
			$css .= $css_font;
		}

		return $css;
	}

	/**
	 * Get the available fonts.
	 *
	 * @since    10.0.0
	 */
	public static function get_available_fonts() {
		$font_url = WPRM_URL . 'assets/fonts/google/';

		return array(
			'asar' => "/* latin-ext */
				@font-face {
					font-family: 'Asar';
					font-style: normal;
					font-weight: 400;
					font-display: swap;
					src: url('{$font_url}asar/sZlLdRyI6TBIbk8aDZtQS6AvcA.woff2') format('woff2');
					unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
					font-family: 'Asar';
					font-style: normal;
					font-weight: 400;
					font-display: swap;
					src: url('{$font_url}asar/sZlLdRyI6TBIbkEaDZtQS6A.woff2') format('woff2');
					unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'open-sans' => "/* latin-ext */
				@font-face {
				font-family: 'Open Sans';
				font-style: italic;
				font-weight: 300 800;
				font-stretch: 100%;
				font-display: swap;
				src: url({$font_url}open-sans/memtYaGs126MiZpBA-UFUIcVXSCEkx2cmqvXlWqWt06FxZCJgvAQ.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Open Sans';
				font-style: italic;
				font-weight: 300 800;
				font-stretch: 100%;
				font-display: swap;
				src: url({$font_url}open-sans/memtYaGs126MiZpBA-UFUIcVXSCEkx2cmqvXlWqWuU6FxZCJgg.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}
				/* latin-ext */
				@font-face {
				font-family: 'Open Sans';
				font-style: normal;
				font-weight: 300 800;
				font-stretch: 100%;
				font-display: swap;
				src: url({$font_url}open-sans/memvYaGs126MiZpBA-UvWbX2vVnXBbObj2OVTSGmu0SC55K5gw.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Open Sans';
				font-style: normal;
				font-weight: 300 800;
				font-stretch: 100%;
				font-display: swap;
				src: url({$font_url}open-sans/memvYaGs126MiZpBA-UvWbX2vVnXBbObj2OVTS-mu0SC55I.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'mulish' => "/* latin-ext */
				@font-face {
				font-family: 'Mulish';
				font-style: italic;
				font-weight: 200 1000;
				font-display: swap;
				src: url({$font_url}mulish/1Pttg83HX_SGhgqk2johaqRFB_ie_Vo.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Mulish';
				font-style: italic;
				font-weight: 200 1000;
				font-display: swap;
				src: url({$font_url}mulish/1Pttg83HX_SGhgqk2jovaqRFB_ie.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}
				/* latin-ext */
				@font-face {
				font-family: 'Mulish';
				font-style: normal;
				font-weight: 200 1000;
				font-display: swap;
				src: url({$font_url}mulish/1Ptvg83HX_SGhgqk0QotYKNnBcif.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Mulish';
				font-style: normal;
				font-weight: 200 1000;
				font-display: swap;
				src: url({$font_url}mulish/1Ptvg83HX_SGhgqk3wotYKNnBQ.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'nunito' => "/* latin-ext */
				@font-face {
				font-family: 'Nunito';
				font-style: italic;
				font-weight: 200 1000;
				font-display: swap;
				src: url({$font_url}nunito/XRXX3I6Li01BKofIMNaNRs7nczIHNHI.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Nunito';
				font-style: italic;
				font-weight: 200 1000;
				font-display: swap;
				src: url({$font_url}nunito/XRXX3I6Li01BKofIMNaDRs7nczIH.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}
				/* latin-ext */
				@font-face {
				font-family: 'Nunito';
				font-style: normal;
				font-weight: 200 1000;
				font-display: swap;
				src: url({$font_url}nunito/XRXV3I6Li01BKofIO-aBTMnFcQIG.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Nunito';
				font-style: normal;
				font-weight: 200 1000;
				font-display: swap;
				src: url({$font_url}nunito/XRXV3I6Li01BKofINeaBTMnFcQ.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'poppins' => "/* latin-ext */
				@font-face {
				font-family: 'Poppins';
				font-style: normal;
				font-weight: 400;
				font-display: swap;
				src: url({$font_url}poppins/pxiEyp8kv8JHgFVrJJnecnFHGPezSQ.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Poppins';
				font-style: normal;
				font-weight: 400;
				font-display: swap;
				src: url({$font_url}poppins/pxiEyp8kv8JHgFVrJJfecnFHGPc.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}/* latin-ext */
				@font-face {
				font-family: 'Poppins';
				font-style: normal;
				font-weight: 700;
				font-display: swap;
				src: url({$font_url}poppins/pxiByp8kv8JHgFVrLCz7Z1JlFd2JQEl8qw.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Poppins';
				font-style: normal;
				font-weight: 700;
				font-display: swap;
				src: url({$font_url}poppins/pxiByp8kv8JHgFVrLCz7Z1xlFd2JQEk.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'quicksand' => "@font-face {
				font-family: 'Quicksand';
				font-style: normal;
				font-weight: 300 700;
				font-display: swap;
				src: url({$font_url}quicksand/6xKtdSZaM9iE8KbpRA_hJVQNYuDyP7bh.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Quicksand';
				font-style: normal;
				font-weight: 300 700;
				font-display: swap;
				src: url({$font_url}quicksand/6xKtdSZaM9iE8KbpRA_hK1QNYuDyPw.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'cormorant-garamond' => "/* latin-ext */
				@font-face {
				font-family: 'Cormorant Garamond';
				font-style: italic;
				font-weight: 300 700;
				font-display: swap;
				src: url({$font_url}cormorant-garamond/co3ZmX5slCNuHLi8bLeY9MK7whWMhyjYrEtGmSqn7B6DxjY.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Cormorant Garamond';
				font-style: italic;
				font-weight: 300 700;
				font-display: swap;
				src: url({$font_url}cormorant-garamond/co3ZmX5slCNuHLi8bLeY9MK7whWMhyjYrEtImSqn7B6D.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}
				/* latin-ext */
				@font-face {
				font-family: 'Cormorant Garamond';
				font-style: normal;
				font-weight: 300 700;
				font-display: swap;
				src: url({$font_url}cormorant-garamond/co3bmX5slCNuHLi8bLeY9MK7whWMhyjYp3tKky2F7i6C.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Cormorant Garamond';
				font-style: normal;
				font-weight: 300 700;
				font-display: swap;
				src: url({$font_url}cormorant-garamond/co3bmX5slCNuHLi8bLeY9MK7whWMhyjYqXtKky2F7g.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'lora' => "/* latin-ext */
				@font-face {
				font-family: 'Lora';
				font-style: italic;
				font-weight: 400 700;
				font-display: swap;
				src: url({$font_url}lora/0QIhMX1D_JOuMw_LL_tLtfOm84TX.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Lora';
				font-style: italic;
				font-weight: 400 700;
				font-display: swap;
				src: url({$font_url}lora/0QIhMX1D_JOuMw_LIftLtfOm8w.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}
				/* latin-ext */
				@font-face {
				font-family: 'Lora';
				font-style: normal;
				font-weight: 400 700;
				font-display: swap;
				src: url({$font_url}lora/0QIvMX1D_JOuMwT7I_FMl_GW8g.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Lora';
				font-style: normal;
				font-weight: 400 700;
				font-display: swap;
				src: url({$font_url}lora/0QIvMX1D_JOuMwr7I_FMl_E.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'playfair-display' => "/* latin-ext */
				@font-face {
				font-family: 'Playfair Display';
				font-style: italic;
				font-weight: 400 900;
				font-display: swap;
				src: url({$font_url}playfair-display/nuFkD-vYSZviVYUb_rj3ij__anPXDTnojEk7yRZrPJ-M.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Playfair Display';
				font-style: italic;
				font-weight: 400 900;
				font-display: swap;
				src: url({$font_url}playfair-display/nuFkD-vYSZviVYUb_rj3ij__anPXDTnogkk7yRZrPA.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}
				/* latin-ext */
				@font-face {
				font-family: 'Playfair Display';
				font-style: normal;
				font-weight: 400 900;
				font-display: swap;
				src: url({$font_url}playfair-display/nuFiD-vYSZviVYUb_rj3ij__anPXDTLYgEM86xRbPQ.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Playfair Display';
				font-style: normal;
				font-weight: 400 900;
				font-display: swap;
				src: url({$font_url}playfair-display/nuFiD-vYSZviVYUb_rj3ij__anPXDTzYgEM86xQ.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'amatic-sc' => "/* latin-ext */
				@font-face {
				font-family: 'Amatic SC';
				font-style: normal;
				font-weight: 400;
				font-display: swap;
				src: url({$font_url}amatic-sc/TUZyzwprpvBS1izr_vOEBOSfU5cP1V3r.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Amatic SC';
				font-style: normal;
				font-weight: 400;
				font-display: swap;
				src: url({$font_url}amatic-sc/TUZyzwprpvBS1izr_vOECuSfU5cP1Q.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}
				/* latin-ext */
				@font-face {
				font-family: 'Amatic SC';
				font-style: normal;
				font-weight: 700;
				font-display: swap;
				src: url({$font_url}amatic-sc//TUZ3zwprpvBS1izr_vOMscGKcLUC_2fi-Vl4.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Amatic SC';
				font-style: normal;
				font-weight: 700;
				font-display: swap;
				src: url({$font_url}amatic-sc/TUZ3zwprpvBS1izr_vOMscGKfrUC_2fi-Q.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'caveat' => "/* latin-ext */
				@font-face {
				font-family: 'Caveat';
				font-style: normal;
				font-weight: 400 700;
				font-display: swap;
				src: url({$font_url}caveat/Wnz6HAc5bAfYB2Q7aDYYiAzcPDKo.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Caveat';
				font-style: normal;
				font-weight: 400 700;
				font-display: swap;
				src: url({$font_url}caveat/Wnz6HAc5bAfYB2Q7ZjYYiAzcPA.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'dancing-script' => "/* latin-ext */
				@font-face {
				font-family: 'Dancing Script';
				font-style: normal;
				font-weight: 400 700;
				font-display: swap;
				src: url({$font_url}dancing-script/If2RXTr6YS-zF4S-kcSWSVi_szLuiuEHiC4Wl-8.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Dancing Script';
				font-style: normal;
				font-weight: 400 700;
				font-display: swap;
				src: url({$font_url}dancing-script/If2RXTr6YS-zF4S-kcSWSVi_szLgiuEHiC4W.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'pacifico' => "/* latin-ext */
				@font-face {
				font-family: 'Pacifico';
				font-style: normal;
				font-weight: 400;
				font-display: swap;
				src: url({$font_url}pacifico/FwZY7-Qmy14u9lezJ-6J6MmBp0u-zK4.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Pacifico';
				font-style: normal;
				font-weight: 400;
				font-display: swap;
				src: url({$font_url}pacifico/FwZY7-Qmy14u9lezJ-6H6MmBp0u-.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'inter' => "/* latin-ext */
				@font-face {
				font-family: 'Inter';
				font-style: italic;
				font-weight: 100 900;
				font-display: swap;
				src: url({$font_url}inter/UcCm3FwrK3iLTcvnUwoT9mI1F55MKw.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Inter';
				font-style: italic;
				font-weight: 100 900;
				font-display: swap;
				src: url({$font_url}inter/UcCm3FwrK3iLTcvnUwQT9mI1F54.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}
				/* latin-ext */
				@font-face {
				font-family: 'Inter';
				font-style: normal;
				font-weight: 100 900;
				font-display: swap;
				src: url({$font_url}inter/UcCo3FwrK3iLTcvsYwYZ8UA3J58.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Inter';
				font-style: normal;
				font-weight: 100 900;
				font-display: swap;
				src: url({$font_url}inter/UcCo3FwrK3iLTcviYwYZ8UA3.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'jost' => "/* latin-ext */
				@font-face {
				font-family: 'Jost';
				font-style: italic;
				font-weight: 100 900;
				font-display: swap;
				src: url({$font_url}jost/92zUtBhPNqw73oHt7j4hXRAy7lRq.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Jost';
				font-style: italic;
				font-weight: 100 900;
				font-display: swap;
				src: url({$font_url}jost/92zUtBhPNqw73oHt4D4hXRAy7g.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}
				/* latin-ext */
				@font-face {
				font-family: 'Jost';
				font-style: normal;
				font-weight: 100 900;
				font-display: swap;
				src: url({$font_url}jost/92zatBhPNqw73ord4jQmfxIC7w.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Jost';
				font-style: normal;
				font-weight: 100 900;
				font-display: swap;
				src: url({$font_url}jost/92zatBhPNqw73oTd4jQmfxI.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
			'gloock' => "/* latin-ext */
				@font-face {
				font-family: 'Gloock';
				font-style: normal;
				font-weight: 400;
				font-display: swap;
				src: url({$font_url}gloock/Iurb6YFw84WUY4NJhhakJLRBjIlJ.woff2) format('woff2');
				unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
				}
				/* latin */
				@font-face {
				font-family: 'Gloock';
				font-style: normal;
				font-weight: 400;
				font-display: swap;
				src: url({$font_url}gloock/Iurb6YFw84WUY4NJiBakJLRBjA.woff2) format('woff2');
				unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
				}",
		);
	}
}

WPRM_Template_Fonts::init();
