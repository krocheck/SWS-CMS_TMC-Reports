<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 *
 * Login and session handler
 * Last Updated: $Date: 2010-04-28 14:46:36 -0500 (Wed, 28 Apr 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Core
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 4 $
 */

class Login extends Command
{
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
	 * The main login processor
	 *
	 * @return User|void
	 * @access public
	 * @since 1.0.0
	 */
	public function backgroundLogin()
	{
		$user = NULL;
		$newLogin = false;

		if ( isset( $this->input['email'] ) && isset( $this->input['password_new'] ) )
		{
			$user = User::getUserByLogin( strtolower( $this->input['email'] ), $this->input['password_new'] );
		}

		return $user;
	}

	/**
	 * The main login processor
	 *
	 * @return User|void
	 * @access public
	 * @since 1.0.0
	 */
	public function processLogin()
	{
		$user = NULL;
		$newLogin = false;

		if ( isset( $this->input['logout'] ) && isset( $this->input['s'] ) && strlen( $this->input['s'] ) == 32 && ! isset( $this->input['login'] ) )
		{
			if ( User::logout( $this->input['s'] ) )
			{
				$this->error->logError( 'logout_success', FALSE );
			}
		}
		else if ( isset( $this->input['logout'] ) && strlen( $this->registry->getCookie()->getCookie(SWS_THIS_APPLICATION) ) == 32 && ! isset( $this->input['login'] ) )
		{
			if ( User::logout( $this->registry->getCookie()->getCookie(SWS_THIS_APPLICATION) ) )
			{
				$this->error->logError( 'logout_success', FALSE );
			}
		}
		else if ( strlen( $this->registry->getCookie()->getCookie(SWS_THIS_APPLICATION) ) == 32 && ! isset( $this->input['login'] ) )
		{
			$user = User::getUserBySession( $this->registry->getCookie()->getCookie(SWS_THIS_APPLICATION) );

			if ( ! is_object( $user ) )
			{
				$this->registry->getCookie()->saveCookie(SWS_THIS_APPLICATION, '');
				$this->error->logError( 'invalid_session', FALSE );
			}
		}
		else if ( isset( $this->input['s'] ) && strlen( $this->input['s'] ) == 32 && ! isset( $this->input['login'] ) )
		{
			$user = User::getUserBySession( $this->input['s'] );

			if ( ! is_object( $user ) )
			{
				$this->error->logError( 'invalid_session', FALSE );
			}
		}
		else if ( isset( $this->input['login'] ) && isset( $this->input['email'] ) && isset( $this->input['password'] ) )
		{
			$user = User::getUserByLogin( strtolower( $this->input['email'] ), $this->input['password'] );

			if ( ! is_object( $user ) )
			{
				$this->error->logError( 'invalid_login', FALSE );
			}

			$newLogin = true;
		}

		$this->registry->clearPasswordInput();

		if ( ! is_object( $user ) )
		{
			if ( $this->registry->getHeader('Content-Type') == 'application/json' )
			{
				$this->display->addJSON( 'status', 'invalid_token' );
				$this->display->doJSON();
			}
			else
			{
				$this->showLogin();
			}
		}
		else
		{
			if ( SWS_THIS_APPLICATION == 'public' && $newLogin == true )
			{
				header( 'Location: ' . $this->display->buildURL( array_merge( $this->registry->filterInputsToKeep(), array('s' => $user->getSessionID() ) ) ) );
				exit();
			}

			return $user;
		}
	}

	/**
	 * Prints the login screen
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function showLogin()
	{
		$this->display->setTitle( $this->lang->getString( 'login_title' ) );
		$this->display->setContent( $this->display->compiledTemplates('skin_'.SWS_THIS_APPLICATION)->loginForm( $this->registry->filterInputsToKeep() ) );

		$this->display->doOutput();
	}
}

?>