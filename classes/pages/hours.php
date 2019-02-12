<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Page content class
 * Last Updated: $Date: 2010-07-02 09:31:44 -0500 (Fri, 02 Jul 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Page
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 32 $
 */

class Hours extends Page
{
	/**
	 * The type name that is stored in the database and used as a key for skinning
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'hours';

	/**
	 * Processes the page contents and print
	 *
	 * @param array $meta the metadata
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function processPage( $meta )
	{

		$out     = "";
		$ids     = array();

		$this->metadata   = $meta;
		$this->team       = $this->metadata['team']['meta_value'];
		$this->billingCat = $this->metadata['billing_cat']['meta_value'];
		$this->billingHrs = $this->metadata['billing_hrs']['meta_value'];
		$this->exclude    = $this->metadata['exclude']['meta_value'];

		$this->fields   = $this->cache->getCache('fields');
		$this->projects = $this->cache->getCache('projects');
		$this->users    = $this->cache->getCache('users');

		$js = $this->registry->parseHTML( $this->metadata['js']['meta_value'] );

		if ( strlen( $js ) > 0 )
		{
			$this->display->addJavascript( $js );
		}

		// Add a breadcrumb for this module
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'page_id' => $this->id ) ), $this->name );

		// Load the language
		$this->lang->loadStrings('hours');

		// Get and load the table/form factory
		$this->html = $this->registry->getClass('AdminSkin');

		if ( is_array( $this->input['extra'] ) && count( $this->input['extra'] ) == 1 )
		{
			$this->input['do'] = 'view';
		}

		// What are we doing?
		switch( $this->input['do'] )
		{
			case 'view':
				$this->view();
				break;
			default:
				$this->listProjects();
				break;
		}

		// Send the final output
		$this->display->doOutput();
	}

	/**
	 * Lists out the proejcts in the team.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function listProjects()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$count      = array();
		$pageWhere  = '';
		$queryWhere = '';
		$status = ( isset( $this->input['archived'] ) ? intval($this->input['archived']) : 0);

		// Page title
		$this->display->setTitle( $this->lang->getString('hours_list_title') );

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( $this->lang->getString('hours_head_name')          , "30%" );
		$this->html->td_header[] = array( $this->lang->getString('hours_head_owner')         , "20%" );
		$this->html->td_header[] = array( $this->lang->getString('hours_head_created')       , "20%" );
		$this->html->td_header[] = array( $this->lang->getString('hours_head_hours')         , "10%" );
		$this->html->td_header[] = array( $this->lang->getString('hours_head_asana')         , "10%" );
		$this->html->td_header[] = array( $this->lang->getString('hours_head_view')          , "10%" );

		//-----------------------------------------

		// Create account link
		$html = "<div style='float:right;'>{$this->lang->getString('hours_form_status')}<form method='post' action='{$this->display->buildURL( array( 'page_id' => $this->id ) )}'>". $this->html->formDropdown('archived',array( array( 0 => 0, 1 => "Active" ), array( 0 => 1, 1 => "Archived" ) ), $status ) ." <input type='submit' value='{$this->lang->getString('go')}' /></form></div>";

		// Begin table
		$html .= $this->html->startTable( $this->name, 'admin' );

		if ( strlen($this->exclude) > 0 )
		{
			$exclude = " AND project_gid NOT IN({$this->exclude})";
		}
		else
		{
			$exclude = '';
		}

		// Query projects for this page
		$this->DB->query(
			"SELECT * FROM project WHERE team_gid = '{$this->team}' AND archived='{$status}'{$exclude} ORDER BY name;"
		);

		$projects = array();
		$tasks = array();

		// Loop through the results and add a row for each
		while( $r = $this->DB->fetchRow() )
		{
			$r['tasks'] = unserialize($r['tasks']);
			$r['custom_field_settings'] = unserialize($r['custom_field_settings']);

			if ( is_array($r['custom_field_settings']) && in_array($this->billingCat, $r['custom_field_settings']) && in_array($this->billingHrs, $r['custom_field_settings']) )
			{
				if ( is_array( $r['tasks'] ) && count( $r['tasks'] ) > 0 )
				{
					foreach( $r['tasks'] as $tid )
					{
						$tasks[] = $tid;
					}
				}

				$projects[$r['project_gid']] = $r;
			}
		}

		// Query tasks for this page
		if ( count($tasks) > 0 )
		{
			$this->DB->query(
				"SELECT task_gid,name,custom_fields FROM task WHERE task_gid IN(".implode(',',$tasks).");"
			);

			$tasks = array();

			// Loop through the results and add a row for each
			while( $r = $this->DB->fetchRow() )
			{
				$r['custom_fields'] = unserialize($r['custom_fields']);
				$tasks[$r['task_gid']] = $r;
			}
		}

		if ( count($projects) > 0 )
		{
			foreach( $projects as $id => $r )
			{
				$totalHours = 0;

				if ( count($tasks) > 0 && is_array($r['tasks']) && count($r['tasks']) > 0 )
				{
					foreach ($r['tasks'] as $tid)
					{
						if ( isset($tasks[$tid]) && isset($tasks[$tid]['custom_fields']) && isset($tasks[$tid]['custom_fields'][$this->billingHrs]) )
						{
							$totalHours += $tasks[$tid]['custom_fields'][$this->billingHrs];
						}
					}
				}

				$html .= $this->html->addTdRow(
					array(
						"<a href='".$this->display->buildURL( array( 'page_id' => $this->id, 'extra' => array($r['project_gid']) ) )."'>{$r['name']}</a>",
						$this->users[$r['owner_gid']]['name'],
						"<center>".date('M j, Y', strtotime($r['created_at']))."</center>",
						"<span style='float:right;'>{$totalHours}&nbsp;</span>",
						"<center><a href='https://app.asana.com/0/{$r['project_gid']}' target='_blank'>Asana</a></center>",
						"<center><a href='".$this->display->buildURL( array( 'page_id' => $this->id, 'extra' => array($r['project_gid']) ) )."'>Hours</a></center>",
					)
				);
			}
		}

		// End table
		$html .= $this->html->endTable();

		//--------------------------------------

		// Send the html to the display handler
		$this->display->addContent( $html );
	}
}

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Page content type class
 * 
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Page
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 */

class HoursType extends PageType
{
	/**
	 * The name of the type
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $name = 'hours';

	/**
	 * MUST BE OVERRIDEN: parses the input and returns true
	 * if there is a problem with an input.
	 *
	 * @return bool
	 * @access public
	 * @since 1.0.0
	 */
	public function adminDoPageSaveChecks()
	{
		$out = FALSE;

		$this->metadata['js']                   = $this->registry->txtStripslashes( trim( $this->input['js'] ) );
		$this->metadata['team']                 = $this->registry->txtStripslashes( trim( $this->input['team'] ) );
		$this->metadata['billing_cat']          = $this->registry->txtStripslashes( trim( $this->input['billing_cat'] ) );
		$this->metadata['billing_hrs']          = $this->registry->txtStripslashes( trim( $this->input['billing_hrs'] ) );
		$this->metadata['exclude']              = $this->registry->txtStripslashes( trim( $this->input['exclude'] ) );

		return $out;
	}

	/**
	 * MUST BE OVERRIDEN: returns the html for the type's
	 * specific settings for the control panel
	 *
	 * @param AdminSkin $html the skin library
	 * @param array $page the db row plus metadata array
	 * @param int $languageID the add/edit language
	 * @param int $compareID the language for text comparison
	 * @return string the html
	 * @access public
	 * @since 1.0.0
	 */
	public function adminPageForm( $html, $metadata, $languageID, $compareID )
	{
		$out = "";

		$out .= $html->addTdRow(
			array(
				$this->lang->getString('pages_form_js'),
				($compareID > 0 ? "<div class='compare'>".$metadata[ $compareID ]['js']['value'] . "</div>" : "") .
				$html->formTextarea( 'js', $this->registry->txtStripslashes( $_POST['js'] ? $_POST['js'] : $this->registry->parseHTML( $metadata[ $languageID ]['js']['value'] ) ) )
			)
		);

		//-----------------------------------------
		// End table and form
		//-----------------------------------------

		$out .= $html->endFieldset();

		$out .= $html->startFieldset($this->lang->getString('pages_fieldset_asana'));

		//-----------------------------------------
		// Form elements
		//-----------------------------------------

		$out .= $html->addTdRow(
			array(
				$this->lang->getString('pages_form_asana_team'),
				($compareID > 0 ? "<div class='compare'>".$metadata[ $compareID ]['team']['value'] . "</div>" : "") .
				$html->formDropdown( 'team', $this->registry->getAPI('asana')->getTeamsDropdown(), $_POST['team'] ? $_POST['team'] : $metadata[ $languageID ]['team']['value'] )
			)
		);

		$out .= $html->addTdRow(
			array(
				$this->lang->getString('pages_form_asana_billing_cat'),
				($compareID > 0 ? "<div class='compare'>".$metadata[ $compareID ]['billing_cat']['value'] . "</div>" : "") .
				$html->formDropdown( 'billing_cat', $this->registry->getAPI('asana')->getFieldsDropdown(), $_POST['billing_cat'] ? $_POST['billing_cat'] : $metadata[ $languageID ]['billing_cat']['value'] )
			)
		);

		$out .= $html->addTdRow(
			array(
				$this->lang->getString('pages_form_asana_billing_hrs'),
				($compareID > 0 ? "<div class='compare'>".$metadata[ $compareID ]['billing_hrs']['value'] . "</div>" : "") .
				$html->formDropdown( 'billing_hrs', $this->registry->getAPI('asana')->getFieldsDropdown(), $_POST['billing_hrs'] ? $_POST['billing_hrs'] : $metadata[ $languageID ]['billing_hrs']['value'] )
			)
		);

		$out .= $html->addTdRow(
			array(
				$this->lang->getString('pages_form_asana_exclude'),
				($compareID > 0 ? "<div class='compare'>".$metadata[ $compareID ]['exclude']['value'] . "</div>" : "") .
				$html->formTextarea( 'exclude', $this->registry->txtStripslashes( $_POST['exclude'] ? $_POST['exclude'] : $metadata[ $languageID ]['exclude']['value'] ) )
			)
		);

		return $out;
	}

	protected function setupMetadata()
	{
		$this->metadata['js']                   = '';
	}
}

?>