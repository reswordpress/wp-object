<?php

use Awethemes\WP_Object\Post;
use Awethemes\WP_Object\Query\Builder;

class Query_Builder_Test extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function testPostBuilder() {
		$this->factory->post->create_many( 7, [ 'post_type' => 'page' ] );

		$posts = TestModelTable::where( 'post_type', 'page' )->limit( 2 )->get();

		dump( count($posts) );
	}
}

class TestModelTable extends \Awethemes\WP_Object\Model {
	protected $table = 'posts';
}
