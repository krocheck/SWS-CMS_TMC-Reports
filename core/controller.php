<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Main controller that preps the files and launches admin or public
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

class Controller
{
	/**
	 * The application registry library
	 *
	 * @access private
	 * @var Registry
	 * @since 1.0.0
	 */
	private static $registry;
	/**
	 * The login handler
	 *
	 * @access private
	 * @var Login
	 * @since 1.0.0
	 */
	private static $login;
	/**
	 * The application
	 *
	 * @access private
	 * @var object
	 * @since 1.0.0
	 */
	private static $application;

	/**
	 * If the configuration is loaded successfully, the core files are loaded,
	 * the registry is launched, the login processes, and the app launches
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public static function execute()
	{
		$config = self::loadConfig();
		
		if ( is_array( $config ) )
		{
			self::loadCoreFiles();
			self::$registry = self::loadRegistry( $config );
			
			self::$application = self::$registry->getApp();
			
			if ( self::$application->requireLogin() || self::$registry->getInput('login') != '' || self::$registry->getInput('logout') != '' || 
				 strlen( self::$registry->getCookie()->getCookie(SWS_THIS_APPLICATION) ) == 32  ||  strlen( self::$registry->getInput('s') ) == 32 )
			{
				self::$login = new Login();
				self::$login->execute( self::$registry );
				self::$registry->setUser( self::$login->processLogin() );
			}
			
			if ( self::$application->getName() == 'public' || self::$application->getName() == 'admin' )
			{
				self::$registry->getDisplay()->addBreadcrumb( self::$registry->getDisplay()->buildURL(), self::$registry->getLang()->getString('home'), 'first' );
			}
			
			self::$application->launch();
		}
		else
		{
			echo( 'FATAL ERROR!  Please notify the administrator at:  temp@localhost' );
			exit;
		}
	}

	/**
	 * Get the main config file in
	 *
	 * @return array the config array
	 * @access private
	 * @since 1.0.0
	 */
	private static function loadConfig()
	{
		require_once( SWS_ROOT_PATH . 'config.php' );
		
		return $config;
	}

	/**
	 * Loads the important files
	 *
	 * @return void
	 * @access private
	 * @since 1.0.0
	 */
	private static function loadCoreFiles()
	{
		require_once( SWS_CORE_PATH. 'registry.php' );
		require_once( SWS_CORE_PATH. 'command.php' );
		require_once( SWS_CORE_PATH. 'cache.php' );
		require_once( SWS_CORE_PATH. 'database.php' );
		require_once( SWS_CORE_PATH. 'display.php' );
		require_once( SWS_CORE_PATH. 'error.php' );
		require_once( SWS_CORE_PATH. 'language.php' );
		require_once( SWS_CORE_PATH. 'cookie.php' );
		require_once( SWS_CORE_PATH. 'user.php' );
		require_once( SWS_CORE_PATH. 'session.php' );
		require_once( SWS_CORE_PATH. 'login.php' );
	}

	/**
	 * We gotta have the registry right?
	 *
	 * @param array $config the config to get this puppy going
	 * @return Registry the Registry ... yay!
	 * @access private
	 * @since 1.0.0
	 */
	private static function loadRegistry( array $config )
	{
		return new Registry( $config );
	}
}

?>