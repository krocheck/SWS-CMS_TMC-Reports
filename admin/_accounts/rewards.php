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

class AdminRewards extends Command
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
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "accounts", 'com' => "rewards" ), 'admin' ), $this->lang->getString('rewards') );

		// Load the language
		$this->lang->loadStrings('rewards');

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
			case 'redeem':
				$this->redeem();
				break;
			case 'doredeem':
				$this->doRedeem();
				break;
			default:
				$this->listRewards();
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
	protected function listRewards()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$count      = array();
		$pageWhere  = '';
		$queryWhere = '';
		$status = ( isset( $this->input['status'] ) ? intval($this->input['status']) : 1);


		// Page title
		$this->display->setTitle( $this->lang->getString('accounts_list_title') );

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( $this->lang->getString('accounts_head_club')       , "6%" );
		$this->html->td_header[] = array( $this->lang->getString('accounts_head_name')       , "70%" );
		$this->html->td_header[] = array( $this->lang->getString('status')                   , "24%" );

		//-----------------------------------------

		$pageWhere .= "WHERE status='{$status}'";
		$queryWhere .= "WHERE r.status='{$status}'";

		// Create account link
		$html = "<div style='float:right;'><form method='post' action='{$this->display->buildURL( array('module' => 'accounts', 'com' => 'rewards'), 'admin')}'>". $this->html->formDropdown('status',array( array( 0 => 0, 1 => "In Progress" ), array( 0 => 1, 1 => "Available" ), array( 0 => 2, 1 => "Redeemed Rewards" ) ), $status ) ." <input type='submit' value='{$this->lang->getString('go')}' /></form></div>";


		// Build the page navigation if there are enough accounts to need multiple pages
		$pagelinks = $this->display->getPagelinks( 'reward', array( 'module' => 'accounts', 'com' => 'rewards', 'status' => $status ), 'admin', $pageWhere );

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

		$rewards = $this->cache->getCache("rewards");

		// Query accounts for this page
		$this->DB->query(
			"SELECT r.*, u.last_name, u.first_name
				FROM reward r
				LEFT JOIN user u ON (u.club_id = r.club_id)
				{$queryWhere}
				GROUP BY r.reward_id, r.club_id, r.status
				ORDER BY r.status DESC, r.club_id, r.reward_id
				LIMIT {$start},{$this->settings['items_per_page']};"
		);

		// Loop through the results and add a row for each
		while( $r = $this->DB->fetchRow() )
		{
			if ( $r['status'] == 0 )
			{
				$text = "In Progress ({$rewards[ $r['club_id'] ]}/53)";
			}
			else if ( $r['status'] == 1 )
			{
				$text = "Available";
			}
			else
			{
				$text = "Redeemed";
			}

			$html .= $this->html->addTdRow(
				array(
					"<div style='text-align:right;'><a href='".$this->display->buildURL( array( 'module' => 'accounts', 'do' => 'view',   'id' => $r['club_id'] ), 'admin')."'><strong>{$r['club_id']}</strong></a></div>",
					"<a href='".$this->display->buildURL( array( 'module' => 'accounts', 'do' => 'view',   'id' => $r['club_id'] ), 'admin')."'>{$r['first_name']} {$r['last_name']}</a>",
					"<a href='". $this->display->buildURL( array( 'module' => 'accounts', 'com' => 'rewards', 'do' => 'redeem', 'id' => $r['reward_id'] ), 'admin')."'>{$text}</a>"
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
	 * Makes sure the user actually wants to delete this user.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function redeem()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$rewardID   = intval($this->input['id']);
		$reward     = array();
		$html       = "";
		$total      = 0;

		// Get the user form the database
		$this->DB->query(
			"SELECT r.*, u.first_name, u.last_name, u.user_id
				FROM reward r
				LEFT JOIN user u ON (r.club_id=u.club_id)
				WHERE r.reward_id = '{$rewardID}';"
		);

		$reward = $this->DB->fetchRow();

		// Throw an error if the user could not be found
		// or the user is trying to delete their own account
		if ( ! $reward['reward_id'] || ! $reward['club_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listRewards();
			return;
		}

		if ( $reward['status'] == 1 )
		{
			// Form setup
			$formcode = 'doredeem';
			$title    = "{$this->lang->getString('reward_redeem_title')} Club #{$reward['club_id']} - {$reward['first_name']} {$reward['last_name']}";
			$button   = $this->lang->getString('reward_redeem_submit');

			// Page title
			$this->display->setTitle( $title );

			// Add breadcrumb for this action
			$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "accounts", 'com' => "rewards", 'do' => 'redeem', 'id' => $rewardID ), 'admin' ), $this->lang->getString('reward_redeem_bread') );

			// Setup the hidden inputs for the form
			$html .= $this->html->startForm(
				array(
					's'       => $this->user->getSessionID(),
					'app'     => 'admin',
					'module'  => 'accounts',
					'com'     => 'rewards',
					'id'      => $rewardID,
					'do'      => $formcode
				)
			);

			//-----------------------------------------
			// Create the table for the form
			//-----------------------------------------

			$html .= $this->html->startTable( $title, 'admin-form' );

			$html .= $this->html->startFieldset();

			$html .= $this->html->addTdBasic( "{$this->lang->getString('reward_redeem_form_text')} {$reward['first_name']} {$reward['last_name']}?", "center" );

			$html .= $this->html->endFieldset();

			$html .= $this->html->endForm( $button, "", " {$this->lang->getString('or')} <a href='".$this->display->buildURL( array( 'module' => 'accounts' ), 'admin')."'>{$this->lang->getString('cancel')}</a>" );
		}

		// Get the user form the database
		$this->DB->query(
			"SELECT COUNT(t.transaction_id) AS transactions, t.menu_id, m.title AS menu_title, m.points
				FROM transaction t
				LEFT JOIN menu_item m ON (t.menu_id = m.menu_item_id)
				WHERE t.reward_ID = {$rewardID}
				GROUP BY t.menu_id
				ORDER BY t.date_time;"
		);

		$this->html->td_header[] = array( "Beer"      , "40%" );
		$this->html->td_header[] = array( "Qty"      , "10%" );
		$this->html->td_header[] = array( "Points"     , "10%" );
		$this->html->td_header[] = array( "Total"      , "10%" );

		// Begin table
		$html .= $this->html->startTable( 'Reward Items', 'admin', '' );

		if ( $reward['carry_over'] > 0 )
		{
			$total += $reward['carry_over'];

			$html .= $this->html->addTdRow(
				array(
					"Carry-over from last reward",
					'',
					'',
					$reward['carry_over'],
					$total
				)
			);			
		}

		// Loop through the results and add a row for each
		while( $r = $this->DB->fetchRow() )
		{
			$total += $r['points'];

			$html .= $this->html->addTdRow(
				array(
					$r['menu_title'],
					$r['transactions'],
					$r['points'],
					$total
				)
			);
		}

		// End table
		$html .= $this->html->endTable();

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
	protected function doRedeem()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$rewardID   = intval($this->input['id']);
		$reward     = array();

		// Get the user form the database
		$this->DB->query(
			"SELECT r.*, u.first_name, u.last_name, u.user_id
				FROM reward r
				LEFT JOIN user u ON (r.club_id=u.club_id)
				WHERE r.reward_id = '{$rewardID}';"
		);

		$reward = $this->DB->fetchRow();

		// Throw an error if the user could not be found
		// or the user is trying to delete their own account
		if ( ! $reward['reward_id'] || ! $reward['club_id'] || $reward['status'] != 1 )
		{
			$this->error->logError( 'invalid_id', FALSE );
		}
		else
		{
			$this->DB->query( "UPDATE reward SET status = '2' WHERE reward_id = {$reward['reward_id']}" );
			// Send a delete request to the User class
			$this->error->logError( 'reward_redeem_success', TRUE, $reward );
		}

		//-----------------------------------------

		// Done, go back to the account list
		$this->listRewards();
	}
}

?>