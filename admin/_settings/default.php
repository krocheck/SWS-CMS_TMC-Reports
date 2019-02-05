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

class AdminSettings extends Command
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
		$this->checkPermission();
		
		// Load the language
		$this->lang->loadStrings('settings');
		
		// Get and load the table/form factory
		$this->html = $this->registry->getClass('AdminSkin');
		
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "settings" ), 'admin' ), $this->lang->getString('settings') );

		// Declaration for the module
		$module = NULL;

		if ( ! isset( $this->input['com'] ) )
		{
			$this->input['com'] = '';
		}

		// Decide if a module needs to be loaded
		// This will need to be fancied up if extra
		// access levels are added
		if ( isset( $this->input['com'] ) )
		{
			switch( $this->input['com'] )
			{
				case 'asana':
					require_once( SWS_ROOT_PATH . 'admin/_settings/asana.php' );
					$module = new AdminAsana();
					$module->execute( $this->registry );
					break;
				case 'transfers':
					require_once( SWS_ROOT_PATH . 'admin/_settings/transfers.php' );
					$module = new AdminTransfers();
					$module->execute( $this->registry );
					break;
			}
		}

		if ( ! isset( $this->input['do'] ) )
		{
			$this->input['do'] = '';
		}

		// What are we doing?
		switch( $this->input['do'] )
		{
			case 'save':
				$this->save();
				break;
			default:
				$this->showForm();
				break;
		}
		
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
	 * This thing saves the account information.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function save()
	{
		//--------------------------------------------
		// INIT
		//--------------------------------------------
		
		$settings = array();
		$settings['session_timeout']        = intval( $this->input['session_timeout'] );
		$settings['public_timeout']         = intval( $this->input['public_timeout'] );
		$settings['items_per_page']         = intval( $this->input['items_per_page'] );
		$settings['padding']                = intval( $this->input['padding'] );
		$settings['public_login']           = intval( $this->input['public_login'] );
		$settings['seo_url']                = intval( $this->input['seo_url'] );
		$settings['home_page']              = intval( $this->input['home_page'] );
		$settings['manager_password']       = trim( $this->input['manager_password'] );
		$settings['cookie_enable']          = intval( $this->input['cookie_enable'] );
		$settings['cookie_path']            = trim( $this->input['cookie_path'] );
		$settings['cookie_domain']          = trim( $this->input['cookie_domain'] );
		$settings['cookie_prefix']          = trim( $this->input['cookie_prefix'] );
		$settings['asana_url']              = trim( $this->input['asana_url'] );
		$settings['asana_client_id']        = trim( $this->input['asana_client_id'] );
		$settings['asana_location']         = trim( $this->input['asana_location'] );
		$settings['asana_token']            = trim( $this->input['asana_token'] );
		$settings['asana_auth']             = trim( $this->input['asana_auth'] );
		$settings['asana_orders']           = trim( $this->input['asana_orders'] );
		$settings['asana_config']           = trim( $this->input['asana_config'] );
		$settings['asana_crm']              = trim( $this->input['asana_crm'] );

		//--------------------------------------------
		// Checks...
		//--------------------------------------------
		
		if ( ! ( $settings['session_timeout'] > 0 ) || ! ( $settings['public_timeout'] > 0 ) )
		{
			$this->error->logError( 'incomplete_form', FALSE );
			$this->showForm();
			return;
		}
		
		if( ! ( $settings['items_per_page'] > 0 ) )
		{
			$this->error->LogError( 'incomplete_form', FALSE );
			$this->showForm();
			return;
		}
		
		if( ! ( $settings['padding'] > 0 ) )
		{
			$this->error->LogError( 'incomplete_form', FALSE );
			$this->showForm();
			return;
		}
		//--------------------------------------------
		// Save...
		//--------------------------------------------
		
		if ( Setting::updateAll( $settings ) )
		{
			$this->error->logError( 'settings_saved', FALSE );
		}
		else
		{
			$this->error->logError( 'settings_not_saved', FALSE );
		}
		
		$this->showForm();
	}

	/**
	 * This thing show the form to add/edit an account.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function showForm()
	{
		//-----------------------------------------
		// Init Vars
		//-----------------------------------------

		$settings    = array();
		$pages       = $this->registry->getClass('PageController')->makeDropdown('home_page', 0, 'array');

		//-----------------------------------------
		// Check (please?)
		//-----------------------------------------

		$this->DB->query("SELECT * FROM metadata_setting;");

		$count = $this->DB->getTotalRows();

		if ( ! $count > 0 )
		{
			$this->error->raiseError( 'settings_not_found', TRUE );
		}

		while( $r = $this->DB->fetchRow() )
		{
			$settings[ $r['meta_key'] ] = $r['meta_value'];
		}

		//--------------------------------------------
		// Figure out UID
		//--------------------------------------------

		$formcode = 'save';
		$title    = $this->lang->getString('settings_edit_title');
		$button   = $this->lang->getString('settings_edit_save');

		//-----------------------------------------
		// Start the form
		//-----------------------------------------

		$this->display->setTitle( $title );

		$html = $this->html->startForm(
			array(
				's'      => $this->user->getSessionID(),
				'app'    => 'admin',
				'module' => 'settings',
				'do'     => $formcode
			)
		);

		$this->html->td_header[] = array( "&nbsp;"  , "40%" );
		$this->html->td_header[] = array( "&nbsp;"  , "60%" );

		$html .= $this->html->startTable( $this->lang->getString('settings_edit_title'), 'admin-form' );

		$html .= $this->html->startFieldset( $this->lang->getString('settings_fieldset_main') );

		//-----------------------------------------
		// Form elements
		//-----------------------------------------

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_public_login'),
				$this->html->formYesNo( 'public_login', $this->registry->txtStripslashes( $_POST['public_login'] ? $_POST['public_login'] : $settings['public_login'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_seo_url'),
				$this->html->formYesNo( 'seo_url', $this->registry->txtStripslashes( $_POST['seo_url'] ? $_POST['seo_url'] : $settings['seo_url'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_home_page'),
				$this->html->formDropdown('home_page', $pages, intval( $_POST['home_page'] ? $_POST['home_page'] : $settings['home_page'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_session_timeout'),
				$this->html->formInput( 'session_timeout', $this->registry->txtStripslashes( $_POST['session_timeout'] ? $_POST['session_timeout'] : $settings['session_timeout'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_public_timeout'),
				$this->html->formInput( 'public_timeout', $this->registry->txtStripslashes( $_POST['public_timeout'] ? $_POST['public_timeout'] : $settings['public_timeout'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_items_per_page'),
				$this->html->formInput( 'items_per_page', $this->registry->txtStripslashes( $_POST['items_per_page'] ? $_POST['items_per_page'] : $settings['items_per_page'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_padding'),
				$this->html->formInput( 'padding', $this->registry->txtStripslashes( $_POST['padding'] ? $_POST['padding'] : $settings['padding'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_manager_password'),
				$this->html->formInput( 'manager_password', $this->registry->txtStripslashes( $_POST['manager_password'] ? $_POST['manager_password'] : $settings['manager_password'] ) )
			)
		);

		//-----------------------------------------
		// End table and form
		//-----------------------------------------

		$html .= $this->html->endFieldset();

		$html .= $this->html->startFieldset( $this->lang->getString('settings_fieldset_cookie') );

		//-----------------------------------------
		// Form elements
		//-----------------------------------------

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_cookie_enable'),
				$this->html->formYesNo( 'cookie_enable', $this->registry->txtStripslashes( $_POST['cookie_enable'] ? $_POST['cookie_enable'] : $settings['cookie_enable'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_cookie_domain'),
				$this->html->formInput( 'cookie_domain', $this->registry->txtStripslashes( $_POST['cookie_domain'] ? $_POST['cookie_domain'] : $settings['cookie_domain'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_cookie_prefix'),
				$this->html->formInput( 'cookie_prefix', $this->registry->txtStripslashes( $_POST['cookie_prefix'] ? $_POST['cookie_prefix'] : $settings['cookie_prefix'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_cookie_path'),
				$this->html->formInput( 'cookie_path', $this->registry->txtStripslashes( $_POST['cookie_path'] ? $_POST['cookie_path'] : $settings['cookie_path'] ) )
			)
		);

		//-----------------------------------------
		// End table and form
		//-----------------------------------------

		$html .= $this->html->endFieldset();

		$html .= $this->html->startFieldset( $this->lang->getString('settings_fieldset_asana') );

		//-----------------------------------------
		// Form elements
		//-----------------------------------------

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_asana_url'),
				$this->html->formInput( 'asana_url', $this->registry->txtStripslashes( $_POST['asana_url'] ? $_POST['asana_url'] : $settings['asana_url'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_asana_client_id'),
				$this->html->formInput( 'asana_client_id', $this->registry->txtStripslashes( $_POST['asana_client_id'] ? $_POST['asana_client_id'] : $settings['asana_client_id'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_asana_location'),
				$this->html->formInput( 'asana_location', $this->registry->txtStripslashes( $_POST['asana_location'] ? $_POST['asana_location'] : $settings['asana_location'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_asana_token'),
				$this->html->formInput( 'asana_token', $this->registry->txtStripslashes( $_POST['asana_token'] ? $_POST['asana_token'] : $settings['asana_token'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_asana_auth'),
				$this->html->formInput( 'asana_auth', $this->registry->txtStripslashes( $_POST['asana_auth'] ? $_POST['asana_auth'] : $settings['asana_auth'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_asana_orders'),
				$this->html->formInput( 'asana_orders', $this->registry->txtStripslashes( $_POST['asana_orders'] ? $_POST['asana_orders'] : $settings['asana_orders'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_asana_config'),
				$this->html->formInput( 'asana_config', $this->registry->txtStripslashes( $_POST['asana_config'] ? $_POST['asana_config'] : $settings['asana_config'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('settings_asana_crm'),
				$this->html->formInput( 'asana_crm', $this->registry->txtStripslashes( $_POST['asana_crm'] ? $_POST['asana_crm'] : $settings['asana_crm'] ) )
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