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
/*
require_once('adldap2/src/Adldap.php');
require_once('adldap2/src/AdldapException.php');
require_once('adldap2/src/AdldapInterface.php');
require_once('adldap2/src/Log/EventLogger');
require_once('adldap2/src/Log/LogsInformation');
require_once('adldap2/src/Events/DispatchesEvents');
require_once('adldap2/src/Connections/Provider');
require_once('adldap2/src/Connections/ProviderInterface');
require_once('adldap2/src/Connections/ConnectionInterface');
require_once('adldap2/src/Configuration/DomainConfiguration');
require_once('adldap2/src/Schemas/Schema.php');
require_once('adldap2/src/Schemas/SchemaInterface.php');
require_once('adldap2/src/Schemas/ActiveDirectory.php');
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
	protected function doExecute( $params )
	{
		/*$this->ad = new \Adldap\Adldap();

		$config = [
			// Mandatory Configuration Options
			'hosts'            => [ 'trimarq.local' ],
			'base_dn'          => 'OU=Employees,OU=Accounts,DC=trimarq,DC=local',
			'username'         => 'null',
			'password'         => 'null',

			// Optional Configuration Options
			'schema'           => Adldap\Schemas\ActiveDirectory::class,
			'account_suffix'   => '@trimarq.local',
			'port'             => 389,
			'follow_referrals' => false,
			'use_ssl'          => false,
			'use_tls'          => false,
			'version'          => 3,
			'timeout'          => 5
		];

		$this->ad->addProvider($config);

		try
		{
			$this->provider = $this->ad->connect();
		}
		catch (BindException $e)
		{
			$this->display->error->raiseError( 'ldap_connect_failed' );
		}*/
	}

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

		/*try
		{
			if ( $this->provider->auth()->attempt($username, $password, true) )
			{
				$this->user = $this->provider->search()->where('samAccountName', '=', $username)->get();
				$out = true;
			}
			else
			{
				//$this->error->logError( 'invalid_login', FALSE );

				// Get the users first name.
				$user->getFirstName();

				// Get the users last name.
				$user->getLastName();
			}
		}
		catch (Adldap\Auth\UsernameRequiredException $e)
		{
			//$this->error->logError( 'invalid_login', FALSE );
		}
		catch (Adldap\Auth\PasswordRequiredException $e)
		{
			//$this->error->logError( 'invalid_login', FALSE );
		}*/
		
		$adServer = "ldap://trimarq.local";

		$ldap = ldap_connect($adServer);
		$username = $username;
		$password = $password;

		$ldaprdn = 'TRIMARQ' . "\\" . $username;

		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

		$bind = @ldap_bind($ldap, $ldaprdn, $password);


		if ($bind) {
			$filter="(sAMAccountName=$username)";
			$result = ldap_search($ldap,"OU=Employees,OU=Accounts,DC=trimarq,DC=local",$filter);
			ldap_sort($ldap,$result,"sn");
			$info = ldap_get_entries($ldap, $result);
			for ($i=0; $i<$info["count"]; $i++)
			{
				if($info['count'] > 1)
					break;
				echo "<p>You are accessing <strong> ". $info[$i]["sn"][0] .", " . $info[$i]["givenname"][0] ."</strong><br /> (" . $info[$i]["samaccountname"][0] .")</p>\n";
				echo '<pre>';
				print_r($info);
				echo '</pre>';
				$userDn = $info[$i]["distinguishedname"][0]; 
			}
			@ldap_close($ldap);
		} else {
			$msg = "Invalid email address / password";
			echo $msg;
		}
exit();
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

		if ( is_object( $this->user ) )
		{
			$out = $this->user->getFirstName();
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

		if ( is_object( $this->user ) )
		{
			$out = $this->user->getLastName();
		}

		return $out;
	}
}