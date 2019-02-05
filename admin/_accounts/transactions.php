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

class AdminTransactions extends Command
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

		// Load the language
		$this->lang->loadStrings('transactions');

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
			case 'void':
				$this->void();
				break;
			case 'unvoid':
				$this->unvoid();
				break;
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
		$dateFrom = new DateTime( ( isset( $this->input['dateFrom'] ) ? $this->input['dateFrom'] : 'now' ) );
		$dateTo = new DateTime( ( isset( $this->input['dateTo'] ) ? $this->input['dateTo'] : 'now' ) );
		$clubID = ( isset( $this->input['club_id'] ) && intval( $this->input['club_id'] ) > 0 ? intval($this->input['club_id']) : 0);

		if ( ! isset( $this->input['dateFrom'] ) )
		{
			$dateFrom->sub( new DateInterval('P7D') );
		}

		// Page title
		$this->display->setTitle( $this->lang->getString('accounts_list_title') );
		$this->display->addJavascript("<script language='javascript' src='{$this->registry->getConfig('base_url')}js/calendar/calendar.js'></script>");

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

		require_once( SWS_JS_PATH . 'calendar/classes/tc_calendar.php');

		ob_start();

		echo("From ");
		$myCalendar = new tc_calendar("dateFrom", true, false);
		$myCalendar->setIcon($this->registry->getConfig('base_url')."js/calendar/images/iconCalendar.gif");
		$myCalendar->setDate($dateFrom->format('d'), $dateFrom->format('m'), $dateFrom->format('Y'));
		$myCalendar->setPath($this->registry->getConfig('base_url')."js/calendar/");
		$myCalendar->setYearInterval(2000, intval(date('Y')));
		$myCalendar->setAlignment('left', 'bottom');
		$myCalendar->setDatePair('dateFrom', 'dateTo', $dateFrom->format('Y-m-d'));
		$myCalendar->writeScript();
		echo(" to ");
		$myCalendar = new tc_calendar("dateTo", true, false);
		$myCalendar->setIcon($this->registry->getConfig('base_url')."js/calendar/images/iconCalendar.gif");
		$myCalendar->setDate($dateTo->format('d'), $dateTo->format('m'), $dateTo->format('Y'));
		$myCalendar->setPath($this->registry->getConfig('base_url')."js/calendar/");
		$myCalendar->setYearInterval(2000, intval(date('Y')));
		$myCalendar->setAlignment('left', 'bottom');
		$myCalendar->setDatePair('dateFrom', 'dateTo', $dateTo->format('Y-m-d'));
		$myCalendar->writeScript();
		echo(" for ");

		$out = ob_get_contents();

		ob_end_clean();

		$pageWhere = " AND t.date_time >= '{$dateFrom->format('Y-m-d')} 00:00:00'";
		$pageWhere .= " AND t.date_time <= '{$dateTo->format('Y-m-d')} 23:59:00'";
		$pageWhere .= ( $clubID > 0 ? " AND t.club_id = '{$clubID}'" : "");

		// Build the page navigation if there are enough accounts to need multiple pages
		$pagelinks = $this->display->getPagelinks(
			"transaction t LEFT JOIN user u ON (t.club_id = u.club_id)
			INNER JOIN menu_item m ON (t.menu_id = m.menu_item_id)
			WHERE m.category_id IN ({$catIDs}) {$pageWhere}",
			array( 'module' => 'accounts', 'com' => 'transactions', 'dateFrom' => $dateFrom->format('Y-m-d'), 'dateTo' => $dateTo->format('Y-m-d'), 'club_id' => $clubID ),
			'admin',
			$pageWhere
		);

		// Create account link
		$html = "<div style='float:right;'><a href='".$this->display->buildURL( array( 'module' => 'accounts', 'com' => 'pending' ), 'admin')."'>Pending Transactions</a> &bull; <form style='display: inline-block;' method='post' action='{$this->display->buildURL( array('module' => 'accounts', 'com' => 'transactions'), 'admin')}'>{$out} ". $this->html->formDropdown('club_id',User::getDropdownArray('club_id > 0', 'last_name,first_name', 'club_id', TRUE), $clubID ) ." <input type='submit' value='{$this->lang->getString('go')}' /></form></div>";

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
			"SELECT t.*, r.status as reward_status, u.first_name, u.last_name, m.title AS menu_title
				FROM transaction t 
				LEFT JOIN user u ON (t.club_id = u.club_id) {$pageWhere}
				INNER JOIN menu_item m ON (t.menu_id = m.menu_item_id)
				LEFT JOIN reward r ON (t.reward_id=r.reward_id)
				WHERE m.category_id IN ({$catIDs}) {$pageWhere}
				ORDER BY t.date_time DESC
				LIMIT $start,{$this->settings['items_per_page']};"
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
			else if ( $r['web_void'] == 1 )
			{
				$r['void_info'] = "<a href='".$this->display->buildURL( array( 'module' => 'accounts', 'com' => 'transactions', 'do' => 'unvoid', 'id' => $r['transaction_id'] ), 'admin')."'>Unvoid</a>";
			}
			else if ( $r['reward_status'] < 1 )
			{
				$r['void_info'] = "<a href='".$this->display->buildURL( array( 'module' => 'accounts', 'com' => 'transactions', 'do' => 'void', 'id' => $r['transaction_id'] ), 'admin')."'>Void</a>";
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

		// Add page links
		$html .= ( strlen( $pagelinks ) > 0 ?"<br /><div class='pagelinks'>{$pagelinks}</div>" : "");

		//--------------------------------------

		// Send the html to the display handler
		$this->display->addContent( $html );
	}

	public function unvoid()
	{
		$transID              = intval( $this->input['id'] );

		$this->DB->query(
			"SELECT * FROM transaction WHERE transaction_id = {$transID};"
		);

		if ( $this->DB->getTotalRows() > 0 )
		{
			$trans = $this->DB->fetchRow();
		}
		else
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listTransactions();
			return;
		}

		if ( $trans['transaction_id'] == $transID && $trans['void'] == 0 )
		{
			$this->DB->query(
				"UPDATE transaction SET web_void = 0 WHERE transaction_id = {$transID};"
			);
		}
		else
		{
			$this->error->logError( 'invalid_id', FALSE );
		}

		$this->listTransactions();
	}

	public function void()
	{
		$transID              = intval( $this->input['id'] );

		$this->DB->query(
			"SELECT * FROM transaction INNER JOIN reward ON (transaction.reward_id = reward.reward_id) WHERE transaction_id = {$transID};"
		);

		if ( $this->DB->getTotalRows() > 0 )
		{
			$trans = $this->DB->fetchRow();
		}
		else
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listTransactions();
			return;
		}

		if ( $trans['transaction_id'] == $transID && $trans['void'] == 0 )
		{
			$this->DB->query(
				"UPDATE transaction SET web_void = 1, reward_id = 0 WHERE transaction_id = {$transID};"
			);
		}
		else
		{
			$this->error->logError( 'invalid_id', FALSE );
		}

		$this->listTransactions();
	}

}

?>