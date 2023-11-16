<?php
/**
 * Represents a list that doesn't have an associated post.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Represents a list that doesn't have an associated post.
 *
 * @since      9.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_List_Shell {

	/**
	 * Data associated with this list.
	 *
	 * @since	9.0.0
	 * @access	private
	 * @var	array    $data    List data.
	 */
	private $data = array();

	/**
	 * Get new list object.
	 *
	 * @since	9.0.0
	 * @param	object $data mixed Data for this recipe.
	 */
	public function __construct( $data = array() ) {
		$defaults = array(
			'name' => '',
			'note' => '',
			'template' => 'default',
			'items' => array(),
			'metadata_output' => true,
			'metadata_name' => '',
			'metadata_description' => '',
		);

		$this->data = array_merge( $defaults, $data );
	}

	/**
	 * Get recipe data.
	 *
	 * @since	9.0.0
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Get recipe data for the manage page.
	 *
	 * @since	9.0.0
	 */
	public function get_data_manage() {
		return $this->data;
	}

	/**
	 * Get metadata value.
	 *
	 * @since	9.0.0
	 * @param	mixed $field	Metadata field to retrieve.
	 * @param	mixed $default	Default to return if metadata is not set.
	 */
	public function meta( $field, $default ) {
		if ( isset( $this->data[ $field ] ) ) {
			return $this->data[ $field ];
		}

		return $default;
	}

	/**
	 * Catch all other recipe function calls.
	 *
	 * @since	9.0.0
	 */
	public function __call( $name, $arguments ) {
		if ( isset( $this->data[ $name ] ) ) {
			return $this->data[ $name ];
		}

		return false;
	}
}
