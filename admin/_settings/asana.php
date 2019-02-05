<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Admin application wrapper
 * Last Updated: $Date: 2010-06-10 22:30:14 -0500 (Thu, 10 Jun 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Admin
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 9 $
 */

class AdminAsana extends Command
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
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "settings", 'com' => "asana" ), 'admin' ), $this->lang->getString('asana_data') );

		// Load the language
		$this->lang->loadStrings('asana');

		// Get and load the table/form factory
		$this->html = $this->registry->getClass('AdminSkin');

		if ( ! isset( $this->input['do'] ) )
		{
			$this->input['do'] = '';
		}

		// What are we doing?
		switch( $this->input['do'] )
		{
			case 'categories':
				$this->categoriesUpdate();
				break;
			case 'discounts':
				$this->discountsUpdate();
				break;
			case 'menu_active':
				$this->menuActive();
				break;
			case 'menu_refresh':
				$this->menuRefresh();
				break;
			case 'menu_update':
				$this->menuUpdate();
				break;
			case 'orders_range':
				$this->ordersRange();
				break;
			case 'orders_today':
				$this->ordersToday();
				break;
			case 'orders_update':
				$this->ordersUpdate();
				break;
			case 'orders_yesterday':
				$this->ordersYesterday();
				break;
			default:
				$this->showOptions();
				break;
		}

		// Send the final output
		$this->display->doOutput();
	}

	/**
	 * Makes sure the user can actually use the app.  Will throw error if not.
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
	 * Run the related Asana API call.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function categoriesUpdate()
	{
		$count = $this->registry->getAPI('asana')->updateCategories();

		if ( $count == 0 )
		{
			$this->error->addErrorLog("Sales Category refresh was NOT successful.  Check the data logs.");
		}
		else
		{
			$this->error->addErrorLog("Successfully refreshed {$count} Sales Categories.");
		}

		$this->showOptions();
	}

	/**
	 * Run the related Asana API call.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function discountsUpdate()
	{
		$count = $this->registry->getAPI('asana')->updateDiscounts();

		if ( $count == 0 )
		{
			$this->error->addErrorLog("Discounts refresh was NOT successful.  Check the data logs.");
		}
		else
		{
			$this->error->addErrorLog("Successfully refreshed {$count} Discounts.");
		}

		$this->showOptions();
	}

	/**
	 * Run the related Asana API call.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function menuActive()
	{
		$count = $this->registry->getAPI('asana')->updateActiveMenuItems();

		if ( $count == 0 )
		{
			$this->error->addErrorLog("Active Menu Item refresh was NOT successful.  Check the data logs.");
		}
		else
		{
			$this->error->addErrorLog("Successfully refreshed {$count} active menu items.");
		}

		$this->showOptions();
	}

	/**
	 * Run the related Asana API call.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function menuRefresh()
	{
		$count = $this->registry->getAPI('asana')->refreshAllMenuItems();

		if ( $count == 0 )
		{
			$this->error->addErrorLog("Complete Menu Item refresh was NOT successful.  Check the data logs.");
		}
		else
		{
			$this->error->addErrorLog("Successfully refreshed {$count} menu items.");
		}

		$this->showOptions();
	}

	/**
	 * Run the related Asana API call.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function menuUpdate()
	{
		$count = $this->registry->getAPI('asana')->updateMenuItems();

		if ( $count == 0 )
		{
			$this->error->addErrorLog("No menu item changes were found.");
		}
		else
		{
			$this->error->addErrorLog("Successfully updated {$count} menu items.");
		}

		$this->showOptions();
	}

	/**
	 * Run the related Asana API call.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function ordersRange()
	{
		if ( isset( $this->input['complete'] ) )
		{
			$dateFrom = new DateTime( $this->input['dateFrom'] );
			$dateTo = new DateTime( $this->input['dateTo'] );
			$count = ( isset( $this->input['count'] ) ? intval( $this->input['count'] ) : 0 );

			$this->error->addErrorLog("Successfully updated {$count} orders from " . $dateFrom->format('m-d-Y') . " through " . $dateTo->format('m-d-Y') . ".");

			$this->showOptions();
		}
		else if ( !isset( $this->input['dateFrom'] ) || !isset( $this->input['dateTo'] ) )
		{
			$this->error->addErrorLog("Could not refresh orders.  Dates were invalid.");
			$this->showOptions();
		}
		else
		{
			$dateFrom = new DateTime( $this->input['dateFrom'] );
			$dateTo = new DateTime( $this->input['dateTo'] );
			$dateNow = new DateTime( ( isset( $this->input['dateNow'] ) ? $this->input['dateNow'] : $this->input['dateFrom'] ) );
			$count = ( isset( $this->input['count'] ) ? intval( $this->input['count'] ) : 0 );

			$thisCount = $this->registry->getAPI('asana')->refreshOrdersByDate( $dateNow );
			$count += $thisCount;

			if ( $thisCount == 0 )
			{
				$this->error->addErrorLog("No orders were found on " . $dateNow->format('m-d-Y') . ".");
			}
			else
			{
				$this->error->addErrorLog("Successfully updated {$thisCount} orders from " . $dateNow->format('m-d-Y') . ".");
			}

			if ( $this->input['dateTo'] == $dateNow->format('Y-m-d') )
			{
				$this->display->addJavascript("<meta http-equiv=\"refresh\" content=\"2;url=".$this->display->buildURL( array( 'module' => 'settings', 'com' => 'asana', 'do' => 'orders_range', 'dateFrom' => $dateFrom->format('Y-m-d'), 'dateTo' => $dateTo->format('Y-m-d'), 'complete' => '1', 'count' => $count ), 'admin')."\">");
			}
			else
			{
				$dateNow->add( new DateInterval('P1D') );
				$this->display->addJavascript("<meta http-equiv=\"refresh\" content=\"2;url=".$this->display->buildURL( array( 'module' => 'settings', 'com' => 'asana', 'do' => 'orders_range', 'dateFrom' => $dateFrom->format('Y-m-d'), 'dateTo' => $dateTo->format('Y-m-d'), 'dateNow' => $dateNow->format('Y-m-d'), 'count' => $count ), 'admin')."\">");
			}
		}
	}

	/**
	 * Run the related Asana API call.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function ordersToday()
	{
		$count = $this->registry->getAPI('asana')->refreshOrdersToday();

		if ( $count == 0 )
		{
			$this->error->addErrorLog("No orders were found for today.");
		}
		else
		{
			$this->error->addErrorLog("Successfully updated {$count} orders.");
		}

		$this->showOptions();
	}

	/**
	 * Run the related Asana API call.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function ordersUpdate()
	{
		$count = $this->registry->getAPI('asana')->updateOrders();

		if ( $count == 0 )
		{
			$this->error->addErrorLog("No recent order changes were found.");
		}
		else
		{
			$this->error->addErrorLog("Successfully updated {$count} orders.");
		}

		$this->showOptions();
	}

	/**
	 * Run the related Asana API call.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function ordersYesterday()
	{
		$count = $this->registry->getAPI('asana')->refreshOrdersYesterday();

		if ( $count == 0 )
		{
			$this->error->addErrorLog("No orders were found for yesterday.");
		}
		else
		{
			$this->error->addErrorLog("Successfully updated {$count} orders.");
		}

		$this->showOptions();
	}

	/**
	 * This thing show the form to add/edit an account.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function showOptions()
	{
		//-----------------------------------------
		// Init Vars
		//-----------------------------------------

		$settings    = array();
		$dateFrom = new DateTime( ( isset( $this->input['dateFrom'] ) ? $this->input['dateFrom'] : 'now' ) );
		$dateTo = new DateTime( ( isset( $this->input['dateTo'] ) ? $this->input['dateTo'] : 'now' ) );

		if ( ! isset( $this->input['dateFrom'] ) )
		{
			$dateFrom->sub( new DateInterval('P7D') );
		}

		//-----------------------------------------
		// Check (please?)
		//-----------------------------------------

		//--------------------------------------------
		// Figure out UID
		//--------------------------------------------

		$title    = $this->lang->getString('asana_main_title');
		$button   = $this->lang->getString('asana_main_run');

		//-----------------------------------------
		// Start the form
		//-----------------------------------------

		$this->display->setTitle( $title );

		$html = $this->html->startForm(
			array(
				's'      => $this->user->getSessionID(),
				'app'    => 'admin',
				'module' => 'settings',
				'com'    => 'asana',
				'do'     => 'orders_range'
			)
		);

		$this->html->td_header[] = array( "&nbsp;"  , "40%" );
		$this->html->td_header[] = array( "&nbsp;"  , "60%" );

		$html .= $this->html->startTable( $title, 'admin-form' );

		$html .= $this->html->startFieldset( $this->lang->getString('asana_fieldset_one') );

		//-----------------------------------------
		// Form elements
		//-----------------------------------------

		$html .= $this->html->addTdBasic(
			"<strong><a href='".$this->display->buildURL( array( 'module' => 'settings', 'com' => 'asana', 'do' => 'menu_update' ), 'admin')."'>{$this->lang->getString('asana_link_menu_update')}</a></strong>"
		);

		$html .= $this->html->addTdBasic(
			"<strong><a href='".$this->display->buildURL( array( 'module' => 'settings', 'com' => 'asana', 'do' => 'menu_active' ), 'admin')."'>{$this->lang->getString('asana_link_menu_active')}</a></strong>"
		);

		$html .= $this->html->addTdBasic(
			"<strong><a href='".$this->display->buildURL( array( 'module' => 'settings', 'com' => 'asana', 'do' => 'menu_refresh' ), 'admin')."'>{$this->lang->getString('asana_link_menu_refresh')}</a></strong> <i>(Only use in extreme situations)"
		);

		$html .= $this->html->addTdBasic(
			"<strong><a href='".$this->display->buildURL( array( 'module' => 'settings', 'com' => 'asana', 'do' => 'orders_update' ), 'admin')."'>{$this->lang->getString('asana_link_orders_update')}</a></strong>"
		);

		$html .= $this->html->addTdBasic(
			"<strong><a href='".$this->display->buildURL( array( 'module' => 'settings', 'com' => 'asana', 'do' => 'orders_today' ), 'admin')."'>{$this->lang->getString('asana_link_orders_today')}</a></strong>"
		);

		$html .= $this->html->addTdBasic(
			"<strong><a href='".$this->display->buildURL( array( 'module' => 'settings', 'com' => 'asana', 'do' => 'orders_yesterday' ), 'admin')."'>{$this->lang->getString('asana_link_orders_yesterday')}</a></strong>"
		);

		$html .= $this->html->addTdBasic(
			"<strong><a href='".$this->display->buildURL( array( 'module' => 'settings', 'com' => 'asana', 'do' => 'categories' ), 'admin')."'>{$this->lang->getString('asana_link_categories')}</a></strong>"
		);

		$html .= $this->html->addTdBasic(
			"<strong><a href='".$this->display->buildURL( array( 'module' => 'settings', 'com' => 'asana', 'do' => 'discounts' ), 'admin')."'>{$this->lang->getString('asana_link_discounts')}</a></strong>"
		);

		//-----------------------------------------
		// End table and form
		//-----------------------------------------

		$html .= $this->html->endFieldset();

		$html .= $this->html->startFieldset( $this->lang->getString('asana_fieldset_range') );

		//-----------------------------------------
		// Form elements
		//-----------------------------------------

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('asana_field_date_from'),
				$this->html->formDate( 'dateFrom', $dateFrom, '', 'dateTo' )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('asana_field_date_to'),
				$this->html->formDate( 'dateTo', $dateTo, 'dateFrom', '' )
			)
		);

		//-----------------------------------------
		// End table and form
		//-----------------------------------------

		$html .= $this->html->endFieldset();

		$html .= $this->html->endForm( $button );

		$this->display->addContent( $html );
	}
}

?>