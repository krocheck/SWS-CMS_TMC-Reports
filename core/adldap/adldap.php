<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * adldap API Library
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

class AdldapAPI extends Command
{
	protected $ad;
	protected $provider;
	protected $user;

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
	 * The login function
	 *
	 * @param string $username the username
	 * @param string $password the password
	 * @return bool success
	 * @access public
	 * @since 1.0.0
	 */
	public function doLogin( $username, $password )
	{
		$out = false;

		$adServer = $this->registry->getSetting('ldap_server');

		$ldap = ldap_connect($adServer);
		$username = $username;
		$password = $password;

		$ldaprdn = $this->registry->getSetting('ldap_domain') . "\\" . $username;

		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

		$bind = @ldap_bind($ldap, $ldaprdn, $password);

		if ( $bind )
		{
			$filter ="(sAMAccountName={$username})";
			$result = ldap_search( $ldap, $this->registry->getSetting('ldap_base_dn'), $filter );

			$info = ldap_get_entries( $ldap, $result );

			if( isset( $info['count'] ) && $info['count'] > 0 )
			{
				$this->user = $info[0];
				$out = true;
			}
		}

		return $out;
	}

	/**
	 * Get the user's first name
	 *
	 * @return string the first name
	 * @access public
	 * @since 1.0.0
	 */
	public function getFirstName()
	{
		$out = "";

		if ( is_array( $this->user ) )
		{
			$out = $this->user['givenname'][0];
		}

		return $out;
	}

	/**
	 * Get the user's last name
	 *
	 * @return string the last name
	 * @access public
	 * @since 1.0.0
	 */
	public function getLastName()
	{
		$out = "";

		if ( is_array( $this->user ) )
		{
			$out = $this->user['sn'][0];
		}

		return $out;
	}
}