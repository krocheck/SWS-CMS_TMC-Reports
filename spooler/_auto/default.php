<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Spooler Auto Exec Processor
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

class SpoolerAuto extends Command
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
		$this->registry->getAPI('asana')->updateWorkspaces();
		$this->registry->getAPI('asana')->updateTeams();
		$this->registry->getAPI('asana')->updateUsers();
		$this->registry->getAPI('asana')->updateTags();
		$this->registry->getAPI('asana')->updateFields();
		$this->registry->getAPI('asana')->updateProjects();

		$this->display->addJSON( 'status', 'complete' );
		$this->display->doJSON();
	}
}

?>