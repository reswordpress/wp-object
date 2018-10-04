<?php

class Test_Model_Query extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function testQueryPost() {
		$posts = $this->factory->post->create_many( 5, [ 'post_type' => 'post' ] );
		$pages = $this->factory->post->create_many( 12, [ 'post_type' => 'page' ] );

		$this->assertInstanceOf( \Awethemes\WP_Object\Collection::class, ModelPageStub::all() );
		$this->assertCount( 12, ModelPageStub::all() );
		$this->assertCount( 5, ModelPageStub::limit( 5 )->get() );

		foreach ( ModelPageStub::all() as $model ) {
			$this->assertInstanceOf( ModelPageStub::class, $model );
			$this->assertEquals( 'page', $model['post_type'] );
		}

		$page1 = ModelPageStub::find( $pages[0] );
		$this->assertInstanceOf( ModelPageStub::class, $page1 );
		$this->assertArrayHasKey( 'ID', $page1 );

		$this->assertNull( ModelPageStub::find( $posts[0] ) );
	}

	public function testQueryTerm() {
		$cates = $this->factory->term->create_many( 5, [ 'taxonomy' => 'category' ] );
		$tags  = $this->factory->term->create_many( 12, [ 'taxonomy' => 'post_tag' ] );

		$this->assertInstanceOf( \Awethemes\WP_Object\Collection::class, ModelTagStub::all() );
		$this->assertCount( 12, ModelTagStub::all() );
		$this->assertCount( 5, ModelTagStub::limit( 5 )->get() );

		foreach ( ModelTagStub::all() as $model ) {
			$this->assertInstanceOf( ModelTagStub::class, $model );
			$this->assertEquals( 'post_tag', $model['taxonomy'] );
		}

		$tag1 = ModelTagStub::find( $tags[0] );
		$this->assertInstanceOf( ModelTagStub::class, $tag1 );
		$this->assertArrayHasKey( 'term_id', $tag1 );

		$this->assertNull( ModelTagStub::find( $cates[0] ) );
	}

	public function testDBQuery() {
		$data = new ModelDBStub([
			'post_type' => 'page',
		]);

		$data->save();
	}
}

class ModelPageStub extends \Awethemes\WP_Object\Post {
	protected $object_type = 'page';
}

class ModelTagStub extends \Awethemes\WP_Object\Term {
	protected $object_type = 'post_tag';
}

class ModelDBStub extends \Awethemes\WP_Object\Model {
	protected $table = 'posts';
}
