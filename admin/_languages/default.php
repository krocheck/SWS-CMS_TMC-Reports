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

class AdminLanguages extends Command
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
	 * The main execute function
	 *
	 * @param object $params extra stuff from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doExecute( $params )
	{
		// Check the user's credentials
		$this->checkPermission();

		// Load the language
		$this->lang->loadStrings('languages');

		// Get and load the table/form factory
		$this->html = $this->registry->getClass('AdminSkin');

		// Add a breadcrumb for this module
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "languages" ), 'admin' ), $this->lang->getString('languages') );

		if ( ! isset( $this->input['do'] ) )
		{
			$this->input['do'] = '';
		}

		// What are we doing?
		switch( $this->input['do'] )
		{
			case 'add':
				$this->showForm( 'add' );
				break;
			case 'edit':
				$this->showForm( 'edit' );
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
			default:
				$this->listLanguages();
				break;
		}

		// Send the final output
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
		if ( $this->user->getPermission() != 'superadmin' )
		{ 
			$this->error->raiseError( 'no_permission', FALSE );
		}
	}

	/**
	 * Makes sure the user actually wants to delete this language.
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

		$languageID   = intval($this->input['id']);
		$language     = array();

		// Get the language from the database
		$this->DB->query("SELECT * FROM language WHERE language_id = '{$languageID}';");

		$language = $this->DB->fetchRow();

		// Throw an error if the language does not exist...
		if ( ! $language['language_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listLanguages();
			return;
		}
		// or if the user if try to delete the default language
		else if ( $this->settings['default_language_id'] == $language['language_id'] )
		{
			$this->error->logError( 'languages_cannot_delete', FALSE );
			$this->listLanguages();
			return;
		}

		// Form setup
		$formcode = 'dodelete';
		$title    = "{$this->lang->getString('languages_delete_title')} {$language['display_name']} ({$language['name']})";
		$button   = $this->lang->getString('languages_delete_submit');

		//-----------------------------------------
		// Start the form
		//-----------------------------------------

		// Page title
		$this->display->setTitle( $title );

		// Add breadcrumb for this action
		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "languages", 'do' => 'delete', 'id' => $languageID ), 'admin' ), $this->lang->getString('languages_delete_bread') );

		// Setup the hidden inputs for the form
		$html = $this->html->startForm(
			array(
				's'       => $this->user->getSessionID(),
				'app'     => 'admin',
				'module'  => 'languages',
				'id'      => $languageID,
				'do'      => $formcode
			)
		);

		//-----------------------------------------
		// Create the table for the form
		//-----------------------------------------

		$html .= $this->html->startTable( $title, 'admin-form' );

		$html .= $this->html->startFieldset();

		$html .= $this->html->addTdBasic( "{$this->lang->getString('languages_delete_form_text')} {$language['name']}?<br>{$this->lang->getString('languages_delete_form_text2')}", "center" );

		$html .= $this->html->endFieldset();

		$html .= $this->html->endForm( $button, "", " {$this->lang->getString('or')} <a href='".$this->display->buildURL( array( 'module' => 'languages' ), 'admin')."'>{$this->lang->getString('cancel')}</a>" );

		//--------------------------------------

		// Send the html to the display handler
		$this->display->addContent( $html );
	}

	/**
	 * Deletes the language
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

		$languageID   = intval($this->input['id']);
		$language     = array();
		$count        = 0;

		// Get the language from the databaes
		$this->DB->query("SELECT * FROM language WHERE language_id = '{$languageID}';");

		$language = $this->DB->fetchRow();

		// Throw and error if the language does not exist or ...
		if ( ! $language['language_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
		}
		// if the user is trying to delete the default language or ...
		else if ( $this->settings['default_language_id'] == $language['language_id'] )
		{
			$this->error->logError( 'languages_cannot_delete', FALSE );
		}
		// Everything's cool, so delete the thing
		else
		{
			// Start a transaction so everything can be undone if there's a problem
			$this->DB->begin();

			// Get all the pages
			$outer = $this->DB->query("SELECT * FROM page;");

			// Loop through the pages
			while( $r = $this->DB->fetchRow( $outer ) )
			{
				// Make the page's languages into an array
				$r['languages'] = unserialize( $r['languages'] );

				// If this language is set then there's work to do
				if ( isset( $r['languages'][ $languageID ] ) )
				{
					// Unset this language
					unset( $r['languages'][ $languageID ] );

					// If the page still has other languages
					// save the updated language array back or ...
					if ( count( $r['languages'] ) > 0 )
					{
						$this->DB->query("UPDATE page SET languages = '".serialize( $r['languages'] )."' WHERE page_id = '{$r['page_id']}';");
					}
					// This was the page's only language, so it must be deleted
					else
					{
						$this->DB->query("DELETE FROM page WHERE page_id = '{$r['page_id']}';");

						// Reset the page order now that this page is gone
						$this->DB->query("UPDATE page SET position = (position-1) WHERE parent_id = '{$r['parent_id']}' AND position > {$r['position']};");
					}
				}
			}

			// Get all the subpages
			$outer = $this->DB->query("SELECT * FROM subpage;");

			// Loop through the subpages
			while( $r = $this->DB->fetchRow( $outer ) )
			{
				// Make the subpages' languages in an array
				$r['languages'] = unserialize( $r['languages'] );

				// If this language is set then there's work to do
				if ( isset( $r['languages'][ $languageID ] ) )
				{
					// Unset the language
					unset( $r['languages'][ $languageID ] );

					// If the subpage still has other languages
					// save the updated language array back or ...
					if ( count( $r['languages'] ) > 0 )
					{
						$this->DB->query("UPDATE subpage SET languages = '".serialize( $r['languages'] )."' WHERE subpage_id = '$pageID'{$r['subpage_id']}';");
					}
					// This was the subpage's only language, so it must be deleted
					else
					{
						$this->DB->query("DELETE FROM subpage WHERE subpage_id = '{$r['subpage_id']}';");

						// Reset the subpage order now that this page is gone
						$this->DB->query("UPDATE subpage SET position = (position-1) WHERE page_id = '{$r['page_id']}' AND position > {$r['position']};");
					}
				}
			}

			// Delete the language and any metadata
			Language::delete( $language['language_id'] );

			// If there are errors roll back the transaction or ...
			if ( $this->DB->checkForErrors() )
			{
				$this->DB->rollBack();
				$this->error->logError( 'languages_delete_fail', TRUE );
			}
			// Everything went OK so commit the transaction
			else
			{
				$this->DB->commit();
				$this->error->logError( 'languages_delete_success', TRUE );
			}

			// Update our caches
			$this->cache->update( 'languages' );
			$this->cache->update( 'pages' );
		}

		//-----------------------------------------

		// Done, go back to the language list
		$this->listLanguages();
	}

	/**
	 * Lists out the languages in the system.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function listLanguages()
	{
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
			case 'savedefault':
				$this->updateDefault();
				break;
			default:
		}

		//-----------------------------------------

		// Page title
		$this->display->setTitle( $this->lang->getString('languages_list_title') );

		// Setup the hidden inputs for the form
		$html = $this->html->startForm(
			array(
				's'       => $this->user->getSessionID(),
				'app'     => 'admin',
				'module'  => 'languages',
				'op'      => 'savedefault'
			)
		);

		//-----------------------------------------
		// Table Headers
		//-----------------------------------------

		$this->html->td_header[] = array( $this->lang->getString('order')               , "12%" );
		$this->html->td_header[] = array( $this->lang->getString('languages_head_name') , "70%" );
		$this->html->td_header[] = array( $this->lang->getString('default')             , "6%" );
		$this->html->td_header[] = array( $this->lang->getString('edit')                , "6%" );
		$this->html->td_header[] = array( $this->lang->getString('delete')              , "6%" );

		//-----------------------------------------

		// Add language link
		$html .= "<div style='float:right;'><a href='".$this->display->buildURL( array( 'module' => 'languages', 'do' => 'add' ), 'admin')."'>{$this->lang->getString('languages_create_new')}</a></div>";

		// Begin table
		$html .= $this->html->startTable( $this->lang->getString('languages_list_table') );

		//-----------------------------------------

		// Get the languages
		$this->DB->query("SELECT * FROM language ORDER BY position;");

		// Total languages used for the order buttons
		$count_order = $this->DB->getTotalRows();

		// Loop through the languages
		while( $r = $this->DB->fetchRow() )
		{
			// Get the order buttons
			$html_order  = $r['position'] > 1            ? $this->html->upButton(   "languages", $r['language_id'] ) : $this->html->blankIMG();
			$html_order .= $r['position'] < $count_order ? $this->html->downButton( "languages", $r['language_id'] ) : $this->html->blankIMG();

			// Check the default language
			$checked = ($this->settings['default_language_id'] == $r['language_id'] ? "checked='checked'" : '');

			// Add the row
			$html .= $this->html->addTdRow(
				array(
					"<center>&nbsp;&nbsp;&nbsp;{$html_order}</center>",
					"{$r['name']} ({$r['code']})",
					"<center><input type='radio' name='DEFAULT' {$checked} value='{$r['language_id']}'></center>",
					"<center><a href='".$this->display->buildURL( array( 'module' => 'languages', 'do' => 'edit',   'id' => $r['language_id'] ), 'admin')."'>{$this->lang->getString('edit')}</a></center>",
					"<center><a href='".$this->display->buildURL( array( 'module' => 'languages', 'do' => 'delete', 'id' => $r['language_id'] ), 'admin')."'>{$this->lang->getString('delete')}</a></center>",
				)
			);
		}

		// Close the table with the submit button
		$html .= $this->html->endForm( $this->lang->getString('languages_default_save') );

		//--------------------------------------

		// Send the html to the display handler
		$this->display->addContent( $html );
	}

	/**
	 * Swaps the position/order of two languages based on the direction
	 *
	 * @param string $direction up|down
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function move( $direction )
	{
		//-----------------------------------------
		// Init Vars
		//-----------------------------------------

		$languageID   = intval($this->input['id']);
		$language     = array();
		$langageOther = array();

		// Get the language
		$this->DB->query("SELECT language_id, position FROM language WHERE language_id = '{$languageID}';");

		$language = $this->DB->fetchRow();

		// Log error if language was not found
		if ( ! $language['language_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			return;
		}

		// Setup the position to find the adjacent
		$prev_order = ( $direction == "up" ? $language['position'] - 1 : $language['position'] + 1 );

		// Get the adjacent language
		$this->DB->query("SELECT language_id, position FROM language WHERE position = '{$prev_order}';");

		$languageOther = $this->DB->fetchRow();

		// Log error if the other language was not found
		if ( ! $languageOther['language_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			return;
		}

		// Move the language to the new location
		$this->DB->query(
			"UPDATE language
				SET position = '{$languageOther['position']}'
				WHERE language_id = '{$language['language_id']}';"
		);

		// Move the adjacent language the opposite direction
		$this->DB->query(
			"UPDATE language
				SET position = '{$language['position']}'
				WHERE language_id = '{$languageOther['language_id']}';"
		);

		// Update the cache
		$this->cache->update('languages');
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
		// Init Vars ... process inputs
		//--------------------------------------------

		$languageID         = intval( $this->input['id'] );
		$name               = $this->registry->txtStripslashes( trim( $this->input['name'] ) );
		$displayName        = $this->registry->txtStripslashes( trim( $this->input['display_name'] ) );
		$code               = strtolower($this->registry->txtStripslashes( trim( $this->input['code'] ) ) );
		$active             = intval( $this->input['active'] );

		// Check for a valid id if edit
		if ( $type == 'edit' && ! $languageID > 0 )
		{
			$this->error->logError( 'invalid_id', FALSE );
			$this->listLanguages();
			return;
		}

		// Make sure the text inputs have something
		if ( ! ( strlen( $name ) > 0 && strlen( $displayName ) > 0 && strlen( $code ) > 0 ) )
		{
			$this->error->logError( 'incomplete_form', FALSE );
			$this->showForm( $type );
			return;
		}

		// Save array
		$array = array(
			'name'          => $name,
			'display_name'  => $displayName,
			'code'          => $code,
			'active'        => $active
		);

		// Folder paths
		$imageFolder = SWS_IMG_PATH. $array['code'];

		// If this is a new language ...
		if ( $type == 'add' )
		{
			// Find a duplicate entry
			$this->DB->query(
				"SELECT language_id FROM language
					WHERE name = '{$name}' AND display_name = '{$displayName}' AND code = '{$code}';"
			);

			$lang = $this->DB->fetchRow();

			// Get the next position
			$this->DB->query("SELECT MAX(position) + 1 AS new_position FROM language;");

			$position = $this->DB->fetchRow();

			// Add the position to the save array
			$array['position'] = $position['new_position'];

			// If the duplicate entry exists show an error
			if ( is_array( $lang ) && isset( $lang['language_id'] ) && $lang['language_id'] > 0 )
			{
				$this->error->logError( 'languages_lang_exists', FALSE );
				$this->listLanguages();
				return;
			}
			// Try to create the language
			else if ( Language::create( $array ) )
			{
				$this->error->logError( 'languages_lang_created', FALSE );

				// If the images directory doesn't exist
				if ( ! is_dir ( $imageFolder ) )
				{
					// Check that we can write to the images folder
					if ( ! is_writable( SWS_IMG_PATH ) )
					{
						$this->error->logError( 'image_permission_not_created', TRUE );
					}
					// Create the new images folder
					else
					{
						mkdir( $imageFolder );
					}
				}
			}
			// The language was not created
			else
			{
				$this->error->logError( 'languages_lang_not_created', TRUE );
				$this->listLanguages();
				return;
			}
		}
		// Or we are editing
		else
		{
			// Add the language id to the save array
			$array['language_id'] = $languageID;

			// Try to update the language
			if ( Language::update( $array ) )
			{
				$this->error->logError( 'languages_lang_updated', FALSE );

				// Check that the images folders are writable
				if ( ! is_writable( SWS_MAG_PATH ) )
				{
					$this->error->logError( 'folder_permission_not_updated', TRUE );
				}
				// Update the image folder names with the new codes
				else
				{
					$imageFolderOld = SWS_IMG_PATH. $this->lang->getLanguageByID($languageID)->getCode();

					rename( $imageFolderOld, $imageFolder);
				}
			}
			// The language was not updated
			else
			{
				$this->error->logError( 'languages_lang_not_updated', TRUE );
				$this->listLanguages();
				return;
			}
		}

		// Update our caches
		$this->cache->update( 'languages' );

		//-----------------------------------------

		// Done, go back to the language list
		$this->listLanguages();
	}

	/**
	 * This thing show the form to add/edit an account.
	 *
	 * @param string $type add|edit
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function showForm( $type='add' )
	{
		//-----------------------------------------
		// Init Vars
		//-----------------------------------------

		$languageID  = intval($this->input['id']);
		$language    = array();

		//-----------------------------------------
		// Check (please?)
		//-----------------------------------------

		if ( $type == 'add' )
		{
			// Form setup
			$formcode = 'add_save';
			$title    = $this->lang->getString('languages_create_new');
			$button   = $this->lang->getString('languages_create_new');
		}
		else
		{
			$this->DB->query("SELECT * FROM language WHERE language_id = '{$languageID}';");

			$language = $this->DB->fetchRow();

			if ( ! $language['language_id'] )
			{
				$this->error->logError( 'invalid_id', FALSE );
				$this->listLanguages();
				return;
			}

			// Form setup
			$formcode = 'edit_save';
			$title    = "{$this->lang->getString('languages_edit_title')} {$language['display_name']} ({$language['name']})";
			$button   = $this->lang->getString('languages_edit_save');
		}

		//-----------------------------------------
		// Start the form
		//-----------------------------------------

		$this->display->setTitle( $title );

		$this->display->addBreadcrumb( $this->display->buildURL( array( 'module' => "languages", 'do' => $type, 'id' => $languageID ), 'admin' ), $this->lang->getString('languages_'.$type.'_bread') );

		$html = $this->html->startForm(
			array(
				's'       => $this->user->getSessionID(),
				'app'     => 'admin',
				'module'  => 'languages',
				'id'      => $languageID,
				'do'      => $formcode
			)
		);

		$this->html->td_header[] = array( "&nbsp;"  , "40%" );
		$this->html->td_header[] = array( "&nbsp;"  , "60%" );

		$html .= $this->html->startTable( $title, 'admin-form' );

		$html .= $this->html->startFieldset( $this->lang->getString('languages_form_lang_info') );

		//-----------------------------------------
		// Form elements
		//-----------------------------------------

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('languages_form_name'),
				$this->html->formInput( 'name', $this->registry->txtStripslashes( $_POST['name'] ? $_POST['name'] : $language['name'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('languages_form_display_name'),
				$this->html->formInput( 'display_name', $this->registry->txtStripslashes( $_POST['display_name'] ? $_POST['display_name'] : $language['display_name'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('languages_form_code'),
				$this->html->formInput( 'code', $this->registry->txtStripslashes( $_POST['code'] ? $_POST['code'] : $language['code'] ) )
			)
		);

		$html .= $this->html->addTdRow(
			array(
				$this->lang->getString('languages_form_active'),
				$this->html->formYesNo( 'active', intval( $_POST['active'] ? $_POST['active'] : $language['active'] ) )
			)
		);

		//-----------------------------------------
		// End table and form
		//-----------------------------------------

		$html .= $this->html->endFieldset();

		$html .= $this->html->endForm( $button );

		$this->display->addContent( $html );
	}

	/**
	 * Changes the default system language.
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function updateDefault()
	{
		//-----------------------------------------
		// Init Vars
		//-----------------------------------------

		$languageID   = intval($this->input['DEFAULT']);
		$language     = array();
		$langageOther = array();

		// Get the language that's the new default
		$this->DB->query("SELECT language_id FROM language WHERE language_id = '{$languageID}';");

		$language = $this->DB->fetchRow();

		// Throw an error if the language does not exist
		if ( ! $language['language_id'] )
		{
			$this->error->logError( 'invalid_id', FALSE );
			return;
		}

		// Update the default in the active settings
		$this->settings['default_language_id'] = $language['language_id'];

		// Update the default in the database
		$this->DB->query(
			"UPDATE metadata
				SET meta_value = '{$language['language_id']}'
				WHERE module = 'setting' AND meta_key = 'default_language_id';"
		);

		// Update our caches
		$this->cache->update('settings');
	}
}

?>