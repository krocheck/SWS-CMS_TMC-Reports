<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Display class to handle all HTML output
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

class Display
{
	/**
	 * The application registry library
	 *
	 * @access protected
	 * @var Registry
	 * @since 1.0.0
	 */
	protected $registry;
	/**
	 * Array of the loaded template classes
	 *
	 * @access public
	 * @var array
	 * @since 1.0.0
	 */
	public $templates = array();
	/**
	 * Navigation breadcrumb links
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $breadcrumb        = array();
	/**
	 * DEBUG PRINTOUT
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $debug             = array();
	/**
	 * Navigation tab links
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $navigation        = array();
	/**
	 * The body content for the page
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $content;
	/**
	 * The final html for print
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $html;
	/**
	 * The json content for the page
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $json              = array();
	/**
	 * The head title for the page
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $title;
	/**
	 * includes to be included in the header
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $js;

	/**
	 * Constructor that loads the registry
	 *
	 * @param Registry $registry the main program registry
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct( Registry $registry )
	{
		$this->registry = $registry;
		
		$this->addDebug( "Display Library Loaded" );
	}

	/**
	 * Add breadcrumb
	 *
	 * @param string $uri everything that needs to be added to the base url
	 * @param string $string the text to accompany the link
	 * @param string $css class name to apply to item
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addBreadcrumb( $url, $string, $css = '' )
	{
		$add = array( 'url' => $url, 'string' => $string );
		
		if ( $css != '' )
		{
			$add['css'] = $css;
		}
		
		$this->breadcrumb[] = $add;
	}

	/**
	 * Add content
	 *
	 * @param string $content content to add
	 * @param boolean $prepend Prepend instead of append
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addContent( $content, $prepend=FALSE )
	{
		if( $prepend )
		{
			$this->content = $content . $this->content;
		}
		else
		{
			$this->content .= $content;
		}
	}

	/**
	 * Add debug line
	 *
	 * @param string $string the text to show in debug
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addDebug( $string )
	{
		//if ( DEBUG )
		//{
			$this->debug[ microtime() ] = $string;
		//}
	}
	
	/**
	 * Add Javascript
	 *
	 * @param string $javascript content to add
	 * @param boolean $prepend Prepend instead of append
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addJavascript( $js, $prepend=FALSE )
	{
		if( $prepend )
		{
			$this->js = $js . $this->js;
		}
		else
		{
			$this->js .= $js;
		}
	}

	/**
	 * Add json
	 *
	 * @param string $key key to add
	 * @param string $value value to add
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addJSON( $key, $value )
	{
		if ( $key == 'status' && $value == 'not_found' )
		{
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		}
		else if ( $key == 'status' && $value == 'forbidden' )
		{
			header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized");
		}

		$this->json[ $key ] = $value;
	}

	/**
	 * Add navigation tab
	 *
	 * @param array $pages nested array
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addNavigation( $pages )
	{
		$this->navigation[] = $pages;
	}

	/**
	 * Add navigation tab
	 *
	 * @param array $pages nested array
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addTitle( $text )
	{
		if ( strlen( $text ) > 0 )
		{
			$this->title .= $this->registry->getLang()->getString('title_sep') . $text;
		}
	}

	/**
	 * Makes the logged in display
	 *
	 * @return string html of the area
	 * @access private
	 * @since 1.0.0
	 */
	private function buildLoggedIn()
	{
		$id   = $this->registry->getUser()->getID();
		$name = $this->registry->getUser()->getFirstName() ." " .$this->registry->getUser()->getLastName();
		
		return $this->compiledTemplates('skin_'.SWS_THIS_APPLICATION)->loggedIn( $id, $name );
	}

