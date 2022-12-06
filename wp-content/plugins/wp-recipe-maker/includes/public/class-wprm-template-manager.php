<?php
/**
 * Responsible for the recipe template.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the recipe template.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Template_Manager {
	/**
	 * Cached version of all the available templates.
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      array    $templates    Array containing all templates that have been loaded.
	 */
	private static $templates = array();

	/**
	 * Templates loaded in the head.
	 *
	 * @since    6.9.0
	 * @access   private
	 * @var      array    $head_templates    Array containing all templates that have been loaded in the head.
	 */
	private static $head_templates = array();

	/**
	 * Templates used in the output.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      array    $used_templates    Array containing all templates that have been used in the output.
	 */
	private static $used_templates = array();

	/**
	 * IDs of recipes that are currently being output.
	 *
	 * @since    3.3.0
	 * @access   private
	 * @var      boolean    $currently_outputting    IDs of recipes that are currently being output.
	 */
	private static $currently_outputting = array();

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'head_css' ) );
		add_action( 'wp_footer', array( __CLASS__, 'templates_css' ), 99 );
		add_action( 'amp_post_template_css', array( __CLASS__, 'amp_style' ) );

		// Legacy.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_legacy_template' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_legacy_template' ) );
	}

	/**
	 * Add CSS to head if enabled in settings.
	 *
	 * @since	6.9.0
	 */
	public static function head_css() {
		
		$templates_to_load = array();

		if ( false === WPRM_Settings::get( 'snippet_templates_in_footer' ) ) {
			$templates_to_load[] = WPRM_Settings::get( 'recipe_snippets_template' );
			$templates_to_load[] = WPRM_Settings::get( 'howto_recipe_snippets_template' );
			$templates_to_load[] = WPRM_Settings::get( 'other_recipe_snippets_template' );
		}

		if ( false === WPRM_Settings::get( 'recipe_templates_in_footer' ) ) {
			$templates_to_load[] = WPRM_Settings::get( 'default_recipe_template_modern' );
			$templates_to_load[] = WPRM_Settings::get( 'default_howto_recipe_template_modern' );
			$templates_to_load[] = WPRM_Settings::get( 'default_other_recipe_template_modern' );
		}

		$style = '';

		$templates_to_load = array_unique( $templates_to_load );
		foreach ( $templates_to_load as $slug ) {
			$template = self::get_template_by_slug( $slug );

			if ( $template ) {
				self::$head_templates[] = $slug;
				$style .= self::get_template_css( $template );
			}
		}
		
		if ( $style ) {
			echo '<style type="text/css">' . $style . '</style>';
		}
	}

	/**
	 * Add CSS to footer for all recipes on this page.
	 *
	 * @since	4.0.0
	 */
	public static function templates_css() {
		if ( count( self::$used_templates ) ) {
			$style = '';
			
			foreach ( self::$used_templates as $slug => $template ) {
				
				// Only output in footer if not already output in head.
				if ( ! in_array( $slug, self::$head_templates ) ) {
					$style .= self::get_template_css( $template );
				}
			}

			if ( $style ) {
				echo '<style type="text/css">' . $style . '</style>';
			}
		}
	}

	/**
	 * Add template as being used on this page to output CSS for later.
	 *
	 * @since	4.3.0
	 */
	public static function add_used_template( $template ) {
		if ( 'modern' === $template['mode'] || ( 'roundup' === $template['type'] && ! $template['custom'] ) ) {
			if ( ! array_key_exists( $template['slug'], self::$used_templates ) ) {
				self::$used_templates[ $template['slug'] ] = $template;
			}
		}
	}

	/**
	 * Enqueue stylesheets and scripts.
	 *
	 * @since    1.0.0
	 */
	public static function enqueue_legacy_template() {
		$template = self::get_template_by_type( 'single' );

		if ( 'legacy' === $template['mode'] ) {
			wp_enqueue_style( 'wprm-template', $template['url'] . '/' . $template['stylesheet'], array(), WPRM_VERSION, 'all' );
		}
	}

	/**
	 * Enqueue template style on AMP pages.
	 *
	 * @since    2.1.0
	 */
	public static function amp_style() {
		$css = '';

		// Get AMP template style.
		$template = self::get_template_by_type( 'amp' );
		$css .= self::get_template_css( $template );

		// Get Snippet template style.
		$snippet_template = self::get_template_by_type( 'snippet' );
		$css .= self::get_template_css( $snippet_template );

		// Get rid of !important flags.
		$css = str_ireplace( ' !important', '', $css );
		$css = str_ireplace( '!important', '', $css );

		echo $css;
	}

	/**
	 * Get template for a specific recipe.
	 *
	 * @since    1.0.0
	 * @param		 object $recipe Recipe object to get the template for.
	 * @param		 mixed  $type 	Type of template we want to get, defaults to single.
	 * @param		 mixed  $slug 	Slug of the specific template we want.
	 */
	public static function get_template( $recipe, $type = 'single', $slug = false ) {
		if ( $slug ) {
			$template = self::get_template_by_slug( $slug );
		}

		if ( ! $slug || ! $template ) {
			$template = self::get_template_by_type( $type, $recipe->type() );
		}

		// Add template to array of used templats.
		self::add_used_template( $template );

		$wprm_template_output = '';
		// Get HTML.
		switch( $template['mode'] ) {
			case 'legacy':
				ob_start();
				require( $template['dir'] . '/' . $template['slug'] . '.php' );
				$wprm_template_output .= ob_get_contents();
				ob_end_clean();
				break;
			case 'modern':
				$wprm_template_output .= '<div class="wprm-recipe wprm-recipe-template-' . $template['slug'] . '">' . $template['html'] . '</div>';
				break;
		}

		// Recursion prevention.
		if ( isset( self::$currently_outputting[ $recipe->id() ] ) ) {
			return '';
		}

		// Set and reset current recipe ID for shortcodes (otherwise jump button won't be first recipe on page but last).
		WPRM_Template_Shortcodes::set_current_recipe_id( $recipe->id() );
		self::$currently_outputting[ $recipe->id() ] = true;
		$wprm_template_output = do_shortcode( $wprm_template_output );
		unset( self::$currently_outputting[ $recipe->id() ] );
		WPRM_Template_Shortcodes::set_current_recipe_id( false );

		return apply_filters( 'wprm_get_template', $wprm_template_output, $recipe, $type, $slug );
	}

	/**
	 * Get template styles for a specific recipe.
	 *
	 * @since	1.0.0
	 * @param	object $recipe Recipe object to get the template for.
	 * @param	mixed  $type 	Type of template we want to get, defaults to single.
	 */
	public static function get_template_styles( $recipe, $type = 'single' ) {
		$recipe_type = $recipe ? $recipe->type() : 'food';
		$template = self::get_template_by_type( $type, $recipe_type );
		return '<style type="text/css">' . self::get_template_css( $template ) . '</style>';
	}

	/**
	 * Get CSS for a specific template.
	 *
	 * @since	4.0.0
	 * @param	object $template Template to get the CSS for.
	 */
	public static function get_template_css( $template ) {
		$css = '';

		if ( ! $template ) {
			return $css;
		}

		if ( 'modern' === $template['mode'] ) {
			if ( 'file' === $template['location'] ) {

				if ( ! $template['custom'] && 'recipe' === $template['type'] && 'excerpt' !== $template['slug'] ) {
					// Get default CSS.
					ob_start();
					include( WPRM_DIR . 'templates/recipe/modern/default.css' );
					$css .= ob_get_contents();
					ob_end_clean();

					// Replace default classic with template specific one.
					$css = preg_replace( '/\.wprm-recipe(\[|:|\s|\{)/im', '.wprm-recipe-template-' . $template['slug'] . '$1', $css );
				}

				// Get CSS from stylesheet.
				if ( $template['stylesheet'] ) {
					ob_start();
					include( $template['dir'] . '/' . $template['stylesheet'] );
					$css .= ob_get_contents();
					ob_end_clean();
				}
			} else {
				// Get virtual CSS.
				$css = $template['css'];
			}
		} else {
			// Legacy template.
			ob_start();
			require( $template['dir'] . '/' . $template['stylesheet'] );
			$css .= ob_get_contents();
			ob_end_clean();
		}

		return $css;
	}

	/**
	 * Get template by name.
	 *
	 * @since	1.2.0
	 * @param	mixed $slug Slug of the template we want to get.
	 */
	public static function get_template_by_slug( $slug ) {
		$templates = self::get_templates();

		// Find by template mode first.
		$template = isset( $templates[ WPRM_Settings::get( 'recipe_template_mode' ) ][ $slug ] ) ? $templates[ WPRM_Settings::get( 'recipe_template_mode' ) ][ $slug ] : false;

		// Not found? Try all template modes with modern as priority.
		if ( ! $template ) {
			$template = isset( $templates[ 'legacy' ][ $slug ] ) ? $templates[ 'legacy' ][ $slug ] : $template;
			$template = isset( $templates[ 'modern' ][ $slug ] ) ? $templates[ 'modern' ][ $slug ] : $template;
		}

		return $template;
	}

	/**
	 * Get template by type.
	 *
	 * @since    1.7.0
	 * @param	 mixed $type Type of template we want to get, defaults to single.
	 * * @param	 mixed $recipe_type Type of recipe we're displaying.
	 */
	public static function get_template_by_type( $type = 'single', $recipe_type = 'food' ) {
		$mode = 'modern' === WPRM_Settings::get( 'recipe_template_mode' ) ? '_modern' : '';

		// Archive and AMP template setting only exists in modern mode.
		if ( in_array( $type, array( 'archive', 'amp' ) ) && '_modern' !== $mode ) {
			$type = 'single';
		}

		switch ( $type ) {
			case 'amp':
				$template_slug = WPRM_Settings::get( 'default_recipe_amp_template' );
				break;
			case 'archive':
				$template_slug = WPRM_Settings::get( 'default_recipe_archive_template' );
				break;
			case 'feed':
				$template_slug = WPRM_Settings::get( 'default_recipe_feed_template' );
				break;
			case 'print':
				if ( '_modern' === $mode ) {
					if ( 'food' === $recipe_type ) { 
						$template_slug = WPRM_Settings::get( 'default_print_template' . $mode );
					} else {
						$template_slug = WPRM_Settings::get( 'default_' . $recipe_type . '_print_template' . $mode );
					}
				} else {
					$template_slug = WPRM_Settings::get( 'default_print_template' . $mode );
				}
				break;
			case 'print-collection':
				$template_slug = WPRM_Settings::get( 'recipe_collections_print_recipes_template' . $mode );
				break;
			case 'snippet':
				if ( 'food' === $recipe_type ) { 
					$template_slug = WPRM_Settings::get( 'recipe_snippets_template' );
				} else {
					$template_slug = WPRM_Settings::get( $recipe_type . '_recipe_snippets_template' );
				}
				break;
			case 'roundup':
				if ( 'food' === $recipe_type ) { 
					$template_slug = WPRM_Settings::get( 'recipe_roundup_template' );
				} else {
					$template_slug = WPRM_Settings::get( $recipe_type . '_recipe_roundup_template' );
				}
				break;
			default:
				if ( '_modern' === $mode ) {
					if ( 'food' === $recipe_type ) { 
						$template_slug = WPRM_Settings::get( 'default_recipe_template' . $mode );
					} else {
						$template_slug = WPRM_Settings::get( 'default_' . $recipe_type . '_recipe_template' . $mode );
					}
				} else {
					$template_slug = WPRM_Settings::get( 'default_recipe_template' . $mode );
				}
		}

		// Account for "Use same as Default Recipe Template" setting.
		if ( 'default_recipe_template' === $template_slug ) {
			return self::get_template_by_type( 'single', $recipe_type );
		}

		$template = self::get_template_by_slug( $template_slug );

		// Only allow Premium templates if Premium is active.
		if ( $template && $template['premium'] && ! WPRM_Addons::is_active( 'premium' ) ) {
			$template = false;
		}

		// Get default template if the template in the settings doesn't exist anymore.
		if ( ! $template ) {
			switch ( $type ) {
				case 'amp':
					$template_slug = WPRM_Settings::get_default( 'default_recipe_amp_template' );
					break;
				case 'archive':
					$template_slug = WPRM_Settings::get_default( 'default_recipe_archive_template' );
					break;
				case 'feed':
					$template_slug = WPRM_Settings::get_default( 'default_recipe_feed_template' );
					break;
				case 'print':
					if ( '_modern' === $mode ) {
						if ( 'food' === $recipe_type ) { 
							$template_slug = WPRM_Settings::get_default( 'default_print_template' . $mode );
						} else {
							$template_slug = WPRM_Settings::get_default( 'default_' . $recipe_type . '_print_template' . $mode );
						}
					} else {
						$template_slug = WPRM_Settings::get_default( 'default_print_template' . $mode );
					}
					break;
				case 'print-collection':
					$template_slug = WPRM_Settings::get( 'recipe_collections_print_recipes_template' . $mode );
					break;
				case 'snippet':
					if ( 'food' === $recipe_type ) { 
						$template_slug = WPRM_Settings::get_default( 'recipe_snippets_template' );
					} else {
						$template_slug = WPRM_Settings::get_default( $recipe_type . '_recipe_snippets_template' );
					}
					break;
				case 'roundup':
					if ( 'food' === $recipe_type ) { 
						$template_slug = WPRM_Settings::get_default( 'recipe_roundup_template' );
					} else {
						$template_slug = WPRM_Settings::get_default( $recipe_type . '_recipe_roundup_template' );
					}
					break;
				default:
					if ( '_modern' === $mode ) {
						if ( 'food' === $recipe_type ) { 
							$template_slug = WPRM_Settings::get_default( 'default_recipe_template' . $mode );
						} else {
							$template_slug = WPRM_Settings::get_default( 'default_' . $recipe_type . '_recipe_template' . $mode );
						}
					} else {
						$template_slug = WPRM_Settings::get_default( 'default_recipe_template' . $mode );
					}
			}

			// Account for "Use same as Default Recipe Template" setting.
			if ( 'default_recipe_template' === $template_slug ) {
				return self::get_template_by_type( 'single', $recipe_type );
			}

			$template = self::get_template_by_slug( $template_slug );
		}

		return $template;
	}

	/**
	 * Save a template.
	 *
	 * @since	4.0.0
	 * @param	mixed $template Template to save.
	 */
	public static function save_template( $template ) {
		$templates = self::get_templates();
		$slug = isset( $template['slug'] ) && false !== $template['slug'] ? self::slugify( $template['slug'] ) : false;
		$old_slug = isset( $template['oldSlug'] ) ? self::slugify( $template['oldSlug'] ) : $slug;

		// New slug needed.
		if ( ! $slug || ( array_key_exists( $slug, $templates['modern'] ) && 'file' === $templates['modern'][ $slug ]['location'] ) ) {
			$slug_base = self::slugify( $template['name'] );

			$slug = $slug_base;
			$i = 2;
			while ( array_key_exists( $slug, $templates['modern'] ) ) {
				$slug = $slug_base . '-' . $i;
				$i++;
			}

			if ( $old_slug ) {
				// Need to update CSS and HTML classes.
				$template['css'] = str_ireplace( '.wprm-recipe-template-' . $old_slug, '.wprm-recipe-template-' . $slug, $template['css'] );
				$template['html'] = str_ireplace( 'wprm-recipe-template-' . $old_slug, 'wprm-recipe-template-' . $slug, $template['html'] );
			}
		}		

		// Sanitize template.
		$sanitized_template['mode'] = 'modern';
		$sanitized_template['location'] = 'database';
		$sanitized_template['custom'] = true;
		$sanitized_template['dir'] = false;
		$sanitized_template['url'] = false;
		$sanitized_template['stylesheet'] = false;
		$sanitized_template['screenshot'] = false;

		$sanitized_template['premium'] = (bool) $template['premium'];
		$sanitized_template['type'] = sanitize_key( $template['type'] );
		$sanitized_template['slug'] = $slug;
		$sanitized_template['name'] = sanitize_text_field( $template['name'] );
		$sanitized_template['css'] = trim( $template['css'] );
		$sanitized_template['html'] = trim( $template['html'] );

		// Make sure list of templates is up to date.
		$templates = get_option( 'wprm_templates', array() );
		if ( ! in_array( $slug, $templates ) ) {
			$templates[] = $slug;
			update_option( 'wprm_templates', $templates );
		}

		// Save template in cache and database.
		self::$templates['modern'][$slug] = $sanitized_template;
		update_option( 'wprm_template_' . $slug, $sanitized_template );

		return $sanitized_template;
	}

	/**
	 * Sluggify the name of a template.
	 *
	 * @since	8.3.0
	 * @param	mixed $slug Starting text.
	 */
	public static function slugify( $slug ) {
		$slug = strtolower( $slug );
		$slug = preg_replace( '/[^a-z0-9-_\s]+/', '', $slug );
		$slug = str_replace( ' ', '-', $slug);
		$slug = trim( $slug, '-' );
		
		if ( ! $slug ) {
			$slug = 'recipe-template';
		}

		return $slug;
	}

	/**
	 * Delete a template.
	 *
	 * @since	4.0.0
	 * @param	mixed $slug Slug of the template to delete.
	 */
	public static function delete_template( $slug ) {
		$slug = self::slugify( $slug );

		// Make sure list of templates is up to date.
		$templates = get_option( 'wprm_templates', array() );
		if ( false !== ( $index = array_search( $slug, $templates ) ) ) {
			unset( $templates[ $index ] );
		}
		update_option( 'wprm_templates', $templates );
		
		delete_option( 'wprm_template_' . $slug );

		return $slug;
	}

	/**
	 * Get all available templates.
	 *
	 * @since    1.2.0
	 * @param	 mixed $mode Mode to get the templates for.
	 */
	public static function get_templates() {
		if ( empty( self::$templates ) ) {
			self::load_templates();
		}

		return self::$templates;
	}

	/**
	 * Load all available templates.
	 *
	 * @since    1.2.0
	 */
	private static function load_templates() {
		$templates = array(
			'legacy' => array(),
			'modern' => array(),
		);

		// Load legacy templates.
		$dirs = array_filter( glob( WPRM_DIR . 'templates/recipe/legacy/*' ), 'is_dir' );
		$url = WPRM_URL . 'templates/recipe/legacy/';

		foreach ( $dirs as $dir ) {
			$template = self::load_template( $dir, $url, false );
			$templates[ $template['mode'] ][ $template['slug'] ] = $template;
		}

		// Load modern templates.
		$dirs = array_filter( glob( WPRM_DIR . 'templates/recipe/modern/*' ), 'is_dir' );
		$url = WPRM_URL . 'templates/recipe/modern/';

		foreach ( $dirs as $dir ) {
			$template = self::load_template( $dir, $url, false );
			$templates[ $template['mode'] ][ $template['slug'] ] = $template;
		}

		// Load premium modern templates.
		$dirs = array_filter( glob( WPRM_DIR . 'templates/recipe/premium/*' ), 'is_dir' );
		$url = WPRM_URL . 'templates/recipe/premium/';

		foreach ( $dirs as $dir ) {
			$template = self::load_template( $dir, $url, false, true );
			$templates[ $template['mode'] ][ $template['slug'] ] = $template;
		}

		// Load premium legacy templates.
		if ( WPRM_Addons::is_active( 'premium' ) ) {
			$dirs = array_filter( glob( WPRMP_DIR . 'templates/recipe/legacy/*' ), 'is_dir' );
			$url = WPRMP_URL . 'templates/recipe/legacy/';

			foreach ( $dirs as $dir ) {
				$template = self::load_template( $dir, $url, false, true );
				$templates[ $template['mode'] ][ $template['slug'] ] = $template;
			}
		}

		// Load custom templates from parent theme.
		$theme_dir = get_template_directory();

		if ( file_exists( $theme_dir . '/wprm-templates' ) && file_exists( $theme_dir . '/wprm-templates/recipe' ) ) {
			$url = get_template_directory_uri() . '/wprm-templates/recipe/';

			$dirs = array_filter( glob( $theme_dir . '/wprm-templates/recipe/*' ), 'is_dir' );

			foreach ( $dirs as $dir ) {
				$template = self::load_template( $dir, $url, true );
				$templates[ $template['mode'] ][ $template['slug'] ] = $template;
			}
		}

		// Load custom templates from child theme (if present).
		if ( get_stylesheet_directory() !== $theme_dir ) {
			$theme_dir = get_stylesheet_directory();

			if ( file_exists( $theme_dir . '/wprm-templates' ) && file_exists( $theme_dir . '/wprm-templates/recipe' ) ) {
				$url = get_stylesheet_directory_uri() . '/wprm-templates/recipe/';

				$dirs = array_filter( glob( $theme_dir . '/wprm-templates/recipe/*' ), 'is_dir' );

				foreach ( $dirs as $dir ) {
					$template = self::load_template( $dir, $url, true );
					$templates[ $template['mode'] ][ $template['slug'] ] = $template;
				}
			}
		}

		// Load templates from database.
		$db_templates = get_option( 'wprm_templates', array() );

		foreach ( $db_templates as $slug ) {
			$template = get_option( 'wprm_template_' . $slug, false );

			if ( $template ) {
				// Check for potential broken slug.
				if ( $slug !== self::slugify( $slug ) ) {
					$template['brokenSlug'] = true;
				}

				$templates['modern'][ $slug ] = $template;
			}
		}

		self::$templates = $templates;
	}

	/**
	 * Load template from directory.
	 *
	 * @since    1.2.0
	 * @param		 mixed 	 $dir 	  Directory to load the template from.
	 * @param		 mixed 	 $url 	  URL to load the template from.
	 * @param		 boolean $custom  Wether or not this is a custom template included by the user.
	 * @param		 boolean $premium Wether or not this is a premium template.
	 */
	private static function load_template( $dir, $url, $custom = false, $premium = false ) {
		$slug = basename( $dir );
		$name = ucwords( str_replace( '-', ' ', $slug ) );
		$screenshot = false;

		if ( defined( 'GLOB_BRACE' ) ) {
			$screenshots = glob( $dir . '/' . $slug . '.{jpg,jpeg,png,gif}', GLOB_BRACE );
		} else {
			$screenshots = array();
			$screenshots = array_merge( $screenshots, glob( $dir . '/' . $slug . '.jpg' ) );
			$screenshots = array_merge( $screenshots, glob( $dir . '/' . $slug . '.jpeg' ) );
			$screenshots = array_merge( $screenshots, glob( $dir . '/' . $slug . '.png' ) );
			$screenshots = array_merge( $screenshots, glob( $dir . '/' . $slug . '.gif' ) );
		}

		if ( ! empty( $screenshots ) ) {
			$info = pathinfo( $screenshots[0] );
			$screenshot = $info['extension'];
		}

		// Allow both .min.css and .css as extension.
		$stylesheet = file_exists( $dir . '/' . $slug . '.min.css' ) ? $slug . '.min.css' : $slug . '.css';

		// Check for HTML file.
		$html = file_exists( $dir . '/' . $slug . '.html' ) ? trim( file_get_contents( $dir . '/' . $slug . '.html' ) ) : false;

		$mode = $html ? 'modern' : 'legacy';

		// Check type if modern.
		$type = 'recipe';
		if ( 'modern' === $mode && ! $custom && 'snippet-' === substr( $slug, 0, 8 ) ) {
			$type = 'snippet';
		} elseif ( 'modern' === $mode && ! $custom && 'roundup-' === substr( $slug, 0, 8 ) ) {
			$type = 'roundup';
		}

		return array(
			'mode' => $mode,
			'type' => $type,
			'location' => 'file',
			'custom' => $custom,
			'premium' => $premium,
			'name' => $name,
			'slug' => $slug,
			'dir' => $dir,
			'url' => $url . $slug,
			'stylesheet' => $stylesheet,
			'html' => $html,
			'screenshot' => $screenshot,
		);
	}
}

WPRM_Template_Manager::init();
