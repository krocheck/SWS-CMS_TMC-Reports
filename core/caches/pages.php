<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 *
 * Page cache type
 * Last Updated: $Date: 2010-06-14 09:29:57 -0500 (Mon, 14 Jun 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Cache
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 15 $
 */

class PagesCache extends CacheType
{
	/**
	 * The name of the cache
	 *
	 * @access public
	 * @var string
	 * @since 1.0.0
	 */
	public $name = 'pages';

	/**
	 * Returns the array value, automatically fetching it from the database
	 * if needed
	 *
	 * @return array the cache
	 * @access public
	 * @since 1.0.0
	 */
	public function getCache()
	{
		if ( ! is_array( $this->value ) )
		{
			$this->DB->query("SELECT * FROM caches WHERE name = '{$this->name}';");

			$temp = $this->DB->fetchRow();

			if ( is_array( $temp ) && count( $temp ) > 0 )
			{
				$this->setCache( $temp );
			}
			else
			{
				$this->save( FALSE );
			}
		}

		return $this->value;
	}

	/**
	 * Sets up the array for the cache value
	 *
	 * @param array $temp the array of pages to process
	 * @return array
	 * @access protected
	 * @since 1.0.0
	 */
	protected function processArray( $temp )
	{
		$out = array();

		$out['all'] = $temp;

		foreach ( $temp as $id => $page )
		{
			if ( $page['parent_id'] < 1 )
			{
				$page['parent_id'] = 'root';
			}

			$out[ $page['parent_id'] ][ $page['page_id'] ] = $page;
		}

		return $out;
	}

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
		$this->DB->query("SELECT * FROM page ORDER BY parent_id, position");
		
		$save = array();
		
		while( $r = $this->DB->fetchRow() )
		{
			$save[ $r['page_id'] ] = $r;
			$save[ $r['page_id'] ]['languages'] = unserialize( $r['languages'] );
		}
		
		$lib = $this->registry->getClass('PageController');
		
		$this->DB->query(
			"SELECT * FROM metadata_page 
				WHERE meta_key IN('name','uri','active','navigation','navigation_text','show_title','sitemap_changefreq','sitemap_priority')
				ORDER BY id, language_id;"
		);
		
		$metadata = array();
		
		while( $r = $this->DB->fetchRow() )
		{
			$metadata[ $r['meta_id'] ] = $r;
		}
		
		if ( count( $metadata ) > 0 )
		{
			$meta = $lib->processMetadataByID( $metadata );
			
			foreach( $meta as $k => $v )
			{
				if ( isset( $save[ $k ] ) && is_array( $save[ $k ] ) )
				{
					$save[ $k ]['metadata'] = $v;
				}
			}
		}
		
		$this->value = $save;

		parent::save( $dbCheck );

		$this->value = $this->processArray( $save );
	}

	/**
	 * Sets up the value and ID from the row
	 *
	 * @param array $dbRow the cache database row
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setCache( $dbRow )
	{
		$this->id    = $dbRow['cache_id'];
		$this->value = array();
		
		$temp = unserialize( $dbRow['value'] );
		
		$this->value = $this->processArray( $temp );
	}
}

?>