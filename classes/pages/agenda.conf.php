<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Page type configuration file
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

/**
 * $TYPE_KEY is the string that defines the page type
 * as well as the name of the module php file
 */ 
$TYPE_KEY     = 'agenda';

/**
 * $TYPE_CLASSES is an array that contains both processing
 * module class names for the module.
 * array (
 *     0 => 'PUBLIC CLASSNAME'
 *     1 => 'ADMIN CLASSNAME'
 * )
 */
$TYPE_CLASSES = array( 'Agenda', 'AgendaType' );

?>