<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Main launcher that grabs the controller - ADMIN
 * Last Updated: $Date: 2010-04-28 13:42:06 -0500 (Wed, 28 Apr 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 2 $
 */

// Needed to setup the paths
require_once( '../init.php' );
// Sets up what this application is called
// MUST BE THE SAME NAME AS THE DIRECTORY
define( SWS_THIS_APPLICATION, 'admin' );
// Overrides the user's language preference, given the
// admin panel is only in English at the moment.
define( SWS_THIS_LANGUAGE, 'en' );

// Get the main controller and launch the application
require_once( SWS_CORE_PATH . 'controller.php' );
Controller::execute();

?>