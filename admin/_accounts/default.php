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

class AdminAccounts extends Command
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
		// Check the user's credentials
		$this->checkPermission();

		// Add a breadcrumb for this module
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "accounts" ), 'admin' ), $this->lang->getString('accounts') );

		// Load the language
		$this->lang->loadStrings('accounts');

		// Get and load the table/form factory
		$this->html = $this->registry->getClass('AdminSkin');

		// Add the permissions to the aray
		$this->types[] = array( 'superadmin', $this->lang->getString('accounts_form_superadmin') );
		$this->types[] = array( 'admin', $this->lang->getString('accounts_form_admin') );
		$this->types[] = array( 'user',  $this->lang->getString('accounts_form_user')  );

		// Declaration for the module
		$module = NULL;

		if ( ! isset( $this->input['do'] ) )
		{
			$this->input['do'] = '';
		}

		// What are we doing?
		switch( $this->input['do'] )
		{
			case 'add':
				$this->showForm( 'add' );
				break;
			case 'edit':
				$this->showForm( 'edit' );
				break;
			case 'add_save':
				$this->save( 'add' );
				break;
			case 'edit_save':
				$this->save( 'edit' );
				break;
			case 'delete':
				$this->delete();
				break;
			case 'dodelete':
				$this->doDelete();
				break;
			default:
				$this->listAccounts();
				break;
		}

		// Send the final output
		$this->display->doOutput();
	}

	/**
	 * Makes sure the user can actually use the module.  Will throw error if not.
	 *
	 * @return void
	 * @access private
	 * @since 1.0.0
	 */
	private function checkPermission()
	{
		if ( $this->user->getPermission() != 'superadmin' )
		{
			$this->error->raiseError( 'no_permission', FALSE );
		}
	}

	/**
	 * Makes sure the user actually wants to delete this user.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function delete()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$userID   = intval($this->input['id']);
		$user     = array();

		// Get the user from the database
		$this->DB->query(
			"SELECT user_id, first_name, last_name, email, language_id, type
				FROM user WHERE user_id = '{$userID}';"
		);

		$user = $this->DB->fetchRow();

		// Throw an error if the user could not be found
		// or the user is trying to delete their own account
		if ( ! $user['user_id'] || $user['user_id'] == $this->user->getID() )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listAccounts();
			return;
		}

		// Form setup
		$formcode = 'dodelete';
		$title    = "{$this->lang->getString('accounts_delete_title')} {$user['first_name']} {$user['last_name']}";
		$button   = $this->lang->getString('accounts_delete_submit');

		// Page title
		$this->display->setTitle( $title );

		// Add breadcrumb for this action
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "accounts", 'do' => 'delete', 'id' => $userID ), 'admin' ), $this->lang->getString('accounts_delete_bread') );

		// Setup the hidden inputs for the form
		$html = $this->html->startForm(
			array(
				's'       => $this->user->getSessionID(),
				'app'     => 'admin',
				'module'  => 'accounts',
				'id'      => $userID,
				'do'      => $formcode
			)
		);

		//-----------------------------------------
		// Create the table for the form
		//-----------------------------------------

		$html .= $this->html->startTable( $title, 'admin-form' );

		$html .= $this->html->startFieldset();

		$html .= $this->html->addTdBasic( "{$this->lang->getString('accounts_delete_form_text')} {$user['first_name']} {$user['last_name']}?", "center" );

		$html .= $this->html->endFieldset();

		$html .= $this->html->endForm( $button, "", " {$this->lang->getString('or')} <a href='".$this->display->buildURL( array( 'module' => 'accounts' ), 'admin')."'>{$this->lang->getString('cancel')}</a>" );

		//--------------------------------------

		// Send the html to the display handler
		$this->display->addContent( $html );
	}

	/**
	 * Deletes the user
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doDelete()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$userID   = intval($this->input['id']);
		$user     = array();

		// Get the user form the database
		$this->DB->query(
			"SELECT user_id, first_name, last_name, email, language_id, type
				FROM user WHERE user_id = '{$userID}';"
		);

		$user = $this->DB->fetchRow();

		// Throw an error if the user could not be found
		// or the user is trying to delete their own account
		if ( ! $user['user_id'] || $user['user_id'] == $this->user->getID() )
		{
			$this->error->logError( 'invalid_id', FALSE );
		}
		else
		{
			// Send a delete request to the User class
			if ( User::delete( $user['user_id'] ) )
			{
				$this->error->logError( 'accounts_delete_success', TRUE, $user );
			}
			else
			{
				$this->error->logError( 'accounts_delete_fail', TRUE, $user );
			}
		}

		//-----------------------------------------

		// Done, go back to the account list
		$this->listAccounts();
	}

	/**
	 * Lists out the accounts in the system.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function listAccounts()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$count      = array();
		$pageWhere  = '';
		$queryWhere = '';
		$categories = $this->cache->getCache('categories');
		$catIDs     = implode(',', array_keys( $categories ) );

		$search = '';
		$linkParams = array( 'module' => 'accounts');

		if ( isset( $this->input['search'] ) && strlen( $this->input['search'] ) > 2 )
		{
			$search = "WHERE (first_name LIKE '%{$this->input['search']}%' OR last_name LIKE '%{$this->input['search']}%')";
			$pageWhere = "WHERE (first_name LIKE '%{$this->input['search']}%' OR last_name LIKE '%{$this->input['search']}%')";
			$linkParams['search'] = $this->input['search'];
		}
		else if ( isset( $this->input['search'] ) )
		{
			$this->error->addErrorLog("Searches must contain at least 3 characters");
		}

		// Page title
		$this->display->setTitle( $this->lang->getString('accounts_list_title') );

		// Build the page navigation if there are enough accounts to need multiple pages
		$pagelinks = $this->display->getPagelinks( 'user', $linkParams, 'admin', $pageWhere );

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( $this->lang->getString('accounts_head_name')       , "80%" );
		$this->html->td_header[] = array( $this->lang->getString('edit')                     , "10%" );
		$this->html->td_header[] = array( $this->lang->getString('delete')                   , "10%" );

		//-----------------------------------------

		// Create account link
		$html = "<div style='float:right;'>";

		$html .= $this->html->startForm(
			array(
				's'       => $this->user->getSessionID(),
				'app'     => 'admin',
				'module'  => 'accounts'
			)
		);

		$html .= "<a href='".$this->display->buildURL( array( 'module' => 'accounts', 'do' => 'add' ), 'admin')."'>{$this->lang->getString('accounts_create_new')}</a> &bull; ";

		$html .= $this->html->formInput( 'search', $this->registry->txtStripslashes( isset( $_POST['search'] ) ? $_POST['search'] : $user['search'] ), 'text', '', '15' ) . " ";

		$html .= $this->html->endForm( 'Search', '', '', 1 );

		$html .="</form></div>";

		// Begin table
		$html .= $this->html->startTable( $this->lang->getString('accounts_list_table'), 'admin', ( strlen( $pagelinks ) > 0 ?"\n<div class='pagelinks'>{$pagelinks}</div>" : "" ) );

		// Check for page number input
		if ( isset( $this->input['page'] ) && intval( $this->input['page'] ) > 1 )
		{
			// Setup limit information for query
			$itemsPerPage = intval( $this->settings['items_per_page'] );
			$currentPage  = intval( $this->input['page'] ) - 1;
			$start        = $itemsPerPage * $currentPage;
		}
		else
		{
			$start        = 0;
		}

		// Query accounts for this page
		$this->DB->query(
			"SELECT *
				FROM user {$search}
				ORDER BY last_name, first_name LIMIT $start,{$this->settings['items_per_page']};"
		);

		// Loop through the results and add a row for each
		while( $r = $this->DB->fetchRow() )
		{
			$html .= $this->html->addTdRow(
				array(
					"{$r['first_name']} {$r['last_name']} ({$this->lang->getString('accounts_form_'.$r['type'])})",
					"<center><a href='".$this->display->buildURL( array( 'module' => 'accounts', 'do' => 'edit',   'id' => $r['user_id'] ), 'admin')."'>{$this->lang->getString('edit')}</a></center>",
					"<center><a href='".$this->display->buildURL( array( 'module' => 'accounts', 'do' => 'delete', 'id' => $r['user_id'] ), 'admin')."'>{$this->lang->getString('delete')}</a></center>"
				)
			);
		}

		// End table
		$html .= $this->html->endTable();

		// Add page links
		$html .= ( strlen( $pagelinks ) > 0 ?"<br /><div class='pagelinks'>{$pagelinks}</div>" : "");

		//--------------------------------------

		// Send the html to the display handler
		$this->display->addContent( $html );
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
		$firstName           = $this->registry->txtStripslashes( trim( $this->input['first_name'] ) );
		$lastName            = $this->registry->txtStripslashes( trim( $this->input['last_name'] ) );
		$email               = strtolower( $this->registry->txtStripslashes( trim( $this->input['email'] ) ) );
		$perm                = $this->registry->txtStripslashes( trim( $this->input['type'] ) );
		$languageID          = intval( $this->input['language_id'] );
		$emailAlerts         = intval( $this->input['email_alerts'] );

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
				$this->listAccounts();
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
				$this->listAccounts();
				return;
			}
		}

		// Make sure the necessary fields were filled out
		if ( ! ( strlen( $firstName ) > 0 && strlen( $lastName ) > 0 && strlen( $email ) > 0 ) )
		{
			$this->error->logError( 'incomplete_form', FALSE );
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
			'language_id'    => $languageID,
			'email_alerts'   => $emailAlerts
		);

		// If this is a new account ...
		if ( $type == 'add' )
		{
			// Make sure this user doesn't already exist ...
			$this->DB->query("SELECT user_id FROM user WHERE email = '{$email}';");

			$user = $this->DB->fetchRow();

			// If they do, throw an error
			if ( is_array( $user ) && isset( $user['user_id'] ) && $user['user_id'] > 0 )
			{
				$this->error->logError( 'accounts_user_exists', FALSE );
				$this->listAccounts();
			}
			else if ( User::create( $array ) )
			{
				$this->error->logError( 'accounts_user_created', FALSE );

				$this->listAccounts();
			}
			else
			{
				$this->error->logError( 'accounts_user_not_created', TRUE );
				$this->listAccounts();
			}
		}
		// This user must already exist ...
		else
		{
			// Set the user id in the save array
			$array['user_id'] = $userID;

			// Send the update request to the User class
			if ( User::update( $array ) )
			{
				$this->error->logError( 'accounts_user_updated', FALSE );

				$this->listAccounts();
			}
			else
			{
				$this->error->logError( 'accounts_user_not_updated', TRUE );
				$this->listAccounts();
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
		//-----------------------------------------
		// Init Vars
		//-----------------------------------------

		$userID  = intval($this->input['id']);
		$user    = array();

		//-----------------------------------------
		// Checks ...
		//-----------------------------------------

		// If we are adding a new user ...
		if ( $type == 'add' )
		{
			// Set default values for form inputs
			$user['type'] = "user";
			$user['language_id'] = $this->settings['default_language_id'];

			// Form setup
			$formcode = 'add_save';
			$title    = $this->lang->getString('accounts_create_new');
			$button   = $this->lang->getString('accounts_create_new');
		}
		// We are editing an existing user ...
		else
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

			// Form setup
			$formcode = 'edit_save';
			$title    = "{$this->lang->getString('accounts_edit_title')} {$user['first_name']} {$user['last_name']}";
			$button   = $this->lang->getString('accounts_edit_save');
		}

		// Page title
		$this->display->setTitle( $title );

		// Add breadcrumb for this action
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "accounts", 'do' => $type, 'id' => $userID ), 'admin' ), $this->lang->getString('accounts_'.$type.'_bread') );

		// Setup the hidden inputs for the form
		$html = $this->html->startForm(
			array(
				's'       => $this->user->getSessionID(),
				'app'     => 'admin',
				'module'  => 'accounts',
				'id'      => $userID,
				'do'      => $formcode
			)
		);

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( "&nbsp;"  , "40%" );
		$this->html->td_header[] = array( "&nbsp;"  , "60%" );

		//-----------------------------------------
		//  Begin table
		//-----------------------------------------

		$html .= $this->html->startTable( $title, 'admin-form' );

		$html .= $this->html->startFieldset( $this->lang->getString('accounts_form_user_info') );

		//-----------------------------------------
		// Information fieldset
		//-----------------------------------------

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('accounts_form_perms'),
				$this->html->formDropdown(
					'type', 
					$this->types, 
					( isset( $_POST['type'] ) ? $_POST['type'] : $user['type'] )
				)
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('accounts_form_first_name'),
				$this->html->formInput( 'first_name', $this->registry->txtStripslashes( isset( $_POST['first_name'] ) ? $_POST['first_name'] : $user['first_name'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('accounts_form_last_name'),
				$this->html->formInput( 'last_name', $this->registry->txtStripslashes( isset( $_POST['last_name'] ) ? $_POST['last_name'] : $user['last_name'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('accounts_form_email'),
				$this->html->formInput( 'email', $this->registry->txtStripslashes( isset( $_POST['email'] ) ? $_POST['email'] : $user['email'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('accounts_form_email_alerts'),
				$this->html->formYesNo( 'email_alerts', intval( $_POST['email_alerts'] ) ? $_POST['email_alerts'] : $user['email_alerts'] )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('accounts_form_lang'),
				$this->html->formDropdown(
					'language_id', 
					$this->lang->getDropdownArray(), 
					( isset( $_POST['language_id'] ) ? $_POST['language_id'] : $user['language_id'] )
				)
			)
		);

		//-----------------------------------------
		// End table and form
		//-----------------------------------------

		$html .= $this->html->endFieldset();

		$html .= $this->html->endForm( $button );

		//--------------------------------------

		// Send the html to the display handler
		$this->display->addContent( $html );
	}
}

?>