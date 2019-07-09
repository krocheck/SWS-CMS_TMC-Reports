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

class Housekeeping extends Subpage
{
	/**
	 * The subpage id
	 *
	 * @access protected
	 * @var int
	 * @since 1.0.0
	 */
	protected static $type = 'housekeeping';

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
		$out = "<ol>";
		$this->project = array();
		$this->tasks   = array();
		$fields = $this->cache->getCache('fields');

		$this->registry->getAPI('asana')->updateProject($this->metadata['project']['value']);

		$this->DB->query("SELECT project_gid,custom_fields,custom_field_settings,tasks FROM project WHERE project_gid = '{$this->metadata['project']['value']}';");

		while( $r = $this->DB->fetchRow() )
		{
			$this->project = $r;
		}

		$this->project['custom_fields'] = unserialize($this->project['custom_fields']);
		$this->project['custom_field_settings'] = unserialize($this->project['custom_field_settings']);
		$this->project['tasks'] = unserialize($this->project['tasks']);

		$this->DB->query("SELECT task_gid,name,completed,custom_fields,due_on,resource_subtype,start_on,tags,html_notes FROM task WHERE task_gid IN(" . implode(",", $this->project['tasks']) . ") AND completed = 0;");

		while( $r = $this->DB->fetchRow() )
		{
			$this->tasks[$r['task_gid']] = $r;
			$this->tasks[$r['task_gid']]['custom_fields'] = unserialize($r['custom_fields']);
			$this->tasks[$r['task_gid']]['tags'] = unserialize($r['tags']);
			$this->tasks[$r['task_gid']]['description'] = $r['html_notes'];
		}

		if ( is_array($this->project['tasks']) && count($this->project['tasks']) > 0 )
		{
			$startSection = 0;
			$inSection = 0;
			$sectionItems = 0;
			$inTable = 0;
			foreach( $this->project['tasks'] as $r )
			{
				if ( isset($this->tasks[$r]) && is_array($this->tasks[$r]) )
				{
					if ( $this->tasks[$r]['resource_subtype'] == 'section' )
					{
						if ( $inSection > 0 )
						{
							if ( $sectionItems > 0 && $inTable == 0)
							{
								$out .= "</ul>";
							}
							else if ( $inTable == 1)
							{
								$out .= "</tr></tbody></table>";
							}

							$out .= "</li>";
						}

						$out .= "<li>{$this->tasks[$r]['name']}";
						$inSection = 1;
						$startSection = 1;
						$sectionItems = 0;
						$inTable = 0;

						if ( $fields[1130376522209435]['enum_options'][$this->tasks[$r]['custom_fields'][1130376522209435]]['name'] == 'Table' )
						{
							$inTable = 1;
							$out .= "<table border='1' cellpadding='1' cellspacing='1' style='width:90%'><tbody><tr>";
						}
					}
					else
					{
						if ($startSection == 1 && $inTable == 0)
						{
							$out .= "<ul>";
							$startSection = 0;
						}
						
						if ( $inTable == 1)
						{
							//$width = intval(100 / $this->tasks[$r]['custom_fields'][1130376522209440]);
							$out .= "<td style='text-align:left; vertical-align:top;'>{$this->tasks[$r]['name']}{$this->tasks[$r]['html_notes']}</td>";
						}
						else
						{
							$out .= "<li>{$this->tasks[$r]['name']}{$this->tasks[$r]['html_notes']}</li>";
						}
						
						$sectionItems += 1;
					}
				}
			}
		}

		if ( $sectionItems > 0 && $inTable == 0)
		{
			$out .= "</ul>";
		}
		else if ( $inTable == 1)
		{
			$out .= "</tr></tbody></table>";
		}

		$out .= '</li></ol>';

		return $out;
	}

	public function getID()
	{
		return '';
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

class HousekeepingType extends SubpageType
{
	/**
	 * The metadata setup: name, type, input, etc.
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $metadata = array( 'name' => '', 'project' => '', 'style' => '', 'columns' => '' );
	/**
	 * The name of the type
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $name = 'housekeeping';

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
		$this->metadata['style']        = $this->registry->txtStripslashes( trim( $this->input['style'] ) );
		$this->metadata['columns']      = $this->registry->txtStripslashes( trim( $this->input['columns'] ) );

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
				$this->lang->getString('subpages_'.$type.'_form_style'),
				($compareID > 0 ? $meta[ $compareID ]['style']['value'] . "<br><br>" : "") .
				$ad_skin->formDropdown( 'style', $this->registry->getAPI('asana')->getFieldsDropdown(), $_POST['style'] ? $_POST['style'] : $meta[ $languageID ]['style']['value'] )
			)
		);

		$out .= $ad_skin->addTdRow(
			array(
				$this->lang->getString('subpages_'.$type.'_form_columns'),
				($compareID > 0 ? $meta[ $compareID ]['columns']['value'] . "<br><br>" : "") .
				$ad_skin->formDropdown( 'columns', $this->registry->getAPI('asana')->getFieldsDropdown(), $_POST['columns'] ? $_POST['columns'] : $meta[ $languageID ]['columns']['value'] )
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