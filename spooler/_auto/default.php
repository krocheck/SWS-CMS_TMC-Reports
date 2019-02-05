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
		$this->registry->getAPI('toast')->updateCategories();
		$this->registry->getAPI('toast')->updateDiscounts();
		$this->registry->getAPI('toast')->updateMenuItems();
		$this->registry->getAPI('toast')->updateActiveMenuItems();
		$this->registry->getAPI('toast')->updateOrders();
		$this->registry->getAPI('toast')->processRewards();

		$this->display->addJSON( 'status', 'complete' );
		$this->display->doJSON();
	}
}

?>