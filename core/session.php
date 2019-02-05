<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Session class for the user
 * Last Updated: $Date: 2010-06-29 14:04:05 -0500 (Tue, 29 Jun 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Core
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 28 $
 */

class Session
{
	/**
	 * The application registry library
	 *
	 * @access protected
	 * @var Registry
	 * @since 1.0.0
	 */
	protected $registry;
	/**
	 * The session id
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $id;
	/**
	 * The user's logged IP Address
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $ipAddress;
	/**
	 * The user's cookie preference for this session
	 *
	 * @access protected
	 * @var bool
	 * @since 1.0.0
	 */
	protected $remember;
	/**
	 * The session's registered application
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $application;

	/**
	 * Constructor that loads the registry
	 *
	 * @param Registry $registry the main program registry
	 * @param array $dbRow array of the session values
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct( Registry $registry, $dbRow )
	{
		$this->registry = $registry;
		
		$this->id             = $dbRow['session_id'];
		$this->ipAddress      = $_SERVER['REMOTE_ADDR'];
		$this->application    = $dbRow['application'];
		$this->remember       = $dbRow['remember'];
		
		$this->registry->getDB()->query(
			"UPDATE session SET last_activity = NOW() WHERE session_id = '{$this->id}';"
		);
	}

	/**
	 * Static function that creates a session post login
	 *
	 * @param int $id the user ID
	 * @param string $email the user login
	 * @return Session the user's session
	 * @static
	 * @access public
	 * @since 1.0.0
	 */
	public static function create( $id, $email, $remember )
	{
		$curDate = date('Y-m-d H:i:s');
		
		$sessionID = md5( md5($email) . md5($curDate) . SWS_THIS_APPLICATION );

		Registry::$instance->getDB()->query(
			"INSERT INTO session (session_id, user_id, session_start, last_activity, ip_address, remember, application)
				VALUES ('{$sessionID}',{$id},'{$curDate}','{$curDate}','{$_SERVER['REMOTE_ADDR']}', '{$remember}', '".SWS_THIS_APPLICATION."');"
		);
		
		Registry::$instance->getDisplay()->addDebug( "Session Created: {$sessionID}" );
		
		$session = array( 'session_id' => $sessionID, 'user_id' => $id, 'session_start' => $curDate, 'last_activity' => $curDate, 'remember' => $remember, 'application' => SWS_THIS_APPLICATION );
		
		return new Session( Registry::$instance, $session );
	}

	/**
	 * Fetch the session id
	 *
	 * @return int the id
	 * @access public
	 * @since 1.0.0
	 */
	public function getID()
	{
		return $this->id;
	}

	/**
	 * Fetch the remember setting
	 *
	 * @return bool the remember settings
	 * @access public
	 * @since 1.0.0
	 */
	public function getRemember()
	{
		return $this->remember;
	}
}

?>