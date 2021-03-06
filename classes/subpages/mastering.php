<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Page type configuration file
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

class Mastering extends Subpage
{
	/**
	 * The subpage id
	 *
	 * @access protected
	 * @var int
	 * @since 1.0.0
	 */
	protected static $type = 'mastering';

	/**
	 * Constructor that loads the registry
	 *
	 * @param array $dbRow array of the language values
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doExecute( $dbRow )
	{
		$this->id            = $dbRow['subpage_id'];
	}

	public function getContent()
	{
		$evo = "";
		$other = "";
		$this->project  = array();
		$this->sections = array();
		$this->tasks    = array();
		$this->users    = $this->cache->getCache('users');

		$this->registry->getAPI('asana')->updateProject($this->metadata['project']['value']);

		$this->DB->query("SELECT project_gid,custom_fields,custom_field_settings,tasks FROM project WHERE project_gid = '{$this->metadata['project']['value']}';");

		while( $r = $this->DB->fetchRow() )
		{
			$this->project = $r;
		}

		$this->project['custom_fields'] = unserialize($this->project['custom_fields']);
		$this->project['custom_field_settings'] = unserialize($this->project['custom_field_settings']);
		$this->project['tasks'] = unserialize($this->project['tasks']);

		$this->DB->query("SELECT task_gid,assignee_gid,name,custom_fields,resource_subtype,modified_at FROM task WHERE task_gid IN(" . implode(",", $this->project['tasks']) . ") AND completed = 0;");

		while( $r = $this->DB->fetchRow() )
		{
			$this->tasks[$r['task_gid']] = $r;
			$this->tasks[$r['task_gid']]['custom_fields'] = unserialize($r['custom_fields']);
			$this->tasks[$r['task_gid']]['tags'] = unserialize($r['tags']);
		}

		if ( is_array($this->project['tasks']) && count($this->project['tasks']) > 0 )
		{
			foreach( $this->project['tasks'] as $r )
			{
				if ( isset($this->tasks[$r]) && is_array($this->tasks[$r]) )
				{
					if ( ($this->filter[0] == 0 && $this->filter[1] == 0 ) || intval($this->tasks[$r]['custom_fields'][ $this->filter[0] ]) == $this->filter[1] )
					{
						$assigned = "";

						if ( isset( $this->users[$this->tasks[$r]['assignee_gid']]) )
						{
							$assigned = " (" . substr($this->users[$this->tasks[$r]['assignee_gid']]['name'],0,strpos($this->users[$this->tasks[$r]['assignee_gid']]['name']," ")) . ")";
						}

						$evo .= "<p>{$this->tasks[$r]['name']} - Last modified: ".date("M j, Y",strtotime($this->tasks[$r]['modified_at']))."{$assigned}</p>";
					}
				}
			}
		}

		if ( strlen( $evo ) == 0 )
		{
			$evo .= "<p>(<em>none</em>)</p>";
		}

		$this->registry->getAPI('asana')->updateProject($this->metadata['other']['value']);

		$this->DB->query("SELECT project_gid,custom_fields,custom_field_settings,sections,tasks FROM project WHERE project_gid = '{$this->metadata['other']['value']}';");

		while( $r = $this->DB->fetchRow() )
		{
			$this->project = $r;
		}

		$this->project['custom_fields'] = unserialize($this->project['custom_fields']);
		$this->project['custom_field_settings'] = unserialize($this->project['custom_field_settings']);
		$this->project['sections'] = unserialize($this->project['sections']);
		$this->project['tasks'] = unserialize($this->project['tasks']);

		$this->DB->query("SELECT section_gid,name,tasks FROM section WHERE project_gid = '{$this->metadata['other']['value']}';");

		while( $r = $this->DB->fetchRow() )
		{
			$this->sections[$r['section_gid']] = $r;
			$this->sections[$r['section_gid']]['tasks'] = unserialize($r['tasks']);
		}

		$this->DB->query("SELECT task_gid,assignee_gid,name,custom_fields,resource_subtype,modified_at,tags FROM task WHERE task_gid IN(" . implode(",", $this->project['tasks']) . ") AND completed = 0;");

		while( $r = $this->DB->fetchRow() )
		{
			$this->tasks[$r['task_gid']] = $r;
			$this->tasks[$r['task_gid']]['custom_fields'] = unserialize($r['custom_fields']);
			$this->tasks[$r['task_gid']]['tags'] = unserialize($r['tags']);
		}

		if ( is_array($this->project['sections']) && count($this->project['sections']) > 0 )
		{
			foreach( $this->project['sections'] as $r )
			{
				if ( isset($this->sections[$r]) && is_array($this->sections[$r]) && $this->sections[$r]['name'] != '(no section)' &&
					 isset($this->sections[$r]['tasks']) && is_array($this->sections[$r]['tasks']) && count($this->sections[$r]['tasks']) >0 )
				{
					$other .= "</div><h4>{$this->sections[$r]['name']}</h4><div class='mastering'>";

					foreach( $this->sections[$r]['tasks'] as $s )
					{
						if ( isset($this->tasks[$s]) && is_array($this->tasks[$s]) )
						{
							$assigned = "";

							if ( isset( $this->users[$this->tasks[$s]['assignee_gid']]) )
							{
								$assigned = " (" . substr($this->users[$this->tasks[$s]['assignee_gid']]['name'],0,strpos($this->users[$this->tasks[$s]['assignee_gid']]['name']," ")) . ")";
							}

							if ( strlen($this->tasks[$s]['custom_fields'][512408346444750]) > 0 )
							{
								$this->tasks[$s]['custom_fields'][512408346444750] = ' | ' . $this->tasks[$s]['custom_fields'][512408346444750];
							}

							if ( strlen($this->tasks[$s]['custom_fields'][1109616918506843]) > 0 )
							{
								$this->tasks[$s]['custom_fields'][1109616918506843] = ' (' . $this->tasks[$s]['custom_fields'][1109616918506843] . ')';
							}

							$other .= "<p>{$this->tasks[$s]['name']}{$this->tasks[$s]['custom_fields'][1109616918506843]}{$this->tasks[$s]['custom_fields'][512408346444750]} | Last modified: ".date("M j, Y",strtotime($this->tasks[$s]['modified_at']))."{$assigned}</p>";
						}
					}
				}
			}
		}

		return "<h4>EVO Workspaces</h4><div class='mastering'>".$evo.$other."</div>";
	}

	public function getID()
	{
		return "";
	}

	public function getName()
	{
		return $this->metadata['name']['value'];
	}

	public function setMeta( $metadata )
	{
		$this->metadata = $metadata;

		if ( isset($this->metadata['filter']) && isset($this->metadata['filter']['value']) )
		{
			$this->filter = explode(":",$this->metadata['filter']['value']);

			if ( ! isset($this->filter[0]) )
			{
				$this->filter[0] = 0;
			}

			if ( ! isset($this->filter[1]) )
			{
				$this->filter[1] = 0;
			}
		}
		else
		{
			$this->filter = array(0,0);
		}
	}
}

/**
 * BLI-CMS System
 *  - Backlot Imaging Programming Team
 * 
 * Subpage content type class
 * 
 * @copyright	2009 BL Imaging, Inc.
 * @package		BLI-CMS
 * @subpackage	Subpage
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 */

class MasteringType extends SubpageType
{
	/**
	 * The metadata setup: name, type, input, etc.
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $metadata = array( 'name' => '', 'project' => '', 'filter' => '', 'other' => '' );
	/**
	 * The name of the type
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $name = 'mastering';

	/**
	 * MUST BE OVERRIDEN: parses the input and returns true
	 * if there is a problem with an input.
	 *
	 * @return bool
	 * @access public
	 * @since 1.0.0
	 */
	public function adminDoSaveChecks()
	{
		$out = FALSE;

		$this->metadata['name']         = $this->registry->txtStripslashes( trim( $this->input['name'] ) );
		$this->metadata['project']      = $this->registry->txtStripslashes( trim( $this->input['project'] ) );
		$this->metadata['filter']       = $this->registry->txtStripslashes( trim( $this->input['filter'] ) );
		$this->metadata['other']        = $this->registry->txtStripslashes( trim( $this->input['other'] ) );

		if ( strlen( $this->metadata['name'] ) < 3 )
		{
			$out = TRUE;
		}

		return $out;
	}

