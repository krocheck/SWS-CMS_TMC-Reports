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
		$out = "";
		$this->project = array();
		$this->tasks   = array();
		$this->users   = $this->cache->getCache('users');

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

						$out .= "<p>{$this->tasks[$r]['name']} - Last modified: ".date("M j, Y",strtotime($this->tasks[$r]['modified_at']))."{$assigned}</p>";
					}
				}
			}
		}

		if ( strlen( $out ) == 0 )
		{
			$out .= "<p>(<em>none</em>)</p>";
		}

		return "<h4>EVO Workspaces</h4><div class='mastering'>".$out."</div><h4>Other Mastering</h4><div class='mastering'>".$this->registry->parseHTML( $this->metadata['description']['value'] )."</div>";
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
	protected $metadata = array( 'name' => '', 'project' => '', 'description' => '' );
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
		$this->metadata['description']  = $this->registry->txtStripslashes( trim( $this->input['description'] ) );

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

		$out .= $ad_skin->addTdRow(
			array(
				$this->lang->getString('subpages_'.$type.'_form_filter'),
				($compareID > 0 ? $meta[ $compareID ]['filter']['value'] . "<br><br>" : "") .
				$ad_skin->formTextarea( 'filter', $this->registry->txtStripslashes( $_POST['filter'] ? $_POST['filter'] : $meta[ $languageID ]['filter']['value'] ) )
			)
		);

		$out .= $ad_skin->addTdRow(
			array(
				$this->lang->getString('subpages_'.$type.'_form_description'),
				($compareID > 0 ? $meta[ $compareID ]['description']['value'] . "<br><br>" : "") .
				$ad_skin->formRTE( 'description', $_POST['description'] ? $_POST['description'] : $meta[ $languageID ]['description']['value'] )
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