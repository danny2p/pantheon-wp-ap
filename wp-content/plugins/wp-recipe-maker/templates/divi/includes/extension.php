<?php

class WPRM_DiviExtension extends DiviExtension {

	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since 5.1.0
	 *
	 * @var string
	 */
	public $gettext_domain = 'wp-recipe-maker';

	/**
	 * The extension's WP Plugin name.
	 *
	 * @since 5.1.0
	 *
	 * @var string
	 */
	public $name = 'divi-wp-recipe-maker';

	/**
	 * The extension's version
	 *
	 * @since 5.1.0
	 *
	 * @var string
	 */
	public $version = '5.1.0';

	/**
	 * WPRM_DiviExtension constructor.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __construct( $name = 'divi-wp-recipe-maker', $args = array() ) {
		$this->plugin_dir     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );

		parent::__construct( $name, $args );
	}
}

new WPRM_DiviExtension;