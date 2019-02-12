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
		$subMeta = array();

		$this->subpage = $this->registry->getClass('SubpageController');

		$js = $this->registry->parseHTML( $meta['js']['meta_value'] );

		if ( strlen( $js ) > 0 )
		{
			$this->display->addJavascript( $js );
		}

		//$out = $this->display->compiledTemplates('skin_agenda')->wrapper( $ids );

		$this->display->addContent( 'test' );

		$this->display->doOutput();
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

		$out .= $html->startFieldset($this->lang->getString('pages_form_asana'));

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