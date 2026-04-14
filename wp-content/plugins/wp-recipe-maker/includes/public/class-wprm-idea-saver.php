<?php
/**
 * Responsible for saving ideas.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for saving ideas.
 *
 * @since      10.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Idea_Saver {

	/**
	 * Sanitize idea array.
	 *
	 * @since    10.4.0
	 * @param    array $idea Idea fields to sanitize.
	 */
	public static function sanitize( $idea ) {
		$sanitized_idea = array();

		$text_fields = array(
			'name',
			'type',
			'status',
			'source',
			'ai_prompt_summary',
		);

		foreach ( $text_fields as $field ) {
			if ( isset( $idea[ $field ] ) ) {
				$sanitized_idea[ $field ] = sanitize_text_field( $idea[ $field ] );
			}
		}

		if ( isset( $sanitized_idea['source'] ) && ! in_array( $sanitized_idea['source'], array( 'manual', 'ai' ), true ) ) {
			$sanitized_idea['source'] = 'manual';
		}

		if ( isset( $sanitized_idea['type'] ) && ! in_array( $sanitized_idea['type'], array( 'recipe', 'list', 'other' ), true ) ) {
			$sanitized_idea['type'] = 'recipe';
		}

		if ( isset( $idea['summary'] ) ) {
			$sanitized_idea['summary'] = wp_kses_post( $idea['summary'] );
		}

		if ( isset( $idea['notes'] ) ) {
			$sanitized_idea['notes'] = wp_kses_post( $idea['notes'] );
		}

		if ( isset( $idea['ai_generated_at'] ) ) {
			$sanitized_idea['ai_generated_at'] = sanitize_text_field( $idea['ai_generated_at'] );
		}

		return apply_filters( 'wprm_idea_sanitize', $sanitized_idea, $idea );
	}

	/**
	 * Update idea fields.
	 *
	 * @since    10.4.0
	 * @param    int   $id Post ID of the idea.
	 * @param    array $idea Idea fields to save.
	 */
	public static function update_idea( $id, $idea ) {
		$meta = array();

		$allowed_meta = array(
			'type',
			'status',
			'source',
			'ai_prompt_summary',
			'ai_generated_at',
		);

		foreach ( $allowed_meta as $meta_key ) {
			if ( isset( $idea[ $meta_key ] ) ) {
				$meta[ 'wprm_' . $meta_key ] = $idea[ $meta_key ];
			}
		}

		$post = array(
			'ID'         => $id,
			'post_type'  => WPRM_IDEA_POST_TYPE,
			'post_status'=> 'draft',
			'meta_input' => $meta,
		);

		if ( isset( $idea['name'] ) ) {
			$post['post_title'] = $idea['name'];
		}

		if ( isset( $idea['summary'] ) ) {
			$post['post_excerpt'] = wp_slash( $idea['summary'] );
		}

		if ( isset( $idea['notes'] ) ) {
			$post['post_content'] = wp_slash( $idea['notes'] );
		}

		WPRM_Idea_Manager::invalidate_idea( $id );
		wp_update_post( $post );
	}
}
