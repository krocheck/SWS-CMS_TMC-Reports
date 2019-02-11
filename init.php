<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Defines most of the system constants
 * Last Updated: $Date: 2010-07-02 09:29:16 -0500 (Fri, 02 Jul 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 31 $
 */

define( 'SWS_ROOT_PATH',    str_replace( "\\", "/", dirname( __FILE__ ) ) . '/' );
define( 'SWS_CORE_PATH',    SWS_ROOT_PATH . 'core/' );
define( 'SWS_CLASSES_PATH', SWS_ROOT_PATH . 'classes/' );
define( 'SWS_IMG_PATH',     SWS_ROOT_PATH . 'images/' );
define( 'SWS_JS_PATH',      SWS_ROOT_PATH . 'js/' );
define( 'SWS_LANG_PATH',    SWS_ROOT_PATH . 'lang/' );
define( 'SWS_SKIN_PATH',    SWS_ROOT_PATH . 'templates/' );
define( 'SWS_STYLE_PATH',   SWS_ROOT_PATH . 'styles/' );
define( 'SWS_VENDOR_PATH',  SWS_ROOT_PATH . 'vendor/' );

?>