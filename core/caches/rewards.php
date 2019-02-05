<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 *
 * Language cache type
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

class RewardsCache extends CacheType
{
	/**
	 * The name of the cache
	 *
	 * @access public
	 * @var string
	 * @since 1.0.0
	 */
	public $name = 'rewards';

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
		// Declaration for the module
		$transactions = array();
		$rewards = array();
		$processQueue = array();
		$clubIDs = array();
		$transactionUpdates = array();
		$rewardUpdates = array();
		$menuItems = array();
		
		$this->DB->query(
			"SELECT r.*, t.menu_id, m.points AS menu_points
				FROM reward r
				LEFT JOIN transaction t ON (r.reward_id = t.reward_id)
				LEFT JOIN menu_item m ON (t.menu_id = m.menu_item_id)
				WHERE r.status = 0
				GROUP BY r.reward_id, r.club_id, t.menu_id, m.points
				ORDER BY r.club_id, r.reward_id"
		);
		
		$save = array();

		if ( $this->DB->getTotalRows() > 0 )
		{
			while( $r = $this->DB->fetchRow() )
			{
				if ( ! isset( $rewards[ $r['reward_id'] ] ) )
				{
					$rewards[ $r['reward_id'] ] = array(
						'reward_id'   => $r['reward_id'],
						'club_id'     => $r['club_id'],
						'beers'       => array(),
						'points'      => $r['carry_over']
					);
				}

				/*if ( ! isset( $clubIDs[ $r['club_id'] ] ) )
				{
					$clubIDs[ $r['club_id'] ] = array(
						'reward_id'   => $r['reward_id'],
						'club_id'     => $r['club_id']
					);
				}

				$menuItems[ $r['menu_id'] ] = $r['menu_points'];*/

				//if ( ! isset( $rewards[ $r['reward_id'] ]['beers'][ $r['menu_id'] ] ) )
				//{
					$rewards[ $r['reward_id'] ]['beers'][ $r['menu_id'] ] = $r['menu_id'];
					$rewards[ $r['reward_id'] ]['points'] += $r['menu_points'];
				//}
				
				//$save[ $r['club_id'] ] = $rewards[ $r['reward_id'] ]['points'];
			}
		}
		
		if ( count( $rewards ) > 0 )
		{
			foreach( $rewards as $k => $v )
			{
				$save[ $v['club_id'] ] = $v['points'];
			}
		}

		$this->value = $save;

		parent::save( $dbCheck );
	}
}

?>