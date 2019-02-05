<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Command is the general abstract that nearly every file extends
 * to have the necessary core classes loaded
 * Last Updated: $Date: 2010-07-02 09:29:16 -0500 (Fri, 02 Jul 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Core
 * @link		http://www.simnaweb.com
 * @abstract
 * @since		1.0.0
 * @version		$Revision: 31 $
 */

abstract class Command
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
	 * Registry database shortcut
	 *
	 * @access protected
	 * @var Database
	 * @since 1.0.0
	 */
	protected $DB;
	/**
	 * Registry display shortcut
	 *
	 * @access protected
	 * @var Display
	 * @since 1.0.0
	 */
	protected $display;
	/**
	 * Registry cache shortcut
	 *
	 * @access protected
	 * @var Cookie
	 * @since 1.0.0
	 */
	protected $cache;
	/**
	 * Registry cookie shortcut
	 *
	 * @access protected
	 * @var Cookie
	 * @since 1.0.0
	 */
	protected $cookie;
	/**
	 * Registry languages shortcut
	 *
	 * @access protected
	 * @var Languages
	 * @since 1.0.0
	 */
	protected $lang;
	/**
	 * Registry user shortcut
	 *
	 * @access protected
	 * @var User
	 * @since 1.0.0
	 */
	protected $user;
	/**
	 * Registry error shortcut
	 *
	 * @access protected
	 * @var Error
	 * @since 1.0.0
	 */
	protected $error;
	/**
	 * Registry input shortcut
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $input;
	/**
	 * Registry settings shortcut
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $settings;

	/**
	 * Constructor
	 *
	 * @return void
	 * @final
	 * @access public
	 * @since 1.0.0
	 */
	final public function __construct()
	{
	}

	/**
	 * Make the registry shortcuts
	 *
	 * @param Registry $registry Registry reference
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function makeRegistryShortcuts( Registry $registry )
	{
		$this->registry  = $registry;
		
		$this->DB        = &$this->registry->DB;
		$this->cache     = &$this->registry->cache;
		$this->display   = &$this->registry->display;
		$this->lang      = &$this->registry->lang;
		$this->cookie    = &$this->registry->cookie;
		$this->user      = &$this->registry->user;
		$this->error     = &$this->registry->error;
		$this->settings  = &$this->registry->settings;
		$this->input     = &$this->registry->input;
	}

	/**
	 * Execute the command (call doExecute)
	 *
	 * @param Registry $registry Registry reference
	 * @param object $param something to be passed to the object
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function execute( Registry $registry, $param = NULL )
	{
		$this->makeRegistryShortcuts( $registry );
		$this->doExecute( $param );
	}

	/**
	 * Do execute method (must be overriden)
	 *
	 * @param object $param something to be passed to the function
	 * @return void
	 * @abstract
	 * @access protected
	 * @since 1.0.0
	 */
	protected abstract function doExecute( $param );
}

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Application extension that adds additional required function
 *
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Core
 * @link		http://www.simnaweb.com
 * @abstract
 * @since		1.0.0
 */

abstract class Application extends Command
{
	/**
	 * Builds an SEO uri from the parameters and removes used ones
	 *
	 * @param array $params paramteres for the url
	 * @return array the good stuff
	 * @access protected
	 * @abstract
	 * @since 1.0.0
	 */
	public abstract function buildSEOURI( $params );
	/**
	 * Makes the user area display
	 *
	 * @return string html of the area
	 * @access private
	 * @since 1.0.0
	 */
	public abstract function buildUserLinks();
	/**
	 * Get the app's name
	 *
	 * @return string the name
	 * @access public
	 * @abstract
	 * @since 1.0.0
	 */
	public abstract function getName();
	/**
	 * Runs the application
	 *
	 * @return void
	 * @access public
	 * @abstract
	 * @since 1.0.0
	 */
	public abstract function launch();
	/**
	 * Searches for input based on an array or bits
	 *
	 * @param array $bits the tokenized uri
	 * @return array new input keys
	 * @access private
	 * @abstract
	 * @since 1.0.0
	 */
	public abstract function parseSEOURI( $bits = array() );
	/**
	 * Tells the controller whether or not to propmt for login
	 *
	 * @return bool the requirement
	 * @access public
	 * @abstract
	 * @since 1.0.0
	 */
	public abstract function requireLogin();
}

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * API extension that adds additional required functions
 *
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Core
 * @link		http://www.simnaweb.com
 * @abstract
 * @since		1.0.0
 */