	/**
	 * Build a proper url with all the good stuff
	 *
	 * @param array $params key=>val array to append to url
	 * @param string $app string of app name if other than public
	 * @return string of the new url
	 * @access public
	 * @since 1.0.0
	 */
	public function buildURL( $params = array(), $app = "" )
	{
		$user      = $this->registry->getUser();
		$sessionID = '';

		if ( is_object( $user ) )
		{
			$sessionID = $user->getSessionID();
		}

		$appAdd    = $app ? "{$app}/" : "";
		$url       = $this->registry->getConfig('base_url') . "{$appAdd}";
		$app       = $app == '' ? 'public' : $app;

		if ( $app != SWS_THIS_APPLICATION )
		{
			$sessionID = '';
		}
		else if ( strlen( $sessionID ) == 32 && strlen( $this->registry->getCookie()->getCookie(SWS_THIS_APPLICATION) ) != 32 )
		{
			$params['s'] = $sessionID;
		}

		if( $this->registry->getSetting('seo_url') == 1 )
		{
			$app = $this->registry->getApp( $app );

			if ( is_object( $app ) )
			{
				$back = $app->buildSEOURI( $params );

				if ( is_array( $back ) && count( $back ) == 2 )
				{
					$url   .= $back['uri'];
					$params = $back['params'];
				}
			}
		}
		else
		{
			$url .= "index.php?";
		}

		if ( count( $params ) > 0 )
		{
			if ( $this->registry->getSetting('seo_url') == 1 )
			{
				$url .= $this->registry->getConfig('seo_marker') . '/';
			}

			if ( isset( $params['extra'] ) && is_array( $params['extra'] ) )
			{
				if ( count( $params['extra'] ) > 0 && $this->registry->getSetting('seo_url') == 1 )
				{
					foreach( $params['extra'] as $v )
					{
						$url .= "{$v}/";
					}
				}
				else if ( $this->registry->getSetting('seo_url') == 1 ) {}
				else
				{
					$url .= "extra=".serialize( $params['extra'] )."&amp;";
				}

				unset( $params['extra'] );
			}

			foreach( $params as $k => $v )
			{
				if ( $this->registry->getSetting('seo_url') == 1 )
				{
					$url .= "{$k}{$this->registry->getConfig('seo_param_sep')}{$v}/";
				}
				else
				{
					$url .= "{$k}={$v}&amp;";
				}
			}
		}

		return $url;
	}

	/**
	 * Returns a template file and initializes the object into the templates array if needed
	 *
	 * @param string $key the template code
	 * @return object skin class
	 * @access public
	 * @since 1.0.0
	 */
	public function compiledTemplates( $key )
	{
		$out = NULL;

		if ( isset( $this->templates[ $key ] ) && is_object( $this->templates[ $key ] ) )
		{
			$out = $this->templates[ $key ];
		}
		else
		{
			$out = $this->loadTemplates( $key );
		}

		return $out;
	}

	/**
	 * Compiles the error page, prints, and exits
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function doError()
	{
		$debug = "";
		
		if ( DEBUG )
		{
			$debug = $this->getDebugOutput();
		}
		
		$this->html = $this->compiledTemplates('skin_global')->errorWrapper( $this->title, $this->content, $debug );
		
		print( $this->html );
		
		$this->registry->cleanUp();
		
		exit;
	}

	/**
	 * Compiles the json output, prints, and exits
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function doJSON()
	{
		header('Content-type: application/json');
		
		$this->registry->cleanUp();
		
		if ( DEBUG )
		{
			$this->json['debug'] = $this->getDebugJSON();
		}
		
		$this->html = json_encode($this->json);
		
		print( $this->html );
		
		exit;
	}

	/**
	 * Compiles the page, prints, and exits
	 *
	 * @param string $css class name to apply to the content block
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function doOutput( $css = '' )
	{
		$navigation = "";
		$breadcrumb = "";
		$userlinks  = "";
		$loggedIn   = "";
		$errors     = "";
		$debug      = "";

		if ( is_object( $this->registry->getUser() ) )
		{
			$loggedIn   = $this->buildLoggedIn();
		}

		$userlinks  = $this->registry->getApp()->buildUserLinks();

		if ( is_array( $this->registry->getError()->getErrors() ) && count( $this->registry->getError()->getErrors() ) > 0 )
		{
			foreach( $this->registry->getError()->getErrors() as $val )
			{
				$errors .= "<li>" . $val . "</li>";
			}
		}

		$navigation = $this->compiledTemplates('skin_'.SWS_THIS_APPLICATION)->navigationWrapper( $this->navigation );
		$breadcrumb = $this->compiledTemplates('skin_'.SWS_THIS_APPLICATION)->breadcrumbWrapper( $this->breadcrumb );

		$this->registry->cleanUp();

		if ( DEBUG )
		{
			$debug = $this->getDebugOutput();
		}

		if ( substr( $this->title, -3, 3 ) == $this->registry->getLang()->getString('title_sep') )
		{
			$this->title = substr( $this->title, 0, -3 );
		}

		if ( $this->registry->getInput('do') == 'pdf' )
		{
			require_once(SWS_VENDOR_PATH . 'autoload.php');
			$mpdf = new \Mpdf\Mpdf(['tempDir' => '/tmp']);
			$style = file_get_contents(SWS_STYLE_PATH.'design.css');
			$mpdf->WriteHTML( $style, \Mpdf\HTMLParserMode::HEADER_CSS );
			$mpdf->WriteHTML( $this->content, \Mpdf\HTMLParserMode::HTML_BODY );
			$mpdf->Output();
		}
		else
		{
			$this->html = $this->compiledTemplates('skin_'.SWS_THIS_APPLICATION)->wrapper( $this->title, $navigation, $breadcrumb, $userlinks, $loggedIn, $this->content, $css, $errors, $debug, $this->js );
			print( $this->html );
		}
	}

	/**
	 * Gets all the necessary debug info
	 *
	 * @return array the info
	 * @access public
	 * @since 1.0.0
	 */
	public function getDebugJSON()
	{
		$out = array();

		$out['sql'] = $this->registry->getDB()->getQueries();
		$out['input'] = $this->registry->getInputs();
		$out['server'] = $_SERVER;
		$out['debug'] = $this->debug;

		return $out;
	}

