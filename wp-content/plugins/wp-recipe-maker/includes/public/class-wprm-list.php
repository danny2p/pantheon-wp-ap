<?php
/**
 * Represents a list.
 *
 * @link       http://bootstrapped.ventures
 * @since      9.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Represents a list.
 *
 * @since      9.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_List {

	/**
	 * WP_Post object associated with this list post type.
	 *
	 * @since    9.0.0
	 * @access   private
	 * @var      object    $post    WP_Post object of this list post type.
	 */
	private $post;

	/**
	 * Metadata associated with this list post type.
	 *
	 * @since    9.0.0
	 * @access   private
	 * @var      array    $meta    List metadata.
	 */
	private $meta = false;

	/**
	 * Get new list object from associated post.
	 *
	 * @since   9.0.0
	 * @param	object $post WP_Post object for this list post type.
	 */
	public function __construct( $post ) {
		$this->post = $post;
	}

	/**
	 * Get list data.
	 *
	 * @since	9.0.0
	 */
	public function get_data() {
		$list = array();

		// Technical Fields.
		$list['id'] = $this->id();

		// Post Type.
		$list['post_status'] = $this->post_status();

		// List Details.
		$list['name'] = $this->name();
		$list['note'] = $this->note();
		$list['template'] = $this->template();
		$list['items'] = $this->items();

		// List Metadata.
		$list['metadata_output'] = $this->metadata_output();
		$list['metadata_name'] = $this->metadata_name();
		$list['metadata_description'] = $this->metadata_description();

		return apply_filters( 'wprm_list_data', $list, $this );
	}

	/**
	 * Get list data for the manage page.
	 *
	 * @since	9.0.0
	 */
	public function get_data_manage() {
		$list = $this->get_data();

		$list['date'] = $this->date();

		// Number of recipes.
		$list['nbr_items'] = $this->nbr_items();
		$list['nbr_items_internal'] = $this->nbr_items_internal();
		$list['nbr_items_external'] = $this->nbr_items_external();

		// Parent Post.
		$list['parent_post_id'] = $this->parent_post_id();
		$list['parent_post'] = $this->parent_post();
		$list['parent_post_url'] = $this->parent_url();
		$list['parent_post_edit_url'] = $this->parent_edit_url();

		return apply_filters( 'wprm_list_manage_data', $list, $this );
	}

	/**
	 * Get metadata value.
	 *
	 * @since	9.0.0
	 * @param	mixed $field		Metadata field to retrieve.
	 * @param	mixed $default	Default to return if metadata is not set.
	 */
	public function meta( $field, $default ) {
		if ( ! $this->meta ) {
			$this->meta = get_post_custom( $this->id() );
		}

		if ( isset( $this->meta[ $field ] ) ) {
			return $this->meta[ $field ][0];
		}

		return $default;
	}

	/**
	 * Try to unserialize as best as possible.
	 *
	 * @since	9.0.0
	 * @param	mixed $maybe_serialized Potentially serialized data.
	 */
	public function unserialize( $maybe_serialized ) {
		$unserialized = @maybe_unserialize( $maybe_serialized );

		if ( false === $unserialized ) {
			$maybe_serialized = preg_replace('/\s+/', ' ', $maybe_serialized );
			$unserialized = unserialize( preg_replace_callback( '!s:(\d+):"(.*?)";!', array( $this, 'regex_replace_serialize' ), $maybe_serialized ) );
		}

		return $unserialized;
	}

	/**
	 * Callback for regex to fix serialize issues.
	 *
	 * @since	9.0.0
	 * @param	mixed $match Regex match.
	 */
	public function regex_replace_serialize( $match ) {
		return ( $match[1] == strlen( $match[2] ) ) ? $match[0] : 's:' . strlen( $match[2] ) . ':"' . $match[2] . '";';
	}

	/**
	 * Get the list publish date.
	 *
	 * @since	9.0.0
	 */
	public function date() {
		return $this->post->post_date;
	}

	/**
	 * Get the list publish date formatted.
	 *
	 * @since	9.0.0
	 */
	public function date_formatted() {
		$datetime = new DateTime( $this->date() );
		return $datetime->format( 'M j' );
	}

	/**
	 * Get the list modified date.
	 *
	 * @since	9.0.0
	 */
	public function date_modified() {
		return $this->post->post_modified;
	}

	/**
	 * Get the list ID.
	 *
	 * @since	9.0.0
	 */
	public function id() {
		return $this->post->ID;
	}

	/**
	 * Get the list post status.
	 *
	 * @since	9.0.0
	 */
	public function post_status() {
		return $this->post->post_status;
	}

	/**
	 * Get the list name.
	 *
	 * @since	9.0.0
	 */
	public function name() {
		return $this->post->post_title;
	}

	/**
	 * Get the list note.
	 *
	 * @since	9.0.0
	 */
	public function note() {
		return $this->post->post_content;
	}

	/**
	 * Get the list template.
	 *
	 * @since	9.0.0
	 */
	public function template() {
		return $this->meta( 'wprm_template', 'default' );
	}

	/**
	 * Wether or not to output list metadata.
	 *
	 * @since	9.0.0
	 */
	public function metadata_output() {
		return $this->meta( 'wprm_metadata_output', true );
	}

	/**
	 * Get the list metadata name.
	 *
	 * @since	9.0.0
	 */
	public function metadata_name() {
		return $this->meta( 'wprm_metadata_name', '' );
	}

	/**
	 * Get the list metadata description.
	 *
	 * @since	9.0.0
	 */
	public function metadata_description() {
		return $this->meta( 'wprm_metadata_description', '' );
	}

	/**
	 * Get the list items.
	 *
	 * @since	9.0.0
	 */
	public function items() {
		$items_array = self::unserialize( $this->meta( 'wprm_items', array() ) );

		// Make sure each item has a unique ID.
		$uid = 0;
		foreach ( $items_array as $index => $item ) {
			$items_array[ $index ]['uid'] = $uid;
			$uid++;
		}

		return $items_array;
	}

	/**
	 * Get the list number of recipes.
	 *
	 * @since	9.0.0
	 */
	public function nbr_items() {
		return intval( $this->meta( 'wprm_nbr_items', 0 ) );
	}
	public function nbr_items_internal() {
		return intval( $this->meta( 'wprm_nbr_items_internal', 0 ) );
	}
	public function nbr_items_external() {
		return intval( $this->meta( 'wprm_nbr_items_external', 0 ) );
	}

	/**
	 * Get the parent post.
	 *
	 * @since	9.0.0
	 */
	public function parent_post() {
		$parent_post_id = $this->parent_post_id();
		if ( $parent_post_id ) {
			return get_post( $parent_post_id );
		}
		return false;
	}

	/**
	 * Get the parent post ID.
	 *
	 * @since    9.0.0
	 */
	public function parent_post_id() {
		return $this->meta( 'wprm_parent_post_id', 0 );
	}

	/**
	 * Get the parent post URL.
	 *
	 * @since	9.0.0
	 */
	public function parent_url() {
		$parent_post_id = $this->parent_post_id();
		return $parent_post_id ? get_permalink( $parent_post_id ) : '';
	}

	/**
	 * Get the parent post edit URL.
	 *
	 * @since	9.0.0
	 */
	public function parent_edit_url() {
		$parent_post_id = $this->parent_post_id();
		return $parent_post_id ? get_edit_post_link( $parent_post_id ) : '';
	}
}
