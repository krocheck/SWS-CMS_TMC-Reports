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

class Shows extends Subpage
{
	/**
	 * The subpage id
	 *
	 * @access protected
	 * @var int
	 * @since 1.0.0
	 */
	protected static $type = 'shows';

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
		$out = "";
		$this->project  = array();
		$this->sections = array();
		$this->tasks    = array();

		$this->registry->getAPI('asana')->updateProject($this->metadata['project']['value']);

		$this->DB->query("SELECT project_gid,custom_fields,custom_field_settings,sections,tasks FROM project WHERE project_gid = '{$this->metadata['project']['value']}';");

		while( $r = $this->DB->fetchRow() )
		{
			$this->project = $r;
		}

		$this->project['custom_fields'] = unserialize($this->project['custom_fields']);
		$this->project['custom_field_settings'] = unserialize($this->project['custom_field_settings']);
		$this->project['sections'] = unserialize($this->project['sections']);
		$this->project['tasks'] = unserialize($this->project['tasks']);

		$this->DB->query("SELECT section_gid,name,tasks FROM section WHERE project_gid = '{$this->metadata['project']['value']}';");

		while( $r = $this->DB->fetchRow() )
		{
			$this->sections[$r['section_gid']] = $r;
			$this->sections[$r['section_gid']]['tasks'] = unserialize($r['tasks']);
		}

		$this->DB->query("SELECT task_gid,name,completed,custom_fields,due_on,resource_subtype,start_on,tags,html_notes FROM task WHERE task_gid IN(" . implode(",", $this->project['tasks']) . ") AND completed = 0;");

		while( $r = $this->DB->fetchRow() )
		{
			$this->tasks[$r['task_gid']] = $r;
			$this->tasks[$r['task_gid']]['custom_fields'] = unserialize($r['custom_fields']);
			$this->tasks[$r['task_gid']]['tags'] = unserialize($r['tags']);
			$this->tasks[$r['task_gid']]['description'] = $r['html_notes'];
		}

		if ( is_array($this->project['sections']) && count($this->project['sections']) > 0 )
		{
			foreach( $this->project['sections'] as $r )
			{
				if ( isset($this->sections[$r]) && is_array($this->sections[$r]) && $this->sections[$r]['name'] != '(no section)' )
				{
					$out .= $this->display->compiledTemplates('skin_agenda')->section( $this->sections[$r] );

					if ( isset($this->sections[$r]['tasks']) && is_array($this->sections[$r]['tasks']) )
					{
						foreach( $this->sections[$r]['tasks'] as $s )
						{
							if ( isset($this->tasks[$s]) && is_array($this->tasks[$s]) )
							{
								$out .= $this->display->compiledTemplates('skin_agenda')->show( $this->tasks[$s] );
							}
						}
					}
				}
			}
		}

		return $out;
	}

	public function getID()
	{
		return " id='shows'";
	}

	public function getName()
	{
		return $this->metadata['name']['value'];
	}

	public function setMeta( $metadata )
	{
		$this->metadata = $metadata;
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

class ShowsType extends SubpageType
{
	/**
	 * The metadata setup: name, type, input, etc.
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $metadata = array( 'name' => '', 'project' => '' );
	/**
	 * The name of the type
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $name = 'shows';

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

		//-----------------------------------------
		// Project fields
		//-----------------------------------------

		/*$this->project = array();
		$this->tasks   = array();
		$this->DB->query("SELECT project_gid,tasks FROM project WHERE project_gid = '{$this->metadata['project']}';");

		while( $r = $this->DB->fetchRow() )
		{
			$this->project = $r;
		}

		$this->project['tasks'] = unserialize($this->project['tasks']);

		$this->DB->query("SELECT task_gid,name,resource_subtype FROM task WHERE task_gid IN(" . implode(",", $this->project['tasks']) . ") AND completed = 0;");

		while( $r = $this->DB->fetchRow() )
		{
			$this->tasks[$r['task_gid']] = $r;
		}

		if ( is_array($this->project['tasks']) && count($this->project['tasks']) > 0 )
		{
			foreach( $this->project['tasks'] as $r )
			{
				if ( isset($this->tasks[$r]) && is_array($this->tasks[$r]) )
				{
					if ( $this->tasks[$r]['resource_subtype'] != 'section' )
					{
						$this->metadata[$r] = $this->registry->txtStripslashes( trim( $this->input[$r] ) );
					}
				}
			}
		}*/

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
				$this->lang->getString('subpages_'.$type.'_form_project'),
				($compareID > 0 ? $meta[ $compareID ]['project']['value'] . "<br><br>" : "") .
				$ad_skin->formDropdown( 'project', $this->registry->getAPI('asana')->getProjectsDropdown(), $_POST['project'] ? $_POST['project'] : $meta[ $languageID ]['project']['value'] )
			)
		);

		//-----------------------------------------
		// End table and form
		//-----------------------------------------

		$out .= $ad_skin->endFieldset();

		/*$out .= $ad_skin->startFieldset( 'subpages_'.$type.'_fieldset_descriptions' );

		//-----------------------------------------
		// Project fields
		//-----------------------------------------

		$this->project = array();
		$this->tasks   = array();

		$this->registry->getAPI('asana')->updateProject($meta[ $languageID ]['project']['value']);

		$this->DB->query("SELECT project_gid,tasks FROM project WHERE project_gid = '{$meta[ $languageID ]['project']['value']}';");

		while( $r = $this->DB->fetchRow() )
		{
			$this->project = $r;
		}

		$this->project['tasks'] = unserialize($this->project['tasks']);

		$this->DB->query("SELECT task_gid,name,resource_subtype FROM task WHERE task_gid IN(" . implode(",", $this->project['tasks']) . ") AND completed = 0;");

		while( $r = $this->DB->fetchRow() )
		{
			$this->tasks[$r['task_gid']] = $r;
		}

		if ( is_array($this->project['tasks']) && count($this->project['tasks']) > 0 )
		{
			foreach( $this->project['tasks'] as $r )
			{
				if ( isset($this->tasks[$r]) && is_array($this->tasks[$r]) )
				{
					if ( $this->tasks[$r]['resource_subtype'] != 'section' )
					{
						$out .= $ad_skin->addTdRow(
							array(
								$this->tasks[$r]['name'],
								$ad_skin->formRTE( $r, $_POST[$r] ? $_POST[$r] : $meta[ $languageID ][$r]['value'] )
							)
						);
					}
				}
			}
		}

		//-----------------------------------------
		// End table and form
		//-----------------------------------------

		$out .= $ad_skin->endFieldset();*/

		return $out;
	}
}

?>