	/**
	 * Gets all the necessary debug info
	 *
	 * @return string the info
	 * @access public
	 * @since 1.0.0
	 */
	public function getDebugOutput()
	{
		$out = "";

		ob_start();

		print("<div id='debug'>\n\t<fieldset>\n\t\t<legend>DEBUG</legend>\n\t\t<div>\n\t\t<h2>SQL</h2>\n\t\t<pre>");
		print_r($this->registry->getDB()->getQueries());
		print("\t\t</pre>\n\t\t<h2>Input</h2>\n\t\t<pre>");
		print_r($this->registry->getInputs());
		print("\t\t</pre>\n\t\t<h2>SERVER</h2>\n\t\t<pre>");
		print_r($_SERVER);
		print("\t\t</pre>\n\t\t<h2>DEBUG-CODE</h2>\n\t\t<pre>");
		print_r($this->debug);
		print("\t\t</pre>\n\t\t</div>\n\t</fieldset>\n</div>");

		$out = ob_get_contents();

		ob_end_clean();

		return $out;
	}

	/**
	 * Gets the navigation HTML, used if navigation needs to be embedded in design
	 *
	 * @return string the HTML
	 * @access public
	 * @since 1.0.0
	 */
	public function getErrorHTML()
	{
		$out = "";

		if ( is_array( $this->registry->getError()->getErrors() ) && count( $this->registry->getError()->getErrors() ) > 0 )
		{
			$out .= "<ul class='error'>";

			foreach( $this->registry->getError()->getErrors() as $val )
			{
				$out .= "<li>" . $val . "</li>";
			}

			$out .= "</ul>";
		}

		return $out;
	}

	/**
	 * Gets the navigation array
	 *
	 * @return array the navigation pieces
	 * @access public
	 * @since 1.0.0
	 */
	public function getNavigation()
	{
		return $this->navigation;
	}

	/**
	 * Gets the navigation HTML, used if navigation needs to be embedded in design
	 *
	 * @return string the HTML
	 * @access public
	 * @since 1.0.0
	 */
	public function getNavigationHTML()
	{
		$out = "";

		if ( count( $this->navigation ) > 0 )
		{
			$out = $this->compiledTemplates('skin_'.SWS_THIS_APPLICATION)->navigationWrapper( $this->navigation );
		}

		return $out;
	}

