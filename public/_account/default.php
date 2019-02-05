<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Admin Account Management
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

class PublicAccount extends Command
{
	/**
	 * The admin app skin generator
	 *
	 * @access protected
	 * @var AdminSkin
	 * @since 1.0.0
	 */
	protected $html;
	/**
	 * Array for the permission types, built based on the user's permission level
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $types = array();

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
		if ( ! isset( $this->input['do'] ) )
		{
			$this->input['do'] = '';
		}

		if ( ! is_object( $this->user ) || $this->user->getID() <= 0 )
		{
			$this->display->silentRedirect( $this->display->buildURL( array() ) );
		}

		// What are we doing?
		switch( $this->input['do'] )
		{
			case 'save':
				$this->save( 'edit' );
				break;
			default:
				$this->showForm( 'edit' );
				break;
		}

		// Send the final output
		$this->display->doOutput();
	}

	/**
	 * This thing saves the account information.
	 *
	 * @param string $type add|edit
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function save( $type = 'edit' )
	{
		//--------------------------------------------
		// INIT
		//--------------------------------------------

		$userID              = $this->user->getID();
		$firstName           = $this->registry->txtStripslashes( trim( $this->input['first_name'] ) );
		$lastName            = $this->registry->txtStripslashes( trim( $this->input['last_name'] ) );
		$email               = strtolower( $this->registry->txtStripslashes( trim( $this->input['email'] ) ) );
		$emailConfirm        = strtolower( $this->registry->txtStripslashes( trim( $this->input['email_confirm'] ) ) );
		$passwordNew         = $this->registry->txtStripslashes( trim($this->input['password_new'] ) );
		$passwordConfirm     = $this->registry->txtStripslashes( trim($this->input['password_confirm'] ) );

		$account             = array();

		//--------------------------------------------
		// Checks...
		//--------------------------------------------

		// If we are editing an exisiting user ...
		if ( $type == 'edit' )
		{
			// Make sure the user_id came in
			if ( ! $userID > 0 )
			{
				$this->error->logError( 'invalid_id', FALSE );
				$this->showForm();
				return;
			}

			$this->DB->query(
				"SELECT * FROM user WHERE user_id = {$userID};"
			);

			if ( $this->DB->getTotalRows() > 0 )
			{
				$account = $this->DB->fetchRow();
			}
			else
			{
				$this->error->logError( 'invalid_id', FALSE );
				$this->showForm();
				return;
			}

			// If they provided a new password, the two password fields must match
			if ( strlen( $passwordNew ) > 0 && $passwordNew != $passwordConfirm )
			{
				$this->error->logError( 'password_no_match', FALSE );
				$this->showForm( $type );
				return;
			}

			/*if ( $email != $account['email'] && $passwordNew == '' )
			{
				$this->error->logError( 'password_change', FALSE );
				$this->showForm( $type );
				return;
			}*/
		}

		// Make sure the necessary fields were filled out
		if ( ! ( strlen( $firstName ) > 0 && strlen( $lastName ) > 0 && strlen( $email ) > 0 ) )
		{
			$this->error->logError( 'incomplete_form', FALSE );
			$this->showForm( $type );
			return;
		}

		// For a new account, there must be a password and the two fields must match
		if ( $type == 'edit' && ( strlen( $emailConfirm ) > 0 && $email != $emailConfirm ) )
		{
			$this->error->logError( 'email_no_match', FALSE );
			$this->showForm( $type );
			return;
		}
		
		if ( $type == 'edit' && ( strlen( $emailConfirm ) == 0 && $email != $account['email'] ) )
		{
			$this->error->logError( 'email_confirm', FALSE );
			$this->showForm( $type );
			return;
		}
		
		if ( $type == 'edit' && strlen( $emailConfirm ) > 0 && ! User::getUserByLogin( $account['email'], $passwordNew ) )
		{
			$this->error->logError( 'email_change_password', FALSE );
			$this->showForm( $type );
			return;
		}

		// For a new account, there must be a password and the two fields must match
		if ( $type == 'add' && ( ! strlen( $passwordNew ) > 0 || $passwordNew != $passwordConfirm ) )
		{
			$this->error->logError( 'password_no_match', FALSE );
			$this->showForm( $type );
			return;
		}

		//--------------------------------------------
		// Save...
		//--------------------------------------------

		// Build the save array
		$array = array(
			'first_name'     => $firstName,
			'last_name'      => $lastName,
			'email'          => $email
		);

		// If this is a new account ...
		if ( $type == 'edit' )
		{
			// If there is a new password ...
			if ( strlen( $passwordNew ) > 0 )
			{
				// Create the password hash
				$array['pass_hash'] = md5( md5( $array['email'] ) . md5( time() ) );

				// Generate the password
				$array['password'] = md5( md5( $passwordNew ) . $array['pass_hash'] );
			}

			// Set the user id in the save array
			$array['user_id'] = $userID;

			// Send the update request to the User class
			if ( User::update( $array ) )
			{
				$this->error->logError( 'accounts_user_updated', FALSE );
				$this->showForm();
			}
			else
			{
				$this->error->logError( 'accounts_user_not_updated', TRUE );
				$this->showForm();
			}
		}
	}

	/**
	 * This thing show the form to add/edit an account.
	 *
	 * @param string $type add|edit
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function showForm( $type = 'edit' )
	{
		//-----------------------------------------
		// Init Vars
		//-----------------------------------------

		$userID  = $this->user->getID();
		$user    = array();

		//-----------------------------------------
		// Checks ...
		//-----------------------------------------

		// If we are adding a new user ...
		if ( $type == 'edit' )
		{
			// So let's make sure this user exists
			$this->DB->query("SELECT * FROM user WHERE user_id = '{$userID}';");

			$user = $this->DB->fetchRow();

			// If this user doesn't exist, throw an error
			if ( ! $user['user_id'] )
			{
				$this->error->logError( 'invalid_id', FALSE );
				$this->listAccounts();
				return;
			}
		}

		$this->display->setTitle( $this->lang->getString( 'account_title' ) );
		$this->display->setContent( $this->display->compiledTemplates('skin_public')->profileForm( array(), $user ) );
	}
}

?>