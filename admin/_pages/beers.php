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

class AdminBeers extends Command
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
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "pages", 'com' => "beers" ), 'admin' ), $this->lang->getString('beers') );

		// Load the language
		$this->lang->loadStrings('beers');

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
				$this->listBeers();
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
	protected function listBeers()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$count      = array( '0' => array( 0 => 0 ), 'a' => array( 0 => 0 ), 'i' => array( 0 => 0 ) );
		$ids        = array( '0' => array(), 'a' => array(), 'i' => array() );
		$rows       = array();
		$pageWhere  = '';
		$queryWhere = '';
		$categories = $this->cache->getCache('categories');

		// Page title
		$this->display->setTitle( $this->lang->getString('beers_list_title') );
		$this->display->addJavascript("<script language='javascript' src='{$this->registry->getConfig('base_url')}js/jquery.js'></script>");
		$this->display->addJavascript("<script language='javascript' src='{$this->registry->getConfig('base_url')}js/admin.beers.js'></script>");

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( $this->lang->getString('beers_head_cat')           , "16%" );
		$this->html->td_header[] = array( $this->lang->getString('beers_head_name')          , "48%" );
		$this->html->td_header[] = array( $this->lang->getString('edit')                     , "6%" );

		//-----------------------------------------

		// Query accounts for this page
		$this->DB->query(
			"SELECT b.menu_item_id, b.title, b.category_id, b.active
				FROM menu_item b
				LEFT JOIN menu_category c ON (b.category_id = c.category_id)
				WHERE c.type <> 'na'
				GROUP BY b.menu_item_id, b.title, b.category_id, b.active
				ORDER BY c.position, b.title;"
		);

		// Loop through the results and add a row for each
		while( $r = $this->DB->fetchRow() )
		{
			if ( $r['active'] == 0 )
			{
				$r['title'] = "<span style='color:#ccc; font-style:italic;'>" . $r['title'] . "</span> ({$this->lang->getString('beers_disabled')})";
			}

			$count['0'][0] += 1;

			if ( ! isset( $count['0'][ $r['category_id'] ] ) )
			{
				$count['0'][ $r['category_id'] ] = 1;
				$ids['0'][ $r['category_id'] ] = array( $r['menu_item_id'] );
			}
			else
			{
				$count['0'][ $r['category_id'] ] += 1;
				$ids['0'][ $r['category_id'] ][] = $r['menu_item_id'];
			}

			if ( $r['active'] == 1 && ! isset( $count['a'][ $r['category_id'] ] ) )
			{
				$count['a'][ $r['category_id'] ] = 1;
				$count['a'][0] += 1;
				$ids['a'][ $r['category_id'] ] = array( $r['menu_item_id'] );
			}
			else if ( $r['active'] == 1 )
			{
				$count['a'][ $r['category_id'] ] += 1;
				$count['a'][0] += 1;
				$ids['a'][ $r['category_id'] ][] = $r['menu_item_id'];
			}
			else if ( $r['active'] == 0 && ! isset( $count['i'][ $r['category_id'] ] ) )
			{
				$count['i'][ $r['category_id'] ] = 1;
				$count['i'][0] += 1;
				$ids['i'][ $r['category_id'] ] = array( $r['menu_item_id'] );
			}
			else
			{
				$count['i'][ $r['category_id'] ] += 1;
				$count['i'][0] += 1;
				$ids['i'][ $r['category_id'] ][] = $r['menu_item_id'];
			}

			$rows[ $r['menu_item_id'] ] = $r;
		}

		// Create account link
		$html = "";

		// Begin table
		$html .= $this->html->startTable( $this->lang->getString('beers_list_table'), 'admin', $this->buildFilters( $count, $ids ) );

		foreach ( $rows as $r )
		{
			$html .= $this->html->addTdRow(
				array(
					$categories[ $r['category_id'] ]['title'], 
					$r['title'], 
					"<center><a href='".$this->display->buildURL( array( 'module' => 'pages', 'com' => 'beers', 'do' => 'edit',   'id' => $r['menu_item_id'] ), 'admin')."'>{$this->lang->getString('edit')}</a></center>",
				),
				'',
				'middle',
				" id='row{$r['menu_item_id']}'"
			);
		}

		// End table
		$html .= $this->html->endTable();

		//--------------------------------------

		// Send the html to the display handler
		$this->display->addContent( $html );
	}

	function buildFilters( $counts, $ids )
	{
		$cats = array();
		$numIDs = array();
		$categories = $this->cache->getCache('categories');

		foreach( $counts['0'] as $key => $val )
		{
			$cats[$key] = "'{$key}':'#sel{$key}ID'";
			$numIDs[$key] = "'{$key}':'#num{$key}ID'";
		}

		$out = $this->display->compiledTemplates('skin_beers')->beerFilters( $counts, $ids, $cats, $numIDs, $categories );

		return $out;
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

			$this->error->logError( 'beers_user_updated', FALSE );
			$this->listBeers();
		}
		else
		{
			$this->error->logError( 'beers_user_not_updated', TRUE );
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
		$title    = "{$this->lang->getString('beers_edit_title')} {$menuItem['title']}";
		$button   = $this->lang->getString('beers_edit_save');

		// Page title
		$this->display->setTitle( $title );

		// Add breadcrumb for this action
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "pages", 'com' => 'beers', 'do' => $type, 'id' => $menuItemID ), 'admin' ), $this->lang->getString('beers_'.$type.'_bread') );

		// Setup the hidden inputs for the form
		$html = $this->html->startForm(
			array(
				's'       => $this->user->getSessionID(),
				'app'     => 'admin',
				'module'  => 'pages',
				'com'     => 'beers',
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

		$html .= $this->html->startFieldset( $this->lang->getString('beers_form_info') );

		//-----------------------------------------
		// Information fieldset
		//-----------------------------------------

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('beers_form_id'),
				$menuItem['menu_item_id']
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('beers_form_guid'),
				$menuItem['toast_guid']
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('beers_form_cat'),
				$cats[ $menuItem['category_id'] ]['title']
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('beers_form_name'),
				$menuItem['title']
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('beers_form_points'),
				$this->html->formInput( 'points', $this->registry->txtStripslashes( isset( $_POST['points'] ) ? $_POST['points'] : $menuItem['points'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('beers_form_price'),
				"$ " . $this->html->formInput( 'price', $this->registry->txtStripslashes( isset( $_POST['price'] ) ? $_POST['price'] : $menuItem['price'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('beers_form_active'),
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