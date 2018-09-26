<?php

class Test_Model extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function testAttributeManipulation() {
		$model       = new ModelStub;
		$model->name = 'foo';

		$this->assertEquals( 'foo', $model->name );
		$this->assertTrue( isset( $model->name ) );
		unset( $model->name );
		$this->assertFalse( isset( $model->name ) );

		// test mutation
		$model->list_items = [ 'name' => 'van anh' ];
		$this->assertEquals( [ 'name' => 'van anh' ], $model->list_items );
		$attributes = $model->get_attributes();
		$this->assertEquals( [ 'name' => 'van anh' ], $attributes['list_items'] );
	}

	public function testDirtyAttributes() {
		$model = new ModelStub( [ 'foo' => '1', 'bar' => 2, 'baz' => 3 ] );
		$model->sync_original();

		$model->foo = 1;
		$model->bar = 20;
		$model->baz = 30;

		$this->assertTrue( $model->is_dirty() );
		$this->assertFalse( $model->is_dirty( 'foo' ) );
		$this->assertTrue( $model->is_dirty( 'bar' ) );
		$this->assertTrue( $model->is_dirty( 'foo', 'bar' ) );
		$this->assertTrue( $model->is_dirty( [ 'foo', 'bar' ] ) );
	}

	public function testCleanAttributes() {
		$model = new ModelStub( [ 'foo' => '1', 'bar' => 2, 'baz' => 3 ] );
		$model->sync_original();

		$model->foo = 1;
		$model->bar = 20;
		$model->baz = 30;

		$this->assertFalse( $model->is_clean() );
		$this->assertTrue( $model->is_clean( 'foo' ) );
		$this->assertFalse( $model->is_clean( 'bar' ) );
		$this->assertFalse( $model->is_clean( 'foo', 'bar' ) );
		$this->assertFalse( $model->is_clean( [ 'foo', 'bar' ] ) );
	}

	public function testArrayAccessToAttributes() {
		$model = new ModelStub( [ 'attributes' => 1, 'connection' => 2, 'table' => 3 ] );
		unset( $model['table'] );

		$this->assertTrue( isset( $model['attributes'] ) );
		$this->assertEquals( $model['attributes'], 1 );
		$this->assertTrue( isset( $model['connection'] ) );
		$this->assertEquals( $model['connection'], 2 );
		$this->assertFalse( isset( $model['table'] ) );
		$this->assertEquals( $model['table'], null );
		$this->assertFalse( isset( $model['with'] ) );
	}

	public function testOnly() {
		$model = new ModelStub;

		$model->first_name = 'van';
		$model->last_name  = 'anh';
		$model->project    = 'wp';

		$this->assertEquals( [ 'project' => 'wp' ], $model->only( 'project' ) );
		$this->assertEquals( [ 'first_name' => 'van', 'last_name' => 'anh' ], $model->only( 'first_name', 'last_name' ) );
		$this->assertEquals( [ 'first_name' => 'van', 'last_name' => 'anh' ], $model->only( [ 'first_name', 'last_name' ] ) );
	}

	public function testNewInstanceReturnsNewInstanceWithAttributesSet() {
		$model    = new ModelStub;
		$instance = $model->new_instance( [ 'name' => 'van anh' ] );
		$this->assertInstanceOf( ModelStub::class, $instance );
		$this->assertEquals( 'van anh', $instance->name );
	}

	public function testModelUser() {
		global $wpdb;

		$this->factory->user->create_many( 4 );

		$users = UserModelUser::limit( 2 )->get();

		dump( $users );
		dump( $wpdb->last_query );
	}

	/*public function testModelUser() {
		$this->factory->post->create_many( 4 );

		$posts = PostModelUser::query()
		                      ->get();
		dump( $posts );
	}*/
}

class ModelStub extends \Awethemes\WP_Object\Model {
}

class UserModelUser extends \Awethemes\WP_Object\Model {
	protected $table = 'users';
}

class PostModelUser extends \Awethemes\WP_Object\Post {
}
