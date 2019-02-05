<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Cookie that handles semi part of the login process
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

class Cookie
{
	/**
	 * Constructor that loads the registry
	 *
	 * @param Registry $registry the main program registry
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct( Registry $registry )
	{
		$this->registry = $registry;
		
		$this->registry->getDisplay()->addDebug( "Cookie Library Loaded" );
	}

	/**
	 * Retireves a cookie from the server
	 *
	 * @param string $name the name of the cookie
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function getCookie( $name )
	{
		$out = "";

		if ( $this->registry->getSetting('cookie_enable') == 1 )
		{
			if ( strlen( $this->registry->getSetting('cookie_prefix') ) > 0 )
			{
				$name = $this->registry->getSetting('cookie_prefix') . $name;
			}

			if ( isset( $_COOKIE[ $name ] ) )
			{
				$out = $_COOKIE[ $name ];
			}
		}

		return $out;
	}

	/**
	 * Sets a cookie on the server
	 *
	 * @param string $name the name of the cookie
	 * @param string $value the value to save in the cookie
	 * @param string $path the path of the site
	 * @param string $domain the domain for the site
	 * @param string $secure SEE PHP SPEC
	 * @param string $httponly SEE PHP SPEC
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function saveCookie( $name, $value='', $expire=0, $path='', $domain='', $secure=FALSE, $httponly=TRUE)
	{
		if ( $this->registry->getSetting('cookie_enable') == 1 )
		{
			if ( strlen( $this->registry->getSetting('cookie_prefix') ) > 0 )
			{
				$name = $this->registry->getSetting('cookie_prefix') . $name;
			}

			if ( strlen( $this->registry->getSetting('cookie_domain') ) > 0 )
			{
				$domain = $this->registry->getSetting('cookie_domain') . $domain;
			}

			if ( strlen( $this->registry->getSetting('cookie_path') ) > 0 )
			{
				$path = $this->registry->getSetting('cookie_path') . $path;
			}
			else
			{
				$path = '/';
			}
			
			if ( SWS_THIS_APPLICATION == 'admin' )
			{
				$expire = time() + (60 * $this->registry->getSetting('session_timeout'));
			}
			else
			{
				$expire = time() + (60 * 60 * 24 * $this->registry->getSetting('public_timeout') );
			}
			
			$this->registry->getDisplay()->addDebug("Saving Cookie: {$name} AS {$value}");
			
			if (phpversion() >= '5.2.0')
			{
				setcookie( $name, $value, ($expire > 0 ? $expire : time()+86400), $path, $domain, $secure, $httponly );
			}
			else
			{
				setcookie( $name, $value, ($expire > 0 ? $expire : time()+86400), $path, $domain, $secure );
			}
		}
	}
}

?>