abstract class ApiCommand extends Command
{
	/**
	 * Execute the command (call doExecute)
	 *
	 * @param Registry $registry Registry reference
	 * @param object $param something to be passed to the object
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function execute( Registry $registry, $param = NULL )
	{
		$this->makeRegistryShortcuts( $registry );
		
		$this->getSession();
		
		switch ( $_SERVER['REQUEST_METHOD'] )
		{
			case('GET'): $this->doGet($param);
				break;
			case('PUT'): $this->doPut($param);
				break;
			case('POST'): $this->doPost($param);
				break;
			case('DELETE'): $this->doDelete($param);
				break;
		}
	}

	/**
	 * Handles getting any basic authentication data and loading the sessions
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function getSession()
	{
		$user = NULL;
		
		if ( $this->auth[ $_SERVER['REQUEST_METHOD'] ] == true && ! is_object( $this->user )  )
		{
			$this->display->addJSON( 'status', 'forbidden' );
			$this->display->doJSON();
		}
	}
	
	/**
	 * Do delete method (must be overriden)
	 *
	 * @param object $param something to be passed to the function
	 * @return void
	 * @abstract
	 * @access protected
	 * @since 1.0.0
	 */
	protected abstract function doDelete( $param );
	
	/**
	 * Do get method (must be overriden)
	 *
	 * @param object $param something to be passed to the function
	 * @return void
	 * @abstract
	 * @access protected
	 * @since 1.0.0
	 */
	protected abstract function doGet( $param );
	
	/**
	 * Do post method (must be overriden)
	 *
	 * @param object $param something to be passed to the function
	 * @return void
	 * @abstract
	 * @access protected
	 * @since 1.0.0
	 */
	protected abstract function doPost( $param );
	
	/**
	 * Do put method (must be overriden)
	 *
	 * @param object $param something to be passed to the function
	 * @return void
	 * @abstract
	 * @access protected
	 * @since 1.0.0
	 */
	protected abstract function doPut( $param );
}

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * API extension that adds additional required functions
 *
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Core
 * @link		http://www.simnaweb.com
 * @abstract
 * @since		1.0.0
 */

class Setting
{
	/**
	 * Updates a setting in the database
	 *
	 * @param array $params an array of the keys and values
	 * @return bool true or false depending on success
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function update( $key, $value )
	{
		$currentSetting = array();
		$out = FALSE;

		Registry::$instance->DB->query(
			"SELECT * FROM metadata WHERE module='setting' AND meta_key='{$key}';"
		);

		if ( Registry::$instance->DB->getTotalRows() > 0 )
		{
			while( $r = Registry::$instance->DB->fetchRow() )
			{
				$currentSetting = $r;
			}
		}

		if ( isset( $currentSetting['meta_id'] ) && $currentSetting['meta_id'] > 0 )
		{
			Registry::$instance->DB->query(
				"UPDATE metadata SET meta_value = '{$value}' WHERE meta_id = {$currentSetting['meta_id']};"
			);
			
			$out = TRUE;
		}

		Registry::$instance->cache->update( 'settings', TRUE );

		return $out;
	}

	/**
	 * Updates all settings in the database
	 *
	 * @param array $params an array of the keys and values
	 * @return bool true or false depending on success
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function updateAll( $settings )
	{
		$count = 0;
		$currentSettings = array();
		$out = FALSE;

		Registry::$instance->DB->query(
			"SELECT * FROM metadata WHERE module='setting';"
		);

		if ( Registry::$instance->DB->getTotalRows() > 0 )
		{
			while( $r = Registry::$instance->DB->fetchRow() )
			{
				$currentSettings[ $r['meta_key'] ] = $r;
			}
		}

		foreach( $settings as $k => $v )
		{
			if ( isset( $currentSettings[ $k ] ) && isset( $currentSettings[ $k ]['meta_id'] ) && $currentSettings[ $k ]['meta_id'] > 0 )
			{
				Registry::$instance->DB->query(
					"UPDATE metadata SET meta_value = '{$v}' WHERE meta_id = {$currentSettings[ $k ]['meta_id']};"
				);
			}
			else
			{
				Registry::$instance->DB->query(
					"INSERT INTO metadata (module, language_id, id, meta_key, meta_value) VALUES ('setting', 0, 0, '{$k}', '{$v}');"
				);
			}
			
			$count++;
		}

		if ( $count > 0 )
		{
			$out = TRUE;
		}

		Registry::$instance->cache->update( 'settings', TRUE );

		return $out;
	}
}

?>