<?php

class WPRM_WPRecipeMakerDiviExtension extends DiviExtension {

	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since 9.7.0
	 *
	 * @var string
	 */
	public $gettext_domain = 'wp-recipe-maker';

	/**
	 * The extension's WP Plugin name.
	 *
	 * @since 9.7.0
	 *
	 * @var string
	 */
	public $name = 'divi-wp-recipe-maker';

	/**
	 * The extension's version
	 *
	 * @since 9.7.0
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * WPRM_DiviExtension constructor.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __construct( $name = 'divi-wp-recipe-maker', $args = array() ) {
		$this->plugin_dir     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );

		$this->_builder_js_data = array(
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'endpoints' => array(
				'utilities' => rtrim( get_rest_url( null, 'wp-recipe-maker/v1/utilities' ), '/' ),
			),
		);

		parent::__construct( $name, $args );
	}
}

new WPRM_WPRecipeMakerDiviExtension;