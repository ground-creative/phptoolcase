<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;

	final class RouterTest extends TestCase
	{
		public function testAddGetRoute( )
		{
			Router::get( '/' , function( )
			{
				print 'called the main page';
			} )->map( 'index' ); // map route to be retrieved later
		}
	}