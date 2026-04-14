<?php
/**
 * Represents an idea.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Represents an idea.
 *
 * @since      10.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Idea {

	/**
	 * WP_Post object associated with this idea.
	 *
	 * @since    10.4.0
	 * @access   private
	 * @var      WP_Post $post Idea post object.
	 */
	private $post;

	/**
	 * Cached meta values.
	 *
	 * @since    10.4.0
	 * @access   private
	 * @var      array $meta Idea meta.
	 */
	private $meta = false;

	/**
	 * Get new idea object from associated post.
	 *
	 * @since    10.4.0
	 * @param    WP_Post $post Idea post object.
	 */
	public function __construct( $post ) {
		$this->post = $post;
	}

	/**
	 * Get metadata value.
	 *
	 * @since    10.4.0
	 * @param    string $field Metadata field to retrieve.
	 * @param    mixed  $default Default value if missing.
	 */
	public function meta( $field, $default ) {
		if ( false === $this->meta ) {
			$this->meta = get_post_custom( $this->id() );
		}

		if ( isset( $this->meta[ $field ] ) ) {
			return $this->meta[ $field ][0];
		}

		return $default;
	}

	/**
	 * Get all idea data.
	 *
	 * @since    10.4.0
	 */
	public function get_data() {
		$idea = array(
			'id'               => $this->id(),
			'post_status'      => $this->post_status(),
			'name'             => $this->name(),
			'summary'          => $this->summary(),
			'type'             => $this->type(),
			'status'           => $this->status(),
			'source'           => $this->source(),
			'notes'            => $this->notes(),
			'ai_prompt_summary'=> $this->ai_prompt_summary(),
			'ai_generated_at'  => $this->ai_generated_at(),
		);

		return apply_filters( 'wprm_idea_data', $idea, $this );
	}

	/**
	 * Get idea data for the manage page.
	 *
	 * @since    10.4.0
	 */
	public function get_data_manage() {
		$idea = $this->get_data();
		$idea['date'] = $this->date();
		$idea['last_updated'] = $this->last_updated();

		return apply_filters( 'wprm_idea_manage_data', $idea, $this );
	}

	/**
	 * Get idea ID.
	 *
	 * @since    10.4.0
	 */
	public function id() {
		return $this->post->ID;
	}

	/**
	 * Get post status.
	 *
	 * @since    10.4.0
	 */
	public function post_status() {
		return $this->post->post_status;
	}

	/**
	 * Get idea creation date.
	 *
	 * @since    10.4.0
	 */
	public function date() {
		return $this->post->post_date;
	}

	/**
	 * Get last updated date.
	 *
	 * @since    10.4.0
	 */
	public function last_updated() {
		return $this->post->post_modified;
	}

	/**
	 * Get idea title.
	 *
	 * @since    10.4.0
	 */
	public function name() {
		return $this->post->post_title;
	}

	/**
	 * Get status.
	 *
	 * @since    10.4.0
	 */
	public function status() {
		return $this->meta( 'wprm_status', 'idea' );
	}

	/**
	 * Get summary.
	 *
	 * @since    10.4.0
	 */
	public function summary() {
		return $this->post->post_excerpt;
	}

	/**
	 * Get type.
	 *
	 * @since    10.4.0
	 */
	public function type() {
		return $this->meta( 'wprm_type', 'recipe' );
	}

	/**
	 * Get source.
	 *
	 * @since    10.4.0
	 */
	public function source() {
		return $this->meta( 'wprm_source', 'manual' );
	}

	/**
	 * Get notes.
	 *
	 * @since    10.4.0
	 */
	public function notes() {
		return $this->post->post_content;
	}

	/**
	 * Get AI prompt summary.
	 *
	 * @since    10.4.0
	 */
	public function ai_prompt_summary() {
		return $this->meta( 'wprm_ai_prompt_summary', '' );
	}

	/**
	 * Get AI generated timestamp.
	 *
	 * @since    10.4.0
	 */
	public function ai_generated_at() {
		return $this->meta( 'wprm_ai_generated_at', '' );
	}
}