	/**
	 * MUST BE OVERRIDEN: returns the html for the type's
	 * specific settings for the control panel
	 *
	 * @param string $type add|edit
	 * @param admin_skin $ad_skin the skin library
	 * @param array $subpage the db row plus metadata array
	 * @param int $languageID the add/edit language
	 * @param int $compareID the language for text comparison
	 * @param string $button the text for the submit button
	 * @return string the html
	 * @access public
	 * @since 1.0.0
	 */
	public function adminPageForm( $ad_skin, $meta, $languageID, $compareID, $type )
	{
		$out = "";

		//-----------------------------------------
		// Form elements
		//-----------------------------------------

		$out .= $ad_skin->startFieldset( $this->lang->getString('subpages_'.$page['type'].'_form_field_info') );

		$out .= $ad_skin->addTdRow(
			array(
				$this->lang->getString('subpages_'.$type.'_form_name'),
				($compareID > 0 ? $meta[ $compareID ]['name']['value'] . "<br><br>" : "") .
				$ad_skin->formInput( 'name', $this->registry->txtStripslashes( $_POST['name'] ? $_POST['name'] : $meta[ $languageID ]['name']['value'] ) )
			)
		);

		$out .= $ad_skin->addTdRow(
			array(
				$this->lang->getString('subpages_'.$type.'_form_evo'),
				($compareID > 0 ? $meta[ $compareID ]['project']['value'] . "<br><br>" : "") .
				$ad_skin->formDropdown( 'project', $this->registry->getAPI('asana')->getProjectsDropdown(), $_POST['project'] ? $_POST['project'] : $meta[ $languageID ]['project']['value'] )
			)
		);

		$out .= $ad_skin->addTdRow(
			array(
				$this->lang->getString('subpages_'.$type.'_form_filter'),
				($compareID > 0 ? $meta[ $compareID ]['filter']['value'] . "<br><br>" : "") .
				$ad_skin->formTextarea( 'filter', $this->registry->txtStripslashes( $_POST['filter'] ? $_POST['filter'] : $meta[ $languageID ]['filter']['value'] ) )
			)
		);

		$out .= $ad_skin->addTdRow(
			array(
				$this->lang->getString('subpages_'.$type.'_form_other'),
				($compareID > 0 ? $meta[ $compareID ]['other']['value'] . "<br><br>" : "") .
				$ad_skin->formDropdown( 'other', $this->registry->getAPI('asana')->getProjectsDropdown(), $_POST['other'] ? $_POST['other'] : $meta[ $languageID ]['other']['value'] )
			)
		);

		//-----------------------------------------
		// End table and form
		//-----------------------------------------

		$out .= $ad_skin->endFieldset();

		return $out;
	}
}

?>