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

class AdminCategory extends Command
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
	protected $types = array(
		'na' => array( 0 => 'na', 1 => 'Not Used' ),
		'tap' => array( 0 => 'tap', 1 => 'Tap Beer' ),
		'reserve' => array( 0 => 'reserve', 1 => 'Cellar Reserve' )
	);

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
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "pages", 'com' => "category" ), 'admin' ), $this->lang->getString('category') );

		// Load the language
		$this->lang->loadStrings('category');

		// Get and load the table/form factory
		$this->html = $this->registry->getClass('AdminSkin');

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
				$this->listCategories();
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
		if ( ! ( $this->user->getPermission() == 'admin' || $this->user->getPermission() == 'superadmin' ) )
		{
			$this->error->raiseError( 'no_permission', FALSE );
		}
	}

	/**
	 * Lists out the categories in the system.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function listCategories()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$count      = array();
		$pageWhere  = '';
		$queryWhere = '';

		//-----------------------------------------
		// Do we need to do something else first?
		//-----------------------------------------
		
		switch ( $this->input['op'] )
		{
			case 'up':
				$this->move('up');
				break;
			case 'down':
				$this->move('down');
				break;
			default:
		}

		// Page title
		$this->display->setTitle( $this->lang->getString('category_list_title') );

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( $this->lang->getString('order')                    , "12%" );
		$this->html->td_header[] = array( $this->lang->getString('category_head_name')       , "44%" );
		$this->html->td_header[] = array( $this->lang->getString('category_head_type')       , "38%" );
		$this->html->td_header[] = array( $this->lang->getString('edit')                     , "6%" );

		//-----------------------------------------

		// Begin table
		$html .= $this->html->startTable( $this->lang->getString('category_list_table'), 'admin');

		// Query categories for this page
		$this->DB->query(
			"SELECT * FROM menu_category ORDER BY position;"
		);

		$count_order = $this->DB->getTotalRows();

		// Loop through the results and add a row for each
		while( $r = $this->DB->fetchRow() )
		{
			$r['print_type'] = $this->types[ $r['type'] ][1];

			if ( $r['active'] == 0 )
			{
				$r['title'] = "<span style='color:#ccc; font-style:italic;'>" . $r['title'] . "</span> (Disabled)";
			}

			$html_order  = $r['position'] > 1            ? $this->html->upButton(   $this->display->buildURL( array( 'module' => 'pages', 'com' => 'category', 'op' => 'up',   'id' => $r['category_id'] ), 'admin' ) ) : $this->html->blankIMG();
			$html_order .= $r['position'] < $count_order ? $this->html->downButton( $this->display->buildURL( array( 'module' => 'pages', 'com' => 'category', 'op' => 'down', 'id' => $r['category_id'] ), 'admin' ) ) : $this->html->blankIMG();

			$html .= $this->html->addTdRow(
				array(
					"<center>&nbsp;&nbsp;&nbsp;{$html_order}</center>",
					$r['title'],
					$r['print_type'],
					"<center><a href='".$this->display->buildURL( array( 'module' => 'pages', 'com' => 'category', 'do' => 'edit',   'id' => $r['category_id'] ), 'admin')."'>{$this->lang->getString('edit')}</a></center>",
				)
			);
		}

		// End table
		$html .= $this->html->endTable();

		//--------------------------------------

		// Send the html to the display handler
		$this->display->addContent( $html );
	}

	/**
	 * Swaps the position/order of two categorys based on the direction
	 *
	 * @param string $direction up|down
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function move( $direction )
	{
		$categoryID       = intval($this->input['id']);
		$category         = array();
		$langageOther = array();
		
		//-----------------------------------------
		// Check (please?)
		//-----------------------------------------
		
		$this->DB->query(
			"SELECT category_id, position
				FROM menu_category WHERE category_id = '{$categoryID}';"
		);
		
		$category = $this->DB->fetchRow();
		
		if ( ! $category['category_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			return;
		}
		
		$prev_order = ( $direction == "up" ? $category['position'] - 1 : $category['position'] + 1 );
		
		$this->DB->query(
			"SELECT category_id, position
				FROM menu_category WHERE position = '{$prev_order}';"
		);
		
		$categoryOther = $this->DB->fetchRow();
		
		if ( ! $categoryOther['category_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			return;
		}
		
		$this->DB->query(
			"UPDATE menu_category
				SET position = '{$categoryOther['position']}'
				WHERE category_id = '{$category['category_id']}';"
		);
		
		$this->DB->query(
			"UPDATE menu_category
				SET position = '{$category['position']}'
				WHERE category_id = '{$categoryOther['category_id']}';"
		);

		$this->cache->update('categories', TRUE);
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

		$categoryID           = intval( $this->input['id'] );
		$type                = $this->registry->txtStripslashes( trim( $this->input['type'] ) );

		$category             = array();

		//--------------------------------------------
		// Checks...
		//--------------------------------------------

		// Make sure the category_id came in
		if ( ! $categoryID > 0 )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listCategories();
			return;
		}

		$this->DB->query(
			"SELECT * FROM menu_category WHERE category_id = {$categoryID};"
		);

		if ( $this->DB->getTotalRows() > 0 )
		{
			$category = $this->DB->fetchRow();
		}
		else
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listCategories();
			return;
		}

		// Make sure the necessary fields were filled out
		if ( ! ( strlen( $type ) > 0 ) )
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
			'category_id' => $categoryID,
			'type'        => $type
		);

		// Send the update request to the User class
		if ( Category::update( $array ) )
		{
			$this->cache->update('categories', TRUE);

			$this->error->logError( 'category_updated', FALSE );
			$this->listCategories();
		}
		else
		{
			$this->error->logError( 'category_not_updated', TRUE );
			$this->listCategories();
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

		$categoryID  = intval($this->input['id']);
		$category    = array();

		//-----------------------------------------
		// Checks ...
		//-----------------------------------------

		// So let's make sure this category exists
		$this->DB->query("SELECT * FROM menu_category WHERE category_id = '{$categoryID}';");

		$category = $this->DB->fetchRow();

		// If this category doesn't exist, throw an error
		if ( ! $category['category_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listCategories();
			return;
		}

		$category['inactive'] = ( $category['active'] == 1 ? 0 : 1 );

		// Form setup
		$formcode = 'edit_save';
		$title    = "{$this->lang->getString('category_edit_title')} {$category['title']}";
		$button   = $this->lang->getString('category_edit_save');

		// Page title
		$this->display->setTitle( $title );

		// Add breadcrumb for this action
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "pages", 'com' => 'category', 'do' => $type, 'id' => $categoryID ), 'admin' ), $this->lang->getString('category_'.$type.'_bread') );

		// Setup the hidden inputs for the form
		$html = $this->html->startForm(
			array(
				's'       => $this->user->getSessionID(),
				'app'     => 'admin',
				'module'  => 'pages',
				'com'     => 'category',
				'id'      => $categoryID,
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

		$html .= $this->html->startFieldset( $this->lang->getString('category_form_info') );

		//-----------------------------------------
		// Information fieldset
		//-----------------------------------------

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('category_form_id'),
				$category['category_id']
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('category_form_guid'),
				$category['toast_guid']
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('category_form_title'),
				$category['title']
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('category_form_type'),
				$this->html->formDropdown(
					'type', 
					$this->types, 
					( isset( $_POST['type'] ) ? $_POST['type'] : $category['type'] )
				)
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('category_form_active'),
				$this->html->formYesNo( 'inactive', intval( isset( $_POST['inactive'] ) ? $_POST['inactive'] : $category['inactive'] ) )
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

class Category
{
	/**
	 * Updates a category in the database
	 *
	 * @param array $params an array of the keys and values
	 * @return bool true or false depending on success
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function update( $params )
	{
		$fieldList = array( 'type' );
		$values = "";
		$out = FALSE;
		
		if ( isset( $params['category_id'] ) && $params['category_id'] > 0 )
		{
			foreach( $params as $k => $v )
			{
				if ( in_array( $k, $fieldList ) )
				{
					$values .= "{$k} = '{$v}',";
				}
			}
			
			Registry::$instance->DB->query(
				"UPDATE menu_category SET " . substr($values,0,strlen($values)-1) . " WHERE category_id = '{$params['category_id']}';"
			);
			
			$out = TRUE;
		}
		
		return $out;
	}
}

?>