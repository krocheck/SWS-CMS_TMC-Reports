<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Cache controller to cut down on database queries
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

class Cache
{
	/**
	 * The caches
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $cache = array();
	/**
	 * The array of caches to save/update later
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $save  = array();

	/**
	 * Constructor that loads the registry and sets up the class
	 *
	 * @param Registry $registry the main program registry
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct( Registry $registry )
	{
		$this->registry = $registry;
		
		$this->registry->getDisplay()->addDebug( "Cache Library Loaded" );
		
		$this->loadFiles();
		$this->loadCaches();
	}

	/**
	 * Get the cache
	 *
	 * @param string $name the cache name
	 * @return array
	 * @access public
	 * @since 1.0.0
	 */
	public function getCache( $name )
	{
		$out = NULL;
		
		if ( is_object( $this->cache[ $name ] ) )
		{
			$out = $this->cache[ $name ]->getCache();
		}
		
		return $out;
	}

	/**
	 * Loads the caches out of the database and sets up the
	 * cache objects
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function loadCaches()
	{
		$this->registry->DB->query("SELECT * FROM caches;");

		if ( $this->registry->DB->getTotalRows() > 0 )
		{
			while( $r = $this->registry->DB->fetchRow() )
			{
				if ( is_object( $this->cache[ $r['name'] ] ) )
				{
					$this->cache[ $r['name'] ]->setCache( $r );
				}
			}
		}
	}

	/**
	 * Loads the cache type files and initiates them
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function loadFiles()
	{
		foreach ( glob( SWS_CORE_PATH ."caches/*.php" ) as $filename)
		{
			require_once($filename);
			
			$filename   = strtolower( basename( $filename, ".php" ) );
			$classname  = ucfirst( $filename )."Cache";
			
			$this->cache[ $filename ] = new $classname();
			$this->cache[ $filename ]->execute( $this->registry );
		}
	}

	/**
	 * Executes cache save/updates on the caches in the save array
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function saveChanges()
	{
		if ( count( $this->save ) > 0 )
		{
			foreach( $this->save as $v )
			{
				$this->cache[ $v ]->save();
			}
		}
	}

	/**
	 * Constructor that loads the registry
	 *
	 * @param string $name the name of the cache
	 * @param bool $saveNow TRUE will execute the save immediately, FALSE by default
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function update( $name, $saveNow = FALSE )
	{
		if ( is_object( $this->cache[ $name ] ) )
		{
			if ( $saveNow )
			{
				$this->cache[ $name ]->save();
			}
			else
			{
				$this->save[ $name ] = $name;
			}
		}
	}
}

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 *
 * Abstract class for cache types to save in the database
 *
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Cache
 * @link		http://www.simnaweb.com
 * @abstract
 * @since		1.0.0
 */

abstract class CacheType extends Command
{
	/**
	 * DB row ID
	 *
	 * @access protected
	 * @var int
	 * @since 1.0.0
	 */
	protected $id = 0;
	/**
	 * The cache
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $value;

	/**
	 * We don't use this for this class
	 *
	 * @param object $param extra thingy from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doExecute( $param )
	{
	}

	/**
	 * Returns the array value, automatically fetching it from the database
	 * if needed
	 *
	 * @return array the cache
	 * @access public
	 * @since 1.0.0
	 */
	public function getCache()
	{
		if ( ! is_array( $this->value ) )
		{
			$this->DB->query("SELECT * FROM caches WHERE name = '{$this->name}';");
			
			$temp = $this->DB->fetchRow();
			
			if ( is_array( $temp ) && count( $temp ) > 0 )
			{
				$this->setCache( $temp );
			}
			else
			{
				$this->save( FALSE );
			}
		}
		
		return $this->value;
	}

	/**
	 * Saves the value to the database.  Must override with the update and call to save
	 *
	 * @param bool $dbCheck flag to query for the ID if it isn't available, DEFAULT is TRUE
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function save( $dbCheck = TRUE )
	{
		if ( $this->id == 0 && dbCheck )
		{
			$this->DB->query("SELECT * FROM caches WHERE name = '{$this->name}';");

			$temp = $this->DB->fetchRow();

			if ( is_array( $temp ) && count( $temp ) > 0 )
			{
				$this->id = $temp['cache_id'];
			}
		}

		if ( $this->id == 0 )
		{
			$this->DB->query("INSERT INTO caches (name, value) VALUES ('{$this->name}', '".serialize($this->value)."');");
		}
		else
		{
			$this->DB->query("UPDATE caches SET value = '".serialize($this->value)."' WHERE cache_id = '{$this->id}';");
		}
	}

	/**
	 * Unserializes the value and sets the ID from the row, override if more needs to be done
	 *
	 * @param array $dbRow the cache database row
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setCache( $dbRow )
	{
		$this->id    = $dbRow['cache_id'];
		$this->value = unserialize( $dbRow['value'] );
	}
}

?>