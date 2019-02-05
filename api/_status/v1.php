<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * API Status Processor
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

class ApiStatusV1 extends ApiCommand
{
	/**
	 * Authentication requirements by request method
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	var $auth = array(
		'DELETE'	=> false,
		'GET'		=> false,
		'POST'		=> false,
		'PUT'		=> false
	);
	
	/**
	 * The main execute function
	 *
	 * @param object $params extra stuff from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doExecute( $params ) {}

	/**
	 * The delete execute function
	 *
	 * @param object $params extra stuff from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doDelete( $params ) {}

	/**
	 * The get execute function
	 *
	 * @param object $params extra stuff from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doGet( $params )
	{
		$this->display->addJSON('status','ok');
		$this->display->doJSON();
	}

	/**
	 * The post execute function
	 *
	 * @param object $params extra stuff from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doPost( $params ) {}

	/**
	 * The put execute function
	 *
	 * @param object $params extra stuff from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doPut( $params ) {}
}

?>