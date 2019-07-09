<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Admin application wrapper
 * Last Updated: $Date: 2010-06-10 22:30:14 -0500 (Thu, 10 Jun 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Admin
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 9 $
 */

class AdminApp extends Application
{
	/**
	 * The modules for navigation
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $modules = array( 'accounts', 'pages', 'settings' );

	/**
	 * The main execute function
	 *
	 * @param object $params extra stuff from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doExecute( $params )
	{

	}

	/**
	 * Builds an SEO uri from the parameters and removes used ones
	 *
	 * @param array $params paramteres for the url
	 * @return array the good stuff
	 * @access protected
	 * @since 1.0.0
	 */
	public function buildSEOURI( $params )
	{
		$out = array( 'uri' => '', 'params' => $params );

		if ( isset( $params['module'] ) && strlen( $params['module'] ) > 0 )
		{
			$out['uri'] .= $params['module'] .'/';
			unset( $out['params']['module'] );
		}

		if ( isset( $params['com'] ) && strlen( $params['com'] ) > 0 )
		{
			$out['uri'] .= $params['com'] .'/';
			unset( $out['params']['com'] );
		}

		return $out;
	}

	/**
	 * Builds the tabs
	 *
	 * @return void
	 * @access private
	 * @since 1.0.0
	 */
	private function buildNavigation()
	{
		// Add the control panel to the breadcrumb
		$this->registry->getDisplay()->addBreadcrumb( $this->registry->getDisplay()->buildURL( array(), 'admin' ), $this->registry->getLang()->getString('admin') );

		// Loop through the menu modules, check permission, and add if needed
		foreach( $this->modules as $val )
		{
			if ( $this->user->getPermission() == "superadmin" || $this->user->getPermission() == "admin" && ( $val == "pages" || $val == "subpages" ) )
			{
				if ( $val == "pages" )
				{
					$this->display->addNavigation( array( 'url' => $this->display->buildURL( array( 'module' => $val ), 'admin' ), 'string' => $this->lang->getString( $val ) ) );
				}
				else if ( $val == "accounts" )
				{
					$this->display->addNavigation( array( 'url' => $this->display->buildURL( array( 'module' => $val ), 'admin' ), 'string' => $this->lang->getString( $val ) ) );
				}
				else if ( $val == 'settings' )
				{
					$this->display->addNavigation( array( 'url' => $this->display->buildURL( array( 'module' => $val ), 'admin' ), 'string' => $this->lang->getString( $val ) ) );
				}
			}
		}
	}

	/**
	 * Makes the user area display
	 *
	 * @return string html of the area
	 * @access private
	 * @since 1.0.0
	 */
	public function buildUserLinks()
	{
		$links   = '';
		$applink = '';
		$appname = SWS_THIS_APPLICATION;
		$logout  = $this->display->buildURL( array_merge( $this->registry->filterInputsToKeep(), array('logout' => "true") ) );

		if ( $appname == 'admin' )
		{
			$applink = $this->display->buildURL();
			$appname = 'public';
			$logout  = $this->display->buildURL( array_merge( $this->registry->filterInputsToKeep(), array('logout' => "true") ), 'admin' );
		}

		$links   = $this->display->compiledTemplates('skin_admin')->userLinks( $applink, $appname, $logout );

		return $links;
	}

	/**
	 * Makes sure the user can actually use the app.  Will throw error if not.
	 *
	 * @return void
	 * @access private
	 * @since 1.0.0
	 */
	private function checkPermission()
	{
		if ( $this->user->getPermission() != 'admin' && $this->user->getPermission() != 'superadmin' )
		{ 
			$this->error->raiseError( 'no_permission', FALSE );
		}
	}

	/**
	 * Get the app's name
	 *
	 * @return string the name
	 * @access public
	 * @since 1.0.0
	 */
	public function getName()
	{
		return "admin";
	}

	/**
	 * Runs the application
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function launch()
	{
		// Check for proper credentials
		$this->checkPermission();

		// Setup the breadcrumb and menu bar
		$this->buildNavigation();

		// Declaration for the module
		$module = NULL;

		// Decide which module needs to be loaded
		// This will need to be fancied up if extra 
		// access levels are added

		if ( ! isset( $this->input['module'] ) )
		{
			$this->input['module'] = '';
		}

		switch( $this->input['module'] )
		{
			case 'subpages':
				require_once( SWS_ROOT_PATH . 'admin/_subpages/default.php' );
				$module = new AdminSubpages();
				break;
			case 'languages':
				require_once( SWS_ROOT_PATH . 'admin/_languages/default.php' );
				$module = new AdminLanguages();
				break;
			case 'settings':
				require_once( SWS_ROOT_PATH . 'admin/_settings/default.php' );
				$module = new AdminSettings();
				break;
			case 'accounts':
				require_once( SWS_ROOT_PATH . 'admin/_accounts/default.php' );
				$module = new AdminAccounts();
				break;
			case 'pages':
			default: 
				require_once( SWS_ROOT_PATH . 'admin/_pages/default.php' );
				$module = new AdminPages();
				break;
		}

		// Launches the admin module
		$module->execute( $this->registry );
	}

	/**
	 * Searches for input based on an array or bits
	 *
	 * @param array $bits the tokenized uri
	 * @return array new input keys
	 * @access private
	 * @since 1.0.0
	 */
	public function parseSEOURI( $bits = array() )
	{
		$out = array();

		if ( isset( $bits[0] ) && strlen( $bits[0] ) > 0 )
		{
			$out['module'] = $bits[0];
		}

		if ( isset( $bits[1] ) && strlen( $bits[1] ) > 0 )
		{
			$out['com'] = $bits[1];
		}

		return $out;
	}

	/**
	 * Tells the controller whether or not to propmt for login
	 *
	 * @return bool the requirement
	 * @access public
	 * @since 1.0.0
	 */
	public function requireLogin()
	{
		return TRUE;
	}
}

?>