<?php
namespace Awethemes\WP_Object;

class Post extends WP_Object {
	use Concerns\Has_Metadata;

	/**
	 * Name of the post type.
	 *
	 * @var string
	 */
	protected $object_type = 'post';

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'posts';

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
	 * Define a relationship with a taxonomy (polymorphic many-to-many).
	 *
	 * @param  string $related
	 * @param  string $taxomony
	 * @return \Awethemes\WP_Object\Relations\Taxonomy
	 */
	public function taxonomy( $related, $taxomony ) {
	}

	/**
	 * Returns the post type name.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return $this->object_type;
	}

	/**
	 * {@inheritdoc}
	 */
	public function new_query() {
		return new Query\Post_Query( [
			'post_type'           => $this->get_post_type(),
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'posts_per_page'      => -1,
		] );
	}
}
