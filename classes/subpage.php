<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Subpage content class
 * Last Updated: $Date: 2010-04-28 14:46:36 -0500 (Wed, 28 Apr 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Subpage
 * @link		http://www.simnaweb.com
 * @abstract
 * @since		1.0.0
 * @version		$Revision: 4 $
 */

abstract class Subpage extends Command
{
	/**
	 * The subpage id
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
	 * The navigation enable flag
	 *
	 * @access protected
	 * @var bool
	 * @since 1.0.0
	 */
	protected $navigation;

	/**
	 * Inserts a new subpage into the database
	 *
	 * @param array $params an array of the keys and values
	 * @return bool true or false depending on success
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function create( $params )
	{
		$fieldList = array( 'type', 'languages', 'position', 'page_id', 'last_update' );
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
			"INSERT INTO subpage (" . substr($keys,0,strlen($keys)-1) . ") VALUES (" . substr($values,0,strlen($values)-1) . ");"
		);
		
		return TRUE;
	}

	/**
	 * Deletes a subpage from the database
	 *
	 * @param int $subpageID the subpagee to be deleted
	 * @return bool true or false depending on success
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function delete( $subpageID )
	{
		Registry::$instance->DB->query(
			"DELETE FROM subpage WHERE subpage_id = '{$subpageID}';"
		);
		
		return TRUE;
	}

	/**
	 * Returns the subpage's id
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
}

/**
 * BLI-CMS System
 *  - Backlot Imaging Programming Team
 * 
 * Subpage content type class
 * Last Updated: $Date: 2009-11-18 21:58:04 -0600 (Wed, 18 Nov 2009) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 BL Imaging, Inc.
 * @package		BLI-CMS
 * @subpackage	Subpage
 * @link		http://www.simnaweb.com
 * @abstract
 * @since		1.0.0
 * @version		$Revision: 79 $
 */

abstract class SubpageType extends Command
{
	/**
	 * The page controller library
	 *
	 * @access protected
	 * @var PageController
	 * @since 1.0.0
	 */
	protected $subpages;

	/**
	 * Brings in Page Controller
	 *
	 * @param object $pages Page Controller
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doExecute( $subpages )
	{
		$this->subpages = $subpages;
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
	public abstract function adminDoSaveChecks();

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
	public function adminForm( $type, $html, $subpage, $languageID, $compareID, $button, $page, $title )
	{
		$out = "";

		if ( $type == 'add' )
		{
			$subpage['metadata'][ $languageID ] = array();
			
			foreach( $this->metadata as $k => $v )
			{
				$subpage['metadata'][ $languageID ][ $k ]['value'] = $v;
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

		$out .= $this->adminPageForm( $html, $subpage['metadata'], $languageID, $compareID, $page['type'] );

		//-----------------------------------------
		// End table and form
		//-----------------------------------------

		$out .= $html->endForm( $button );

		return $out;
	}

	protected abstract function adminPageForm( $html, $metadata, $languageID, $compareID, $type );

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
			$metadataSorted = $this->subpages->processMetadataByLanguage( $meta );
		}

		foreach( $this->metadata as $k => $v )
		{
			if ( isset( $metadataSorted[ $languageID ] ) && isset( $metadataSorted[ $languageID ][ $k ] ) )
			{
				$this->DB->query("UPDATE metadata SET meta_value = '{$v}' WHERE meta_id = {$metadataSorted[ $languageID ][ $k ]['id']};");
			}
			else
			{
				$this->DB->query("INSERT INTO metadata (module, language_id, id, meta_key, meta_value) VALUES ('subpage', {$languageID}, {$pageID}, '{$k}', '{$v}');");
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
}

/**
 * BLI-CMS System
 *  - Backlot Imaging Programming Team
 * 
 * Subpage controller class
 * 
 * @copyright	2009 BL Imaging, Inc.
 * @package		BLI-CMS
 * @subpackage	Subpage
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 */

class SubpageController extends Command
{
	/**
	 * The subpage types available
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $types;
	/**
	 * The loaded subpages types
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $loaded;
	/**
	 * The current page type being processed
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $activePageType;

	/**
	 * Call the load function
	 *
	 * @param string $pageType the proper type of subpages to load
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doExecute( $params )
	{
		$this->display->addDebug( "Subpage Controller Loaded");

		$this->loadTypes();
	}

	/**
	 * Get a specific type
	 *
	 * @param string $type the key of the type
	 * @return string the required type
	 * @access public
	 * @since 1.0.0
	 */
	public function getClass( $type )
	{
		$out = "";
		
		if ( isset( $this->types[ $type ] ) && is_array( $this->types[ $type ] ) )
		{
			$out = $this->types[ $type ][0];
		}
		
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

		if ( count( $this->loaded ) > 0 && isset( $this->matches[ $this->activePageType ] ) && count( $this->matches[ $this->activePageType ] ) )
		{
			foreach( $this->loaded as $type )
			{
				if ( in_array( $type->getName(), $this->matches[ $this->activePageType ] ) )
				{
					$out[ $type->getName() ] = array( $type->getName(), $type->getString() );
				}
			}
		}

		return $out;
	}

	/**
	 * Get a specific type
	 *
	 * @param string $type the key of the type
	 * @return SubpageType the required type
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
	 * Loads in the various types of pages
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function loadTypes()
	{
		foreach ( glob( SWS_CLASSES_PATH ."subpages/*.conf.php" ) as $filename)
		{
			$TYPE_KEY     = '';
			$TYPE_CLASSES = array();
			$TYPE_PAGES   = array();

			include($filename);

			if ( is_array( $TYPE_CLASSES ) && count( $TYPE_CLASSES ) == 2 )
			{
				require_once( SWS_CLASSES_PATH ."subpages/{$TYPE_KEY}.php" );

				$this->types[ $TYPE_KEY ] = $TYPE_CLASSES;

				if ( is_array( $TYPE_PAGES ) && count( $TYPE_PAGES ) > 0 )
				{
					foreach( $TYPE_PAGES as $match )
					{
						$this->matches[ $match ][] = $TYPE_KEY;
					}
				}

				if ( class_exists( $TYPE_CLASSES[1] ) )
				{
					$this->loaded[ $TYPE_KEY ] = new $TYPE_CLASSES[1]();
					$this->loaded[ $TYPE_KEY ]->execute( $this->registry, $this );
				}
			}
		}
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
	public function processMetadataByID( $meta )
	{
		$out = array();
		
		if ( is_array( $meta ) && count( $meta ) > 0 )
		{
			foreach( $meta as $k => $v )
			{
				if ( is_array( $out[ $v['id'] ] ) )
				{
					$out[ $v['id'] ][ $v['meta_key'] ] = array( 'id' => $v['meta_id'], 'value' => $v['meta_value'] );
				}
				else
				{
					$out[ $v['id'] ] = array( $v['meta_key'] => array( 'id' => $v['meta_id'], 'value' => $v['meta_value'] ) );
				}
			}
		}
		
		return $out;
	}
	
	public function setType( $type )
	{
		$this->activePageType = $type;
	}
}

?>