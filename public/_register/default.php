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

class PublicRegister extends Command
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

		if ( is_object( $this->user ) && $this->user->getID() > 0 )
		{
			$this->display->silentRedirect( $this->display->buildURL( array() ) );
		}

		// What are we doing?
		switch( $this->input['do'] )
		{
			case 'create':
				$this->save( 'add' );
				break;
			default:
				$this->showForm( 'add' );
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
	protected function save($type='add')
	{
		//--------------------------------------------
		// INIT
		//--------------------------------------------

		$userID              = intval( $this->input['id'] );
		$firstName           = ucfirst( $this->registry->txtStripslashes( trim( $this->input['first_name'] ) ) );
		$lastName            = ucfirst( $this->registry->txtStripslashes( trim( $this->input['last_name'] ) ) );
		$email               = strtolower( $this->registry->txtStripslashes( trim( $this->input['email'] ) ) );
		$emailConfirm        = strtolower( $this->registry->txtStripslashes( trim( $this->input['email_confirm'] ) ) );
		$perm                = 'user';
		$clubID              = intval( $this->input['club_id'] );
		$languageID          = $this->registry->getSetting('default_language_id');
		$passwordNew         = $this->registry->txtStripslashes( trim($this->input['password_new'] ) );
		$passwordConfirm     = $this->registry->txtStripslashes( trim($this->input['password_confirm'] ) );

		$account             = array();

		//--------------------------------------------
		// Checks...
		//--------------------------------------------

		// Make sure the necessary fields were filled out
		if ( ! ( strlen( $firstName ) > 0 && strlen( $lastName ) > 0 && strlen( $email ) > 0 && strlen( $emailConfirm ) > 0 && $clubID > 0 ) )
		{
			$this->error->logError( 'incomplete_form', FALSE );
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

		// For a new account, there must be a password and the two fields must match
		if ( $type == 'add' && ( ! strlen( $emailConfirm ) > 0 || $email != $emailConfirm ) )
		{
			$this->error->logError( 'email_no_match', FALSE );
			$this->showForm( $type );
			return;
		}

		//--------------------------------------------
		// Save...
		//--------------------------------------------

		// Build the save array
		$array = array(
			'type'           => $perm,
			'first_name'     => $firstName,
			'last_name'      => $lastName,
			'email'          => $email,
			'language_id'    => $languageID
		);

		// If this is a new account ...
		if ( $type == 'add' )
		{
			// Create the password hash
			$array['pass_hash'] = md5( md5( $array['email'] ) . md5( time() ) );

			// Generate the password
			$array['password'] = md5( md5( $array['email'] ) . md5( md5( $passwordNew ) . $array['pass_hash'] ) );

			// Add the Club ID
			$array['club_id'] = $clubID;

			// Make sure this user doesn't already exist ...
			$this->DB->query("SELECT user_id FROM user WHERE email = '{$email}';");

			$user = $this->DB->fetchRow();

			// Make sure this user doesn't already exist ...
			$this->DB->query("SELECT club_id FROM user WHERE club_id = '{$clubID}';");

			$club = $this->DB->fetchRow();

			// If they do, throw an error
			if ( is_array( $user ) && isset( $user['user_id'] ) && $user['user_id'] > 0 )
			{
				$this->error->logError( 'accounts_user_exists', FALSE );
				$this->showForm();
			}
			else if ( $clubID > 0 && is_array( $club ) && isset( $club['club_id'] ) && $club['club_id'] > 0 )
			{
				$this->error->logError( 'accounts_club_exists', FALSE );
				$this->showForm();
			}
			// If they don't, then send a create request to the User class
			else if ( User::create( $array ) )
			{
				$this->error->logError( 'accounts_user_created', FALSE );

				$login = new Login();
				$login->execute( $this->registry );
				$this->registry->setUser( $login->backgroundLogin() );

				$this->display->silentRedirect( $this->display->buildURL( array() ) );
			}
			else
			{
				$this->error->logError( 'accounts_user_not_created', TRUE );
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
	protected function showForm( $type='add' )
	{
		$this->display->setTitle( $this->lang->getString( 'register_title' ) );
		$this->display->setContent( $this->display->compiledTemplates('skin_public')->registerForm( array() ) );

		$this->display->doOutput();
	}
}

?>