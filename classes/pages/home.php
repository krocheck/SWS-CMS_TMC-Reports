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

class Home extends Page
{
	/**
	 * The type name that is stored in the database and used as a key for skinning
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'home';

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
		$out  = "";

		$js = $this->registry->parseHTML( $meta['js']['meta_value'] );

		if ( strlen( $js ) > 0 )
		{
			$this->display->addJavascript( $js );
		}

		$text        = $this->registry->parseHTML( $meta['content']['meta_value'] );

		$consumedCount = 0;
		$pendingCount = 0;

		$cats   = $this->cache->getCache('categories');
		$onTap  = array();
		$cellar = array();
		$consumed = array();
		$pending = array();
		$rewards  = array();

		$activeReward = 0;
		$points = 0;
		$currentBeers = array();

		$this->DB->query(
			"SELECT m.*
				FROM menu_item m
				INNER JOIN menu_category c ON (m.category_id=c.category_id)
				WHERE c.type <> 'na' AND m.active = 1
				ORDER BY c.position, m.title;"
		);

		if ( $this->DB->getTotalRows() > 0 )
		{
			while( $row = $this->DB->fetchRow() )
			{
				if ( isset( $cats[ $row['category_id'] ] ) && $cats[ $row['category_id'] ]['type'] == 'tap' )
				{
					$onTap[ $row['menu_item_id'] ] = $row;
				}
				else if ( isset( $cats[ $row['category_id'] ] ) && $cats[ $row['category_id'] ]['type'] == 'reserve' )
				{
					$cellar[ $row['menu_item_id'] ] = $row;
				}
			}
		}

		if ( is_object( $this->registry->getUser() ) )
		{
			$clubID = $this->user->getClubID();

			$this->DB->query(
				"SELECT DISTINCT m.*
					FROM menu_item m
					INNER JOIN transaction t ON (t.menu_id = m.menu_item_id)
					INNER JOIN menu_category c ON (m.category_id = c.category_id)
					WHERE c.type <> 'na' AND t.void = 0 AND t.web_void = 0
					ORDER BY m.title"
			);

			if ( $this->DB->getTotalRows() > 0 )
			{
				while( $row = $this->DB->fetchRow() )
				{
					$consumed[ $row['menu_item_id'] ] = $row;

					if ( isset( $onTap[ $row['menu_item_id'] ] ) )
					{
						$consumedCount++;
					}
				}
			}

			$this->DB->query(
				"SELECT DISTINCT m.*
					FROM menu_item m
					INNER JOIN pending_transaction t ON (t.menu_id = m.menu_item_id)
					INNER JOIN menu_category c ON (m.category_id = c.category_id)
					WHERE c.type <> 'na' AND t.void = 0
					ORDER BY m.title"
			);

			if ( $this->DB->getTotalRows() > 0 )
			{
				while( $row = $this->DB->fetchRow() )
				{ $this->display->addDebug($row);
					$pending[ $row['menu_item_id'] ] = $row;
					$pendingCount++;
				}
			}

			$this->DB->query(
				"SELECT * FROM reward WHERE status < 2;"
			);

			if ( $this->DB->getTotalRows() > 0 )
			{
				while( $row = $this->DB->fetchRow() )
				{
					$rewards[ $row['reward_id'] ] = $row;
				}
			}

			if ( count( $rewards ) > 0 )
			{
				foreach( $rewards as $id => $r )
				{
					if ( $r['status'] == 1 )
					{
						
					}
					else if ( $r['status'] == 0 )
					{
						$activeReward = $r['reward_id'];
						$points = $r['carry_over'];
					}
				}

				if ( $activeReward > 0 )
				{
					$this->DB->query(
						"SELECT DISTINCT t.menu_id, m.points
							FROM transaction t
							LEFT JOIN menu_item m ON (m.menu_item_id = t.menu_id)
							WHERE t.reward_id = {$activeReward} "
					);

					if ( $this->DB->getTotalRows() > 0 )
					{
						while( $row = $this->DB->fetchRow() )
						{
							$currentBeers[ $row['menu_id'] ] = $row['points'];
							$points += $row['points'];
						}
					}
				}
			}
		}

		$out = $this->display->compiledTemplates('skin_home')->wrapper( $onTap, $cellar, $text, $consumed, $consumedCount, (53 - $points), $currentBeers, $pending);

		$this->display->addContent( $out );

		$this->display->doOutput( "home" );
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

class HomeType extends PageType
{
	/**
	 * The name of the type
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $name = 'home';

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
		$this->metadata['content']              = $this->registry->txtStripslashes( trim( $this->input['content'] ) );

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
		
		$out .= $html->addTdRow(
			array(
				$this->lang->getString('pages_form_content'),
				($compareID > 0 ? "<div class='compare'>".$metadata[ $compareID ]['content']['value'] . "</div>" : "") .
				$html->formRTE( 'content', $this->registry->txtStripslashes( $_POST['content'] ? $_POST['content'] : $metadata[ $languageID ]['content']['value'] ) )
			)
		);
		
		return $out;
	}

	protected function setupMetadata()
	{
		$this->metadata['js']                   = '';
		$this->metadata['content']              = '';
	}
}

?>