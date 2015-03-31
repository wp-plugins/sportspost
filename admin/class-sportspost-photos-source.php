<?php
/**
 * Base class for Photos sources.
 *
 * @package    SportsPost
 * @subpackage SportsPost/admin
 * @since   2.0.0
 */
if( ! class_exists( 'SportsPost_Photos_Source' ) ) {
	
	class SportsPost_Photos_Source {
		
		/* Contains the name of the source. */
		protected $name;
		
		/* Contains the last HTTP status code returned. */
		public $http_code;
		
		/* Contains the last API call. */
		public $url;
		
		/* Set up the API root URL. */
		public $host;
		
		/* Set timeout default. */
		public $timeout = 30;
		
		/* Set connect timeout. */
		public $connecttimeout = 30; 
		
		/* Verify SSL Cert. */
		public $ssl_verifypeer = FALSE;
		
		/* Respons format. */
		public $format;
		
		/* Decode returned json data. */
		public $decode_json = TRUE;
		
		/* Contains the last HTTP headers returned. */
		public $http_info;
		
		/* Set the useragnet. */
		public $useragent = 'SportsPost';
		/* Settings */
		private $settings;
		
		/* Default results count */
		private $default_count;
	
		/**
		* Debug helper for status code
		*/
		function lastStatusCode() {
			return $this->http_status;
		}
		
		/**
		* Debug helper for last api call
		*/
		function lastAPICall() {
			return $this->last_api_call;
		}
		
		/**
		* Constructor
		*/
		function __construct( $host, $format,  $settings, $default_count ) {
			$this->host = $host;
			$this->format = $format;
			$this->settings = $settings;
			$this->default_count = $default_count;
		}

		/**
		* GET wrapper for request.
		*/
		function get( $url, $parameters = array(), $mode = 0 ) {
			$response = $this->request( $url, 'GET', $parameters );  
			if ( $this->format === 'json' && $this->decode_json && $response ) {
				return json_decode( $response );
			}
			return $response;
		}
		
		/**
		* POST wrapper for request.
		*/
		function post( $url, $parameters = array() ) {
			$response = $this->request( $url, 'POST', $parameters );
			if ($this->format === 'json' && $this->decode_json) {
				return json_decode( $response );
			}
			return $response;
		}
		
		/**
		* DELETE wrapper for request.
		*/
		function delete( $url, $parameters = array() ) {
			$response = $this->request( $url, 'DELETE', $parameters );
			if ( $this->format === 'json' && $this->decode_json ) {
				return json_decode( $response );
			}
			return $response;
		}
		
		function get_format( $url ) {
			return "{$this->host}{$url}.{$this->format}"; 
		}
		
		/**
		* Execute request
		*/
		function request( $url, $method, $parameters ) {
			if ( strrpos( $url, 'https://' ) !== 0 && strrpos( $url, 'http://' ) !== 0 ) {
				$url = $this->get_format( $url );
			}
			switch ( $method ) {
				case 'GET':
					return $this->http( $url . '?' . self::build_http_query( $parameters ), 'GET' );
				default:
					return $this->http( $url, $method, $parameters );
			}
		}
		
		/**
		* Helper function for url encoding
		*/
		public static function urlencode_rfc3986( $input ) {
			if ( is_array( $input ) ) {
				return array_map( array( 'self', 'urlencode_rfc3986' ), $input );
			} 
			else if ( is_scalar( $input ) ) {
				return str_replace( '+', ' ', str_replace( '%7E', '~', rawurlencode( $input ) ) );
			} else {
				return '';
			}
		}
		
		
		/**
		* Helper function for parameter building
		*/
		public static function build_http_query( $params ) {
			if (!$params) return '';
			// Urlencode both keys and values
			$keys = self::urlencode_rfc3986( array_keys( $params ) );
			$values = self::urlencode_rfc3986( array_values( $params ) );
			$params = array_combine( $keys, $values );
			// Parameters are sorted by name, using lexicographical byte value ordering.
			// Ref: Spec: 9.1.1 (1)
			uksort( $params, 'strcmp' );
			$pairs = array();
			foreach ( $params as $parameter => $value ) {
				if ( is_array( $value ) ) {
					// If two or more parameters share the same name, they are sorted by their value
					// Ref: Spec: 9.1.1 (1)
					natsort( $value );
					foreach ( $value as $duplicate_value ) {
						$pairs[] = $parameter . '=' . $duplicate_value;
					}
				} else {
					$pairs[] = $parameter . '=' . $value;
				}
			}
			// For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
			// Each name-value pair is separated by an '&' character (ASCII code 38)
			return implode( '&', $pairs );
		}
		
		
		/**
		* Make an HTTP request
		*
		* @return API results
		*/
		function http( $url, $method, $postfields = NULL ) {
			$this->http_info = array();
			$ci = curl_init();
			/* Curl settings */	    
			curl_setopt( $ci, CURLOPT_USERAGENT, $this->useragent );
			curl_setopt( $ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout );
			curl_setopt( $ci, CURLOPT_TIMEOUT, $this->timeout );
			curl_setopt( $ci, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $ci, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
			curl_setopt( $ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer );
			curl_setopt( $ci, CURLOPT_HEADERFUNCTION, array( $this, 'get_header') );
			curl_setopt( $ci, CURLOPT_HEADER, FALSE );
			
			switch ( $method ) {
				case 'POST':
					curl_setopt( $ci, CURLOPT_POST, TRUE );
					if ( ! empty( $postfields ) ) {
						curl_setopt( $ci, CURLOPT_POSTFIELDS, $postfields );
					}
					break;
				case 'DELETE':
					curl_setopt( $ci, CURLOPT_CUSTOMREQUEST, 'DELETE' );
					if ( ! empty( $postfields ) ) {
						$url = "{$url}?{$postfields}";
					}
			}
			curl_setopt( $ci, CURLOPT_URL, $url );
			$response = curl_exec( $ci );
			$this->http_code = curl_getinfo( $ci, CURLINFO_HTTP_CODE );
			$this->http_info = array_merge( $this->http_info, curl_getinfo( $ci ) );
			$this->url = $url;
			curl_close( $ci );
			return $response;
		}
		
		/**
		* Get the header info to store.
		*/
		function get_header($ch, $header) {
			$i = strpos( $header, ':' );
			if ( ! empty( $i ) ) {
				$key = str_replace( '-', '_', strtolower( substr( $header, 0, $i ) ) );
				$value = trim( substr( $header, $i + 2 ) );
				$this->http_header[ $key ] = $value;
			}
			return strlen( $header );
		}
		
		/**
		* Helper function to filter text
		*/	
		function filter_text( $text ) {
			return trim( filter_var( $text, FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_LOW ) );
		}
		
		
		/**
		* Return source settings
		*/	
		public function get_settings() {
			return $this->settings;
		}
		
		/**
		* Return source name
		*/	
		public function get_name() {
			return $this->name;
		}
		
		/**
		* Return source param choices
		*/	
		function get_param_choices($type = '') {
		}
		
	}
	
}