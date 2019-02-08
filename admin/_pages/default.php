<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 *
 * Admin application wrapper
 * Last Updated: $Date: 2010-06-12 17:39:13 -0500 (Sat, 12 Jun 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Admin
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 14 $
 */

class AdminPages extends Command
{
	/**
	 * The page controller library
	 *
	 * @access protected
	 * @var PageController
	 * @since 1.0.0
	 */
	protected $controller;
	/**
	 * The admin app skin generator
	 *
	 * @access protected
	 * @var AdminSkin
	 * @since 1.0.0
	 */
	protected $html;
	/**
	 * The page cache
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $pages;

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

		// Get and load the table/form factory
		$this->html = $this->registry->getClass('AdminSkin');

		$this->controller = $this->registry->getClass('PageController');
		$this->pages = $this->controller->getCache();

		if ( isset( $this->input['parent_id'] ) && intval( $this->input['parent_id'] ) > 0 )
		{
			$this->parentID = intval( $this->input['parent_id'] );

			$this->DB->query("SELECT * FROM page WHERE page_id = '{$this->parentID}';");

			$this->par = $this->DB->fetchRow();

			if ( ! is_array( $this->par ) && ! isset( $this->par['page_id'] ) )
			{
				$this->error->raiseError( 'invalid_id', FALSE );
			}

			$this->par['languages'] = unserialize( $this->par['languages'] );

			$this->lang->loadFromDatabase( TRUE, $this->par['languages'] );

			$this->DB->query("SELECT * FROM metadata_page WHERE id = '{$this->parentID}';");

			$metadata = array();

			while ( $r = $this->DB->fetchRow() )
			{
				$metadata[ $r['meta_id'] ] = $r;
			}

			if ( count( $metadata ) > 0 )
			{
				$this->par['metadata'] = $this->controller->processMetadataByLanguage( $metadata );
			}
			else
			{
				$this->par['metadata'] = array();
			}
		}
		else
		{
			$this->parentID = 0;
			$this->par = array( 'page_id' => 0 );
			$this->lang->loadFromDatabase( TRUE );
		}

		// Load the language
		$this->lang->loadStrings('pages');

		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "pages" ), 'admin' ), $this->lang->getString('pages') );

		$this->controller->buildBreadcrumb( $this->parentID, array( 'module' => 'pages' ), 'admin' );

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
				$this->listPages();
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
	 * Makes sure the user actually wants to delete this page.
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

		$pageID      = intval($this->input['id']);
		$languageID  = intval($this->input['language_id']);
		$page        = array();

		$this->DB->query("SELECT p.*, m.meta_value AS name FROM page p
			LEFT JOIN metadata_page m ON m.language_id='{$languageID}' AND m.id=p.page_id AND m.meta_key='name'
			WHERE page_id = '{$pageID}';");

		$page = $this->DB->fetchRow();

		if ( ! $page['page_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listPages();
			return;
		}

		$page['languages'] = unserialize( $page['languages'] );

		if ( ! isset( $page['languages'][ $languageID ] ) )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listPages();
			return;
		}

		$formcode = 'dodelete';
		$title    = "{$this->lang->getString('pages_delete_title')} {$page['name']} ({$this->lang->getLanguageByID($languageID)->getName()})";
		$button   = $this->lang->getString('pages_delete_submit');

		//-----------------------------------------
		// Start the form
		//-----------------------------------------

		$this->display->setTitle( $title );

		$html = $this->html->startForm(
			array(
				's'           => $this->user->getSessionID(),
				'app'         => 'admin',
				'module'      => 'pages',
				'parent_id'   => $this->parentID,
				'id'          => $pageID,
				'do'          => $formcode,
				'language_id' => $languageID
			)
		);

		//-----------------------------------------
		// Main form
		//-----------------------------------------

		$html .= $this->html->startTable( $title, 'admin-form' );

		$html .= $this->html->startFieldset();

		$html .= $this->html->addTdBasic( "{$this->lang->getString('pages_delete_form_text')} {$page['name']} ({$this->lang->getLanguageByID($languageID)->getName()})?<br>{$this->lang->getString('pages_delete_form_text2')}", "center" );

		$html .= $this->html->endFieldset();

		$html .= $this->html->endForm( $button, "", " {$this->lang->getString('or')} <a href='".$this->display->buildURL( array( 'module' => 'pages', 'parent_id' => $this->parentID ), 'admin')."'>{$this->lang->getString('cancel')}</a>" );

		$this->display->addContent( $html );
	}

	/**
	 * Deletes the page
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

		$pageID      = intval($this->input['id']);
		$languageID  = intval($this->input['language_id']);
		$page        = array();
		$cache       = array();
		$pagesString = "";
		$others      = array();
		$subpages    = array();

		$this->cache->update('pages', TRUE);
		$cache = $this->cache->getCache('pages');

		if ( ! isset( $cache['all'][ $pageID ] ) )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listPages();
			return;
		}
		else
		{
			$others[ $pageID ] = $pageID;
		}

		if ( isset( $cache[ $pageID ] ) && count( $cache[ $pageID ] ) > 0 )
		{
			foreach( $cache[ $pageID ] as $k => $v )
			{
				$others = array_merge( $others, $this->findDelete( $cache, $k ) );
			}
		}

		$pagesString = implode( ",", $others );
		$others      = array();

		$this->DB->begin();

		$outer = $this->DB->query(
			"SELECT * FROM page WHERE page_id IN({$pagesString});"
		);

		while( $r = $this->DB->fetchRow( $outer ) )
		{
			$r['languages'] = unserialize( $r['languages'] );

			if ( isset( $r['languages'][ $languageID ] ) )
			{
				unset( $r['languages'][ $languageID ] );

				$others[ $r['page_id'] ] = $r['page_id'];

				if ( count( $r['languages'] ) > 0 )
				{
					$this->DB->query(
						"UPDATE page SET languages = '".serialize( $r['languages'] )."' WHERE page_id = '{$r['page_id']}';"
					);
				}
				else
				{
					$this->DB->query(
						"DELETE FROM page WHERE page_id = '{$r['page_id']}';"
					);

					$this->DB->query(
						"UPDATE page SET position = (position-1) WHERE parent_id = '{$r['parent_id']}' AND position > {$r['position']};"
					);
				}
			}
		}

		$pagesString = implode( ",", $others );
		$others      = array();

		$this->DB->query(
			"DELETE FROM metadata WHERE module = 'page' AND id IN({$pagesString}) AND language_id = '{$languageID}';"
		);

		$outer = $this->DB->query(
			"SELECT * FROM subpage WHERE page_id IN({$pagesString});"
		);

		while( $r = $this->DB->fetchRow( $outer ) )
		{
			$r['languages'] = unserialize( $r['languages'] );

			if ( isset( $r['languages'][ $languageID ] ) )
			{
				unset( $r['languages'][ $languageID ] );

				$others[ $r['subpage_id'] ] = $r['subpage_id'];

				if ( count( $r['languages'] ) > 0 )
				{
					$this->DB->query(
						"UPDATE subpage SET languages = '".serialize( $r['languages'] )."' WHERE subpage_id = '$pageID'{$r['subpage_id']}';"
					);
				}
				else
				{
					$this->DB->query(
						"DELETE FROM subpage WHERE subpage_id = '{$r['subpage_id']}';"
					);

					$this->DB->query(
						"UPDATE subpage SET position = (position-1) WHERE page_id = '{$r['page_id']}' AND position > {$r['position']};"
					);
				}
			}
		}

		$pagesString = implode( ",", $others );

		if ( count( $others ) > 0 )
		{
			$this->DB->query(
				"DELETE FROM metadata WHERE module = 'subpage' AND id IN({$pagesString}) AND language_id = '{$languageID}';"
			);
		}
		
		if ( $this->DB->checkForErrors() )
		{
			$this->DB->rollBack();
			$this->error->logError( 'pages_delete_fail', TRUE );
		}
		else
		{
			$this->DB->commit();
			$this->error->logError( 'pages_delete_success', TRUE );
		}

		//-----------------------------------------
		// Update statistics & projects
		//-----------------------------------------
		
		$this->cache->update('pages', TRUE);
		$this->pages = $this->cache->getCache('pages');

		$this->listPages();
	}

	/**
	 * RECURRSIVE: Returns an array of all children to a root page
	 * for a delete action.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function findDelete( $cache, $root )
	{
		$out = array();

		if ( isset( $cache['all'][ $root ] ) )
		{
			$out[ $root ] = $root;
		}

		if ( isset( $cache[ $root ] ) && count( $cache[ $root ] ) > 0 )
		{
			foreach( $cache[ $root ] as $k => $v )
			{
				$out = array_merge( $out, $this->findDelete( $cache, $k ) );
			}
		}

		return $out;
	}

	/**
	 * Lists out the pages in the system.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function listPages()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$html      = '';
		$languages = $this->lang->getLanguages();

		//-----------------------------------------
		// Do we need to do something else first?
		//-----------------------------------------

		if ( ! isset( $this->input['op'] ) )
		{
			$this->input['op'] = '';
		}

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
		// Page Information
		//-----------------------------------------

		$this->display->setTitle( $this->lang->getString('pages_list_title') );

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( $this->lang->getString('order')               , "12%" );
		$this->html->td_header[] = array( $this->lang->getString('pages_head_name')     , "40%" );
		$this->html->td_header[] = array( $this->lang->getString('languages')           , "48%" );

		//-----------------------------------------

		$html .= "<div style='float:right;'><a href='".$this->display->buildURL( array( 'module' => 'pages', 'do' => 'create', 'parent_id' => $this->parentID ), 'admin')."'>{$this->lang->getString('pages_create_new')}</a></div>";

		$html .= $this->html->startTable( $this->lang->getString('pages_list_table') );

		//-----------------------------------------
		// Get categories
		//-----------------------------------------

		if ( $this->parentID == 0 )
		{
			$this->parentID = 'root';
		}

		if ( isset( $this->pages[ $this->parentID ] ) && is_array( $this->pages[ $this->parentID ] ) && count( $this->pages[ $this->parentID ] ) > 0 )
		{
			$count_order = count( $this->pages[ $this->parentID ] );

			foreach( $this->pages[ $this->parentID ] as $r )
			{
				$langs = $r['languages'];

				$html_order  = $r['position'] > 1            ? $this->html->upButton(   $this->display->buildURL( array( 'module' => 'pages', 'op' => 'up',   'id' => $r['page_id'], 'parent_id' => $this->parentID ), 'admin' ) ) : $this->html->blankIMG();
				$html_order .= $r['position'] < $count_order ? $this->html->downButton( $this->display->buildURL( array( 'module' => 'pages', 'op' => 'down', 'id' => $r['page_id'], 'parent_id' => $this->parentID ), 'admin' ) ) : $this->html->blankIMG();

				$lang  = "";
				$count = 0;

				foreach( $languages as $v )
				{
					if ( in_array( $v->getID(), $langs ) )
					{
						$lang .= "<li>{$v->getName()} ";
						$lang .= "<a href='".$this->display->buildURL( array( 'module' => 'pages', 'parent_id' => $this->parentID, 'do' => 'edit', 'id' => $r['page_id'], 'language_id' => $v->getID() ), 'admin')."'>{$this->lang->getString('edit')}</a> ";
						$lang .= "<a href='".$this->display->buildURL( array( 'module' => 'pages', 'parent_id' => $this->parentID, 'do' => 'delete', 'id' => $r['page_id'], 'language_id' => $v->getID() ), 'admin')."'>{$this->lang->getString('delete')}</a></li>";
						$count ++;
					}
				}

				if ( $count < count( $languages ) )
				{
					$lang .= "<li><a href='".$this->display->buildURL( array( 'module' => 'pages', 'parent_id' => $this->parentID, 'do' => 'add', 'id' => $r['page_id'] ), 'admin')."'>{$this->lang->getString('pages_add_trans')}</a></li>";
				}

				if ( isset( $r['metadata'][ $this->lang->getLanguageID() ] ) && is_array( $r['metadata'][ $this->lang->getLanguageID() ] ) )
				{
					$r['name'] = $r['metadata'][ $this->lang->getLanguageID() ]['name']['value'];
				}
				else
				{
					foreach( $r['languages'] as $k => $v )
					{
						if ( is_array( $r['metadata'][ $v ] ) )
						{
							$r['name'] = $r['metadata'][ $v ]['name']['value'];
							break;
						}
					}
				}

				$extra = '';

				if ( file_exists( SWS_CLASSES_PATH . "pages/{$r['type']}.page.php" ) )
				{
					include( SWS_CLASSES_PATH . "pages/{$r['type']}.page.php" );
				}

				$html .= $this->html->addTdRow(
					array(
						"<center>&nbsp;&nbsp;&nbsp;{$html_order}</center>",
						"<a href='".$this->display->buildURL( array( 'module' => 'pages', 'parent_id' => $r['page_id'] ), 'admin')."'>{$r['name']}</a> ({$this->lang->getString($r['type'])}){$extra}",
						"<ul>{$lang}</ul>"
					)
				);
			}
		}

		//-----------------------------------------
		// End the table and print
		//-----------------------------------------

		$html .= $this->html->endTable();

		$this->display->addContent( $html );
	}

	/**
	 * Swaps the position/order of two pages based on the direction
	 *
	 * @param string $direction up|down
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function move( $direction )
	{
		$pageID       = intval($this->input['id']);
		$page         = array();
		$langageOther = array();

		//-----------------------------------------
		// Check (please?)
		//-----------------------------------------

		$this->DB->query(
			"SELECT page_id, position
				FROM page WHERE page_id = '{$pageID}';"
		);

		$page = $this->DB->fetchRow();

		if ( ! $page['page_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			return;
		}

		$prev_order = ( $direction == "up" ? $page['position'] - 1 : $page['position'] + 1 );

		$this->DB->query(
			"SELECT page_id, position
				FROM page WHERE position = '{$prev_order}' AND parent_id = '{$this->parentID}';"
		);

		$pageOther = $this->DB->fetchRow();

		if ( ! $pageOther['page_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			return;
		}

		$this->DB->query(
			"UPDATE page
				SET position = '{$pageOther['position']}'
				WHERE page_id = '{$page['page_id']}';"
		);

		$this->DB->query(
			"UPDATE page
				SET position = '{$page['position']}'
				WHERE page_id = '{$pageOther['page_id']}';"
		);

		$this->cache->update( 'pages', TRUE );
		$this->pages = $this->controller->getCache();
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

		$pageID          = intval( $this->input['id'] );
		$languageID      = intval( $this->input['language_id'] );
		$pageType        = $this->registry->txtStripslashes( trim( $this->input['type'] ) );
		$page            = array();

		//--------------------------------------------
		// Checks...
		//--------------------------------------------

		if ( $this->controller->getType( $pageType )->adminDoSaveChecks($pageType) )
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
			$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "pages", 'parent_id' => $this->parentID, 'do' => 'add' ), 'admin' ), $page['name'] );

			$this->DB->query(
				"SELECT IF( MAX(position), MAX(position) + 1, 1 ) as new_position FROM page WHERE parent_id = '{$this->parentID}';"
			);

			$position = $this->DB->fetchRow();
			$languages = serialize( array( $languageID => $languageID ) );

			$array = array( 'type' => $pageType, 'languages' => $languages, 'position' => $position['new_position'], 'parent_id' => $this->parentID, 'last_update' => date('Y-m-d H:i:s') );

			if ( Page::create( $array ) )
			{
				$id = $this->DB->getInsertID();

				if( $this->controller->getType( $pageType )->adminSave( $type, $id, $languageID ) )
				{
					$this->error->logError( 'pages_lang_created', FALSE );
				}
				else
				{
					$this->error->logError( 'pages_lang_not_created', TRUE );
					$this->listPages();
					return;
				}
			}
			else
			{
				$this->error->logError( 'pages_lang_not_created', TRUE );
				$this->listPages();
				return;
			}
		}
		else
		{
			$this->DB->query(
				"SELECT page_id, languages FROM page WHERE page_id = '{$pageID}'"
			);

			$langs = $this->DB->fetchRow();

			$languages = unserialize( $langs['languages'] );

			if ( ! in_array( $languageID, $languages ) )
			{
				$languages[$languageID] = $languageID;

				$this->DB->query(
					"UPDATE page SET languages = '".serialize($languages)."' WHERE page_id = '{$pageID}';"
				);
			}

			$this->DB->query("UPDATE page SET last_update = '".date('Y-m-d H:i:s')."' WHERE page_id = '{$pageID}';");

			$this->DB->query(
				"SELECT * FROM metadata_page WHERE id = '{$pageID}' AND language_id = '{$languageID}';"
			);

			$meta = array();

			while( $r = $this->DB->fetchRow() )
			{
				$meta[ $r['meta_id'] ] = $r;
			}

			if ( $this->controller->getType( $pageType )->adminSave( $type, $pageID, $languageID, $meta ) )
			{
				$this->error->logError( 'pages_lang_updated', FALSE );
			}
			else
			{
				$this->error->logError( 'pages_lang_not_updated', TRUE );
				$this->listPages();
				return;
			}
		}

		$this->cache->update( 'pages', TRUE );
		$this->pages = $this->controller->getCache();
		
		$this->controller->loadPages();
		
		$this->listPages();
	}

	/**
	 * This thing show the form to add/edit an account.
	 *
	 * @param string $type add|edit
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function showForm( $type, $pageID, $languageID, $compareID, $pageType, $page )
	{
		//-----------------------------------------
		// Check (please?)
		//-----------------------------------------

		if ( $type == 'create' )
		{
			$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => 'pages', 'parent_id' => $this->parentID, 'do' => 'create', 'language_id' => $languageID, 'type' => $pageType), 'admin'), $this->lang->getString('pages_create_new') );

			$formcode = 'create_save';
			$title    = "{$this->lang->getString('pages_create_new')} ({$this->lang->getLanguageByID($languageID)->getName()})";
			$button   = $this->lang->getString('pages_create_new');
		}
		else if ( $type == 'add' )
		{
			$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => 'pages', 'parent_id' => $this->parentID, 'do' => 'add', 'id' => $pageID, 'language_id' => $languageID, 'compare_id' => $compareID), 'admin'), $page['name']);

			$formcode = 'add_save';
			$title    = "{$this->lang->getString('pages_add_title')} {$page['name']} ({$this->lang->getLanguageByID($languageID)->getName()})";
			$button   = $this->lang->getString('pages_add_submit');
		}
		else
		{
			$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => 'pages', 'parent_id' => $this->parentID, 'do' => 'edit', 'id' => $pageID, 'language_id' => $languageID, 'compare_id' => $compareID ), 'admin'), $page['name']);

			$formcode = 'edit_save';
			$title    = "{$this->lang->getString('pages_edit_title')} {$page['name']} ({$this->lang->getLanguageByID($languageID)->getName()})";
			$button   = $this->lang->getString('pages_edit_submit');
		}

		//-----------------------------------------
		// Start the form
		//-----------------------------------------

		$this->display->setTitle( $title );

		$html = $this->html->startForm(
			array(
				's'           => $this->user->getSessionID(),
				'app'         => 'admin',
				'module'      => 'pages',
				'parent_id'   => $this->parentID,
				'id'          => $pageID,
				'do'          => $formcode,
				'language_id' => $languageID,
				'type'        => $pageType,
				'compare_id'  => $compareID
			)
		);

		$html .= $this->controller->getType( $pageType )->adminForm( $type, $this->html, $page, $languageID, $compareID, $button, $title );

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

		$pageID        = intval($this->input['id']);
		$languageID    = intval($this->input['language_id']);
		$compareID     = intval($this->input['compare_id']);
		$pageType      = $this->registry->txtStripslashes( trim( $this->input['type'] ) );
		$page          = array();
		$languageArray = array();
		$compareArray  = array( 0 => array( 0, $this->lang->getString('none') ) );
		$pageTypes     = $this->controller->getDropdownArray();

		//-----------------------------------------
		// Check (please?)
		//-----------------------------------------

		if ( $type == 'create' )
		{
			$languageArray = $this->lang->getDropdownArray();

			$formcode = 'create';
			$title    = $this->lang->getString('pages_step_2');
			$button   = $this->lang->getString('pages_create_new');
		}
		else
		{
			$this->DB->query("SELECT * FROM page WHERE page_id = '{$pageID}';");

			$page = $this->DB->fetchRow();

			if ( ! $page['page_id'] )
			{
				$this->error->logError( 'invalid_id', FALSE );
				$this->listPages();
				return;
			}

			$pageType = $page['type'];

			$this->DB->query("SELECT * FROM metadata_page WHERE id = '{$pageID}';");

			$metadata = array();

			while( $r = $this->DB->fetchRow() )
			{
				$metadata[ $r['meta_id'] ] = $r;
			}

			if ( is_array( $metadata ) && count( $metadata ) > 0 )
			{
				$page['metadata'] = $this->controller->processMetadataByLanguage( $metadata );
			}

			$page['languages'] = unserialize( $page['languages'] );

			foreach( $this->lang->getDropdownArray() as $k => $v )
			{
				if ( ! in_array( $k, $page['languages'] ) || ( $type == 'edit' && $languageID == $k ) )
				{
					$languageArray[ $k ] = $v;
				}
				else
				{
					$compareArray[ $k ] = $v;
				}
			}

			if ( is_array( $page['metadata'] ) )
			{
				if ( is_array( $page['metadata'][ $this->lang->getLanguageID() ] ) )
				{
					$page['name'] = $page['metadata'][ $this->lang->getLanguageID() ]['name']['value'];
				}
				else if ( is_array( $page['metadata'][ $languageID ] ) )
				{
					$page['name'] = $page['metadata'][ $languageID ]['name']['value'];
				}
				else
				{
					foreach( $page['languages'] as $k => $v )
					{
						if ( is_array( $page['metadata'][ $v ] ) )
						{
							$page['name'] = $page['metadata'][ $v ]['name']['value'];
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
				$title    = "{$this->lang->getString('pages_add_title')} {$page['name']}";
				$button   = $this->lang->getString('pages_step_2');
			}
			else
			{
				$formcode = 'edit';
				$title    = "{$this->lang->getString('pages_edit_title')} {$page['name']} ({$this->lang->getLanguageByID($languageID)->getName()})";
				$button   = $this->lang->getString('pages_step_2');
			}
		}

		$hiddenInputs = array(
			's'           => $this->user->getSessionID(),
			'app'         => 'admin',
			'module'      => 'pages',
			'parent_id'   => $this->parentID,
			'id'          => $pageID,
			'do'          => $formcode
		);

		if ( $type == 'create' && count( $pageTypes ) <= 1 )
		{
			foreach( $pageTypes as $v )
			{
				$hiddenInputs['type'] = $v[0];
				$pageType = $v[0];
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

		if ( ( ! ( $type == 'create' && ( count( $pageTypes ) > 1 && ! ( strlen( $pageType ) > 0 ) ) ) &&
		     ( ! ( count( $languageArray ) > 1 ) || $languageID > 0 ) &&
		     ( ! ( ($type == 'add' || $type == 'edit') && count( $compareArray ) > 1 ) || isset( $this->input['compare_id'] ) ) ) )
		{
			if ( ! is_object( $this->lang->getLanguageByID( $languageID ) ) )
			{
				$this->error->logError( 'invalid_id', FALSE );
				$this->listPages();
				return;
			}

			$this->showForm( $type, $pageID, $languageID, $compareID, $pageType, $page );
			return;
		}
		else
		{
			if ( $type == 'create' )
			{
				$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => 'pages', 'parent_id' => $this->parentID, 'do' => 'create' ), 'admin'), $this->lang->getString('pages_create_new') );
			}
			else if( $type == 'add')
			{
				$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => 'pages', 'parent_id' => $this->parentID, 'do' => 'add', 'id' => $pageID, 'language_id' => $languageID), 'admin'), $page['name'] );
			}
			else
			{
				$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => 'pages', 'parent_id' => $this->parentID, 'do' => 'edit', 'id' => $pageID, 'language_id' => $languageID), 'admin' ), $page['name'] );
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

		if ( $type == 'create' && count( $pageTypes ) > 1 )
		{
			$html .= $this->html->addTdRow(
				array(
					$this->lang->getString('pages_form_type'),
					$this->html->formDropdown(
						'type',
						$pageTypes
					)
				)
			);
		}

		if ( count( $languageArray ) > 1 )
		{
			$html .=$this->html->addTdRow(
				array(
					$this->lang->getString('pages_form_lang_sel'),
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
					$this->lang->getString('pages_form_lang_comp'),
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