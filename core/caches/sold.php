<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 *
 * Discount cache type
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

class SoldCache extends CacheType
{
	/**
	 * The name of the cache
	 *
	 * @access public
	 * @var string
	 * @since 1.0.0
	 */
	public $name = 'sold';

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
		$this->DB->query("SELECT menu_id, sum(quantity) AS quantity FROM `transaction` GROUP BY menu_id ORDER BY menu_id");
		
		$save = array();
		
		while( $r = $this->DB->fetchRow() )
		{
			$save[ $r['menu_id'] ] = $r['quantity'];
		}
		
		$this->value = $save;

		parent::save( $dbCheck );
	}
}

?>