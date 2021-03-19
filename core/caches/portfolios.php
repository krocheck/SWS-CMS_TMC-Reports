<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 *
 * Tags cache type
 * Last Updated: $Date: 2010-04-28 13:42:06 -0500 (Wed, 28 Apr 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Cache
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 2 $
 */

class PortfoliosCache extends CacheType
{
	/**
	 * The name of the cache
	 *
	 * @access public
	 * @var string
	 * @since 1.0.0
	 */
	public $name = 'portfolios';

	/**
	 * Gets the values from the database, calls the parent
	 *
	 * @param bool $dbCheck flag to query for the ID if it isn't available, DEFAULT is TRUE
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function save( $dbCheck = TRUE )
	{
		$this->DB->query("SELECT * FROM portfolio ORDER BY name;");
		
		$save = array();
		
		while( $r = $this->DB->fetchRow() )
		{
			$r['followers'] = unserialize( $r['followers'] );
			$r['custom_field_settings'] = unserialize( $r['custom_field_settings'] );

			$save[ $r['portfolio_gid'] ] = $r;
		}
		
		$this->value = $save;

		parent::save( $dbCheck );
	}
}

?>