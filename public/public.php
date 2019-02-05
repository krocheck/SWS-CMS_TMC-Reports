<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Public application wrapper
 * Last Updated: $Date: 2010-06-12 17:39:13 -0500 (Sat, 12 Jun 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Public
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 14 $
 */

class PublicApp extends Application
{
	/**
	 * Page controller
	 *
	 * @access protected
	 * @var PageController
	 * @since 1.0.0
	 */
	protected $controller;

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
		$this->loadModules();
		$this->loadContent();
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

		if ( isset( $params['page_id'] ) && $params['page_id'] > 0 )
		{
			$uri = $this->controller->buildSEOURI( $params['page_id'] );

			if ( strlen( $uri ) > 0 )
			{
				$out['uri'] .= $uri;
			}

			unset( $out['params']['page_id'] );
		}

		return $out;
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
		$user  = $this->registry->getUser();
		$links = array();
		$out   = '';

		if ( is_object( $user ) )
		{
			$links[]  = array( 'url' => $this->display->buildURL( array_merge( $this->registry->filterInputsToKeep(), array('logout' => "true") ) ), 'text' => 'LOG OUT' );
		}
		else
		{
			if ( ! isset( $this->input['login'] ) && ! isset( $this->input['logout'] ) )
			{
				$links[]  = array( 'url' => $this->display->buildURL( array_merge( $this->registry->filterInputsToKeep(), array('login' => "false") ) ), 'text' => 'LOG IN' );
			}
		}

		$out   = $this->display->compiledTemplates('skin_public')->userLinks( $links );

		return $out;
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
		return "public";
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
		if ( isset( $this->input['page_id'] ) )
		{
			$temp = $this->controller->getPage( intval( $this->input['page_id'] ) );
		}
		else
		{
			$temp = $this->controller->getHomePage();
		}
		
		if ( is_object( $temp ) && $temp->getID() > 0 )
		{
			$temp->process();
		}
		else
		{
			$this->error->raiseError( 'not_found', FALSE );
		}
	}

	/**
	 * Get the stuff from the database
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	private function loadContent()
	{
		$this->controller->getCache();
		$this->controller->loadPages();
	}

	/**
	 * Loads and sets up the controllers
	 *
	 * @return void
	 * @access private
	 * @since 1.0.0
	 */
	private function loadModules()
	{
		$this->controller = $this->registry->getClass('PageController');
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
		return $this->controller->findPageFromURI( $bits );
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
		$out = FALSE;
		
		if ( $this->registry->getSetting('public_login') == 1 )
		{
			$out = TRUE;
		}
		
		return $out;
	}
}

?>