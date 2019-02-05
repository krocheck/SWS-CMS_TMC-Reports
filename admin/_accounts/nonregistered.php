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

class AdminNonregistered extends Command
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
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "accounts", 'com' => "nonregistered" ), 'admin' ), $this->lang->getString('nonregistered') );

		// Load the language
		$this->lang->loadStrings('nonregistered');

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
		$this->display->addJavascript("<script language='javascript' src='{$this->registry->getConfig('base_url')}js/calendar/calendar.js'></script>");

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( $this->lang->getString('accounts_head_club')           , "6%" );
		$this->html->td_header[] = array( $this->lang->getString('nonregistered_head_visits')    , "12%" );
		$this->html->td_header[] = array( $this->lang->getString('nonregistered_head_total')     , "12%" );
		$this->html->td_header[] = array( $this->lang->getString('nonregistered_head_unique')    , "12%" );
		$this->html->td_header[] = array( $this->lang->getString('nonregistered_head_last')      , "40%" );
		$this->html->td_header[] = array( $this->lang->getString('view')                         , "6%" );
		$this->html->td_header[] = array( $this->lang->getString('nonregistered_head_register')  , "12%" );

		//-----------------------------------------

		// Begin table
		$html = $this->html->startTable( $this->lang->getString('accounts_list_table'), 'admin' );

		// Query accounts for this page
		$this->DB->query(
			"SELECT t.club_id, MAX(t.date_time) as last_time, COUNT(t.transaction_id) AS total, COUNT(DISTINCT t.menu_id) AS spec, COUNT(DISTINCT t.date_time) AS visits
				FROM transaction t
				LEFT JOIN user u ON (t.club_id = u.club_id)
				LEFT JOIN menu_item m ON (t.menu_id = m.menu_item_ID)
				WHERE u.club_id IS NULL AND  m.category_id IN({$catIDs})
				GROUP BY t.club_id
				ORDER BY t.club_id;"
		);

		// Loop through the results and add a row for each
		while( $r = $this->DB->fetchRow() )
		{
			$r['date'] = date_create( $r['last_time'] );

			$html .= $this->html->addTdRow(
				array(
					"<div style='text-align:right;'><a href='".$this->display->buildURL( array( 'module' => 'accounts', 'do' => 'view',   'id' => $r['club_id'] ), 'admin')."'><strong>{$r['club_id']}</strong></a></div>",
					"<center>{$r['visits']}</center>",
					"<center>{$r['total']}</center>",
					"<center>{$r['spec']}</center>",
					$r['date']->format('m/d/Y h:i A'),
					"<center><a href='".$this->display->buildURL( array( 'module' => 'accounts', 'do' => 'view', 'id' => $r['club_id'] ), 'admin')."'>View</a></center>",
					"<center><a href='".$this->display->buildURL( array( 'module' => 'accounts', 'do' => 'add', 'club_id' => $r['club_id'] ), 'admin')."'>Register Now</a></center>"
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