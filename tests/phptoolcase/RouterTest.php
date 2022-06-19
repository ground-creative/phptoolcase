<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;

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
			try 
			{
				$response = $this->client->get( static::$_baseUri . 'param-test/failure/' );
			} 
			catch ( \GuzzleHttp\Exception\RequestException $e ) 
			{
				$this->assertEquals( 404 , $e->getResponse( )->getStatusCode( ) );
			}
			try 
			{
				$response = $this->client->get( static::$_baseUri . 'param-test/faiure/again/' );
			} 
			catch ( \GuzzleHttp\Exception\RequestException $e ) 
			{
				$this->assertEquals( 404 , $e->getResponse( )->getStatusCode( ) );
				return;
			}
			try 
			{
				$response = $this->client->get( static::$_baseUri . 'param-test/33/failure/' );
			} 
			catch ( \GuzzleHttp\Exception\RequestException $e ) 
			{
				$this->assertEquals( 404 , $e->getResponse( )->getStatusCode( ) );
				return;
			}
			$this->assertTrue( false );
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
		
		protected static$_baseUri= null;
	}