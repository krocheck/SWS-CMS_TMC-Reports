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

class AdminMenu extends Command
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
		// Add a breadcrumb for this module
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "pages", 'com' => "menu" ), 'admin' ), $this->lang->getString('full_menu') );

		// Load the language
		$this->lang->loadStrings('menu');

		// Get and load the table/form factory
		$this->html = $this->registry->getClass('AdminSkin');

		// Add the permissions to the aray
		$this->types = $params;

		if ( ! isset( $this->input['do'] ) )
		{
			$this->input['do'] = '';
		}

		// What are we doing?
		switch( $this->input['do'] )
		{
			case 'edit':
				$this->showForm( 'edit' );
				break;
			case 'edit_save':
				$this->save( 'edit' );
				break;
			default:
				$this->listMenu();
				break;
		}

		// Send the final output
		$this->display->doOutput();
	}

	/**
	 * Lists out the accounts in the system.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function listMenu()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$count      = array();
		$pageWhere  = '';
		$queryWhere = '';
		$sold       = $this->cache->getCache('sold');

		$search = '';
		$linkParams = array( 'module' => "pages", 'com' => "menu" );

		if ( isset( $this->input['search'] ) && strlen( $this->input['search'] ) > 1 )
		{
			$test = str_replace(' ','%',$this->input['search'] );
			$search = "WHERE (title LIKE '%{$test}%')";
			$pageWhere = "WHERE (title LIKE '%{$test}%')";
			$linkParams['search'] = $test;
		}
		else if ( isset( $this->input['search'] ) )
		{
			$this->error->addErrorLog("Searches must contain at least 2 characters");
		}

		// Page title
		$this->display->setTitle( $this->lang->getString('menu_list_title') );

		// Build the page navigation if there are enough accounts to need multiple pages
		$pagelinks = $this->display->getPagelinks( 'menu_item', $linkParams, 'admin', $pageWhere );

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( $this->lang->getString('menu_head_id')             , "6%" );
		$this->html->td_header[] = array( $this->lang->getString('menu_head_name')           , "76%" );
		$this->html->td_header[] = array( $this->lang->getString('menu_head_qty')            , "6%" );
		$this->html->td_header[] = array( $this->lang->getString('edit')                     , "6%" );

		//-----------------------------------------

		// Create account link
		$html = "<div style='float:right;'>";
		
		$html .= $this->html->startForm(
			array(
				's'       => $this->user->getSessionID(),
				'app'     => 'admin',
				'module'  => 'pages',
				'com'     => 'menu'
			)
		);

		$html .= $this->html->formInput( 'search', $this->registry->txtStripslashes( isset( $_POST['search'] ) ? $_POST['search'] : $user['search'] ), 'text', '', '15' ) . " ";

		$html .= $this->html->endForm( 'Search', '', '', 1 );

		$html .="</form></div>";

		// Begin table
		$html .= $this->html->startTable( $this->lang->getString('menu_list_table'), 'admin', ( strlen( $pagelinks ) > 0 ?"\n<div class='pagelinks'>{$pagelinks}</div>" : "" ) );

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
				FROM menu_item {$search}
				ORDER BY menu_item_id DESC LIMIT $start,{$this->settings['items_per_page']};"
		);

		// Loop through the results and add a row for each
		while( $r = $this->DB->fetchRow() )
		{
			$html .= $this->html->addTdRow(
				array(
					"<div style='text-align:right;'><strong>{$r['menu_item_id']}</strong></div>",
					"{$r['title']}",
					"<div style='text-align:center;'>{$sold[ $r['menu_item_id'] ]}</div>",
					"<center><a href='".$this->display->buildURL( array( 'module' => 'pages', 'com' => 'menu', 'do' => 'edit', 'id' => $r['menu_item_id'] ), 'admin')."'>{$this->lang->getString('edit')}</a></center>",
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
	protected function save($type='edit')
	{
		//--------------------------------------------
		// INIT
		//--------------------------------------------

		$menuItemID          = intval( $this->input['id'] );
		$points              = intval( $this->input['points'] );
		$price               = floatval( $this->input['price'] );

		$menuItem             = array();

		//--------------------------------------------
		// Checks...
		//--------------------------------------------

		// Make sure the menu_item_id came in
		if ( ! $menuItemID > 0 )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listBeers();
			return;
		}

		$this->DB->query(
			"SELECT * FROM menu_item WHERE menu_item_id = {$menuItemID};"
		);

		if ( $this->DB->getTotalRows() > 0 )
		{
			$menuItem = $this->DB->fetchRow();
		}
		else
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listBeers();
			return;
		}

		//--------------------------------------------
		// Save...
		//--------------------------------------------

		// Build the save array
		$array = array(
			'title'      => $title,
			'price'      => $price,
			'points'     => $points
		);

		// Set the user id in the save array
		$array['menu_item_id'] = $menuItemID;

		// Send the update request to the User class
		if ( Beer::update( $array ) )
		{
			$this->cache->update('categories', TRUE);

			$this->error->logError( 'menu_user_updated', FALSE );
			$this->listBeers();
		}
		else
		{
			$this->error->logError( 'menu_user_not_updated', TRUE );
			$this->listBeers();
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
	protected function showForm( $type='edit' )
	{
		//-----------------------------------------
		// Init Vars
		//-----------------------------------------

		$menuItemID  = intval($this->input['id']);
		$menuItem    = array();
		$cats        = $this->cache->getCache('categories');

		//-----------------------------------------
		// Checks ...
		//-----------------------------------------

		// So let's make sure this category exists
		$this->DB->query("SELECT * FROM menu_item WHERE menu_item_id = '{$menuItemID}';");

		$menuItem = $this->DB->fetchRow();

		// If this category doesn't exist, throw an error
		if ( ! $menuItem['menu_item_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listBeers();
			return;
		}

		$menuItem['inactive'] = ( $menuItem['active'] == 1 ? 0 : 1 );

		// Form setup
		$formcode = 'edit_save';
		$title    = "{$this->lang->getString('menu_edit_title')} {$menuItem['title']}";
		$button   = $this->lang->getString('menu_edit_save');

		// Page title
		$this->display->setTitle( $title );

		// Add breadcrumb for this action
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "pages", 'com' => 'menu', 'do' => $type, 'id' => $menuItemID ), 'admin' ), $this->lang->getString('menu_'.$type.'_bread') );

		// Setup the hidden inputs for the form
		$html = $this->html->startForm(
			array(
				's'       => $this->user->getSessionID(),
				'app'     => 'admin',
				'module'  => 'pages',
				'com'     => 'menu',
				'id'      => $menuItemID,
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

		$html .= $this->html->startFieldset( $this->lang->getString('menu_form_info') );

		//-----------------------------------------
		// Information fieldset
		//-----------------------------------------

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('menu_form_id'),
				$menuItem['menu_item_id']
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('menu_form_guid'),
				$menuItem['toast_guid']
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('menu_form_cat'),
				$cats[ $menuItem['category_id'] ]['title']
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('menu_form_name'),
				$menuItem['title']
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('menu_form_points'),
				$this->html->formInput( 'points', $this->registry->txtStripslashes( isset( $_POST['points'] ) ? $_POST['points'] : $menuItem['points'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('menu_form_price'),
				"$ " . $this->html->formInput( 'price', $this->registry->txtStripslashes( isset( $_POST['price'] ) ? $_POST['price'] : $menuItem['price'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('menu_form_active'),
				$this->html->formYesNo( 'inactive', intval( isset( $_POST['inactive'] ) ? $_POST['inactive'] : $menuItem['inactive'] ) )
			)
		);

		//-----------------------------------------
		// Next set, Password fieldset
		//-----------------------------------------

		$html .= $this->html->endFieldset();

		$html .= $this->html->endForm( $button );

		//--------------------------------------

		// Send the html to the display handler
		$this->display->addContent( $html );
	}
}

class Beer
{
	/**
	 * Updates a menu item in the database
	 *
	 * @param array $params an array of the keys and values
	 * @return bool true or false depending on success
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function update( $params )
	{
		$fieldList = array( 'points', 'price' );
		$values = "";
		$out = FALSE;
		
		if ( isset( $params['menu_item_id'] ) && $params['menu_item_id'] > 0 )
		{
			foreach( $params as $k => $v )
			{
				if ( in_array( $k, $fieldList ) )
				{
					$values .= "{$k} = '{$v}',";
				}
			}
			
			Registry::$instance->DB->query(
				"UPDATE menu_item SET " . substr($values,0,strlen($values)-1) . " WHERE menu_item_id = '{$params['menu_item_id']}';"
			);
			
			$out = TRUE;
		}
		
		return $out;
	}
}

?>