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

class SpoolerApp extends Application
{
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
		$this->registry->overrideSetting('seo_url', 0);
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
		return $params;
	}

	/**
	 * Builds the tabs
	 *
	 * @return void
	 * @access private
	 * @since 1.0.0
	 */
	private function buildNavigation() { }

	/**
	 * Makes the user area display
	 *
	 * @return string html of the area
	 * @access private
	 * @since 1.0.0
	 */
	public function buildUserLinks() { }

	/**
	 * Makes sure the user can actually use the app.  Will throw error if not.
	 *
	 * @return void
	 * @access private
	 * @since 1.0.0
	 */
	private function checkPermission() { }

	/**
	 * Get the app's name
	 *
	 * @return string the name
	 * @access public
	 * @since 1.0.0
	 */
	public function getName()
	{
		return "spooler";
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
			case 'auto':
				require_once( SWS_ROOT_PATH . 'spooler/_auto/default.php' );
				$module = new SpoolerAuto();
				break;
			case 'fields':
				require_once( SWS_ROOT_PATH . 'spooler/_fields/default.php' );
				$module = new SpoolerFields();
				break;
			case 'items':
				require_once( SWS_ROOT_PATH . 'spooler/_items/default.php' );
				$module = new SpoolerItems();
				break;
			case 'projects':
				require_once( SWS_ROOT_PATH . 'spooler/_projects/default.php' );
				$module = new SpoolerProjects();
				break;
			case 'sections':
				require_once( SWS_ROOT_PATH . 'spooler/_sections/default.php' );
				$module = new SpoolerSections();
				break;
			case 'tags':
				require_once( SWS_ROOT_PATH . 'spooler/_tags/default.php' );
				$module = new SpoolerTags();
				break;
			case 'tasks':
				require_once( SWS_ROOT_PATH . 'spooler/_tasks/default.php' );
				$module = new SpoolerTasks();
				break;
			case 'teams':
				require_once( SWS_ROOT_PATH . 'spooler/_teams/default.php' );
				$module = new SpoolerTeams();
				break;
			case 'users':
				require_once( SWS_ROOT_PATH . 'spooler/_users/default.php' );
				$module = new SpoolerUsers();
				break;
			case 'workspaces':
				require_once( SWS_ROOT_PATH . 'spooler/_workspaces/default.php' );
				$module = new SpoolerWorkspaces();
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
	public function parseSEOURI( $bits = array() ) { }

	/**
	 * Tells the controller whether or not to propmt for login
	 *
	 * @return bool the requirement
	 * @access public
	 * @since 1.0.0
	 */
	public function requireLogin()
	{
		return FALSE;
	}
}

?>