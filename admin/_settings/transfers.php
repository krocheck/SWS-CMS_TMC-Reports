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

class AdminTransfers extends Command
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
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "settings", 'com' => "transfers" ), 'admin' ), $this->lang->getString('transfers') );

		// Load the language
		$this->lang->loadStrings('transfer');

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

	protected function flush( $type )
	{
		if ( $type == 'prices' )
		{
			$this->DB->query("DELETE FROM logs WHERE type = 'prices' AND result = 1;");
			$this->DB->query("DELETE FROM logs WHERE total = 0;");
			$this->DB->query("OPTIMIZE TABLE `logs`;");
		}
	}

	/**
	 * Lists out the transfers in the system.
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
		$modules    = array( 'transactions' => "Transactions Update", 'transactions_manual' => "Manual Transaction Batch", 'items' => "Menu Items Update", 'prices' => "Menu Item Active Prices Update", 'rewards' => "Reward Transactions Processed" );

		//-----------------------------------------
		// Do we need to do something else first?
		//-----------------------------------------

		if ( ! isset( $this->input['op'] ) )
		{
			$this->input['op'] = '';
		}

		switch ( $this->input['op'] )
		{
			case 'flush_prices':
				$this->flush('prices');
				break;
		}

		// Build the page navigation if there are enough accounts to need multiple pages
		$pagelinks = $this->display->getPagelinks( 'logs', array( 'module' => 'settings', 'com' => 'transfers' ), 'admin', $pageWhere );

		// Page title
		$this->display->setTitle( $this->lang->getString('transfer_list_title') );

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( $this->lang->getString('transfer_head_date')             , "16%" );
		$this->html->td_header[] = array( $this->lang->getString('transfer_head_action')           , "36%" );
		$this->html->td_header[] = array( $this->lang->getString('transfer_head_ids')              , "36%" );
		$this->html->td_header[] = array( $this->lang->getString('transfer_head_result')           , "12%" );

		//-----------------------------------------

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

		// CFlush link
		$html = "<div style='float:right;'><a href='".$this->display->buildURL( array( 'module' => 'settings', 'com' => 'transfers', 'op' => 'flush_prices' ), 'admin')."'>{$this->lang->getString('transfer_flush_prices')}</a></div>";

		// Begin table
		$html .= $this->html->startTable( $this->lang->getString('transfer_list_table'), 'admin', ( strlen( $pagelinks ) > 0 ?"\n<div class='pagelinks'>{$pagelinks}</div>" : "" ) );

		// Query transfers for this page
		$this->DB->query("SELECT * FROM logs ORDER BY date_time DESC LIMIT $start,{$this->settings['items_per_page']};");

		// Loop through the results and add a row for each
		while( $r = $this->DB->fetchRow() )
		{
			$r['date'] = date_create( $r['date_time'] );
			$r['items'] = $r['total'] . " " . $this->lang->getString('transfer_items');

			if ( $r['start_id'] > 0 || $r['end_id'] > 0 )
			{
				$r['items'] .= " (ID#'s  {$r['start_id']} - {$r['end_id']})";
			}

			$html .= $this->html->addTdRow(
				array(
					"<center>".$r['date']->format('m/d/Y h:i A')."</center>",
					$modules[ $r['type'] ],
					"{$r['items']}",
					"<center>". ( $r['result'] ? "<span style='color:#0b0;'>{$this->lang->getString('success')}</span>" : "<span style='color:#f00;'>{$this->lang->getString('error')}</span>" ) ."</center>"
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

}

?>