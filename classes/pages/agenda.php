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

class Agenda extends Page
{
	/**
	 * The type name that is stored in the database and used as a key for skinning
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'agenda';

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

		$this->DB->query("SELECT subpage_id FROM subpage WHERE page_id = '{$this->id}' ORDER BY position;");

		while( $r = $this->DB->fetchRow() )
		{
			$TYPE_CLASSES = $this->subpage->getType($r['type']);
			$ids[$r['subpage_id']] = $TYPE_CLASSES[0]($r);
		}

		$this->DB->query("SELECT * FROM metadata_subpage WHERE language_id = '{$this->lang->getLanguageID()}' AND id IN(".implode(",",$ids).");");

		while( $r = $this->DB->fetchRow() )
		{
			$subMeta[ $r['meta_id'] ] = $r;
		}

		$subMeta = $this->subpage->processMetadataByID( $subMeta );

		foreach( $ids as $k => $v )
		{
			$ids[$k]->setMeta( $subMeta[ $k ] );
		}


		$out = $this->display->compiledTemplates('skin_agenda')->wrapper( $ids );

		$this->display->addContent( $out );

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

class AgendaType extends PageType
{
	/**
	 * The name of the type
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $name = 'agenda';

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

		return $out;
	}

	protected function setupMetadata()
	{
		$this->metadata['js']                   = '';
	}
}

?>