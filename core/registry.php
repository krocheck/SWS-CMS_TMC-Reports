<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Application registry that handles the core services
 * Last Updated: $Date: 2010-07-02 09:29:16 -0500 (Fri, 02 Jul 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Core
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 31 $
 */

class Registry
{
	/**
	 * A static instance for the static functions
	 *
	 * @access public
	 * @var Registry
	 * @static
	 * @since 1.0.0
	 */
	public static $instance;
	/**
	 * I think the database is a rather important piece of the puzzle
	 *
	 * @access public
	 * @var Database
	 * @since 1.0.0
	 */
	public    $DB;
	/**
	 * This thing that makes this look good
	 *
	 * @access public
	 * @var Display
	 * @since 1.0.0
	 */
	public    $display;
	/**
	 * Just in case there's problems we've got something to take care of it
	 *
	 * @access public
	 * @var Error
	 * @since 1.0.0
	 */
	public    $error;
	/**
	 * The language class to handle abstraction
	 *
	 * @access public
	 * @var Languages
	 * @since 1.0.0
	 */
	public    $lang;
	/**
	 * The cache class to do cache stuff
	 *
	 * @access public
	 * @var Cookie
	 * @since 1.0.0
	 */
	public    $cache;
	/**
	 * The cookie class to do cookie stuff
	 *
	 * @access public
	 * @var Cookie
	 * @since 1.0.0
	 */
	public    $cookie;
	/**
	 * The loaded user
	 *
	 * @access public
	 * @var User
	 * @since 1.0.0
	 */
	public    $user;
	/**
	 * The main application configuration
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $config;
	/**
	 * GET and POST variable combined as one
	 *
	 * @access public
	 * @var array
	 * @since 1.0.0
	 */
	public    $input;
	/**
	 * The settings group from the database
	 *
	 * @access public
	 * @var array
	 * @since 1.0.0
	 */
	public    $settings;
	/**
	 * The application that will run
	 *
	 * @access protected
	 * @var object
	 * @since 1.0.0
	 */
	protected $application;
	/**
	 * Stores instances of apps other than the current
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $otherApps = array();

	/**
	 * Constructor that sets up the whole smash
	 *
	 * @param array $config the config to get this puppy going
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct( array $config )
	{
		Registry::$instance = $this;

		$this->config   = $config;
		$this->display  = new Display( $this );

		$this->display->loadTemplates( 'skin_global' );

		$this->error    = new Error( $this );
		$this->DB       = new Database( $this, $this->config );
		$this->cache    = new Cache( $this );
		$this->settings = $this->loadSettings();
		$this->lang     = new Languages( $this );
		$this->cookie   = new Cookie( $this );

		$this->setupApplication();

		$this->input    = $this->parseIncoming();
	}

	/**
	 * Performs basic cleaning recursively
	 * Null characters, etc
	 *
	 * @param array $data an array of stuff to be cleaned
	 * @param int $iteration [Optional] counter to keep from going too deep into the array
	 * @return array|void in the case of a big iteration the array is returned otherwise void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function cleanGlobals( $data, $iteration = 0 )
	{
		// Crafty hacker could send something like &foo[][][][][][]....to kill Apache process
		// We should never have an globals array deeper than 10..

		if ( $iteration >= 10 )
		{
			return $data;
		}

		if ( count( $data ) )
		{
			foreach( $data as $k => $v )
			{
				if ( is_array( $v ) )
				{
					$this->cleanGlobals( $data[ $k ], $iteration+1 );
				}
				else
				{
					# Null byte characters
					$v = preg_replace( '/\\\0/' , '&#92;&#48;', $v );
					$v = preg_replace( '/\\x00/', '&#92;x&#48;&#48;', $v );
					$v = str_replace( '%00'     , '%&#48;&#48;', $v );

					# File traversal
					$v = str_replace( '../'    , '&#46;&#46;/', $v );

					$data[ $k ] = $v;
				}
			}
		}
	}

	/**
	 * Do stuff before the application exits
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function cleanUp()
	{
		if ( is_object( $this->cache ) )
		{
			$this->cache->saveChanges();
		}

		if ( is_object( $this->DB ) )
		{
			$this->DB->close();
		}
	}

	/**
	 * Get rid of the password just in case
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function clearPasswordInput()
	{
		if ( isset( $this->input['password'] ) )
		{
			unset( $this->input['password'] );
		}
	}

	/**
	 * If we're saving input to the database or form we better make sure we filter some stuff
	 *
	 * @return array  an array of the good inputs
	 * @access public
	 * @since 1.0.0
	 */
	public function filterInputsToKeep()
	{
		$inputsToKeep = array();

		foreach( $this->input as $k => $v )
		{
			if ( strlen( $v ) > 0 )
			{
				switch( $k )
				{
					case 's': break;
					case 'email': break;
					case 'password': break;
					case 'logout': break;
					case 'login': break;
					case 'submit': break;
					case 'op': break;
					case 'submit_x': break;
					case 'submit_y': break;
					case 'remember': break;
					default: $inputsToKeep[ $k ] = $v;
				}
			}
		}

		return $inputsToKeep;
	}
	
	/**
	 * Fetch the needed API handler
	 *
	 * @param string $className the name of the API
	 * @return object the app
	 * @access public
	 * @since 1.0.0
	 */
	public function getAPI( $apiName = '' )
	{
		$out = NULL;

		if ( isset( $this->otherClasses[ $apiName ] ) && is_object( $this->otherClasses[ $apiName ] ) )
		{
			$out = $this->otherClasses[ $apiName ];
		}
		else
		{
			$classPath   = SWS_CORE_PATH . 'api/' . $apiName . '.php';
			$className   = ucfirst( $apiName ) . 'API';

			if ( file_exists( $classPath ) )
			{
				require_once( $classPath );
			}
			else
			{
				$this->error->raiseError( 'class_missing', TRUE );
			}
			
			if ( class_exists( $className ) )
			{
				$this->otherClasses[ $apiName ] = new $className();
				$this->otherClasses[ $apiName ]->execute( $this );

				$out = $this->otherClasses[ $apiName ];
			}
		}

		return $out;
	}

	/**
	 * Fetch the application
	 *
	 * @param string $appName the name of the app or blank for current
	 * @return object the app
	 * @access public
	 * @since 1.0.0
	 */
	public function getApp( $appName = '' )
	{
		$out = NULL;

		if ( $appName == '' || $appName == SWS_THIS_APPLICATION )
		{
			$out = $this->application;
		}
		else if ( isset( $this->otherApps[ $appName ] ) && is_object( $this->otherApps[ $appName ] ) )
		{
			$out = $this->otherApps[ $appName ];
		}
		else
		{
			$out = $this->loadApplication( $appName );

			if ( is_object( $out ) )
			{
				$this->otherApps[ $appName ] = $out;
			}
		}

		return $out;
	}

	/**
	 * Get a cache setting
	 *
	 * @param string $key Key name
	 * @return string config setting
	 * @access public
	 * @since 1.0.0
	 */
	public function getCache( $key )
	{
		return $this->cache->getCache( $key );
	}

	/**
	 * Fetch the needed class
	 *
	 * @param string $appName the name of the class
	 * @return object the app
	 * @access public
	 * @since 1.0.0
	 */
	public function getClass( $className = '' )
	{
		$out = NULL;

		if ( isset( $this->otherClasses[ $className ] ) && is_object( $this->otherClasses[ $className ] ) )
		{
			$out = $this->otherClasses[ $className ];
		}
		else
		{
			if ( $className == 'PageController' )
			{
				$filePath = SWS_CLASSES_PATH . 'page.php';
			}
			else if ( $className == 'SubpageController' )
			{
				$filePath = SWS_CLASSES_PATH . 'subpage.php';
			}
			else if ( $className == 'AdminSkin' )
			{
				$filePath = SWS_CORE_PATH . 'admin_skin.php';
			}
			else if ( $className == 'tc_calendar' )
			{
				$filePath = SWS_JS_PATH . 'calendar/classes/tc_calendar.php';
			}
			else
			{
				$this->error->raiseError( 'class_missing', TRUE );
			}

			if ( file_exists( $filePath ) )
			{
				require_once( $filePath );
			}

			if ( class_exists( $className ) )
			{
				$this->otherClasses[ $className ] = new $className();
				$this->otherClasses[ $className ]->execute( $this );

				$out = $this->otherClasses[ $className ];
			}
		}

		return $out;
	}

	/**
	 * Get a config setting
	 *
	 * @param string $key Key name
	 * @return string config setting
	 * @access public
	 * @since 1.0.0
	 */
	public function getConfig( $key )
	{
		$out = "";

		if ( isset( $this->config[ $key ] ) )
		{
			$out = $this->config[ $key ];
		}

		return $out;
	}

	/**
	 * Fetch the cookie class
	 *
	 * @return Cookie the cookie class
	 * @access public
	 * @since 1.0.0
	 */
	public function getCookie()
	{
		return $this->cookie;
	}

	/**
	 * Fetch the database class
	 *
	 * @return Database the db
	 * @access public
	 * @since 1.0.0
	 */
	public function getDB()
	{
		return $this->DB;
	}

	/**
	 * Fetch the display class
	 *
	 * @return Display the display
	 * @access public
	 * @since 1.0.0
	 */
	public function getDisplay()
	{
		return $this->display;
	}

	/**
	 * Fetch the error class
	 *
	 * @return Error the error
	 * @access public
	 * @since 1.0.0
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Get a header string
	 *
	 * @param string $key Key name
	 * @return string heaader
	 * @access public
	 * @since 1.0.0
	 */
	public function getHeader( $key )
	{
		$out = "";

		if ( is_array( $this->headers ) && isset( $this->headers[ $key ] ) )
		{
			$out = $this->headers[ $key ];
		}

		return $out;
	}

	/**
	 * Get an input string
	 *
	 * @param string $key Key name
	 * @return string system setting
	 * @access public
	 * @since 1.0.0
	 */
	public function getInput( $key )
	{
		$out = "";

		if ( is_array( $this->input ) && isset( $this->input[ $key ] ) )
		{
			$out = $this->input[ $key ];
		}

		return $out;
	}

	/**
	 * Fetch the input array
	 *
	 * @return array the input
	 * @access public
	 * @since 1.0.0
	 */
	public function getInputs()
	{
		return $this->input;
	}

	/**
	 * Fetch the language class
	 *
	 * @return Languages the lang
	 * @access public
	 * @since 1.0.0
	 */
	public function getLang()
	{
		return $this->lang;
	}

	/**
	 * Get a system setting
	 *
	 * @param string $key Key name
	 * @return string system setting
	 * @access public
	 * @since 1.0.0
	 */
	public function getSetting( $key )
	{
		$out = "";

		if ( is_array( $this->settings ) && isset( $this->settings[ $key ] ) )
		{
			$out = $this->settings[ $key ];
		}

		return $out;
	}

	/**
	 * Fetch the settings array
	 *
	 * @return array the settings
	 * @access public
	 * @since 1.0.0
	 */
	public function getSettings()
	{
		return $this->settings;
	}

	/**
	 * Fetch the user class
	 *
	 * @return User the user
	 * @access public
	 * @since 1.0.0
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Initializes the requested application
	 *
	 * @param string $appName the name of the application
	 * @return object the application
	 * @access protected
	 * @since 1.0.0
	 */
	protected function loadApplication( $appName )
	{
		$appPath   = SWS_ROOT_PATH . $appName . '/' . $appName . '.php';
		$className = ucfirst( $appName ) . 'App';
		$out       = NULL;

		if ( file_exists( $appPath ) )
		{
			require_once( $appPath );
		}

		if ( class_exists( $className ) )
		{
			$out = new $className();
			$out->execute( $this );
		}

		return $out;
	}

	/**
	 * Fetch the settings from the database
	 *
	 * @return array the settings
	 * @access protected
	 * @since 1.0.0
	 */
	protected function loadSettings()
	{
		return $this->getCache('settings');
	}

	/**
	 * Creates an array that the admin skin module accepts as a form dropdown array
	 *
	 * @param array $array the array of tems to build from
	 * @param string $key the array key to be used as the item key
	 * @param string $value the array key to be used as the item value
	 * @return array the settings
	 * @access protected
	 * @since 1.0.0
	 */
	public function makeDropdownArray( $array, $key, $value )
	{
		$out = array();
		
		if ( count( $array ) > 0 )
		{
			foreach( $array as $item )
			{
				$out[ $item[ $key ] ] = array( $item[ $key ], $item[ $value ] );
			}
		}
		
		return $out;
	}

	/**
	 * Sets the user
	 *
	 * @param string $key the setting key
	 * @param string $value the setting value
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function overrideSetting( $key, $value )
	{
		if ( isset( $this->settings[ $key ] ) )
		{
			$this->settings[ $key ] = $value;
		}
	}

	/**
	 * Clean _GET _POST key
	 *
	 * @param string $key Key name
	 * @return string Cleaned key name
	 * @access protected
	 * @since 1.0.0
	 */
	protected function parseCleanKey( $key )
	{
		if ($key == "")
		{
			return "";
		}

		$key = htmlspecialchars(urldecode($key));
		$key = str_replace( ".."                , ""  , $key );
		$key = preg_replace( "/\_\_(.+?)\_\_/"  , ""  , $key );
		$key = preg_replace( "/^([\w\.\-\_]+)$/", "$1", $key );

		return $key;
	}

	/**
	 * UnHTML and stripslashes _GET _POST value
	 *
	 * @param string $val Input
	 * @return string Cleaned Input
	 * @access protected
	 * @since 1.0.0
	 */
	protected function parseCleanValue( $val )
	{
		if ( $val == "" )
		{
			return "";
		}

		$val = str_replace( "&#032;", " ", $this->txtStripslashes($val) );

		$val = str_replace( chr(0xCA), "", $val );  //Remove sneaky spaces

		// As cool as this entity is...

		$val = str_replace( "&#8238;"       , ''              , $val );

		$val = str_replace( "&"             , "&amp;"         , $val );
		$val = str_replace( "<!--"          , "&#60;&#33;--"  , $val );
		$val = str_replace( "-->"           , "--&#62;"       , $val );
		$val = str_replace( ">"             , "&gt;"          , $val );
		$val = str_replace( "<"             , "&lt;"          , $val );
		$val = str_replace( '"'             , "&quot;"        , $val );
		$val = str_replace( "\n"            , "<br />"        , $val ); // Convert literal newlines
		$val = str_replace( "$"             , "&#036;"        , $val );
		$val = str_replace( "\r"            , ""              , $val ); // Remove literal carriage returns
		$val = str_replace( "!"             , "&#33;"         , $val );
		$val = str_replace( "'"             , "&#39;"         , $val ); // IMPORTANT: It helps to increase sql query safety.

		// Ensure unicode chars are OK

		$val = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $val );

		//-----------------------------------------
		// Try and fix up HTML entities with missing ;
		//-----------------------------------------

		$val = preg_replace( "/&#(\d+?)([^\d;])/i", "&#\\1;\\2", $val );

		// Check to see if the PHP version running is higher than or equal to 4.3.0...
		$val = mysql_real_escape_string( $val );

		return $val;
	}

	/**
	 * Convert back a string to HTML
	 *
	 * @param string $sting db string value
	 * @return string html
	 * @access public
	 * @since 1.0.0
	 */
	public function parseHTML( $string )
	{
		$string = str_replace( "<br>",   "\n" , $string );
		$string = str_replace( "<br />", "\n" , $string );
		$string = str_replace( "&#39;",  "'", $string );
		$string = str_replace( "&#33;",  "!", $string );
		$string = str_replace( "&#036;", "$", $string );
		$string = str_replace( "&#124;", "|", $string );
		$string = str_replace( "&amp;",  "&", $string );
		$string = str_replace( "&gt;",   ">", $string );
		$string = str_replace( "&lt;",   "<", $string );
		$string = str_replace( "&quot;", '"', $string );

		return $string;
	}

	/**
	 * Convert back a string to HTML (just line breaks and stuff
	 *
	 * @param string $sting db string value
	 * @return string html
	 * @access public
	 * @since 1.0.0
	 */
	public function parseHTMLtoEdit( $string )
	{
		$string = str_replace( "&#39;",  "'", $string );
		$string = str_replace( "\n",     "",  $string );
		$string = str_replace( "<br>",   "\n", $string );
		$string = str_replace( "<br />", "\n", $string );

		return $string;
	}

	/**
	 * Parse _GET _POST data
	 *
	 * Clean up and unHTML
	 *
	 * @return array the inputs
	 * @access protected
	 * @since 1.0.0
	 */
	protected function parseIncoming()
	{
		//-----------------------------------------
		// Attempt to switch off magic quotes
		//-----------------------------------------

		$this->display->addDebug( "Parse Incoming Variables Start" );

		@set_magic_quotes_runtime(0);

		$this->getMagicQuotes = @get_magic_quotes_gpc();

		//-----------------------------------------
		// Clean globals, first.
		//-----------------------------------------

		$this->cleanGlobals( $_GET );
		$this->cleanGlobals( $_POST );
		$this->cleanGlobals( $_COOKIE );
		$this->cleanGlobals( $_REQUEST );

		# Find a session ID in the URI
		$input = $this->parseURI( array() );

		# GET first
		$input = $this->parseIncomingRecursively( $_GET, $input );

		# Then overwrite with POST
		$input = $this->parseIncomingRecursively( $_POST, $input );

		# Then add JSON input
		if ( $this->getHeader('Content-Type') == 'application/json' )
		{
			$temp = json_decode( file_get_contents('php://input'), true );

			if ( is_array( $temp ) )
			{
				$input = $this->parseIncomingRecursively( $temp, $input );
			}
		}

		# Then process authorization header
		if ( isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) && strlen( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) > 0 )
		{
			$auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];

			if ( substr( $auth, 0, 5 ) == "Basic" )
			{
				$auth = substr( $auth, 6 );
				$auth = base64_decode( $auth );

				$sep = strpos( $auth, ':' );
				$email = substr( $auth, 0, $sep );
				$session = substr( $auth, $sep + 1 );

				$array = array( 'email' => $email, 's' => $session );

				$input = $this->parseIncomingRecursively( $array, $input );
			}
		}

		$this->display->addDebug( "Parse Incoming Variables Completed" );

		return $input;
	}

	/**
	 * Recursively cleans keys and values and
	 * inserts them into the input array
	 *
	 * @param array $data the data to be processed
	 * @param array $input [Deprecated]
	 * @param int $iteration counter to keep from going too deep into the array
	 * @return array inputs processed
	 * @access protected
	 * @since 1.0.0
	 */
	protected function parseIncomingRecursively( $data, $input=array(), $iteration = 0 )
	{
		// Crafty hacker could send something like &foo[][][][][][]....to kill Apache process
		// We should never have an input array deeper than 10..

		if ( $iteration >= 10 )
		{
			return $input;
		}

		if ( count( $data ) )
		{
			foreach( $data as $k => $v )
			{
				if ( is_array( $v ) )
				{
					//$input = $this->parseIncomingRecursively( $data[ $k ], $input );
					$input[ $k ] = $this->parseIncomingRecursively( $data[ $k ], array(), $iteration+1 );
				}
				else
				{	
					$k = $this->parseCleanKey( $k );
					$v = $this->parseCleanValue( $v );

					$input[ $k ] = $v;
				}
			}
		}

		return $input;
	}

	/**
	 * Find a session ID in a SEO URI
	 *
	 * @param array $input [Deprecated]
	 * @return array inputs processed
	 * @access protected
	 * @since 1.0.0
	 */
	protected function parseURI( $input = array() )
	{
		$out = array();

		// Check that we're supposed to be doing that
		if ( $this->getSetting('seo_url') == 1 )
		{
			//-----------------------------------------
			// Get request uri
			//-----------------------------------------

			if ( substr( $this->getConfig('base_url'), 0, 5 ) == "https" )
			{
				$host   = substr( $this->getConfig('base_url'), 8, -strlen( $this->getConfig('base_uri') ) );
			}
			else
			{
				$host   = substr( $this->getConfig('base_url'), 7, -strlen( $this->getConfig('base_uri') ) );
			}
			
			$marker = '/' . $this->getConfig('seo_marker') . '/';
			$left   = strlen( $this->getConfig('base_uri') );

			$uri    = substr( $_SERVER['REQUEST_URI'], $left );
			$inputs = "";

			$this->display->addDebug( array( 'check_point' => 1, 'host' => $host, 'marker' => $marker, 'uri' => $uri ) );

			//-----------------------------------------
			// Redirect if trailing & is present
			//-----------------------------------------

			if ( strlen ( $uri ) > 0 && substr( $uri, strlen( $uri ) - 1, 1 ) == '&' )
			{
				header($_SERVER["SERVER_PROTOCOL"]." 301 Moved Permanently");
				header( 'Location: ' . $this->getConfig('base_url') . substr( $uri, 0, strlen( $uri ) - 1 ) );
				exit();
			}

			//-----------------------------------------
			// Redirect if trailing slash is missing
			//-----------------------------------------

			if ( strlen ( $uri ) > 0 && substr( $uri, strlen( $uri ) - 1, 1 ) != '/' && substr( $uri, strlen( $uri ) - 4, 4 ) != '.xml' )
			{
				header($_SERVER["SERVER_PROTOCOL"]." 301 Moved Permanently");
				header( 'Location: ' . $this->getConfig('base_url') . $uri . '/' );
				exit();
			}

			//-----------------------------------------
			// Redirect if host doesn't match ... such as www. missing
			//-----------------------------------------

			if ( $_SERVER['HTTP_HOST'] != $host )
			{
				header($_SERVER["SERVER_PROTOCOL"]." 301 Moved Permanently");
				header( 'Location: ' . $this->getConfig('base_url') . $uri );
				exit();
			}

			//-----------------------------------------
			// Does URI have db marker in it
			//-----------------------------------------

			if ( strpos( $uri, $marker ) !== false )
			{
				$inputs = substr( $uri, ( strpos( $uri, $marker ) + strlen( $marker ) ) );
				$uri    = substr( $uri, 0, strpos( $uri, $marker ) );
			}
			else if( substr( $uri, 0, 2 ) == $this->getConfig('seo_marker') . '/' )
			{
				$inputs = substr( $uri, 2 );
				$uri    = '';
			}

			//-----------------------------------------
			// Trim off slashes, if present
			//-----------------------------------------

			$uri    = rtrim( $uri,    '/' );
			$inputs = ltrim( $inputs, '/' );
			$inputs = rtrim( $inputs, '/' );

			if ( SWS_THIS_APPLICATION != 'public' && strlen( $uri ) > strlen( SWS_THIS_APPLICATION ) )
			{
				$uri = substr( $uri, strlen( SWS_THIS_APPLICATION ) + 1 );
			}
			else if ( SWS_THIS_APPLICATION != 'public' && $uri == SWS_THIS_APPLICATION )
			{
				$uri = '';
			}

			$this->display->addDebug( array( 'check_point' => 2, 'uri' => $uri, 'inputs' => $inputs ) );

			//-----------------------------------------
			// Sort out folders
			//-----------------------------------------

			$uriBits   = explode( '/', $uri );
			$inputBits = explode( '/', $inputs );
			$extraBits = array();

			if( strlen( $uri ) > 0 && is_array( $uriBits ) && count( $uriBits ) > 0 )
			{
				$out = $this->application->parseSEOURI( $uriBits );
			}

			// Parse the input bits as actual input
			if ( is_array( $inputBits ) && count( $inputBits ) > 0 )
			{
				foreach( $inputBits as $bit )
				{
					if ( strpos( $bit, $this->getConfig('seo_param_sep') ) !== false )
					{
						$key   = substr( $bit, 0, strpos( $bit, $this->getConfig('seo_param_sep') ) );
						$value = substr( $bit, (  strpos( $bit, $this->getConfig('seo_param_sep') ) + strlen( $this->getConfig('seo_param_sep') - 1 ) ) );

						$key   = $this->parseCleanKey( $key );
						$value = $this->parseCleanValue( $value );

						if ( strlen( $key ) > 0 && strlen( $value ) > 0 )
						{
							$out[ $key ] = $value;
						}
					}
					else
					{
						if ( strlen( $bit ) > 0 )
						{
							$extraBits[] = $this->parseCleanValue( $bit );
						}
					}
				}
			}

			if ( count( $extraBits ) > 0 )
			{
				$out['extra'] = $extraBits;
			}
		}

		return $out;
	}

	/**
	 * Sets the user
	 *
	 * @param User $user the user
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setUser( $user )
	{
		$this->user = $user;
	}

	/**
	 * Make sure we load the correct application
	 *
	 * @return object the application admin|public
	 * @access public
	 * @since 1.0.0
	 */
	private function setupApplication()
	{
		$this->application = $this->loadApplication( SWS_THIS_APPLICATION );

		if ( ! is_object( $this->application ) )
		{
			$this->error->raiseError( 'application_not_found', TRUE );
		}
	}

	/**
	 * Remove slashes if magic_quotes enabled
	 *
	 * @param string $t Input String
	 * @return string Parsed string
	 * @access public
	 * @since 1.0.0
	 */
	public function txtStripslashes($t)
	{
		if ( $this->getMagicQuotes )
		{
			$t = stripslashes($t);
			$t = preg_replace( "/\\\(?!&amp;#|\?#)/", "&#092;", $t );
		}
		
		return $t;
	}

	/**
	 * Permanetly updates a setting in the database
	 *
	 * @param string $key the setting key
	 * @param string $value the setting value
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function updateSetting( $key, $value )
	{
		if ( isset( $this->settings[ $key ] ) )
		{
			$this->settings[ $key ] = $value;
		}

		Setting::update( $key, $value );
	}
}

?>