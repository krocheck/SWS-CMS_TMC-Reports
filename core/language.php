<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Language controller class
 * Last Updated: $Date: 2010-06-29 14:04:05 -0500 (Tue, 29 Jun 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Core
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 28 $
 */

class Languages
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
	 * Language object pulled from the DB
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $languages = array();
	/**
	 * The active language
	 *
	 * @access protected
	 * @var Language
	 * @since 1.0.0
	 */
	protected $active;

	/**
	 * Contructor fetching the registry information
	 *
	 * @param registry $registry the main program registry and information from the database
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct( Registry $registry )
	{
		$this->registry = $registry;
		
		$this->registry->getDisplay()->addDebug( "Language Library Loaded" );
		
		$this->loadFromDatabase();
	}

	/**
	 * Get an array of the languages to create a dropdown or multi-select
	 *
	 * @return array the languages
	 * @access public
	 * @since 1.0.0
	 */
	public function getDropdownArray()
	{
		$out = array();
		
		if ( count( $this->languages ) > 0 )
		{
			foreach( $this->languages as $lang )
			{
				$out[ $lang->getID() ] = array( $lang->getID(), $lang->getDisplayName() );
			}
		}
		
		return $out;
	}

	/**
	 * Gets the language from the array with a matching code
	 *
	 * @param string $code code of the language
	 * @return Language|void the language
	 * @access public
	 * @since 1.0.0
	 */
	public function getLanguageByCode( $code )
	{
		$out = NULL;
		
		foreach( $this->languages as $lang )
		{
			if ( $lang->getCode() == $code )
			{
				$out = $lang;
				break;
			}
		}
		
		return $out;
	}

	/**
	 * Gets the language from the array with a matching id
	 *
	 * @param int $id id of the language
	 * @return Language|void the language
	 * @access public
	 * @since 1.0.0
	 */
	public function getLanguageByID( $id )
	{
		$out = NULL;
		
		if ( isset( $this->languages[ $id ] ) )
		{
			$out = $this->languages[ $id ];
		}
		
		return $out;
	}

	/**
	 * Fetch the id from the active lang
	 *
	 * @return string the active code
	 * @access public
	 * @since 1.0.0
	 */
	public function getLanguageCode()
	{
		$out = NULL;

		if ( is_object( $this->active ) )
		{
			$out = $this->active->getCode();
		}

		return $out;
	}

	/**
	 * Fetch the id from the active lang
	 *
	 * @return int the active id
	 * @access public
	 * @since 1.0.0
	 */
	public function getLanguageID()
	{
		$out = NULL;
		
		if ( is_object( $this->active ) )
		{
			$out = $this->active->getID();
		}
		
		return $out;
	}

	/**
	 * Fetch the languages
	 *
	 * @return array fetching for language
	 * @access public
	 * @since 1.0.0
	 */
	public function getLanguages()
	{
		return $this->languages;
	}

	/**
	 * Gets a string from the active language
	 *
	 * @param string $key the string to be retrieved
	 * @return string the string from the active language
	 * @access public
	 * @since 1.0.0
	 */
	public function getString( $key )
	{
		$out = "";
		
		if ( is_object( $this->active ) )
		{
			$out = $this->active->getString( $key );
		}
		
		return $out;
	}

	/**
	 * Grabs the lanaguges from the database and loads the array
	 *
	 * @param bool $loadAll If true will load active and inactive languages, DEFAULT is FALSE
	 * @param array $loadIDs This will retrict which languages become active
	 *                       to the ones in this array AND $loadAll is TRUE
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function loadFromDatabase( $loadAll = FALSE, $loadIDs = array() )
	{
		$lang = $this->registry->getCache( 'languages' );
		
		if ( count( $lang ) > 0 )
		{
			foreach( $lang as $k => $v )
			{
				if ( $loadAll && count( $loadIDs ) > 0 )
				{
					if ( ! in_array( $k, $loadIDs ) )
					{
						unset( $lang[ $k ] );
					}
				}
				else if( $loadAll ) {}
				else
				{
					if ( ! $v['active'] == '1' )
					{
						unset( $lang[ $k ] );
					}
				}
			}
		}
		
		$oldLang = array();
		
		if ( count( $this->languages ) > 0 )
		{
			foreach( $this->languages as $k => $v )
			{
				$oldLang[ (int)$k ] = $v;
			}
		}
		
		$this->languages = array();
		
		foreach( $lang as $r )
		{
			if ( isset( $oldLang[ (int)$r['language_id'] ] ) && is_object( $oldLang[ (int)$r['language_id'] ] ) )
			{
				$this->languages[ (int)$r['language_id'] ] = $oldLang[ (int)$r['language_id'] ];
			}
			else
			{
				$this->languages[ (int)$r['language_id'] ] = new Language();
				$this->languages[ (int)$r['language_id'] ]->execute( $this->registry, $r );
				
				$this->registry->getDisplay()->addDebug( "Language Loaded: ".$this->languages[ (int)$r['language_id'] ]->getCode() );
			}
		}
		
		if ( isset( $this->languages[ (int)$this->registry->getSetting('default_language_id') ] ) )
		{
			$this->active = $this->languages[ (int)$this->registry->getSettings('default_language_id') ];
		}
		
		if ( is_object( $this->getLanguageByCode( SWS_THIS_LANGUAGE ) ) )
		{
			$this->active = $this->getLanguageByCode( SWS_THIS_LANGUAGE );
		}
		
		$this->active->loadStrings();
	}

	/**
	 * Sends a load language pack command to the active language
	 *
	 * @param string $pack the language pack to load
	 * @param string $app the application the pack is for, by default the current application
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function loadStrings( $pack = 'global', $app = SWS_THIS_APPLICATION )
	{
		$this->active->loadStrings( $pack, $app );
	}

	/**
	 * Set the active Language and loads the strings
	 *
	 * @param Language $lang the new active lang
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setActive( Language $lang )
	{
		if ( is_object( $this->getLanguageByCode( SWS_THIS_LANGUAGE ) ) ) {}
		else if ( $this->active != $lang && is_object( $lang ) )
		{
			if ( is_object( $this->active ) )
			{
				$this->active->unloadStrings();
			}
			
			$this->active = $lang;
			
			$this->active->loadStrings();
			
			$this->registry->getError()->setLanguageCode( $this->active->getCode() );
		}
	}
}

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Language object class
 *
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Core
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 */

class Language extends Command
{
	/**
	 * The language id
	 *
	 * @access public
	 * @var int
	 * @since 1.0.0
	 */
	protected $id;
	/**
	 * The English name for this language
	 *
	 * @access public
	 * @var string
	 * @since 1.0.0
	 */
	protected $name;
	/**
	 * The international code for this language
	 *
	 * @access public
	 * @var string
	 * @since 1.0.0
	 */
	protected $code;
	/**
	 * The public name for this language
	 *
	 * @access public
	 * @var string
	 * @since 1.0.0
	 */
	protected $displayName;
	/**
	 * Language strings from the lang files
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $strings = array();

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
		$this->id               = $dbRow['language_id'];
		$this->name             = $dbRow['name'];
		$this->code             = $dbRow['code'];
		$this->displayName      = $dbRow['display_name'];
	}

	/**
	 * Inserts a new user into the database
	 *
	 * @param array $params an array of the keys and values
	 * @return bool true or false depending on success
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function create( $params )
	{
		$fieldList = array( 'name', 'display_name', 'code', 'active', 'position' );
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
			"INSERT INTO language (" . substr($keys,0,strlen($keys)-1) . ") VALUES (" . substr($values,0,strlen($values)-1) . ");"
		);
		
		return TRUE;
	}

	/**
	 * Deletes a language from the database
	 *
	 * @param int $langID the language to be deleted
	 * @return bool true or false depending on success
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function delete( $langID )
	{
		Registry::$instance->DB->query(
			"DELETE FROM language WHERE language_id = '{$langID}';"
		);
		Registry::$instance->DB->query(
			"DELETE FROM metadata WHERE language_id = '{$langID}';"
		);
		Registry::$instance->DB->query(
			"UPDATE user 
				SET language_id = '{Registry::$instance::getSetting('default_language_id')} 
				WHERE language_id = '{$langID}';"
		);
		
		return TRUE;
	}

	/**
	 * Fetch the language code
	 *
	 * @return string the code
	 * @access public
	 * @since 1.0.0
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Fetch the display name
	 *
	 * @return string the dislpay name
	 * @access public
	 * @since 1.0.0
	 */
	public function getDisplayName()
	{
		return $this->displayName;
	}

	/**
	 * Fetch the language id
	 *
	 * @return int the id
	 * @access public
	 * @since 1.0.0
	 */
	public function getID()
	{
		return $this->id;
	}

	/**
	 * Fetch the name
	 *
	 * @return string the name
	 * @access public
	 * @since 1.0.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Gets a string
	 *
	 * @param string $key the string toget
	 * @return string the string requested
	 * @access public
	 * @since 1.0.0
	 */
	public function getString( $key )
	{
		$out = "";
		
		if ( isset( $this->strings[ $key ] ) )
		{
			$out = $this->strings[ $key ];
		}
		
		return $out;
	}

	/**
	 * Load the strings from the lang file
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function loadStrings( $pack = 'global', $app = SWS_THIS_APPLICATION )
	{
		if ( $pack == 'global' || $pack == '' )
		{
			if ( file_exists( SWS_LANG_PATH . strtolower( $this->code ). '.global.php' ) )
			{
				require( SWS_LANG_PATH . strtolower( $this->code ). '.global.php' );
				
				$this->registry->getDisplay()->addDebug( "Language Package Loaded: {$this->code}, global" );
			}
		}
		else
		{
			if ( file_exists( SWS_LANG_PATH . strtolower( $this->code ) . ".{$app}.{$pack}.php" ) )
			{
				require( SWS_LANG_PATH . strtolower( $this->code ) . ".{$app}.{$pack}.php" );
				
				$this->registry->getDisplay()->addDebug( "Language Package Loaded: {$this->code}, {$app}, {$pack}" );
			}
		}
		
		if ( isset( $lang ) && is_array( $lang ) && count( $lang ) > 0 )
		{
			$this->strings = array_merge( $this->strings, $lang );
		}
		else
		{
			$this->error->raiseError( 'lang_file_missing', TRUE );
		}
	}

	/**
	 * Updates the code of the language
	 *
	 * @param string $code the new code
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setCode( $code )
	{
		$this->code = $code;
	}

	/**
	 * Updates the display name of the language
	 *
	 * @param string $displayName the new display name
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setDisplayName( $displayName )
	{
		$this->displayName = $displayName;
	}

	/**
	 * Updates the name of the language
	 *
	 * @param string $name the new name
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setName( $name )
	{
		$this->name = $name;
	}

	/**
	 * Drops the strings array to free up memory
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function unloadStrings()
	{
		unset( $this->strings );
		
		$this->strings = array();
	}

	/**
	 * Updates a language in the database
	 *
	 * @param array $params an array of the keys and values
	 * @return bool true or false depending on success
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function update( $params )
	{
		$fieldList = array( 'name', 'display_name', 'code', 'active' );
		$values = "";
		$out = FALSE;
		
		if ( isset( $params['language_id'] ) && $params['language_id'] > 0 )
		{
			foreach( $params as $k => $v )
			{
				if ( in_array( $k, $fieldList ) )
				{
					$values .= "{$k} = '{$v}',";
				}
			}
			
			Registry::$instance->DB->query(
				"UPDATE language SET " . substr($values,0,strlen($values)-1) . " WHERE language_id = '{$params['language_id']}';"
			);
			
			$out = TRUE;
		}
		
		return $out;
	}
}

?>