<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Page content class
 * Last Updated: $Date: 2010-07-02 09:29:16 -0500 (Fri, 02 Jul 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Page
 * @link		http://www.simnaweb.com
 * @abstract
 * @since		1.0.0
 * @version		$Revision: 31 $
 */

abstract class Page extends Command
{
	/**
	 * The page id
	 *
	 * @access protected
	 * @var int
	 * @since 1.0.0
	 */
	protected $id;
	/**
	 * The enabled flag
	 *
	 * @access protected
	 * @var bool
	 * @since 1.0.0
	 */
	protected $active;
	/**
	 * The page's children objects
	 *
	 * @access protected
	 * @var Page[]
	 * @since 1.0.0
	 */
	protected $children;
	/**
	 * The controller
	 *
	 * @access protected
	 * @var PageController
	 * @since 1.0.0
	 */
	protected $controller;
	/**
	 * The name of the page
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $name;
	/**
	 * The name of the page
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $navigation;
	/**
	 * The name of the page
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $navigationText;
	/**
	 * The page's parent id
	 *
	 * @access protected
	 * @var int
	 * @since 1.0.0
	 */
	protected $parentID;
	/**
	 * The page position #
	 *
	 * @access protected
	 * @var int
	 * @since 1.0.0
	 */
	protected $position;
	/**
	 * The show name in title flag
	 *
	 * @access protected
	 * @var bool
	 * @since 1.0.0
	 */
	protected $showTitle;
	/**
	 * The URI for full URLs
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $uri;

	/**
	 * Constructor that loads the registry
	 *
	 * @param array $dbRow array of the language values
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doExecute( $dbRow )
	{
		$this->id                 = $dbRow['page_id'];
		$this->active             = $dbRow['metadata'][ $this->lang->getLanguageID() ]['active']['value'];
		$this->name               = $dbRow['metadata'][ $this->lang->getLanguageID() ]['name']['value'];
		$this->navigation         = $dbRow['metadata'][ $this->lang->getLanguageID() ]['navigation']['value'];
		$this->navigationText     = $dbRow['metadata'][ $this->lang->getLanguageID() ]['navigation_text']['value'];
		$this->uri                = $dbRow['metadata'][ $this->lang->getLanguageID() ]['uri']['value'];
		$this->showTitle          = $dbRow['metadata'][ $this->lang->getLanguageID() ]['show_title']['value'];
		$this->position           = $dbRow['position'];
		$this->parentID           = $dbRow['parent_id'];
		$this->controller         = $dbRow['controller'];
	}

	/**
	 * Build the necessary nested array for the navigation menu (recurrsive)
	 *
	 * @param int $id the current page id
	 * @return array structured nested arrays of the links
	 * @access public
	 * @since 1.0.0
	 */
	public function buildNavigation( $id, $depth = 0, $count = 0 )
	{
		$out = NULL;
		$current = false;
		
		if ( $this->active == 1 && $this->navigation == 1 )
		{
			$css     = '';
			$current = '';
			$string  = '';
			$url     = '';
			
			if ( $this->type == 'search' )
			{
				$css = 'search';
			}
			
			if ( is_array( $this->children ) && count( $this->children ) > 0 )
			{
				$innerCount = 0;

				foreach( $this->children as $k => $v )
				{
					$back = $v->buildNavigation( $id, ($depth + 1), $innerCount );
					
					if ( is_array( $back ) && count( $back ) > 0 )
					{
						if ( count( $back ) > 1 )
						{
							$extra[] = $back;
						}
						
						if ( $back['current'] != "" )
						{
							$current = 'current';
						}
						
						$innerCount++;
					}
				}
			}

			if ( $id == $this->id )
			{
				$current = 'current';
			}

			$url    = $this->display->buildURL( array( 'page_id' => $this->id ) );
			$string = $this->navigationText;

			$out = array( 'url' => $url, 'string' => $string, 'extra' => $extra, 'current' => $current, 'css' => $css );
		}
		
		return $out;
	}

	/**
	 * Searches a child by page id (recurrsive)
	 *
	 * @param int $id the current page id
	 * @return Page the found child
	 * @access public
	 * @since 1.0.0
	 */
	public function buildSEOURI( $id )
	{
		$out = "";

		if ( count( $this->children ) > 0 )
		{
			foreach( $this->children as $v )
			{
				$out = $v->buildSEOURI( $id );

				if ( $out != "" )
				{
					break;
				}
			}
		}

		if ( $id == $this->id || $out != "" )
		{
			$out = $this->uri . '/' . $out;
		}

		return $out;
	}

	/**
	 * Builds the title using the parents titles when enabled
	 *
	 * @return string the complete title
	 * @access public
	 * @since 1.0.0
	 */
	public function buildTitle()
	{
		$active  = NULL;
		$link    = "";
		$pages   = array();

		$active  = $this;
		$pages[] = $active;

		if ( is_object($active) )
		{
			$this->display->addDebug('Title - Outer - '.$active->getID() );

			while ( $active->getParentID() != 0 )
			{
				$this->display->addDebug('Title - Inner - '.$active->getParentID() );
				$active  = $this->controller->getPage( $active->getParentID() );
				$pages[] = $active;
			}
		}

		for ($i = count( $pages ) - 1; $i >= 0; $i-- )
		{
			if ( $pages[ $i ]->getTitle() != "" )
			{
				$link .= $this->lang->getString('title_sep') . $pages[ $i ]->getTitle();
			}
		}

		return $link;
	}

	/**
	 * Inserts a new page into the database
	 *
	 * @param array $params an array of the keys and values
	 * @return bool true or false depending on success
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function create( $params )
	{
		$fieldList = array( 'type', 'languages', 'position', 'parent_id', 'last_update' );
		$keys = "";
		$values = "";
		
		foreach( $params as $k => $v )
		{
			if ( in_array( $k, $fieldList ) )
			{
				$keys .= "{$k},";
				$values .= "'{$v}',";
			}
		}
		
		Registry::$instance->DB->query(
			"INSERT INTO page (" . substr($keys,0,strlen($keys)-1) . ") VALUES (" . substr($values,0,strlen($values)-1) . ");"
		);
		
		return TRUE;
	}

	/**
	 * Searches a child by page id (recurrsive)
	 *
	 * @param int $id the current page id
	 * @return Page the found child
	 * @access public
	 * @since 1.0.0
	 */
	public function findChild( $id )
	{
		$out = NULL;
		
		if ( count( $this->children ) > 0 )
		{
			foreach( $this->children as $v )
			{
				if ( $v->getID() == $id )
				{
					$out = $v;
				}
				else
				{
					$out = $v->findChild( $id );
				}
				
				if ( is_object( $out ) && $out->getID() == $id )
				{
					break;
				}
			}
		}
		
		return $out;
	}

	/**
	 * Searches for a page from a uri array (recurrsive)
	 *
	 * @param array $uri the tokenized uri
	 * @param int $iteration the index to use in the array
	 * @return int the page ID
	 * @access public
	 * @since 1.0.0
	 */
	public function findPageFromURI( $uri, $iteration )
	{
		$out = 0;

		if ( isset( $uri[ $iteration ] ) )
		{
			if ( $this->uri == $uri[ $iteration ] )
			{
				if ( count( $uri ) == ( $iteration + 1 ) )
				{
					$out = $this->id;
				}
				else
				{
					foreach( $this->children as $v )
					{
						$out = $v->findPageFromURI( $uri, $iteration + 1 );

						if ( $out > 0 )
						{
							break;
						}
					}
				}
			}
		}

		return $out;
	}

	/**
	 * Returns the active flag
	 *
	 * @return bool
	 * @access public
	 * @since 1.0.0
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * Returns the page's id
	 *
	 * @return int
	 * @access public
	 * @since 1.0.0
	 */
	public function getID()
	{
		return $this->id;
	}

	/**
	 * Returns the page's name
	 *
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the page's navigation flag
	 *
	 * @return bool
	 * @access public
	 * @since 1.0.0
	 */
	public function getNavigation()
	{
		return $this->navigation;
	}

	/**
	 * Returns the page's navigation text
	 *
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function getNavigationText()
	{
		return $this->navigationText;
	}

	/**
	 * Returns the page's parent id
	 *
	 * @return int
	 * @access public
	 * @since 1.0.0
	 */
	public function getParentID()
	{
		return $this->parentID;
	}

	/**
	 * Returns the page's position
	 *
	 * @return int
	 * @access public
	 * @since 1.0.0
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 * Returns the page's section
	 *
	 * @return Section
	 * @access public
	 * @since 1.0.0
	 */
	public function getSection()
	{
		return $this->section;
	}

	/**
	 * Returns the page's section id
	 *
	 * @return int
	 * @access public
	 * @since 1.0.0
	 */
	public function getSectionID()
	{
		return $this->sectionID;
	}

	/**
	 * Returns the page's show title flag
	 *
	 * @return bool
	 * @access public
	 * @since 1.0.0
	 */
	public function getShowTitle()
	{
		return $this->showTitle;
	}

	/**
	 * Returns the page's title if enabled
	 *
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function getTitle()
	{
		$out = "";
		
		if ( $this->showTitle == 1 )
		{
			$out = $this->name;
		}

		return $out;
	}

	/**
	 * Returns the type string
	 *
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Returns the uri string
	 *
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function getURI()
	{
		return $this->uri;
	}

	/**
	 * Initializes the children (recurrsive)
	 *
	 * @param array $pages page cache
	 * @param array $types the page types
	 * @return Page the required page
	 * @access public
	 * @since 1.0.0
	 */
	public function loadChildren( $pages, $types )
	{
		$this->children  = array();
		$this->display->addDebug( "Page Loaded: #{$this->id}" );
		
		if ( is_array( $pages ) && isset( $pages[ $this->id ] ) && is_array( $pages[ $this->id ] ) && count( $pages[ $this->id ] ) > 0 )
		{
			foreach( $pages[ $this->id ] as $k => $v )
			{
				if ( isset( $types[ $v['type'] ] ) )
				{
					$v['controller'] = $this->controller;
					
					$this->children[ $v['page_id'] ] = new $types[ $v['type'] ][0]();
					$this->children[ $v['page_id'] ]->execute( $this->registry, $v );
					$this->children[ $v['page_id'] ]->loadChildren( $pages, $types );
				}
			}
		}
	}

	/**
	 * Pre-Processes the page contents then call type's process function.
	 *
	 * @return array the page's metadata in the current language
	 * @access public
	 * @since 1.0.0
	 */
	public function process()
	{
		$meta = "";
		
		$this->display->loadTemplates('skin_'.$this->type);
		
		if ( ! ($this->active == 1) )
		{
			$this->error->raiseError( 'invalid_id', FALSE );
			return;
		}
		
		$this->controller->buildNavigation( $this->id );
		$this->display->setTitle( $this->buildTitle() );
		
		$this->DB->query("SELECT * FROM metadata_page WHERE language_id = '{$this->lang->getLanguageID()}' AND id = '{$this->id}';");
		
		while( $r = $this->DB->fetchRow() )
		{
			$meta[ $r['meta_key'] ] = $r;
		}
		
		$this->processPage( $meta );
	}

	/**
	 * MUST BE OVERRIDDEN: Process the page contents and set output
	 *
	 * @param array $metadata the metadata
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected abstract function processPage( $metadata );
}

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Page content type class
 * Last Updated: $Date: 2010-07-02 09:29:16 -0500 (Fri, 02 Jul 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Page
 * @link		http://www.simnaweb.com
 * @abstract
 * @since		1.0.0
 * @version		$Revision: 31 $
 */

abstract class PageType extends Command
{

	/**
	 * The metadata setup: name, type, input, etc.
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $metadata = array(
		'name'                => '',
		'uri'                 => '',
		'active'              => 0,
		'show_title'          => 1,
		'navigation'          => 0,
		'navigation_text'     => ''
	);
	/**
	 * The page controller library
	 *
	 * @access protected
	 * @var PageController
	 * @since 1.0.0
	 */
	protected $pages;

	/**
	 * Brings in Page Controller
	 *
	 * @param object $pages Page Controller
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doExecute( $pages )
	{
		$this->pages = $pages;
		$this->setupMetadata();
	}

	/**
	 * MUST BE OVERRIDEN: parses the input and returns true
	 * if there is a problem with an input.
	 *
	 * @return bool
	 * @abstract
	 * @access public
	 * @since 1.0.0
	 */
	public function adminDoSaveChecks( $type )
	{
		$out = FALSE;

		$this->metadata['name']                 = $this->registry->txtStripslashes( trim( $this->input['name'] ) );
		$this->metadata['uri']                  = $this->registry->txtStripslashes( trim( $this->input['uri'] ) );
		$this->metadata['active']               = intval( $this->input['active'] );
		$this->metadata['show_title']           = intval( $this->input['show_title'] );
		$this->metadata['navigation']           = intval( $this->input['navigation'] );
		$this->metadata['navigation_text']      = $this->registry->txtStripslashes( trim( $this->input['navigation_text'] ) );

		if( $type != 'redirect' )
		{
			$this->metadata['uri'] = strtolower( $this->metadata['uri'] );
		}

		if ( strlen( $this->metadata['name'] ) < 3 || strlen( $this->metadata['uri'] ) < 3 || $this->adminDoPageSaveChecks() )
		{
			$out = TRUE;
		}

		return $out;
	}

	protected abstract function adminDoPageSaveChecks();

	/**
	 * MUST BE OVERRIDEN: returns the html for the type's
	 * specific settings for the control panel
	 *
	 * @param string $type add|edit
	 * @param AdminSkin $html the skin library
	 * @param array $page the db row plus metadata array
	 * @param int $languageID the add/edit language
	 * @param int $compareID the language for text comparison
	 * @param string $button the text for the submit button
	 * @param string $title the title for the top of the table
	 * @return string the html
	 * @abstract
	 * @access public
	 * @since 1.0.0
	 */
	public function adminForm( $type, $html, $page, $languageID, $compareID, $button, $title )
	{
		$out = "";
		
		if ( $type == 'add' )
		{
			$page['metadata'][ $languageID ] = array();
			
			foreach( $this->metadata as $k => $v )
			{
				$page['metadata'][ $languageID ][ $k ]['value'] = $v;
			}
		}

		//-----------------------------------------
		// Start the form
		//-----------------------------------------
		
		$html->td_header[] = array( "&nbsp;"  , "40%" );
		$html->td_header[] = array( "&nbsp;"  , "60%" );
		
		$this->display->addJavascript( $this->display->compiledTemplates('skin_global')->rteJSHead() );
		
		$out .= $html->startTable( $title, 'admin-form' );
		
		//-----------------------------------------
		// Form elements
		//-----------------------------------------
		
		$out .= $html->startFieldset( $this->lang->getString('pages_form_field_info') );
		
		$out .= $html->addTdRow(
			array(
				$this->lang->getString('pages_form_name'),
				($compareID > 0 ? "<div class='compare'>".$page['metadata'][ $compareID ]['name']['value'] . "</div>" : "") .
				$html->formInput( 'name', $this->registry->txtStripslashes( isset( $_POST['name'] ) ? $_POST['name'] : $page['metadata'][ $languageID ]['name']['value'] ) )
			)
		);
		
		$out .= $html->addTdRow(
			array(
				$this->lang->getString('pages_form_uri'),
				($compareID > 0 ? "<div class='compare'>".$page['metadata'][ $compareID ]['uri']['value'] . "</div>" : "") .
				$html->formInput( 'uri', $this->registry->txtStripslashes( isset( $_POST['uri'] ) ? $_POST['uri'] : $page['metadata'][ $languageID ]['uri']['value'] ) )
			)
		);
		
		$out .= $html->addTdRow(
			array(
				$this->lang->getString('pages_form_enabled'),
				($compareID > 0 ? "<div class='compare'>".( $page['metadata'][ $compareID ]['active']['value'] == "1" ? $this->lang->getString('yes') : $this->lang->getString('no') ) . "</div>" : "") .
				$html->formYesNo( 'active', intval( isset( $_POST['active'] ) ? $_POST['active'] : $page['metadata'][ $languageID ]['active']['value'] ) )
			)
		);
		
		$out .= $html->addTdRow(
			array(
				$this->lang->getString('pages_form_show_title'),
				($compareID > 0 ? "<div class='compare'>".( $page['metadata'][ $compareID ]['show_title']['value'] == "1" ? $this->lang->getString('yes') : $this->lang->getString('no') ) . "</div>" : "") .
				$html->formYesNo( 'show_title', intval( isset( $_POST['show_title'] ) ? $_POST['show_title'] : $page['metadata'][ $languageID ]['show_title']['value'] ) )
			)
		);
		
		$out .= $html->endFieldset();
		
		$out .= $html->startFieldset( $this->lang->getString('pages_form_field_nav') );
		
		$out .= $html->addTdRow(
			array(
				$this->lang->getString('pages_form_navigation'),
				($compareID > 0 ? "<div class='compare'>" . ( $page['metadata'][ $compareID ]['navigation']['value'] == "1" ? $this->lang->getString('yes') : $this->lang->getString('no') ) . "</div>" : "") .
				$html->formYesNo( 'navigation', intval( isset( $_POST['navigation'] ) ? $_POST['navigation'] : $page['metadata'][ $languageID ]['navigation']['value'] ) )
			)
		);
		
		$out .= $html->addTdRow(
			array(
				$this->lang->getString('pages_form_navigation_text'),
				($compareID > 0 ? "<div class='compare'>".$page['metadata'][ $compareID ]['navigation_text']['value'] . "</div>" : "") .
				$html->formInput( 'navigation_text', $this->registry->txtStripslashes( isset( $_POST['navigation_text'] ) ? $_POST['navigation_text'] : $page['metadata'][ $languageID ]['navigation_text']['value'] ) )
			)
		);
		
		$out .= $html->endFieldset();
		
		$out .= $html->startFieldset( $this->lang->getString('pages_form_field_content') );
		
		$out .= $this->adminPageForm( $html, $page['metadata'], $languageID, $compareID );
		
		$out .= $html->endFieldset();
		
		//-----------------------------------------
		// End table and form
		//-----------------------------------------
		
		$out .= $html->endForm( $button );
		
		return $out;
	}

	protected abstract function adminPageForm( $html, $metadata, $languageID, $compareID );

	/**
	 * Saves any metadata for the control panel
	 *
	 * @param string $type add|edit
	 * @param int $pageID the id of the page
	 * @param int $languageID the id of the add/edit language
	 * @param array $meta the already existing metadata
	 * @return bool the result
	 * @access public
	 * @since 1.0.0
	 */
	public function adminSave( $type, $pageID, $languageID, $meta = array() )
	{
		$count = 0;
		$out   = FALSE;
		
		$metadataSorted = array();
		
		if ( count( $meta ) > 0 )
		{
			$metadataSorted = $this->pages->processMetadataByLanguage( $meta );
		}
		
		foreach( $this->metadata as $k => $v )
		{
			if ( isset( $metadataSorted[ $languageID ] ) && isset( $metadataSorted[ $languageID ][ $k ] ) )
			{
				$this->DB->query(
					"UPDATE metadata SET meta_value = '{$v}' WHERE meta_id = {$metadataSorted[ $languageID ][ $k ]['id']};"
				);
			}
			else
			{
				$this->DB->query(
					"INSERT INTO metadata (module, language_id, id, meta_key, meta_value) VALUES ('page', {$languageID}, {$pageID}, '{$k}', '{$v}');"
				);
			}
			
			$count++;
		}
		
		if ( $count > 0 )
		{
			$out = TRUE;
		}
		
		return $out;
	}

	/**
	 * Returns the type's name
	 *
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the type's print name.
	 *
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function getString()
	{
		return $this->lang->getString( $this->name );
	}

	protected abstract function setupMetadata();
}

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Page controller class
 * 
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Page
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 */

class PageController extends Command
{
	/**
	 * The page types available
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $types = array();
	/**
	 * The loaded pages types
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $loaded;
	/**
	 * The loaded pages
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $pages;
	/**
	 * Copy of the page cache
	 *
	 * @access public
	 * @var array
	 * @since 1.0.0
	 */
	protected $pageCache;

	/**
	 * Calls to load the types
	 *
	 * @param object $param extra thingy from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doExecute( $param )
	{
		$this->display->addDebug( "Page Controller Loaded");
		
		$this->loadTypes();
	}

	/**
	 * Builds a breadcrumb starting with the furthest child node
	 *
	 * @param int $rootID the current page
	 * @param array $url url parameters
	 * @param string $app the current application
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function buildBreadcrumb( $rootID, $url = array( 'module' => 'pages' ), $app = "" )
	{
		$active   = '';
		$pages = array();

		if ( ! count( $this->pageCache ) > 0 )
		{
			$this->getCache();
		}

		if ( isset( $this->pageCache['all'][ $rootID ] ) && is_array( $this->pageCache['all'][ $rootID ] ) )
		{
			$pages[] = $active = $this->pageCache['all'][ $rootID ];

			while ( $active['parent_id'] != 0 )
			{
				$pages[] = $active = $this->pageCache['all'][ $active['parent_id'] ];
			}
		}

		if ( count( $pages ) > 0 )
		{
			for ($i = count( $pages ) - 1; $i >= 0; $i-- )
			{
				$url['parent_id'] = $pages[ $i ]['page_id'];
				
				$uri    = $this->display->buildURL( $url, $app );
				$string = "";
				
				if ( isset( $pages[ $i ]['metadata'][ $this->lang->getLanguageID() ] ) && is_array( $pages[ $i ]['metadata'][ $this->lang->getLanguageID() ] ) )
				{
					$string = $pages[ $i ]['metadata'][ $this->lang->getLanguageID() ]['name']['value'];
				}
				else
				{
					foreach( $pages[ $i ]['languages'] as $k => $v )
					{
						if ( is_array( $pages[ $i ]['metadata'][ $v ] ) )
						{
							$string = $pages[ $i ]['metadata'][ $v ]['name']['value'];
							break;
						}
					}
				}
				
				$this->display->addBreadcrumb( $uri, $string );
			}
		}
	}

	/**
	 * Iterates through the root pages to build the navigation links
	 *
	 * @param int $id the id of the current page for active highlighting
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function buildNavigation( $id )
	{
		$count = 0;

		foreach( $this->pages as $k => $v )
		{
			$out = $v->buildNavigation( $id, 0, $count );

			if ( is_array( $out ) && count( $out ) > 1 )
			{
				$this->display->addNavigation( $out );

				$count++;
			}
		}
	}

	/**
	 * Iterates through the root pages to find the page and build the full URI
	 *
	 * @param int $id the id of the page to build the full uri
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function buildSEOURI( $id )
	{
		$out = "";
		
		if ( ! is_object( $out ) && $id != $this->settings['home_page'] )
		{
			foreach( $this->pages as $v )
			{
				$out = $v->buildSEOURI( $id );
				
				if ( $out != "" )
				{
					break;
				}
			}
		}
		
		return $out;
	}

	/**
	 * Iterates through the root pages to find the page from a uri
	 *
	 * @param string $uri the full uri
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function findPageFromURI( $uri )
	{
		$out = array( 'page_id' => 0 );

		if ( is_array( $uri ) && count( $uri ) > 0 )
		{
			foreach( $this->pages as $v )
			{
				$back = $v->findPageFromURI( $uri, 0 );

				if ( $back > 0 )
				{
					$out['page_id'] = $back;
					break;
				}
			}
		}

		if ( $out['page_id'] == $this->settings['home_page'] )
		{
			header($_SERVER["SERVER_PROTOCOL"]." 301 Moved Permanently");
			header( 'Location: ' . $this->display->buildURL( array() ) );
			exit();
		}

		return $out;
	}

	/**
	 * Sets the page cache within the controller and returns the page cache
	 *
	 * @return array the cache
	 * @access public
	 * @since 1.0.0
	 */
	public function getCache()
	{
		$out = array();
		
		$this->pageCache = $this->cache->getCache('pages');
		
		$out = $this->pageCache;
		
		return $out;
	}

	/**
	 * Get an array of the types to create a dropdown or multi-select
	 *
	 * @return array the types
	 * @access public
	 * @since 1.0.0
	 */
	public function getDropdownArray()
	{
		$out = array();
		
		if ( count( $this->loaded ) > 0 )
		{
			foreach( $this->loaded as $type )
			{
				$out[ $type->getName() ] = array( $type->getName(), $type->getString() );
			}
		}
		
		return $out;
	}

	/**
	 * Gts the first page in the array
	 *
	 * @return Page the home page
	 * @access public
	 * @since 1.0.0
	 */
	public function getHomePage()
	{
		$out = NULL;
		
		foreach( $this->pages as $v )
		{
			$out = $v;
			break;
		}
		
		return $out;
	}

	/**
	 * Get a specific page
	 *
	 * @param int $id the page id
	 * @return Page the required page
	 * @access public
	 * @since 1.0.0
	 */
	public function getPage( $id )
	{
		$out = NULL;
		
		if ( isset( $this->pages[ $id ] ) && is_object( $this->pages[ $id ] ) )
		{
			$out = $this->pages[ $id ];
		}
		
		if ( ! is_object( $out ) )
		{
			foreach( $this->pages as $v )
			{
				$out = $v->findChild( $id );
				
				if ( is_object( $out ) && $out->getID() == $id )
				{
					break;
				}
			}
		}
		
		return $out;
	}

	/**
	 * Get the array of pages
	 *
	 * @return array the types
	 * @access public
	 * @since 1.0.0
	 */
	public function getPages()
	{
		return $this->pages;
	}

	/**
	 * Get a specific type
	 *
	 * @param string $type the key of the type
	 * @return PageType the required type
	 * @access public
	 * @since 1.0.0
	 */
	public function getType( $type )
	{
		$out = NULL;
		
		if ( isset( $this->loaded[ $type ] ) && is_object( $this->loaded[ $type ] ) )
		{
			$out = $this->loaded[ $type ];
		}
		
		return $out;
	}

	/**
	 * Loads in the pages
	 *
	 * @param array $dbRows the pages from the database
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	public function loadPages()
	{
		foreach( $this->pageCache['root'] as $k => $v )
		{
			$v['controller'] = $this;

			if ( isset( $this->types[ $v['type'] ] ) )
			{
				$this->pages[ $v['page_id'] ] = new $this->types[ $v['type'] ][0]();
				$this->pages[ $v['page_id'] ]->execute( $this->registry, $v );
				$this->pages[ $v['page_id'] ]->loadChildren( $this->pageCache, $this->types );
			}
		}
	}

	/**
	 * Loads in the various types of pages
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function loadTypes()
	{
		foreach ( glob( SWS_CLASSES_PATH ."pages/*.conf.php" ) as $filename)
		{
			$TYPE_KEY     = '';
			$TYPE_CLASSES = array();

			include($filename);

			if ( is_array( $TYPE_CLASSES ) && count( $TYPE_CLASSES ) == 2 )
			{
				require_once( SWS_CLASSES_PATH ."pages/{$TYPE_KEY}.php" );

				$this->types[ $TYPE_KEY ] = $TYPE_CLASSES;

				if ( class_exists( $TYPE_CLASSES[1] ) && SWS_THIS_APPLICATION == 'admin' )
				{
					$this->loaded[ $TYPE_KEY ] = new $TYPE_CLASSES[1]();
					$this->loaded[ $TYPE_KEY ]->execute( $this->registry, $this );
				}
			}
		}
	}

	/**
	 * Make dropdown of projects
	 *
	 * @param string $selectName Optional: defaults 'project_id'; the name to be given to the select (DROPDOWN ONLY)
	 * @param int $selected Optional: defaults 0; the ID of the status that should be selected
	 * @param string $returnType Optional: default 'drop; drop|array|options
	 * @param boolean $depth Optional: defaults TRUE; enables the depth indentations for sub-projects
	 * @return string|array output based on $returnType parameter
	 * @access public
	 * @since 1.0.0
	 */
	public function makeDropdown( $selectName='page_id', $selected=0, $returnType='drop', $depth=TRUE )
	{
		$depthGuide = '';
		$names      = array();
		$pages      = array();
		$out        = NULL;

		if ( $depth )
		{
			$depthGuide = $this->lang->getString('depth_guide');;
		}
		
		if ( ! count( $this->pageCache ) > 0 )
		{
			$this->getCache();
		}

		if ( is_array( $this->pageCache['root'] ) && count( $this->pageCache['root'] ) > 0 )
		{
			foreach( $this->pageCache['root'] as $id => $data )
			{
				$sel = ( $selected == $id )? "selected='selected'" : '';
				$pages[$id] = "<option value='{$id}' {$sel}>{$data['metadata'][$this->lang->getLanguageID()]['name']['value']}</option>";
				$pages['names'][$id] = $data['metadata'][$this->lang->getLanguageID()]['name']['value'];

				$pages = $this->makeDropdownInternal( $pages, $id, $depthGuide, $depthGuide, $selected );
			}
		}

		$names = $pages['names'];
		unset( $pages['names'] );

		switch( $returnType )
		{
			case 'drop':    $out = $this->display->compiledTemplates('skin_global')->dropdown_wrapper( $selectName, implode("\n", $pages) );
				break;
			case 'array':  foreach( $pages as $id => $data ) { $out[] = array( $id, $names[$id] ); }
				break;
			case 'options': $out = implode("\n", $pages);
				break;
		}

	return $out;
	}

	/**
	 * Recurrsion method for building project dropdowns with depth (if enabled)
	 *
	 * @param array $projects the dropdown array
	 * @param int $rootID the project ID to build children from
	 * @param boolean $depth Optional: defaults ''; the current depth
	 * @param boolean $depthGuide Optional: defaults ''; the depth level guide
	 * @param int $selected Optional: defaults 0; the ID of the status that should be selected
	 * @return array of projects for dropdown
	 * @access private
	 * @since 1.0.0
	 */
	private function makeDropdownInternal( $pages, $rootID, $depth='', $depthGuide='', $selected=0 )
	{
		if ( is_array( $this->pageCache ) && is_array( $this->pageCache[ $rootID ] ) && count( $this->pageCache[ $rootID ] ) > 0 )
		{
			foreach( $this->pageCache[ $rootID ] as $id => $data )
			{
				$level = strlen( $depthGuide ) > 0 ? "&nbsp;&nbsp;&#0124;{$depth} " : '';

				$sel = ( $selected == $data['page_id'] )? "selected='selected'" : "" ;
				$pages[$id] = "<option value='{$id}' {$sel}>{$level}{$data['metadata'][$this->lang->getLanguageID()]['name']['value']}</option>";
				$pages['names'][$id] = $level . $data['metadata'][$this->lang->getLanguageID()]['name']['value'];

				$pages = $this->makeDropdownInternal( $pages, $id, $depth . $depthGuide, $depthGuide, $selected );
			}
		}

		return $pages;
	}

	/**
	 * Processes the metadata rows into a structured array:
	 * <pre>
	 *  $out => (
	 *    *language_id => (
	 *      *meta_key    => (
	 *        [id]        => *meta_id
	 *        [value]     => *meta_value
	 *      )
	 *    )
	 *  )
	 * </pre>
	 * * denotes database value
	 *
	 * @param array $meta the database rows
	 * @return array the new array
	 * @access protected
	 * @since 1.0.0
	 */
	public function processMetadataByLanguage( $meta )
	{
		$out = array();
		
		if ( is_array( $meta ) && count( $meta ) > 0 )
		{
			foreach( $meta as $k => $v )
			{
				if ( is_array( $out[ $v['language_id'] ] ) )
				{
					$out[ $v['language_id'] ][ $v['meta_key'] ] = array( 'id' => $v['meta_id'], 'value' => $v['meta_value'] );
				}
				else
				{
					$out[ $v['language_id'] ] = array( $v['meta_key'] => array( 'id' => $v['meta_id'], 'value' => $v['meta_value'] ) );
				}
			}
		}
		
		return $out;
	}

	/**
	 * Processes the metadata rows into a structured array:
	 * <pre>
	 *  $out => (
	 *    *id => (
	 *      *language_id => (
	 *        *meta_key    => (
	 *          [id]        => *meta_id
	 *          [value]     => *meta_value
	 *      )
	 *    )
	 *  )
	 * </pre>
	 * * denotes database value
	 *
	 * @param array $meta the database rows
	 * @return array the new array
	 * @access protected
	 * @since 1.0.0
	 */
	public function processMetadataByID( $meta )
	{
		$out = array();
		
		if ( is_array( $meta ) && count( $meta ) > 0 )
		{
			foreach( $meta as $k => $v )
			{
				if ( is_array( $out[ $v['id'] ] ) && is_array( $out[ $v['id'] ][ $v['language_id'] ] ) )
				{
					$out[ $v['id'] ][ $v['language_id'] ][ $v['meta_key'] ] = array( 'id' => $v['meta_id'], 'value' => $v['meta_value'] );
				}
				else if ( is_array( $out[ $v['id'] ] ) )
				{
					$out[ $v['id'] ][ $v['language_id'] ] = array( $v['meta_key'] => array( 'id' => $v['meta_id'], 'value' => $v['meta_value'] ) );
				}
				else
				{
					$out[ $v['id'] ] = array( $v['language_id'] => array( $v['meta_key'] => array( 'id' => $v['meta_id'], 'value' => $v['meta_value'] ) ) );
				}
			}
		}
		
		return $out;
	}
}

?>