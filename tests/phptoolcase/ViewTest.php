<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;

	final class ViewTest extends TestCase
	{
		public static function setUpBeforeClass( )
		{
			View::path( dirname( __FILE__ ) . '/../../examples/view' );
		}

		public function testMake( )
		{
			$view = View::make( 'test.view' , [ 'test' => 'some data' , 'child' => 'some more data' ] );
			$this->assertEquals( 'some data' , $view->get( 'test' ) );
			$this->assertEquals( 'some more data' , $view->get( 'child' ) );
		}
	
		public function testCompile( )
		{
			$html = View::make( 'test.view' , [ 'test' => 'some data' , 'child' => 'some more data' ] )
					->compile( );
			$this->assertEquals( 343 , strlen( $html ) );
			$this->assertTrue( ( false !== strpos( $html , 'This is my web page some data' ) ) );
		}
		
		public function testSetParametersSeperately( )
		{
			$view = View::make( 'test.view' );
			$view->set( 'test' , 'some data' );
			$view->set( 'child' , 'some more data' );
			$html = $view->compile( );
			$this->assertEquals( 343 , strlen( $html ) );
			$this->assertTrue( ( false !== strpos( $html , 'This is my web page some data' ) ) );
		}
		
		public function testRender( )
		{
			ob_start( );
			$html = View::make( 'test.view' , [ 'test' => 'some data' , 'child' => 'some more data' ] )
					->render( );
			$html = ob_get_clean( );
			$this->assertEquals( 343 , strlen( $html ) );
			$this->assertTrue( ( false !== strpos( $html , 'This is my web page some data' ) ) );
		}
		
		public function testNestingViews( )
		{
			ob_start( );
			$html = View::make( 'test.view' , [ 'test' => 'some data' ] )
					->nest( 'child' , 'nested.view' , [ 'some' => 'view data' ] )
					->render( );
			$html = ob_get_clean( );
			$this->assertEquals( 365 , strlen( $html ) );
			$this->assertTrue( ( false !== strpos( $html , 'this view is nested view data' ) ) );
		}
	}