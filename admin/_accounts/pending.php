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

class AdminPending extends Command
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
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "accounts", 'com' => "transactions" ), 'admin' ), $this->lang->getString('transactions') );
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "accounts", 'com' => "pending" ), 'admin' ), $this->lang->getString('pending') );

		// Load the language
		$this->lang->loadStrings('pending');

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
			default:
				$this->listTransactions();
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
	protected function listTransactions()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$count      = array();
		$pageWhere  = '';
		$queryWhere = '';
		$categories = $this->cache->getCache('categories');
		$catIDs     = implode(',', array_keys( $categories ) );

		// Page title
		$this->display->setTitle( $this->lang->getString('accounts_list_title') );

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( $this->lang->getString('datetime')                 , "18%" );
		$this->html->td_header[] = array( $this->lang->getString('accounts_head_club')       , "6%" );
		$this->html->td_header[] = array( $this->lang->getString('accounts_head_name')       , "32%" );
		$this->html->td_header[] = array( $this->lang->getString('transactions_head_beer')   , "32%" );
		$this->html->td_header[] = array( $this->lang->getString('quantity')                 , "6%" );
		$this->html->td_header[] = array( $this->lang->getString('void')                     , "6%" );

		//-----------------------------------------

		// Begin table
		$html .= $this->html->startTable( $this->lang->getString('accounts_list_table'), 'admin' );

		// Query accounts for this page
		$this->DB->query(
			"SELECT t.*, u.first_name, u.last_name, m.title AS menu_title
				FROM pending_transaction t 
				LEFT JOIN user u ON (t.club_id = u.club_id) {$pageWhere}
				INNER JOIN menu_item m ON (t.menu_id = m.menu_item_id)
				WHERE m.category_id IN ({$catIDs}) {$pageWhere}
				ORDER BY t.date_time DESC"
		);

		// Loop through the results and add a row for each
		while( $r = $this->DB->fetchRow() )
		{
			$r['date'] = new DateTime( $r['date_time'], new DateTimeZone('UTC') );
			$r['date']->setTimeZone( new DateTimeZone("America/Chicago") );

			$r['void_info'] = '';
			
			if ( $r['void'] == 1 )
			{
				$r['void_info'] = "POS Void";
			}

			$html .= $this->html->addTdRow(
				array(
					"<center>".$r['date']->format('m/d/Y h:i A')."</center>",
					"<div style='text-align:right;'><a href='".$this->display->buildURL( array( 'module' => 'accounts', 'do' => 'view',   'id' => $r['club_id'] ), 'admin')."'><strong>{$r['club_id']}</strong></a></div>",
					"<a href='".$this->display->buildURL( array( 'module' => 'accounts', 'do' => 'view',   'id' => $r['club_id'] ), 'admin')."'>{$r['first_name']} {$r['last_name']}</a>",
					"{$r['menu_title']}",
					"<center>{$r['quantity']}</center>",
					"<center>{$r['void_info']}</center>"
				)
			);
		}

		// End table
		$html .= $this->html->endTable();

		//--------------------------------------

		// Send the html to the display handler
		$this->display->addContent( $html );
	}
}

?>