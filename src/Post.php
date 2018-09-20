<?php
namespace Awethemes\WP_Object;

class Post extends Model {
	/**
	 * The WP_Post attributes.
	 *
	 * @var array
	 */
	public static $post_attributes = [
		'post_author'           => 0,
		'post_date'             => '0000-00-00 00:00:00',
		'post_date_gmt'         => '0000-00-00 00:00:00',
		'post_content'          => '',
		'post_title'            => '',
		'post_excerpt'          => '',
		'post_status'           => 'publish',
		'comment_status'        => 'open',
		'ping_status'           => 'open',
		'post_password'         => '',
		'post_name'             => '',
		'to_ping'               => '',
		'pinged'                => '',
		'post_modified'         => '0000-00-00 00:00:00',
		'post_modified_gmt'     => '0000-00-00 00:00:00',
		'post_content_filtered' => '',
		'post_parent'           => 0,
		'guid'                  => '',
		'menu_order'            => 0,
		'post_type'             => 'post',
		'post_mime_type'        => '',
		'comment_count'         => 0,
	];

	/**
	 * Returns the post type name.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return $this->object_type;
	}

	/**
	 * Define a relationship with a taxonomy (polymorphic many-to-many).
	 *
	 * @param  string $related
	 * @param  string $taxomony
	 *
	 * @return \Awethemes\WP_Object\Relations\Taxonomy
	 */
	public function taxonomy( $related, $taxomony ) {
		$instance = new $related;
	}

	/**
	 * {@inheritdoc}
	 */
	public function new_query() {
		return new Query\Post_Query( [
			'post_type'           => $this->get_post_type(),
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		] );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setup_instance() {
		$wp_post = get_post( $this->get_id() );

		if ( ! is_null( $wp_post ) && get_post_type( $wp_post->ID ) === $this->get_post_type() ) {
			$this->set_instance( $wp_post );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function perform_delete( $force ) {
		if ( ! $force && EMPTY_TRASH_DAYS && 'trash' !== get_post_status( $this->get_id() ) ) {
			$delete = wp_trash_post( $this->get_id() );
		} else {
			$delete = wp_delete_post( $this->get_id(), true );
		}

		return ( ! is_null( $delete ) && ! is_wp_error( $delete ) && false !== $delete );
	}

	/**
	 * Helper: Safely update a post.
	 *
	 * @see \Awethemes\WP_Object\Utils::update_the_post
	 *
	 * @param  array $post_data An array post data to update.
	 * @return bool|null
	 */
	protected function update_the_post( array $post_data ) {
		return Utils::update_the_post( $this->get_id(), $post_data );
	}
}
