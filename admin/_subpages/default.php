<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Admin application wrapper
 * Last Updated: $Date: 2010-06-10 22:30:14 -0500 (Thu, 10 Jun 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Admin
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 9 $
 */

class AdminSubpages extends Command
{
	/**
	 * The admin app skin generator
	 *
	 * @access protected
	 * @var AdminSkin
	 * @since 1.0.0
	 */
	protected $html;
	/**
	 * The subpage controller library
	 *
	 * @access protected
	 * @var SubpageController
	 * @since 1.0.0
	 */
	protected $subpages;

	/**
	 * The main execute function
	 *
	 * @param object $params extra stuff from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doExecute( $params )
	{
		$this->checkPermission();

		// Load the language
		$this->lang->loadStrings('subpages');

		// Get and load the table/form factory
		$this->html = $this->registry->getClass('AdminSkin');

		$this->controller = $this->registry->getClass('PageController');

		$this->DB->query("SELECT * FROM page WHERE page_id = '{$this->input['page_id']}';");

		$this->page = $this->DB->fetchRow();

		if ( ! $this->page['page_id'] )
		{
			$this->error->raiseError( 'invalid_id', FALSE );
		}

		$this->pageType = $this->page['type'];

		$this->DB->query("SELECT * FROM metadata_page WHERE id = '{$this->page['page_id']}';");

		$metadata = array();

		while( $r = $this->DB->fetchRow() )
		{
			$metadata[ $r['meta_id'] ] = $r;
		}

		if ( is_array( $metadata ) && count( $metadata ) > 0 )
		{
			$this->page['metadata'] = $this->controller->processMetadataByLanguage( $metadata );
		}

		$this->page['languages'] = unserialize( $this->page['languages'] );

		if ( is_array( $this->page['metadata'] ) )
		{
			if ( is_array( $this->page['metadata'][ $this->lang->getLanguageID() ] ) )
			{
				$this->page['name'] = $this->page['metadata'][ $this->lang->getLanguageID() ]['name']['value'];
			}
			else if ( is_array( $this->page['metadata'][ $languageID ] ) )
			{
				$this->page['name'] = $this->page['metadata'][ $languageID ]['name']['value'];
			}
			else
			{
				foreach( $this->page['languages'] as $k => $v )
				{
					if ( is_array( $this->page['metadata'][ $v ] ) )
					{
						$this->page['name'] = $this->page['metadata'][ $v ]['name']['value'];
						break;
					}
				}
			}
		}

		$this->lang->loadFromDatabase( TRUE, $this->page['languages'] );

		$this->subpages = $this->registry->getClass('SubpageController');
		$this->subpages->setType( $this->page['type'] );

		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "pages" ), 'admin' ), $this->lang->getString('pages') );
		$this->controller->buildBreadcrumb( $this->page['parent_id'], array( 'module' => 'pages' ), 'admin' );

		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => 'subpages', 'page_id' => $this->page['page_id'] ), 'admin' ), $this->page['name'] );

		if ( ! isset( $this->input['do'] ) )
		{
			$this->input['do'] = '';
		}

		switch( $this->input['do'] )
		{
			case 'create':
				$this->showStartForm( 'create' );
				break;
			case 'add':
				$this->showStartForm( 'add' );
				break;
			case 'edit':
				$this->showStartForm( 'edit' );
				break;
			case 'create_save':
				$this->save( 'create' );
				break;
			case 'add_save':
				$this->save( 'add' );
				break;
			case 'edit_save':
				$this->save( 'edit' );
				break;
			case 'delete':
				$this->delete();
				break;
			case 'dodelete':
				$this->doDelete();
				break;
			case 'view':
				$this->view();
				break;
			default:
				$this->listSubpages();
				break;
		}

		$this->display->doOutput();
	}

	/**
	 * Makes sure the user can actually use the app.  Will throw error if not.
	 *
	 * @return void
	 * @access private
	 * @since 1.0.0
	 */
	private function checkPermission()
	{
		if ( ! ( $this->user->getPermission() == 'admin' || $this->user->getPermission() == 'superadmin' ) )
		{
			$this->error->raiseError( 'no_permission', FALSE );
		}
	}

	/**
	 * Makes sure the user actually wants to delete this subpage.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function delete()
	{
		//-----------------------------------------
		// Init Vars
		//-----------------------------------------
		
		$subpageID   = intval($this->input['id']);
		$languageID  = intval($this->input['language_id']);
		$subpage     = array();
		
		$this->DB->query(
			"SELECT sub.*, ma.meta_value AS name
				FROM subpage sub
				LEFT JOIN metadata_subpage ma ON (sub.subpage_id = ma.id AND ma.language_id = {$languageID} AND ma.meta_key = 'name')
				WHERE sub.subpage_id = '{$subpageID}';"
		);
		
		$subpage = $this->DB->fetchRow();
		
		if ( ! $subpage['subpage_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listSubpages();
			return;
		}
		
		$subpage['languages'] = unserialize( $subpage['languages'] );
		
		if ( ! isset( $subpage['languages'][ $languageID ] ) )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listSubpages();
			return;
		}
		else if ( $this->settings['default_subpage_id'] == $subpage['subpage_id'] && ! ( count( $subpage['languages'] ) > 1 ) )
		{
			$this->error->logError( 'subpages_cannot_delete', FALSE );
			$this->listSubpages();
			return;
		}
		
		$formcode = 'dodelete';
		$title    = $this->lang->getString('subpages_'.$this->page['type'].'_delete_title') . " {$subpage['name']} ({$this->lang->getLanguageByID($languageID)->getName()})";
		$button   = $this->lang->getString('subpages_'.$this->page['type'].'_delete_submit');
		
		//-----------------------------------------
		// Start the form
		//-----------------------------------------
		
		$this->display->setTitle( $title );
		
		$html = $this->html->startForm(
			array(
				's'           => $this->user->getSessionID(),
				'app'         => 'admin',
				'module'      => 'subpages',
				'page_id'     => $this->page['page_id'],
				'language_id' => $languageID,
				'id'          => $subpageID,
				'do'          => $formcode
			)
		);
		
		//-----------------------------------------
		// Main form
		//-----------------------------------------
		
		$html .= $this->html->startTable( $title, 'admin-form' );

		$html .= $this->html->startFieldset();
		
		$html .= $this->html->addTdBasic( $this->lang->getString('subpages_'.$this->page['type'].'_delete_form_text') ." {$subpage['name']} ({$this->lang->getLanguageByID($languageID)->getName()})?<br>".$this->lang->getString('subpages_'.$this->page['type'].'_delete_form_text2'), "center" );

		$html .= $this->html->endFieldset();
		
		$html .= $this->html->endForm( $button, "", " {$this->lang->getString('or')} <a href='".$this->display->buildURL( array( 'module' => 'subpages', 'page_id' => $this->page['page_id'] ), 'admin')."'>{$this->lang->getString('cancel')}</a>" );
		
		$this->display->addContent( $html );
	}

	/**
	 * Deletes the subpage
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doDelete()
	{
		//-----------------------------------------
		// Init Vars
		//-----------------------------------------
		
		$subpageID   = intval($this->input['id']);
		$languageID  = intval($this->input['language_id']);
		$subpage     = array();
		$subpages       = array();
		$subpagesString = "";
		
		$this->DB->query(
			"SELECT * FROM subpage WHERE subpage_id = '{$subpageID}';"
		);
		
		$subpage = $this->DB->fetchRow();
		
		if ( ! $subpage['subpage_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listSubpages();
			return;
		}
		
		$subpage['languages'] = unserialize( $subpage['languages'] );
		
		if ( ! isset( $subpage['languages'][ $languageID ] ) )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listSubpages();
			return;
		}
		
		$this->DB->query(
			"SELECT subpage_id FROM subpage WHERE subpage_id = '{$subpageID}';"
		);
		
		while( $r = $this->DB->fetchRow() )
		{
			$subpages[] = $r['subpage_id'];
		}
		
		if( count( $subpages ) > 0 )
		{
			$subpagesString = implode(",", $subpages );
		}
		
		unset( $subpage['languages'][ $languageID ] );
		
		if ( count( $subpage['languages'] ) > 0 )
		{
			$this->DB->query(
				"UPDATE subpage SET languages = '".serialize( $subpage['languages'] )."' WHERE subpage_id = {$subpageID};"
			);
			$this->DB->query(
				"DELETE FROM metadata WHERE module = 'subpage' AND id = '{$subpageID}' AND language_id = '{$languageID}';"
			);
			if ( count( $subpages ) > 0 )
			{
				$this->DB->query(
					"DELETE FROM metadata WHERE module = 'subpage' AND id IN({$subpagesString})' AND language_id = '{$languageID}';"
				);
			}
			
			$this->error->logError( 'subpages_delete_success', TRUE, $subpage );
		}
		else if ( ! ( count( $subpage['languages'] ) > 0 ) && Subpage::delete( $subpage['subpage_id'] ) )
		{
			$this->DB->query(
				"DELETE FROM metadata WHERE module = 'subpage' AND id = '{$subpageID}' AND language_id = '{$languageID}';"
			);
			if ( count( $subpages ) > 0 )
			{
				$this->DB->query(
					"DELETE FROM metadata WHERE module = 'subpage' AND id IN({$subpagesString})' AND language_id = '{$languageID}';"
				);
			}
			
			$this->error->logError( 'subpages_delete_success', TRUE, $subpage );
		}
		else
		{
			$this->error->logError( 'subpages_delete_fail', TRUE, $subpage );
		}
		
		//-----------------------------------------
		// Update statistics & projects
		//-----------------------------------------
		
		$this->listSubpages();
	}

	/**
	 * Lists out the subpages in the system.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function listSubpages()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$languages = $this->lang->getLanguages();
		
		//-----------------------------------------
		// Do we need to do something else first?
		//-----------------------------------------
		
		switch ( $this->input['op'] )
		{
			case 'up':
				$this->move('up');
				break;
			case 'down':
				$this->move('down');
				break;
			default:
		}
		
		//-----------------------------------------
		// Subpage Information
		//-----------------------------------------
		
		$this->display->setTitle( $this->lang->getString('subpages_'.$this->page['type'].'_list_title') );
		
		//-----------------------------------------
		// Table Headers
		//-----------------------------------------
		
		$this->html->td_header[] = array( $this->lang->getString('order')               , "12%" );
		$this->html->td_header[] = array( $this->lang->getString('subpages_'.$this->page['type'].'_head_name')     , "40%" );
		$this->html->td_header[] = array( $this->lang->getString('languages')           , "48%" );
		
		//-----------------------------------------
		
		$html = "<div style='float:right;'><a href='".$this->display->buildURL( array( 'module' => 'subpages', 'do' => 'create', 'page_id' => $this->page['page_id'] ), 'admin')."'>".$this->lang->getString('subpages_'.$this->page['type'].'_create_new')."</a></div>";
		
		$html .= $this->html->startTable( $this->lang->getString('subpages_'.$this->page['type'].'_list_table') );
		
		//-----------------------------------------
		// Get categories
		//-----------------------------------------
		
		$this->DB->query(
			"SELECT s.*, m.meta_value as name
				FROM subpage s
				LEFT JOIN metadata_subpage m ON (s.subpage_id=m.id AND m.meta_key='name' AND m.language_id='{$this->lang->getLanguageID()}')
				WHERE page_id = '{$this->page['page_id']}'
				ORDER BY position;"
		);
		
		$count_order = $this->DB->getTotalRows();
		
		while( $r = $this->DB->fetchRow() )
		{
			$langs = unserialize( $r['languages'] );
			
			$html_order  = $r['position'] > 1            ? $this->html->upButton(   $this->display->buildURL( array( 'module' => 'subpages', 'op' => 'up',   'id' => $r['subpage_id'], 'page_id' => $this->page['page_id'] ), 'admin' ) ) : $this->html->blankIMG();
			$html_order .= $r['position'] < $count_order ? $this->html->downButton( $this->display->buildURL( array( 'module' => 'subpages', 'op' => 'down', 'id' => $r['subpage_id'], 'page_id' => $this->page['page_id'] ), 'admin' ) ) : $this->html->blankIMG();
			
			$checked = ($this->settings['default_subpage_id'] == $r['subpage_id'] ? "checked='checked'" : '');
			
			$lang  = "";
			$count = 0;
			
			foreach( $languages as $v )
			{
				if ( in_array( $v->getID(), $langs ) )
				{
					$lang .= "<li>{$v->getName()} ";
					$lang .= "<a href='".$this->display->buildURL( array( 'module' => 'subpages', 'page_id' => $this->page['page_id'], 'do' => 'edit', 'id' => $r['subpage_id'], 'language_id' => $v->getID() ), 'admin')."'>{$this->lang->getString('edit')}</a> ";
					$lang .= "<a href='".$this->display->buildURL( array( 'module' => 'subpages', 'page_id' => $this->page['page_id'], 'do' => 'delete', 'id' => $r['subpage_id'], 'language_id' => $v->getID() ), 'admin')."'>{$this->lang->getString('delete')}</a></li>";
					$count ++;
				}
			}
			
			if ( $count < count( $languages ) )
			{
				$lang .= "<li><a href='".$this->display->buildURL( array( 'module' => 'subpages', 'page_id' => $this->page['page_id'], 'do' => 'add', 'id' => $r['subpage_id'] ), 'admin')."'>".$this->lang->getString('subpages_'.$this->page['type'].'_add_trans')."</a></li>";
			}
			
			$html .= $this->html->addTdRow(
				array(
					"<center>&nbsp;&nbsp;&nbsp;{$html_order}</center>",
					$r['name'],
					"<ul>{$lang}</ul>"
				)
			);
		}
		
		//-----------------------------------------
		// End the table and print
		//-----------------------------------------
		
		$html .= $this->html->endTable();
		
		$this->display->addContent( $html );
	}

	/**
	 * Swaps the position/order of two subpages based on the direction
	 *
	 * @param string $direction up|down
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function move( $direction )
	{
		$subpageID       = intval($this->input['id']);
		$subpage         = array();
		$langageOther = array();
		
		//-----------------------------------------
		// Check (please?)
		//-----------------------------------------
		
		$this->DB->query(
			"SELECT subpage_id, position
				FROM subpage WHERE subpage_id = '{$subpageID}' AND page_id = '{$this->page['page_id']}';"
		);
		
		$subpage = $this->DB->fetchRow();
		
		if ( ! $subpage['subpage_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			return;
		}
		
		$prev_order = ( $direction == "up" ? $subpage['position'] - 1 : $subpage['position'] + 1 );
		
		$this->DB->query(
			"SELECT subpage_id, position
				FROM subpage WHERE position = '{$prev_order}' AND page_id = '{$this->page['page_id']}';"
		);
		
		$subpageOther = $this->DB->fetchRow();
		
		if ( ! $subpageOther['subpage_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			return;
		}
		
		$this->DB->query(
			"UPDATE subpage
				SET position = '{$subpageOther['position']}'
				WHERE subpage_id = '{$subpage['subpage_id']}';"
		);
		
		$this->DB->query(
			"UPDATE subpage
				SET position = '{$subpage['position']}'
				WHERE subpage_id = '{$subpageOther['subpage_id']}';"
		);
	}

	/**
	 * This thing saves the account information.
	 *
	 * @param string $type add|edit
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function save($type='add')
	{
		//--------------------------------------------
		// INIT
		//--------------------------------------------
		
		$subpageID          = intval( $this->input['id'] );
		$languageID         = intval( $this->input['language_id'] );
		$subpageType        = $this->registry->txtStripslashes( trim( $this->input['type'] ) );
		$subpage            = array();

		//--------------------------------------------
		// Checks...
		//--------------------------------------------
		
		if ( $this->subpages->getType( $subpageType )->adminDoSaveChecks() )
		{
			$this->error->logError( 'incomplete_form', FALSE );
			$this->showStartForm( $type );
			return;
		}
		
		//--------------------------------------------
		// Save...
		//--------------------------------------------
		
		if ( $type == 'create' )
		{
			$this->DB->query(
				"SELECT IF( MAX(position), MAX(position) + 1, 1 ) as new_position FROM subpage WHERE page_id = '{$this->page['page_id']}';"
			);
			
			$position = $this->DB->fetchRow();
			$languages = serialize( array( $languageID => $languageID ) );
			
			$array = array( 'type' => $subpageType, 'languages' => $languages, 'position' => $position['new_position'], 'page_id' => $this->page['page_id'], 'last_update' => date('Y-m-d H:i:s') );
			
			if ( Subpage::create( $array ) )
			{
				$id = $this->DB->getInsertID();
				
				if( $this->subpages->getType( $subpageType )->adminSave( $type, $id, $languageID ) )
				{
					$this->error->logError( 'subpages_lang_created', FALSE );
					$this->listSubpages();
					return;
				}
				else
				{
					$this->error->logError( 'subpages_lang_not_created', TRUE );
					$this->listSubpages();
					return;
				}
			}
			else
			{
				$this->error->logError( 'subpages_lang_not_created', TRUE );
				$this->listSubpages();
				return;
			}
		}
		else
		{
			$this->DB->query(
				"SELECT subpage_id, languages FROM subpage WHERE subpage_id = '{$subpageID}'"
			);
			
			$languages = $this->DB->fetchRow();
			
			$languages = unserialize( $languages['languages'] );
			
			if ( ! in_array( $languageID, $languages ) )
			{
				$languages[$languageID] = $languageID;
				
				$this->DB->query(
					"UPDATE subpage SET languages = '".serialize($languages)."' WHERE subpage_id = '{$subpageID}';"
				);
			}
			
			$this->DB->query("UPDATE subpage SET last_update = '".date('Y-m-d H:i:s')."' WHERE subpage_id = '{$subpageID}';");
			
			$this->DB->query(
				"SELECT * FROM metadata_subpage WHERE id = '{$subpageID}' AND language_id = '{$languageID}';"
			);
			
			$meta = array();
			
			while( $r = $this->DB->fetchRow() )
			{
				$meta[ $r['meta_id'] ] = $r;
			}
			
			if ( $this->subpages->getType( $subpageType )->adminSave( $type, $subpageID, $languageID, $meta ) )
			{
				$this->error->logError( 'subpages_lang_updated', FALSE );
				$this->listSubpages();
				return;
			}
			else
			{
				$this->error->logError( 'subpages_lang_not_updated', TRUE );
				$this->listSubpages();
				return;
			}
		}
	}

	/**
	 * This thing show the form to add/edit an account.
	 *
	 * @param string $type add|edit
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function showForm( $type, $subpageID, $languageID, $compareID, $subpageType, $subpage )
	{
		//-----------------------------------------
		// Check (please?)
		//-----------------------------------------
		
		if ( $type == 'create' )
		{
			$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => 'subpages', 'do' => 'create', 'page_id' => $this->page['page_id'], 'language_id' => $languageID, 'type' => $subpageType), 'admin'), $this->lang->getString('subpages_'.$this->page['type'].'_create_new') );
			
			$formcode = 'create_save';
			$title    = $this->lang->getString('subpages_'.$this->page['type'].'_create_new') . " ({$this->lang->getLanguageByID($languageID)->getName()})";
			$button   = $this->lang->getString('subpages_'.$this->page['type'].'_create_new');
		}
		else if( $type == 'add')
		{
			$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => 'subpages', 'page_id' => $this->page['page_id'], 'do' => 'add', 'id' => $subpageID, 'language_id' => $languageID, 'compare_id' => $compareID), 'admin'), $subpage['name']);
			
			$formcode = 'add_save';
			$title    = $this->lang->getString('subpages_'.$this->page['type'].'_add_title') . " {$subpage['name']} ({$this->lang->getLanguageByID($languageID)->getName()})";
			$button   = $this->lang->getString('subpages_'.$this->page['type'].'_add_save');
		}
		else
		{
			$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => 'subpages', 'page_id' => $this->page['page_id'], 'do' => 'edit', 'id' => $subpageID, 'language_id' => $languageID ), 'admin'), $subpage['name']);
			
			$formcode = 'edit_save';
			$title    = $this->lang->getString('subpages_'.$this->page['type'].'_edit_title') . " {$subpage['name']} ({$this->lang->getLanguageByID($languageID)->getName()})";
			$button   = $this->lang->getString('subpages_'.$this->page['type'].'_edit_save');
		}
		
		//-----------------------------------------
		// Start the form
		//-----------------------------------------
		
		$this->display->setTitle( $title );
		
		$html = $this->html->startForm(
			array(
				's'           => $this->user->getSessionID(),
				'app'         => 'admin',
				'module'      => 'subpages',
				'page_id'     => $this->page['page_id'],
				'id'          => $subpageID,
				'do'          => $formcode,
				'language_id' => $languageID,
				'type'        => $subpageType,
				'compare_id'  => $compareID
			)
		);
		
		$html .= $this->subpages->getType( $subpageType )->adminForm( $type, $this->html, $subpage, $languageID, $compareID, $button, $this->page, $title );
		
		$this->display->addContent( $html );
	}

	/**
	 * This thing show the language picker to add/edit an account.
	 *
	 * @param string $type add|edit
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function showStartForm( $type='add' )
	{
		//-----------------------------------------
		// Init Vars
		//-----------------------------------------
		
		$subpageID      = intval($this->input['id']);
		$languageID     = intval($this->input['language_id']);
		$compareID      = intval($this->input['compare_id']);
		$subpageType    = $this->registry->txtStripslashes( trim( $this->input['type'] ) );
		$subpage        = array();
		$languageArray  = array();
		$compareArray   = array( 0 => array( 0, $this->lang->getString('none') ) );
		$subpageTypes   = $this->subpages->getDropdownArray();

		//-----------------------------------------
		// Check (please?)
		//-----------------------------------------

		if ( $type == 'create' )
		{
			$languageArray = $this->lang->getDropdownArray();
			
			$formcode = 'create';
			$title    = $this->lang->getString('subpages_'.$this->page['type'].'_step_2');
			$button   = $this->lang->getString('subpages_'.$this->page['type'].'_create_new');
		}
		else
		{
			$this->DB->query(
				"SELECT *
					FROM subpage WHERE subpage_id = '{$subpageID}';"
			);
			
			$subpage = $this->DB->fetchRow();
			
			if ( ! $subpage['subpage_id'] )
			{
				$this->error->logError( 'invalid_id', FALSE );
				$this->listSubpages();
				return;
			}
			
			$subpageType = $subpage['type'];
			
			$this->DB->query(
				"SELECT *
					FROM metadata_subpage WHERE id = '{$subpageID}';"
			);
			
			$metadata = array();
			
			while( $r = $this->DB->fetchRow() )
			{
				$metadata[ $r['meta_id'] ] = $r;
			}
			
			if ( is_array( $metadata ) && count( $metadata ) > 0 )
			{
				$subpage['metadata'] = $this->subpages->processMetadataByLanguage( $metadata );
			}
			
			$subpage['languages'] = unserialize( $subpage['languages'] );
			
			foreach( $this->lang->getDropdownArray() as $k => $v )
			{
				if ( ! in_array( $k, $subpage['languages'] ) || ( $type == 'edit' && $languageID == $k ) )
				{
					$languageArray[ $k ] = $v;
				}
				else
				{
					$compareArray[ $k ] = $v;
				}
			}
			
			if ( is_array( $subpage['metadata'] ) )
			{
				if ( is_array( $subpage['metadata'][ $this->lang->getLanguageID() ] ) )
				{
					$subpage['name'] = $subpage['metadata'][ $this->lang->getLanguageID() ]['name']['value'];
				}
				else
				{
					foreach( $subpage['languages'] as $k => $v )
					{
						if ( is_array( $subpage['metadata'][ $v ] ) )
						{
							$subpage['name'] = $subpage['metadata'][ $v ]['name']['value'];
							break;
						}
					}
				}
			}
			
			//--------------------------------------------
			// Figure out UID
			//--------------------------------------------
			
			
			if ( $type == 'add' )
			{
				$formcode = 'add';
				$title    = $this->lang->getString('subpages_'.$this->page['type'].'_add_title') . " {$subpage['name']}";
				$button   = $this->lang->getString('subpages_'.$this->page['type'].'_step_2');
			}
			else
			{
				$formcode = 'edit';
				$title    = $this->lang->getString('subpages_'.$this->page['type'].'_edit_title') . " {$subpage['name']} ({$this->lang->getLanguageByID($languageID)->getName()})";
				$button   = $this->lang->getString('subpages_'.$this->page['type'].'_step_2');
			}
		}
		
		$hiddenInputs = array(
			's'           => $this->user->getSessionID(),
			'app'         => 'admin',
			'module'      => 'subpages',
			'page_id'     => $this->page['page_id'],
			'id'          => $subpageID,
			'do'          => $formcode
		);
		
		if ( $type == 'create' && count( $subpageTypes ) <= 1 )
		{
			foreach( $subpageTypes as $v )
			{
				$hiddenInputs['type'] = $v[0];
				$subpageType = $v[0];
			}
		}
		
		if ( count( $languageArray ) <= 1 )
		{
			foreach( $languageArray as $v )
			{
				$hiddenInputs['language_id'] = $v[0];
				$languageID = $v[0];
			}
		}
		
		if ( ($type == 'add' || $type == 'edit') && count( $compareArray ) <= 1 )
		{
			foreach( $compareArray as $v )
			{
				$hiddenInputs['compare_id'] = $v[0];
				$compareID = $v[0];
			}
		}
		
		if ( ( ! ( $type == 'create' && ( count( $subpageTypes ) > 1 && ! ( strlen( $subpageType ) > 0 ) ) ) &&
		     ( ! ( count( $languageArray ) > 1 ) || $languageID > 0 ) &&
		     ( ! ( ($type == 'add' || $type == 'edit') && count( $compareArray ) > 1 ) || isset( $this->input['compare_id'] ) ) ) )
		{
			if ( ! is_object( $this->lang->getLanguageByID( $languageID ) ) )
			{
				$this->error->logError( 'invalid_id', FALSE );
				$this->listSubpages();
				return;
			}
			
			$this->showForm( $type, $subpageID, $languageID, $compareID, $subpageType, $subpage );
			return;
		}
		else
		{
			if ( $type == 'create' )
			{
				$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => 'subpages', 'do' => 'create', 'page_id' => $this->page['page_id'] ), 'admin'), $this->lang->getString('subpages_'.$this->page['type'].'_create_new') );
			}
			else if( $type == 'add')
			{
				$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => 'subpages', 'page_id' => $this->page['page_id'], 'do' => 'add', 'id' => $subpageID, 'language_id' => $languageID), 'admin'), $subpage['name'] );
			}
			else
			{
				$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => 'subpages', 'page_id' => $this->page['page_id'], 'do' => 'edit', 'id' => $subpageID, 'language_id' => $languageID), 'admin' ), $subpage['name'] );
			}
		}
		
		//-----------------------------------------
		// Start the form
		//-----------------------------------------
		
		$this->display->setTitle( $title );
		
		$html = $this->html->startForm( $hiddenInputs );
		
		$this->html->td_header[] = array( "&nbsp;"  , "40%" );
		$this->html->td_header[] = array( "&nbsp;"  , "60%" );
		
		$html .= $this->html->startTable( $title );
		
		//-----------------------------------------
		// Form elements
		//-----------------------------------------
		
		if ( $type == 'create' && count( $subpageTypes ) > 1 )
		{
			$html .= $this->html->addTdRow(
				array(
					$this->lang->getString('subpages_'.$this->page['type'].'_form_type'),
					$this->html->formDropdown(
						'type',
						$subpageTypes
					)
				)
			);
		}
		
		if ( count( $languageArray ) > 1 )
		{
			$html .= $this->html->addTdRow(
				array(
					$this->lang->getString('subpages_'.$this->page['type'].'_form_lang_sel'),
					$this->html->formDropdown(
						'language_id', 
						$languageArray,
						$languageID
					)
				)
			);
		}
		

		if ( ($type == 'add' || $type == 'edit') && count( $compareArray ) > 1 )
		{
			$html .= $this->html->addTdRow(
				array(
					$this->lang->getString('subpages_'.$this->page['type'].'_form_lang_comp'),
					$this->html->formDropdown(
						'compare_id', 
						$compareArray,
						$compareID
					)
				)
			);
		}
		
		//-----------------------------------------
		// End table and form
		//-----------------------------------------
		
		$html .= $this->html->endForm( $button );
		
		$this->display->addContent( $html );
	}
}

?>