	/**
	 * Build pages to keep things organized
	 *
	 * @param string $table the table to count
	 * @param array $params link parameters
	 * @param string $app the app name if other than public
	 * @param string $where additional query info
	 * @return string html
	 * @access public
	 * @since 1.0.0
	 */
	public function getPagelinks( $table, $params, $app = "", $where = '' )
	{
		$out = "";

		// Establish the page number
		$pageNumber = is_numeric( $this->registry->getInput('page') ) ? $this->registry->getInput('page') : 1;

		// Establish default results per page
		$perPage = $this->registry->getSetting('items_per_page');

		// Establish a padding value
		$padding = $this->registry->getSetting('padding');

		// Get total number of database entires
		$this->registry->getDB()->query( "SELECT COUNT(*) AS count FROM {$table} {$where}" );
		$count = $this->registry->getDB()->fetchRow();

		if( $count['count'] <= $perPage)
		{
			$pageNumber = '';
			$firstPage  = '';
			$lastPage   = '';
			$nextPage   = '';
		}
		else
		{
			// Get total number of pages
			$numOfPages = ceil($count['count'] / $perPage);

			// If there is only one page result let's get rid of First otherwise output it
			$params['page'] = 1;
			$baselink = $this->buildURL( $params, $app );
			$firstPage = (($count['count'] > $perPage) && ($pageNumber > 1)) ? "<a href='{$baselink}'>{$this->registry->getLang()->getString('first')}</a> " : "";

			// Here will will generate our 'previous' link...
			$previousValue = ($pageNumber - 1);
			$params['page'] = $previousValue;
			$baselink = $this->buildURL( $params, $app );
			$previousPage = ($pageNumber <= $numOfPages && $pageNumber > 1) ? " <a href='{$baselink}'>{$this->registry->getLang()->getString('previous')}</a> " : "";

			// If value is greater than 1...
			if ( ( $pageNumber - $padding ) > 1)
			{
				$lowerLimit = $pageNumber - $padding;

				// Print all padded numbers between lowerLimit and current page
				$pageLinks.= '...';

				for($i = $lowerLimit; $i < $pageNumber; $i++)
				{
					$params['page'] = $i;
					$baselink = $this->buildURL( $params, $app );
					$pageLinks.= " <a href='{$baselink}'>{$i}</a> ";
				}
			}
			else
			{
				// Print all numbers between current page and first page
				for($i = 1; $i < $pageNumber; $i++)
				{
					$params['page'] = $i;
					$baselink = $this->buildURL( $params, $app );
					$pageLinks.= " <a href='{$baselink}'>{$i}</a> ";
				}
			}

			// Let's print out the current page
			$pageLinks.= "<b>" .$pageNumber. "</b>";

			// If our current page, plus our padding, is less than the total number of pages
			if(($pageNumber + $padding) < $numOfPages)
			{
				// Set upper limit
				$upperLimit = $pageNumber + $padding;

				// Print all numbers from padded pages above current page
				for($i = ($pageNumber + 1); $i <= $upperLimit; $i++)
				{
					$params['page'] = $i;
					$baselink = $this->buildURL( $params, $app );
					$pageLinks.= " <a href='{$baselink}'>{$i}</a> ";
				}

				$pageLinks.= '...';
			}
			else
			{
				// Print all page numbers between number of pages and current page
				for($i = ($pageNumber + 1); $i <= $numOfPages; $i++)
				{
					$params['page'] = $i;
					$baselink = $this->buildURL( $params, $app );
					$pageLinks.= " <a href='{$baselink}'>{$i}</a> ";
				}
			}

			// Here will will generate our 'next' link...
			$nextValue = ($pageNumber += 1);
			$params['page'] = $nextValue;
			$baselink = $this->buildURL( $params, $app );
			$nextPage = ($numOfPages >= $pageNumber) ? " <a href='{$baselink}'>{$this->registry->getLang()->getString('next')}</a> " : "";

			// If there is only one page result let's get rid of last otherwise output it
			$params['page'] = $numOfPages;
			$baselink = $this->buildURL( $params, $app );
			$lastPage = (($count['count'] > $perPage) && ($numOfPages >= $pageNumber)) ? " <a href='{$baselink}'>{$this->registry->getLang()->getString('last')}</a>" : "";

			$out = $firstPage . $previousPage . $pageLinks . $nextPage . $lastPage;
		}
		
		return $out;
	}

	/**
	 * Loads a template file and initializes the object into the templates array
	 *
	 * @param string $key the template code
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function loadTemplates( $key )
	{
		$out = NULL;

		if ( ! isset( $this->templates[ $key ] ) || ! is_object( $this->templates[ $key ] ) )
		{
			if ( file_exists( SWS_SKIN_PATH . strtolower($key) . '.php' ) )
			{
				require_once( SWS_SKIN_PATH . strtolower($key) . '.php' );
				
				if ( class_exists( $key ) )
				{
					$this->templates[ $key ] = new $key();

					$this->templates[ $key ]->execute( $this->registry );

					$out = $this->templates[ $key ];

					$this->addDebug( "Templates Loaded: {$key}" );
				}
			}
		}

		if ( ! isset( $this->templates[ $key ] ) && ! is_object( $this->templates[ $key ] ) )
		{
			if ( is_object( $this->registry->getError() ) )
			{
				$this->registry->getError()->raiseError( 'template_file_missing', TRUE, array( 'skin' => $key ) );
			}
			else
			{
				echo( 'FATAL ERROR!  Template not found.  Please notify the administrator at:  temp@localhost' );
				exit();
			}
		}

		return $out;
	}

	/**
	 * Set page content
	 *
	 * @param string $content new content
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setContent( $content )
	{
		$this->content = $content;
	}

	/**
	 * Set javascript includes
	 *
	 * @param string $js for javascript
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setJavascript( $js )
	{
		$this->js = $js;
	}

	/**
	 * Set json content
	 *
	 * @param string $json for json
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setJSON( $json )
	{
		$this->json = $json;
	}

	/**
	 * Set page title
	 *
	 * @param string $title new title
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setTitle( $title )
	{
		if ( substr( $title, 0, strlen( $this->registry->getLang()->getString('title_sep') ) ) != $this->registry->getLang()->getString('title_sep') )
		{
			$title = $this->registry->getLang()->getString('title_sep') . $title;
		}

		$this->title = $this->registry->getLang()->getString('site_title') . $title;
	}

	/**
	 * Qucikly redirects the user to a new page
	 *
	 * @param string $stringURL
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function silentRedirect( $stringURL )
	{
		header('Location: '. $stringURL);
		exit();
	}
}

?>