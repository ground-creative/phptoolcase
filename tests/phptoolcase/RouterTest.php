<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;
	use PHPUnit\Framework\Assert;

	final class RouterTest extends TestCase
	{
		public static function setUpBeforeClass( ) : void
		{
			static::$_baseUri = $_SERVER[ 'HTTP_HOST' ] . $GLOBALS[ 'ROUTER_REQUESTS_PATH' ];
		}
		
		 protected function setUp( ) : void 
		{
			$this->client = new \GuzzleHttp\Client(
			[
				'base_uri' => static::$_baseUri // this is not working apparently
			] );
		}
	
		public function testAddGetRoute( )
		{
			$response = $this->client->get( static::$_baseUri );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'called the main page by get request' , ( string ) $response->getBody( ) );
		}
		
		public function testAddPostRoute( )
		{
			$response = $this->client->post( static::$_baseUri );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'called the main page by post request' , ( string ) $response->getBody( ) );
		}
		public function testAddPutRoute( )
		{
			$response = $this->client->put( static::$_baseUri );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'called the main page by put request' , ( string ) $response->getBody( ) );
		}

		public function testAddDeleteRoute( )
		{
			$response = $this->client->delete( static::$_baseUri );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'called the main page by delete request' , ( string ) $response->getBody( ) );
		}
		
		public function testAddAnyRoute( )
		{
			$response = $this->client->get( static::$_baseUri . 'any-request/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'called any request uri' , ( string ) $response->getBody( ) );
			$response = $this->client->post( static::$_baseUri . 'any-request/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'called any request uri' , ( string ) $response->getBody( ) );
			$response = $this->client->put( static::$_baseUri . 'any-request/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'called any request uri' , ( string ) $response->getBody( ) );
			$response = $this->client->delete( static::$_baseUri . 'any-request/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'called any request uri' , ( string ) $response->getBody( ) );
		}
		
		public function testAddRouteWithParams( )
		{
			$response = $this->client->get( static::$_baseUri . 'user/test-param/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing a parameter 123' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/test-param/charlie/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing a parameter charlie-123' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/test-param/charlie/123/22-11-1978/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing a parameter charlie-123/22-11-1978' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/test-param/charlie/123/22-11-1978/1235/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing a parameter charlie-123/22-11-1978/1235' , ( string ) $response->getBody( ) );
		}
		
		public function testAddRouteWithParamsBasedOnPattern( )
		{
			$response = $this->client->get( static::$_baseUri . 'param-test/es/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing a parameter against a pattern es' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'param-test/123/es/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing a parameter against a pattern 123-es' , ( string ) $response->getBody( ) );
			$this->assertTrue( static::_assertUrlIsNotFound( $this->client , 'param-test/failure/' ) );
			$this->assertTrue( static::_assertUrlIsNotFound( $this->client , 'param-test/failure/again/' ) );
			$this->assertTrue( static::_assertUrlIsNotFound( $this->client , 'param-test/33/failure/' ) );
		}
		
		public function testAddRouteWithOptionalParams( )
		{
			$response = $this->client->get( static::$_baseUri . 'user/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter 123' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter ' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/area/charlie/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter charlie-123' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/area/charlie/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter charlie-' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/private/charlie/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter charlie-123' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/private/charlie/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter charlie-' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/private/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter -' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/account/345we89jhg/charlie/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter 345we89jhg-charlie-123' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/account/345we89jhg/charlie/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter 345we89jhg-charlie-' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/account/345we89jhg/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter 345we89jhg--' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/member/345we89jhg/charlie/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter 345we89jhg-charlie-123' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/member/345we89jhg/charlie/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter 345we89jhg-charlie-' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/member/345we89jhg/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter 345we89jhg--' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'user/member/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter --' , ( string ) $response->getBody( ) );
		}
		
		public function testAddRouteWithOptionalParamsBasedOnPattern( )
		{
			$response = $this->client->get( static::$_baseUri . 'lang-test/es/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter against a pattern es' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'lang-test/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter against a pattern ' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'lang-multiple/123/es/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter against a pattern 123-es' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'lang-multiple/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter against a pattern 123-' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'lang-multiple/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter against a pattern -' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'lang-more/123/es/1978-11-22/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter against a pattern 123-es/1978-11-22' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'lang-more/123/es/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter against a pattern 123-es/' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'lang-more/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter against a pattern 123-/' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'lang-more/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter against a pattern -/' , ( string ) $response->getBody( ) );			
			$this->assertTrue( static::_assertUrlIsNotFound( $this->client , 'lang-test/failure/' ) );
			$this->assertTrue( static::_assertUrlIsNotFound( $this->client , 'lang-multiple/123/failure/' ) );
			$this->assertTrue( static::_assertUrlIsNotFound( $this->client , 'lang-multiple/failure/es/' ) );
			$this->assertTrue( static::_assertUrlIsNotFound( $this->client , 'lang-multiple/failure/again' ) );
			$this->assertTrue( static::_assertUrlIsNotFound( $this->client , 'lang-multiple/failure/' ) );
			$this->assertTrue( static::_assertUrlIsNotFound( $this->client , 'lang-more/123/es/22-11-1978/' ) );	
		}
		
		public function testNotFoundUrl( )
		{
			$this->assertTrue( static::_assertUrlIsNotFound( $this->client , 'notfound/' ) );
		}
		
		public function testGetRouterValues( )
		{
			$response = $this->client->get( static::$_baseUri . 'router-parameter/123/charlie/22-11-1978/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'testing optional parameter 123-charlie/22-11-1978' , ( string ) $response->getBody( ) );
		}
		
		public function testControllerClass( )
		{
			$response = $this->client->get( static::$_baseUri . 'controller/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'executing restful controller index page' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'controller/user/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'executing restful controller get request with param 123' , ( string ) $response->getBody( ) );
			$response = $this->client->post( static::$_baseUri . 'controller/user/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'executing restful controller post request with param 123' , ( string ) $response->getBody( ) );
			$response = $this->client->delete( static::$_baseUri . 'controller/user/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'executing restful controller delete request with param 123' , ( string ) $response->getBody( ) );
			$response = $this->client->put( static::$_baseUri . 'controller/user/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'executing restful controller put request with param 123' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'controller/optional-param/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'executing restful controller get request with optonal param 123' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'controller/optional-param/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'executing restful controller get request with optonal param ' , ( string ) $response->getBody( ) );
			$this->assertTrue( static::_assertUrlIsNotFound( $this->client , 'controller/user/failure/' ) );
		}
		
		public function testNoTrailingSlashRedirect( )
		{
			$response = $this->client->get( static::$_baseUri . 'any-request' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
		}

		public function testTrailingSlashConfig( )
		{
			$response = $this->client->get( static::$_baseUri . 'any-request/?test_no_trailing_slash=true' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$response = $this->client->get( static::$_baseUri . 'any-request' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
		}
		
		public function testRouteMap( )
		{
			$response = $this->client->get( static::$_baseUri . 'test-map/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertStringEndsWith( '{param?}/' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'test-map/123/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertStringEndsWith( '123/' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'test-map/456/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertStringEndsWith( '{param?}/' , ( string ) $response->getBody( ) );
		}	
		
		public function testRouteFilters( )
		{
			$response = $this->client->get( static::$_baseUri . 'test-before-filter/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'before filter executed' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'test-after-filter/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'executed after filter' , ( string ) $response->getBody( ) );
			$response = $this->client->get( static::$_baseUri . 'test-after-filter/' );
			$this->assertEquals( 200 , $response->getStatusCode( ) );
			$this->assertEquals( 'executed after filter' , ( string ) $response->getBody( ) );
			
			$response = $this->client->get( static::$_baseUri . 'test-discard-route-filter/' );
			var_dump($response->getStatusCode( ) );
			//$this->assertTrue( static::_assertUrlIsNotFound( $this->client , 'test-discard-route-filter/' ) );
		}
		
		protected static function _assertUrlIsNotFound( $client , $uri )
		{
			try 
			{
				$response = $client->get( static::$_baseUri . $uri );
			} 
			catch ( \GuzzleHttp\Exception\RequestException $e ) 
			{
				return ( 404 == $e->getResponse( )->getStatusCode( ) ) ? true : false;
			}
			return false;			
		}
		
		protected static$_baseUri= null;
	}