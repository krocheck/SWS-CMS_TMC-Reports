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
		$this->project = array();
		$this->DB->query("SELECT * FROM project WHERE project_gid = '{$this->metadata['project']['value']}';");

		while( $r = $this->DB->fetchRow() )
		{
			$this->project = $r;
		}

		$this->project['current_status'] = unserialize($this->project['current_status']);
		$this->project['members'] = unserialize($this->project['members']);
		$this->project['followers'] = unserialize($this->project['followers']);
		$this->project['custom_fields'] = unserialize($this->project['custom_fields']);
		$this->project['custom_field_settings'] = unserialize($this->project['custom_field_settings']);
		$this->project['sections'] = unserialize($this->project['sections']);
		$this->project['tasks'] = unserialize($this->project['tasks']);

		$this->display->addDebug($this->project);
		/*
		'project_gid'           => $projectID,
		'owner_gid'             => $row['owner']['gid'],
		'workspace_gid'         => $workspace,
		'team_gid'              => $row['team']['gid'],
		'name'                  => $row['name'],
		'due_date'              => $row['due_date'],
		'start_on'              => $row['start_on'],
		'created_at'            => $this->parseDate( $row['created_at'] ),
		'modified_at'           => $this->parseDate( $row['modified_at'] ),
		'archived'              => ( $row['archived'] ? 1 : 0 ),
		'public'                => ( $row['public'] ? 1 : 0 ),
		'color'                 => $row['color'],
		'html_notes'            => mysqli_real_escape_string( $this->DB->getConnection(), $row['html_notes'] )
		*/

		return "Test";
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

		return $out;
	}
}

?>