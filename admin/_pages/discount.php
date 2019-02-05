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

class AdminDiscount extends Command
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
	 * Array for the discount types
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $types = array(
		'PERCENT' => array( 0 => 'PERCENT', 1 => 'Percent', 2 => 'percentage' ),
		'FIXED' => array( 0 => 'FIXED', 1 => 'Fixed Value', 2 => 'amount' ),
		'OPEN_PERCENT' => array( 0 => 'OPEN_PERCENT', 1 => 'Open Percent', 2 => 'percentage' ),
		'OPEN_FIXED' => array( 0 => 'OPEN_FIXED', 1 => 'Open Fixed Value', 2 => 'amount' ),
		'BOGO' => array( 0 => 'BOGO', 1 => 'Buy One, Get One', 2 => '' ),
		'FIXED_TOTAL' => array( 0 => 'FIXED_TOTAL', 1 => 'Fixed Total', 2 => 'amount' )
	);
	/**
	 * Array for the selection types
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $selectionTypes = array(
		'CHECK' => array( 0 => 'CHECK', 1 => 'Check' ),
		'BOGO' => array( 0 => 'BOGO', 1 => 'Buy One, Get One' ),
		'ITEM' => array( 0 => 'ITEM', 1 => 'Item' )
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
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "pages", 'com' => "discount" ), 'admin' ), $this->lang->getString('discount') );

		// Load the language
		$this->lang->loadStrings('discount');

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
				$this->listDiscounts();
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
	 * Lists out the discounts in the system.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function listDiscounts()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$count      = array();
		$pageWhere  = '';
		$queryWhere = '';

		//---------------------------------------

		// Page title
		$this->display->setTitle( $this->lang->getString('discount_list_title') );

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( $this->lang->getString('discount_head_name')       , "30%" );
		$this->html->td_header[] = array( $this->lang->getString('discount_head_type')       , "29%" );
		$this->html->td_header[] = array( $this->lang->getString('discount_head_selection')  , "29%" );
		$this->html->td_header[] = array( $this->lang->getString('discount_head_exclude')    , "6%" );
		$this->html->td_header[] = array( $this->lang->getString('edit')                     , "6%" );

		//-----------------------------------------

		// Begin table
		$html .= $this->html->startTable( $this->lang->getString('discount_list_table'), 'admin');

		// Query discounts for this page
		$this->DB->query(
			"SELECT * FROM discount ORDER BY title;"
		);

		// Loop through the results and add a row for each
		while( $r = $this->DB->fetchRow() )
		{
			$r['print_type'] = $this->types[ $r['type'] ][1];

			if ( $r['active'] == 0 )
			{
				$r['title'] = "<span style='color:#ccc; font-style:italic;'>" . $r['title'] . "</span> (Disabled)";
			}

			$html .= $this->html->addTdRow(
				array(
					$r['title'],
					$this->types[ $r['type'] ][1],
					$this->selectionTypes[ $r['selection_type'] ][1],
					( $r['exclude'] == 1 ? 'Yes' : "<span style='color:#ccc; font-style:italic;'>No</span>" ),
					"<center><a href='".$this->display->buildURL( array( 'module' => 'pages', 'com' => 'discount', 'do' => 'edit',   'id' => $r['toast_guid'] ), 'admin')."'>{$this->lang->getString('edit')}</a></center>",
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

		$discountID          = $this->input['id'];
		$exclude             = intval( $this->input['exclude'] );

		$discount             = array();

		//--------------------------------------------
		// Checks...
		//--------------------------------------------

		// Make sure the toast_guid came in
		if ( ! $discountID > 0 )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listDiscounts();
			return;
		}

		$this->DB->query(
			"SELECT * FROM discount WHERE toast_guid = '{$discountID}';"
		);

		if ( $this->DB->getTotalRows() > 0 )
		{
			$discount = $this->DB->fetchRow();
		}
		else
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listDiscounts();
			return;
		}

		//--------------------------------------------
		// Save...
		//--------------------------------------------

		// Build the save array
		$array = array(
			'toast_guid'    => $discountID,
			'exclude'       => $exclude
		);

		// Set the user id in the save array
		$array['toast_guid'] = $discountID;

		// Send the update request to the User class
		if ( Discount::update( $array ) )
		{
			$this->cache->update('discounts', TRUE);

			$this->error->logError( 'discount_updated', FALSE );
			$this->listDiscounts();
		}
		else
		{
			$this->error->logError( 'discount_not_updated', TRUE );
			$this->listDiscounts();
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

		$discountID  = $this->input['id'];
		$discount    = array();

		//-----------------------------------------
		// Checks ...
		//-----------------------------------------

		// So let's make sure this discount exists
		$this->DB->query("SELECT * FROM discount WHERE toast_guid = '{$discountID}';");

		$discount = $this->DB->fetchRow();

		// If this discount doesn't exist, throw an error
		if ( ! $discount['toast_guid'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listDiscounts();
			return;
		}

		$discount['inactive'] = ( $discount['active'] == 1 ? 0 : 1 );

		// Form setup
		$formcode = 'edit_save';
		$title    = "{$this->lang->getString('discount_edit_title')} {$discount['title']}";
		$button   = $this->lang->getString('discount_edit_save');

		// Page title
		$this->display->setTitle( $title );

		// Add breadcrumb for this action
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "pages", 'com' => 'discount', 'do' => $type, 'id' => $discountID ), 'admin' ), $this->lang->getString('discount_'.$type.'_bread') );

		// Setup the hidden inputs for the form
		$html = $this->html->startForm(
			array(
				's'       => $this->user->getSessionID(),
				'app'     => 'admin',
				'module'  => 'pages',
				'com'     => 'discount',
				'id'      => $discountID,
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

		$html .= $this->html->startFieldset( $this->lang->getString('discount_form_info') );

		//-----------------------------------------
		// Information fieldset
		//-----------------------------------------

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('discount_form_guid'),
				$discount['toast_guid']
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('discount_form_title'),
				$discount['title']
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('discount_form_selection_type'),
				$this->selectionTypes[ $discount['selection_type'] ][1]
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('discount_form_type'),
				$this->types[ $discount['type'] ][1]
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('discount_form_amount'),
				( $this->types[ $discount['type'] ][2] <> '' ? ( $this->types[ $discount['type'] ][2] == 'amount' ? '$'.$discount['amount'] : $discount['percentage'].'%') : '' )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('discount_form_active'),
				$this->html->formYesNo( 'inactive', intval( isset( $_POST['inactive'] ) ? $_POST['inactive'] : $discount['inactive'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('discount_form_exclude'),
				$this->html->formYesNo( 'exclude', intval( isset( $_POST['exclude'] ) ? $_POST['exclude'] : $discount['exclude'] ) )
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

class Discount
{
	/**
	 * Updates a discount in the database
	 *
	 * @param array $params an array of the keys and values
	 * @return bool true or false depending on success
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function update( $params )
	{
		$fieldList = array( 'exclude' );
		$values = "";
		$out = FALSE;

		if ( isset( $params['toast_guid'] ) && strlen( $params['toast_guid'] ) > 0 )
		{
			foreach( $params as $k => $v )
			{
				if ( in_array( $k, $fieldList ) )
				{
					$values .= "{$k} = '{$v}',";
				}
			}

			Registry::$instance->DB->query(
				"UPDATE discount SET " . substr($values,0,strlen($values)-1) . " WHERE toast_guid = '{$params['toast_guid']}';"
			);

			$out = TRUE;
		}

		return $out;
	}
}

?>