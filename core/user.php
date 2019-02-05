<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * User data class
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

class User
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
	 * The user's session
	 *
	 * @access protected
	 * @var Session
	 * @since 1.0.0
	 */
	protected $session;
	/**
	 * The user's preferred language
	 *
	 * @access protected
	 * @var Language
	 * @since 1.0.0
	 */
	protected $language;
	/**
	 * The user id
	 *
	 * @access protected
	 * @var int
	 * @since 1.0.0
	 */
	protected $id;
	/**
	 * The club id
	 *
	 * @access protected
	 * @var int
	 * @since 1.0.0
	 */
	protected $clubID;
	/**
	 * The user's permission level
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $permission;
	/**
	 * The user's first name
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $firstName;
	/**
	 * The user's last name
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $lastName;
	/**
	 * The user's email/username
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $email;

	/**
	 * Constructor that loads the registry
	 *
	 * @param Registry $registry the main program registry
	 * @param array $dbRow array from the DB with the user information
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct( Registry $registry, array $dbRow )
	{
		$this->registry = $registry;

		$this->id             = $dbRow['user_id'];
		$this->firstName      = $dbRow['first_name'];
		$this->lastName       = $dbRow['last_name'];
		$this->email          = $dbRow['email'];
		$this->permission     = $dbRow['type'];

		$this->language       = $this->registry->getLang()->getLanguageByID( $dbRow['language_id'] );

		$this->registry->getLang()->setActive( $this->language );

		$this->registry->getDisplay()->addDebug( "User Loaded: {$this->firstName} {$this->lastName}" );

		if ( isset( $dbRow['session_id'] ) && strlen( $dbRow['session_id'] ) == 32 )
		{
			$this->session = new Session( $this->registry, array( 'session_id' => $dbRow['session_id'], 'session_start' => $dbRow['session_start'], 'remember' => $dbRow['remember'], 'application' => SWS_THIS_APPLICATION ) );
		}
		else
		{
			$this->session = Session::create( $this->id, $this->email, $dbRow['remember'] );
		}

		if ( $dbRow['remember'] == '1' || $dbRow['remember'] == 1 )
		{
			$this->cookie  = $this->registry->getCookie()->saveCookie(SWS_THIS_APPLICATION, $this->session->getID(), ( time() + $this->registry->getSetting('session_timeout') * 60 ) );
		}
	}

	/**
	 * Inserts a new user into the database
	 *
	 * @param array $params an array of the keys and values
	 * @return bool true or false depending on success
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function create( $params )
	{
		$fieldList = array( 'first_name', 'last_name', 'email', 'email_alerts', 'type', 'language_id' );
		$keys = "";
		$values = "";
		
		foreach( $params as $k => $v )
		{
			if ( in_array( $k, $fieldList ) )
			{
				$keys .= "{$k},";
				$values .= "'{$v}',";
			}
		}
		
		Registry::$instance->DB->query(
			"INSERT INTO user (" . substr($keys,0,strlen($keys)-1) . ") VALUES (" . substr($values,0,strlen($values)-1) . ");"
		);
		
		return TRUE;
	}

	/**
	 * Deletes a user from the database
	 *
	 * @param int $userID the user to be deleted
	 * @return bool true or false depending on success
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function delete( $userID )
	{
		Registry::$instance->DB->query(
			"DELETE FROM user WHERE user_id = '{$userID}';"
		);
		Registry::$instance->DB->query(
			"DELETE FROM session WHERE user_id = '{$userID}';"
		);
		Registry::$instance->DB->query(
			"DELETE FROM metadata WHERE module = 'user' AND id = '{$userID}';"
		);
		
		return TRUE;
	}

	public static function getDropdownArray( $where = '', $order = '', $key = 'user_id', $all = FALSE )
	{
		$out = array();

		if ( $all )
		{
			$out[0] = array( 0, Registry::$instance->lang->getString('all_accounts') );
		}

		if ( strlen( $where ) > 0 )
		{
			$where = " WHERE " . $where;
		}

		if ( strlen( $order ) > 0 )
		{
			$order = " ORDER BY " . $order;
		}

		Registry::$instance->DB->query(
			"SELECT * FROM user{$where}{$order};"
		);

		while( $row = Registry::$instance->DB->fetchRow() )
		{
			$out[ $row[ $key ] ] = array( $row[ $key ], sprintf( "%s, %s", $row['last_name'], $row['first_name'] ) );
		}
		
		return $out;
	}

	/**
	 * Returns the user's email address
	 *
	 * @return string the email
	 * @access public
	 * @since 1.0.0
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Returns the user's first name
	 *
	 * @return string the first name
	 * @access public
	 * @since 1.0.0
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * Returns the user id
	 *
	 * @return int the user id
	 * @access public
	 * @since 1.0.0
	 */
	public function getID()
	{
		return $this->id;
	}

	/**
	 * Returns the user's last name
	 *
	 * @return string the last name
	 * @access public
	 * @since 1.0.0
	 */
	public function getLastName()
	{
		return $this->lastName;
	}

	/**
	 * Returns the user's session
	 *
	 * @return session the session object
	 * @access public
	 * @since 1.0.0
	 */
	public function getPermission()
	{
		return $this->permission;
	}

	/**
	 * Returns the user's session
	 *
	 * @return session the session object
	 * @access public
	 * @since 1.0.0
	 */
	public function getSession()
	{
		return $this->session;
	}

	/**
	 * Returns the user's session id (shortcut)
	 *
	 * @return int session id
	 * @access public
	 * @since 1.0.0
	 */
	public function getSessionID()
	{
		return $this->session->getID();
	}

	/**
	 * Return the user with email and password provided.  Null return if params are not valid together.
	 *
	 * @param string $email the email/username
	 * @param string $password the password
	 * @return User|void
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function getUserByLogin( $email, $password )
	{
		$out       = NULL;
		$passCheck = '';

		Registry::$instance->getDB()->query(
			"SELECT u.user_id
				FROM user u
				WHERE u.email = '{$email}';"
		);

		$check = Registry::$instance->getDB()->fetchRow();
		$ad    = Registry::$instance->getClass('AdldapAPI');

		if ( $ad->doLogin( $email, $password ) )
		{
			if ( is_array( $check ) && isset( $check['user_id'] ) )
			{
				Registry::$instance->getDB()->query(
					"SELECT u.user_id, u.first_name, u.last_name, u.email, u.language_id, u.type,
						s.session_id, s.session_start, s.last_activity, s.ip_address
						FROM user u
						LEFT JOIN session s ON u.user_id = s.user_id AND s.application = '".SWS_THIS_APPLICATION."'
						WHERE u.email = '{$email}';"
				);

				$row = Registry::$instance->getDB()->fetchRow();
			}
			else
			{
				$row = [
					'first_name'  => $ad->getFirstName(),
					'last_name'   => $ad->getLastName(),
					'user_id'     => 0,
					'email'       => $username,
					'type'        => 'user',
					'language_id' => 1
				];

				if ( User::create( $row ) )
				{
					$row['user_id'] = Registry::$instance->getDB()->getInsertID();
				}
			}

			if ( SWS_THIS_APPLICATION == 'public' )
			{
				if ( Registry::$instance->getInput('remember') == '1' )
				{
					$row['remember'] = 1;
				}
				else
				{
					$row['remember'] = 0;
				}
			}
			else
			{
				$row['remember'] = 1;
			}

			if ( is_array( $row ) && isset( $row['user_id'] ) )
			{
				Registry::$instance->getDB()->query(
					"INSERT INTO metadata (module, id, meta_key, meta_value) VALUES ('user', '{$row['user_id']}', 'login', NOW() );"
				);

				$out = new User( Registry::$instance, $row );
			}
		}

		return $out;
	}

	/**
	 * Return the user with the session provided.  Null return if id is not valid or expired.
	 *
	 * @param string $session the session id
	 * @return User|void
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function getUserBySession( $session )
	{
		Registry::$instance->getDB()->query(
			"SELECT u.user_id, u.first_name, u.last_name, u.email, u.language_id, u.type,
				s.session_id, s.session_start, s.last_activity, s.remember, s.ip_address
				FROM session s
				LEFT JOIN user u ON u.user_id = s.user_id
				WHERE s.session_id = '{$session}' AND s.application = '".SWS_THIS_APPLICATION."';"
		);
		
		$row = Registry::$instance->getDB()->fetchRow();
		
		if ( is_array( $row ) && isset( $row['user_id'] ) )
		{
			$out = new User( Registry::$instance, $row );
		}

		return $out;
	}

	/**
	 * Removes the session from the database if it exists
	 *
	 * @param string $session the session id
	 * @return bool true if seesion was terminated
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function logout( $session )
	{
		$out = FALSE;
		
		Registry::$instance->getDB()->query(
			"SELECT user_id FROM session
				WHERE session_id = '{$session}';"
		);
		
		$row = Registry::$instance->getDB()->fetchRow();
		
		if ( is_array($row) && count($row) > 0 )
		{
			$out = TRUE;
		}
		
		Registry::$instance->getDB()->query(
			"DELETE FROM session 
				WHERE session_id = '{$session}';"
		);
		
		Registry::$instance->getCookie()->saveCookie(SWS_THIS_APPLICATION, '');
		
		return $out;
	}

	/**
	 * Saves a user form
	 *
	 * @param string $type save type add|edit
	 * @return string status
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function saveForm( $type = 'add' )
	{
		//--------------------------------------------
		// INIT
		//--------------------------------------------

		$userID              = intval( Registry::$instance->input['id'] );
		$firstName           = Registry::$instance->txtStripslashes( trim( Registry::$instance->input['first_name'] ) );
		$lastName            = Registry::$instance->txtStripslashes( trim( Registry::$instance->input['last_name'] ) );
		$email               = strtolower( Registry::$instance->txtStripslashes( trim( Registry::$instance->input['email'] ) ) );
		$emailConfirm        = strtolower( Registry::$instance->txtStripslashes( trim( Registry::$instance->input['email_confirm'] ) ) );
		$perm                = Registry::$instance->txtStripslashes( trim( Registry::$instance->input['type'] ) );
		$languageID          = intval( Registry::$instance->input['language_id'] );
		$emailAlerts         = intval( Registry::$instance->input['email_alerts'] );

		$account             = array();

		//--------------------------------------------
		// Checks...
		//--------------------------------------------

		if ( $type == 'edit' && SWS_THIS_APPLICATION == 'api')
		{
			$userID = Registry::$instance->user->getID();
		}

		// If we are editing an exisiting user ...
		if ( $type == 'edit' )
		{
			// Make sure the user_id came in
			if ( ! $userID > 0 )
			{
				return('invalid_id');
			}

			Registry::$instance->DB->query(
				"SELECT * FROM user WHERE user_id = {$userID};"
			);

			if ( Registry::$instance->DB->getTotalRows() > 0 )
			{
				$account = Registry::$instance->DB->fetchRow();
			}
			else
			{
				return('invalid_id');
			}
		}

		// Make sure the necessary fields were filled out
		if ( ! ( strlen( $firstName ) > 0 && strlen( $lastName ) > 0 && strlen( $email ) > 0 ) )
		{
			return('incomplete_form');
		}

		// Make sure the necessary fields were filled out
		if ( $type == 'add' && $clubID == 0 )
		{
			return('incomplete_form');
		}

		// For a new account, there must be a password and the two fields must match
		if ( $type == 'add' && ( ! strlen( $emailConfirm ) > 0 || $email != $emailConfirm ) )
		{
			return('email_no_match');
		}

		// For a new account, there must be a password and the two fields must match
		if ( $type == 'edit' && ( strlen( $emailConfirm ) > 0 && $email != $emailConfirm ) )
		{
			return('email_no_match');

		}
		
		if ( $type == 'edit' && ( strlen( $emailConfirm ) == 0 && $email != $account['email'] ) )
		{
			return('email_confirm');
		}
		
		if ( $type == 'edit' && strlen( $emailConfirm ) > 0 && ! User::getUserByLogin( $account['email'], $passwordNew ) )
		{
			return('email_change_password');
		}
		
		if ( $languageID == 0 )
		{
			$languageID = Registry::$instance->getSetting('default_language_id');
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
			'language_id'    => $languageID,
			'email_alerts'   => $emailAlerts
		);

		// If this is a new account ...
		if ( $type == 'add' )
		{
			// Add user type if not set
			if ( $array['type'] == '' || SWS_THIS_APPLICATION == 'api' )
			{
				$array['type'] = 'user';
			}

			// Make sure this user doesn't already exist ...
			Registry::$instance->DB->query("SELECT user_id FROM user WHERE email = '{$email}';");

			$user = Registry::$instance->DB->fetchRow();

			// If they do, throw an error
			if ( is_array( $user ) && isset( $user['user_id'] ) && $user['user_id'] > 0 )
			{
				return('accounts_user_exists');
			}
			// If they don't, then send a create request to the User class
			else if ( User::create( $array ) )
			{
				return('accounts_user_created');
			}
			else
			{
				return('accounts_user_not_created');
			}
		}
		// This user must already exist ...
		else
		{
			// Remove user type if API
			if ( SWS_THIS_APPLICATION == 'api' )
			{
				unset( $array['type'] );
			}

			// Set the user id in the save array
			$array['user_id'] = $userID;

			// Send the update request to the User class
			if ( User::update( $array ) )
			{
				return('accounts_user_updated');
			}
			else
			{
				return('accounts_user_not_updated');
			}
		}
	}

	/**
	 * Updates a user in the database
	 *
	 * @param array $params an array of the keys and values
	 * @return bool true or false depending on success
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function update( $params )
	{
		$fieldList = array( 'first_name', 'last_name', 'email', 'email_alerts', 'type', 'language_id' );
		$values = "";
		$out = FALSE;
		
		if ( isset( $params['user_id'] ) && $params['user_id'] > 0 )
		{
			foreach( $params as $k => $v )
			{
				if ( in_array( $k, $fieldList ) )
				{
					$values .= "{$k} = '{$v}',";
				}
			}
			
			Registry::$instance->DB->query(
				"UPDATE user SET " . substr($values,0,strlen($values)-1) . " WHERE user_id = '{$params['user_id']}';"
			);
			
			$out = TRUE;
		}
		
		return $out;
	}
}

?>