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

class ApiApp extends Application
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
		return "api";
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
		
		if ( ! isset( $this->input['com'] ) )
		{
			$this->input['com'] = '';
		}
		
		if ( $this->input['module'] == '' || $this->input['com'] == '' ) {}
		else if ( file_exists( SWS_ROOT_PATH . 'api/_' . $this->input['com'] . '/' . $this->input['module'] . '.php' ) )
		{
			require_once( SWS_ROOT_PATH . 'api/_' . $this->input['com'] . '/' . $this->input['module'] . '.php' );
			$temp = 'Api' . ucfirst( $this->input['com'] ) . ucfirst( $this->input['module'] );
			$module = new $temp();
			
			// Launches the admin module
			$module->execute( $this->registry );
		}

		$this->display->addJSON( 'status', 'not_found' );
		$this->display->doJSON();
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
		return FALSE;
	}
}

?>