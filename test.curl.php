			function curl( $url , $method = 'GET' , $data = null , $params = array( ) )
			{
				$defaultUserAgent = 'unknown';
				$defaultHttpReferer = '';
				$defaultParams = array
				(
					'user_agent'			=>	null ,
					'http_referer'			=>	null ,
					'return_transfer'		=>	1 ,
					'connection_timeout'	=>	30 ,
					'header'				=>	0 ,
					'verify_peer'			=>	false
				);
				$params = array_merge( $params , $defaultParams );
				$agent = ( $params[ 'user_agent' ] ) ? 
							$params[ 'user_agent' ] : @$_SERVER[ 'HTTP_USER_AGENT' ];
				$referer = ( $params[ 'http_referer' ] ) ? 
							$params[ 'http_referer' ] : @$_SERVER[ 'HTTP_REFERER' ];
				$fields = ( $data ) ? http_build_query( $data ) : null;
				$ch = curl_init( );
				curl_setopt( $ch , CURLOPT_HEADER , $params[ 'header' ] );
				curl_setopt( $ch , CURLOPT_RETURNTRANSFER , $params[ 'return_transfer' ] );
				curl_setopt( $ch , CURLOPT_CONNECTTIMEOUT , $params[ 'connection_timeout' ] );
				curl_setopt( $ch , CURLOPT_SSL_VERIFYPEER , $params[ 'verify_peer' ] );
				curl_setopt( $ch , CURLOPT_REFERER , ( ( $params[ 'http_referer' ] ) ? 
									$params[ 'http_referer' ] : $defaultHttpReferer ) );
				curl_setopt( $ch , CURLOPT_USERAGENT , ( ( $params[ 'user_agent' ] ) ? 
									$params[ 'user_agent' ] : $defaultUserAgent ) );	
				curl_setopt( $ch , CURLOPT_CUSTOMREQUEST , strtoupper( $method ) );	
				if ( $fields )
				{
					if ( 'POST' === strtoupper( $method ) )
					{
						curl_setopt( $ch , CURLOPT_POST , count( $data ) );
						curl_setopt( $ch , CURLOPT_POSTFIELDS , $fields );
					}
					else{ $url = $url . '?' . $fields; } // get , put and delete
				}
				curl_setopt( $ch , CURLOPT_URL , $url );
				$result = curl_exec( $ch );
				$http_code = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
				$content_type = curl_getinfo( $ch , CURLINFO_CONTENT_TYPE );
				if ( $curl_error = curl_error( $ch ) ) 
				{
					throw new \Exception( 'Handyman curl request returned an error: ' . $curl_error );
				} 
				//else{ $json_decode = json_decode( $result , true ); }
				curl_close( $ch );
				return $result;
				
			}