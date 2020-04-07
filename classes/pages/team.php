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

class Team extends Page
{
	/**
	 * The type name that is stored in the database and used as a key for skinning
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'team';

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

		$this->metadata       = $meta;
		$this->team           = $this->metadata['team']['meta_value'];
		$this->billingCat     = $this->metadata['billing_cat']['meta_value'];
		$this->billingHrs     = $this->metadata['billing_hrs']['meta_value'];
		$this->scheduleEnable = $this->metadata['schedule_enable']['meta_value'];
		$this->respParty      = $this->metadata['responsible_party']['meta_value'];
		$this->exclude        = $this->metadata['exclude']['meta_value'];

		$this->fields     = $this->cache->getCache('fields');
		$this->projects   = $this->cache->getCache('projects');
		$this->users      = $this->cache->getCache('users');
		$this->categories = array('0' => 'None');

		if ( isset($this->fields[ $this->billingCat ]) && $this->fields[ $this->billingCat ]['resource_subtype'] == 'enum' && is_array($this->fields[ $this->billingCat ]['enum_options']) )
		{
			foreach( $this->fields[ $this->billingCat ]['enum_options'] as $id => $r )
			{
				$this->categories[$id] = $r['name'];
			}
		}

		$js = $this->registry->parseHTML( $this->metadata['js']['meta_value'] );

		if ( strlen( $js ) > 0 )
		{
			$this->display->addJavascript( $js );
		}

		// Add a breadcrumb for this module
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'page_id' => $this->id ) ), $this->name );

		// Load the language
		$this->lang->loadStrings('team');

		// Get and load the table/form factory
		$this->html = $this->registry->getClass('AdminSkin');

		if ( is_array( $this->input['extra'] ) && count( $this->input['extra'] ) == 2 )
		{
			$this->input['do'] = $this->input['extra'][1];
		}
		else
		{
			$this->input['do'] = '';
		}

		// What are we doing?
		switch( $this->input['do'] )
		{
			case 'hours':
				$this->hours();
				break;
			case 'schedule':
				$this->schedule();
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

		$status = ( isset( $this->input['archived'] ) ? intval($this->input['archived']) : 0);

		// Page title
		$this->display->setTitle( $this->lang->getString('hours_list_title') );

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( $this->lang->getString('hours_head_name')          , "32%" );
		$this->html->td_header[] = array( $this->lang->getString('hours_head_owner')         , "22%" );
		$this->html->td_header[] = array( $this->lang->getString('hours_head_created')       , "22%" );
		$this->html->td_header[] = array( $this->lang->getString('hours_head_schedule')      , "12%" );
		$this->html->td_header[] = array( $this->lang->getString('hours_head_hours')         , "12%" );

		//-----------------------------------------

		// Create account link
		$html = "<div style='float:right;'>{$this->lang->getString('hours_form_status')}&nbsp;<form method='post' action='{$this->display->buildURL( array( 'page_id' => $this->id ) )}'>". $this->html->formDropdown('archived',array( array( 0 => 0, 1 => "Active" ), array( 0 => 1, 1 => "Archived" ) ), $status ) ." <input type='submit' value='{$this->lang->getString('go')}' /></form></div>";

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

			if ( is_array( $r['tasks'] ) && count( $r['tasks'] ) > 0 )
			{
				foreach( $r['tasks'] as $tid )
				{
					$tasks[] = $tid;
				}
			}

			$projects[$r['project_gid']] = $r;
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
						"<a  href='https://app.asana.com/0/{$r['project_gid']}' target='_blank'>{$r['name']}</a>",
						$this->users[$r['owner_gid']]['name'],
						"<center>".date('M j, Y', strtotime($r['created_at']))."</center>",
						"<center><a href='".$this->display->buildURL( array( 'page_id' => $this->id, 'extra' => array($r['project_gid'], 'schedule') ) )."'>Schedule</a></center>",
						"<center><a href='".$this->display->buildURL( array( 'page_id' => $this->id, 'extra' => array($r['project_gid'], 'hours') ) )."'>Hours</a></center>",
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

	/**
	 * Processes the production schedule and output PDF
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function schedule()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$projectID = intval($this->input['extra'][0]);

		if ( $projectID == 0 )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listProjects();
			return;
		}

		$this->registry->getAPI('asana')->updateProject($projectID);

		// Query projects for this page
		$this->DB->query(
			"SELECT * FROM project WHERE project_gid = '{$projectID}';"
		);

		$project = array();
		$tasks = array();

		$users = array(0=>0);

		if ( count($this->users) > 0 )
		{
			foreach( $this->users as $id => $r )
			{
				$users[$id] = 0;
			}
		}

		$cats = array();

		if ( count($this->categories) > 0 )
		{
			foreach( $this->categories as $id => $r )
			{
				$cats[$id] = 0;
			}
		}

		// Loop through the results and add a row for each
		while( $r = $this->DB->fetchRow() )
		{
			$r['tasks'] = unserialize($r['tasks']);
			$r['custom_field_settings'] = unserialize($r['custom_field_settings']);

			if ( substr($r['name'],0,3) == '201' || substr($r['name'],0,3) == '202' || substr($r['name'],0,3) == '203' )
			{
				$r['name'] = substr( $r['name'], 8 );
				$r['name'] = trim( $r['name'] );
			}

			if ( is_array( $r['tasks'] ) && count( $r['tasks'] ) > 0 )
			{
				foreach( $r['tasks'] as $tid )
				{
					$tasks[] = $tid;
				}
			}

			$project = $r;
		}

		if ( count($project) == 0 )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listProjects();
			return;
		}

		// Query tasks for this page
		if ( count($tasks) > 0 )
		{
			$this->DB->query(
				"SELECT task_gid,assignee_gid,name,custom_fields,due_on,start_on,html_notes FROM task WHERE task_gid IN(".implode(',',$tasks).");"
			);

			$tasks = array();

			// Loop through the results and add a row for each
			while( $r = $this->DB->fetchRow() )
			{
				$r['custom_fields'] = unserialize($r['custom_fields']);
				$tasks[$r['task_gid']] = $r;
			}
		}

		//-----------------------------------------

		$scheduleTasks = array();

		if ( count($project['tasks']) > 0 )
		{
			foreach( $project['tasks'] as $tid )
			{
				if ( isset($tasks[$tid]) && isset($tasks[$tid]['custom_fields']) && isset($tasks[$tid]['custom_fields'][$this->scheduleEnable]) )
				{
					if ( $tasks[$tid]['custom_fields'][$this->scheduleEnable] <> null && $tasks[$tid]['due_on'] <> '0000-00-00' )
					{
						if ( strlen($tasks[$tid]['html_notes']) > 0 )
						{
							$tasks[$tid]['name'] .= "<br /><span class='desc'>{$tasks[$tid]['html_notes']}</span>";
						}

						if ( strpos($tasks[$tid]['name'],':') > 0 )
						{
							$tasks[$tid]['name'] = substr($tasks[$tid]['name'], strpos($tasks[$tid]['name'],':')+1);
						}

						$find = array('(AV1)', '(AV2)', '(MSN)', '(MGFX)');
						$replace = array('', '', '', '');

						$tasks[$tid]['name'] = str_replace($find, $replace, $tasks[$tid]['name']);

						$scheduleTasks[] = array(
							'name' => trim($tasks[$tid]['name']),
							'responsible_party' => $tasks[$tid]['custom_fields'][$this->respParty],
							'start' => ($tasks[$tid]['start_on'] <> '0000-00-00' ? date('M. jS',strtotime($tasks[$tid]['start_on'])) : date('M. jS',strtotime($tasks[$tid]['due_on']))),
							'start_on' => ($tasks[$tid]['start_on'] <> '0000-00-00' ? strtotime($tasks[$tid]['start_on']) : strtotime($tasks[$tid]['due_on'])),
							'end' => date('M. jS',strtotime($tasks[$tid]['due_on'])),
						);
					}
				}
			}
		}

		function dateCompare($a, $b)
		{
			$t1 = $a['start_on'];
			$t2 = $b['start_on'];
			return $t1 - $t2;
		}
		usort($scheduleTasks, 'dateCompare');

		//--------------------------------------

		$out = $this->display->compiledTemplates('skin_team')->schedulePDF( $project['name'], $project['html_notes'], $scheduleTasks );

		$this->display->addContent( $out );

		$date = date('Y-m-d');

		$this->display->doOutput('pdf', "{$project['name']}_Production Schedule_{$date}.pdf");
	}

	/**
	 * View a specific project.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function hours()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$projectID = intval($this->input['extra'][0]);

		if ( $projectID == 0 )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listProjects();
			return;
		}

		$this->registry->getAPI('asana')->updateProject($projectID);

		// Query projects for this page
		$this->DB->query(
			"SELECT * FROM project WHERE project_gid = '{$projectID}';"
		);

		$project = array();
		$tasks = array();

		$users = array(0=>0);

		if ( count($this->users) > 0 )
		{
			foreach( $this->users as $id => $r )
			{
				$users[$id] = 0;
			}
		}

		$cats = array();

		if ( count($this->categories) > 0 )
		{
			foreach( $this->categories as $id => $r )
			{
				$cats[$id] = 0;
			}
		}

		// Loop through the results and add a row for each
		while( $r = $this->DB->fetchRow() )
		{
			$r['tasks'] = unserialize($r['tasks']);
			$r['custom_field_settings'] = unserialize($r['custom_field_settings']);

			if ( is_array( $r['tasks'] ) && count( $r['tasks'] ) > 0 )
			{
				foreach( $r['tasks'] as $tid )
				{
					$tasks[] = $tid;
				}
			}

			$project = $r;
		}

		if ( count($project) == 0 )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listProjects();
			return;
		}

		// Add a breadcrumb for this project
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'page_id' => $this->id, 'extra' => array($projectID) ) ), $project['name'] );

		// Query for subtasks
		if ( count($tasks) > 0 )
		{
			$this->DB->query(
				"SELECT task_gid FROM task WHERE parent_gid IN(".implode(',',$tasks).");"
			);

			while( $r = $this->DB->fetchRow() )
			{
				if ( strlen( $r['task_gid'] ) > 0 )
				{
					$tasks[] = $r['task_gid'];
				}
			}
		}

		// Query tasks for this page
		if ( count($tasks) > 0 )
		{
			$this->DB->query(
				"SELECT task_gid,assignee_gid,name,completed,completed_at,custom_fields FROM task WHERE task_gid IN(".implode(',',$tasks).");"
			);

			$tasks = array();

			// Loop through the results and add a row for each
			while( $r = $this->DB->fetchRow() )
			{
				$r['custom_fields'] = unserialize($r['custom_fields']);

				if ( strpos($r['name'],':') > 0 )
				{
					$r['name'] = substr($r['name'], strpos($r['name'],':')+1);
				}

				$find = array('(AV1)', '(AV2)', '(MSN)', '(MGFX)');
				$replace = array('', '', '', '');

				$r['name'] = str_replace($find, $replace, $r['name']);

				$tasks[$r['task_gid']] = $r;
			}
		}

		// Page title
		$this->display->setTitle( $this->lang->getString('hours_view_title') );

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( $this->lang->getString('tasks_head_name')          , "25%" );
		$this->html->td_header[] = array( $this->lang->getString('tasks_head_owner')         , "20%" );
		$this->html->td_header[] = array( $this->lang->getString('tasks_head_completed')     , "20%" );
		$this->html->td_header[] = array( $this->lang->getString('tasks_head_category')      , "25%" );
		$this->html->td_header[] = array( $this->lang->getString('tasks_head_hours')         , "10%" );

		//-----------------------------------------

		// Begin table
		$taskTable = $this->html->startTable();

		$totalHours = 0;

		if ( count($tasks) > 0 )
		{
			foreach( $tasks as $task )
			{
				if ( isset($task) && isset($task['custom_fields']) && isset($task['custom_fields'][$this->billingHrs]) )
				{
					$totalHours += $task['custom_fields'][$this->billingHrs];

					if ( isset($users[$task['assignee_gid']]) )
					{
						$users[$task['assignee_gid']] += $task['custom_fields'][$this->billingHrs];
					}
					else
					{
						$users[0] += $task['custom_fields'][$this->billingHrs];
					}

					if ( isset($cats[$task['custom_fields'][$this->billingCat]]) )
					{
						$cats[$task['custom_fields'][$this->billingCat]] += $task['custom_fields'][$this->billingHrs];
					}
					else
					{
						$cats[0] += $task['custom_fields'][$this->billingHrs];
					}

					$taskTable .= $this->html->addTdRow(
						array(
							"<a href='https://app.asana.com/0/{$projectID}/{$tid}' target='_blank'>{$task['name']}</a>",
							$this->users[$task['assignee_gid']]['name'],
							"<center>".($task['completed'] == 1 ? date('M j, Y', strtotime($task['completed_at'])) : '')."</center>",
							$this->categories[$task['custom_fields'][$this->billingCat]],
							$task['custom_fields'][$this->billingHrs],
						)
					);
				}
			}
		}

		$taskTable .= $this->html->endTable();

		$html = "<h2>{$project['name']}</h2>";
		$html .= "\n<h3>Total Hours: {$totalHours}</h3>";

		$html .= "\n<h4>Breakdown by Category:</h4>\n<ul>";

		if ( count($cats) > 0 )
		{
			foreach( $cats as $id => $r )
			{
				if ( $r <> 0 )
				{
					$html .= "\n\t<li><div class='hours-inline'>{$this->categories[$id]}</div>{$r} hours</li>";
				}
			}
		}

		$html .= "\n</ul>";
		$html .= "\n<h4>Breakdown by Person:</h4>\n<ul>";

		if ( count($users) > 0 )
		{
			foreach( $users as $id => $r )
			{
				if ( $r <> 0 )
				{
					if ( isset( $this->users[$id] ) )
					{
						$html .= "\n\t<li><div class='hours-inline'>{$this->users[$id]['name']}</div>{$r} hours</li>";
					}
					else
					{
						$html .= "\n\t<li><div class='hours-inline'>Unassigned</div>{$r} hours</li>";
					}
				}
			}
		}

		$html .= "\n</ul>";
		$html .= "\n<h4>Tasks</h4>";
		$html .= $taskTable;

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

class TeamType extends PageType
{
	/**
	 * The name of the type
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $name = 'team';

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
		$this->metadata['schedule_enable']      = $this->registry->txtStripslashes( trim( $this->input['schedule_enable'] ) );
		$this->metadata['responsible_party']    = $this->registry->txtStripslashes( trim( $this->input['responsible_party'] ) );
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
				$this->lang->getString('pages_form_asana_schedule_enable'),
				($compareID > 0 ? "<div class='compare'>".$metadata[ $compareID ]['schedule_enable']['value'] . "</div>" : "") .
				$html->formDropdown( 'schedule_enable', $this->registry->getAPI('asana')->getFieldsDropdown(), $_POST['schedule_enable'] ? $_POST['schedule_enable'] : $metadata[ $languageID ]['schedule_enable']['value'] )
			)
		);

		$out .= $html->addTdRow(
			array(
				$this->lang->getString('pages_form_asana_responsible_party'),
				($compareID > 0 ? "<div class='compare'>".$metadata[ $compareID ]['responsible_party']['value'] . "</div>" : "") .
				$html->formDropdown( 'responsible_party', $this->registry->getAPI('asana')->getFieldsDropdown(), $_POST['responsible_party'] ? $_POST['responsible_party'] : $metadata[ $languageID ]['responsible_party']['value'] )
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