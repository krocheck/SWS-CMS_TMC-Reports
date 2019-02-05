<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Spooler Transaction Processor
 * Last Updated: $Date: 2010-06-28 21:31:06 -0500 (Mon, 28 Jun 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Admin
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 27 $
 */

class SpoolerItems extends Command
{
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
		if ( $this->input['type'] == 'update' )
		{
			$count = $this->registry->getAPI('toast')->updateMenuItems();
			$this->display->addJSON( 'status', 'complete' );
		}
		else if ( $this->input['type'] == 'refresh_all' )
		{
			if ( $this->registry->getAPI('toast')->refreshAllMenuItems() > 0 )
			{
				$this->display->addJSON( 'status', 'complete' );
			}
			else
			{
				$this->display->addJSON( 'status', 'error' );
			}
		}
		else if ( $this->input['type'] == 'active' )
		{
			if ( $this->registry->getAPI('toast')->updateActiveMenuItems() > 0 )
			{
				$this->display->addJSON( 'status', 'complete' );
			}
			else
			{
				$this->display->addJSON( 'status', 'error' );
			}
		}
		else
		{
			$this->display->addJSON( 'status', 'error' );
		}

		$this->display->doJSON();
	}
}

?>