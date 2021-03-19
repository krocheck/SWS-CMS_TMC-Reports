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

class Production extends Subpage
{
	/**
	 * The subpage id
	 *
	 * @access protected
	 * @var int
	 * @since 1.0.0
	 */
	protected static $type = 'production';

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
		$this->portfolio = array();
		$this->projects  = array();

		$this->registry->getAPI('asana')->updatePortfolio($this->metadata['portfolio']['value']);

		$this->DB->query("SELECT * FROM portfolio WHERE portfolio_gid = '{$this->metadata['portfolio']['value']}';");

		while( $r = $this->DB->fetchRow() )
		{
			$this->portfolio = $r;
		}

		$this->portfolio['custom_field_settings'] = unserialize($this->portfolio['custom_field_settings']);
		$this->portfolio['projects'] = unserialize($this->portfolio['projects']);

		$this->DB->query("SELECT * FROM project WHERE project_gid IN(" . implode(",", $this->project['projects']) . ")");

		while( $r = $this->DB->fetchRow() )
		{
			$this->projects[$r['project_gid']] = $r;
			$this->projects[$r['project_gid']]['custom_fields'] = unserialize($r['custom_fields']);
		}

		if ( is_array($this->portfolio['projects']) && count($this->portfolio['projects']) > 0 )
		{
			foreach( $this->portfolio['projects'] as $r )
			{
				if ( isset($this->projects[$r]) && is_array($this->projects[$r]) )
				{
					$out .= $this->display->compiledTemplates('skin_agenda')->production( $this->projects[$r] );
				}
			}
		}

		return $out;
	}

	public function getID()
	{
		return " id='production'";
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

class ProductionType extends SubpageType
{
	/**
	 * The metadata setup: name, type, input, etc.
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $metadata = array( 'name' => '', 'portfolio' => '' );
	/**
	 * The name of the type
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $name = 'production';

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
		$this->metadata['portfolio']    = $this->registry->txtStripslashes( trim( $this->input['portfolio'] ) );

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
				$this->lang->getString('subpages_'.$type.'_form_portfolio'),
				($compareID > 0 ? $meta[ $compareID ]['portfolio']['value'] . "<br><br>" : "") .
				$ad_skin->formDropdown( 'portfolio', $this->registry->getAPI('asana')->getPortfoliosDropdown(), $_POST['portfolio'] ? $_POST['portfolio'] : $meta[ $languageID ]['portfolio']['value'] )
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