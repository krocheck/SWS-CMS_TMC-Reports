<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * API Session Processor
 * Last Updated: $Date: 2010-06-28 21:31:06 -0500 (Mon, 28 Jun 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Admin
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 27 $
 */

class ApiSessionsV1 extends ApiCommand
{
	/**
	 * Authentication requirements by request method
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	var $auth = array(
		'DELETE'	=> true,
		'GET'		=> false,
		'POST'		=> false,
		'PUT'		=> false
	);
	
	/**
	 * The main execute function
	 *
	 * @param object $params extra stuff from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doExecute( $params ) {}

	/**
	 * The delete execute function
	 *
	 * @param object $params extra stuff from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doDelete( $params )
	{
		if ( User::logout( $this->input['s'] ) )
		{
			$this->display->addJSON( 'status', 'ok' );
		}
		else
		{
			$this->display->addJSON( 'status', 'error' );
		}
		
		$this->display->doJSON();
	}

	/**
	 * The get execute function
	 *
	 * @param object $params extra stuff from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doGet( $params ) {}

	/**
	 * The post execute function
	 *
	 * @param object $params extra stuff from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doPost( $params )
	{
		$user = User::getUserByLogin( strtolower( $this->input['email'] ), $this->input['password'] );

		if ( ! is_object( $user ) )
		{
			$this->display->addJSON( 'status', 'invalid_login' );
		}
		else
		{
			$this->registry->clearPasswordInput();
		
			$this->display->addJSON( 'user', array( 'email' => $this->input['email'], 'auth_token' => $user->getSessionID() ) );
		}
		
		$this->display->doJSON();
	}

	/**
	 * The put execute function
	 *
	 * @param object $params extra stuff from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doPut( $params ) {}
}

?>