<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Asana API Library
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
class AsanaAPI extends Command
{
	protected $apiURL = "";
	protected $endpoints = array();
	protected $httpCode;
	protected $token = "";
	protected $userAgent = "TrimarqReports-API-1.0";

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
		$this->apiURL = $this->registry->getSetting('asana_url');

		$this->endpoints = array(
			'tasks' => array(
				'uri'    => $this->registry->getSetting('asana_tasks'),
				'fields' => array('gid', 'parent', 'workspace', 'assignee', 'resource_subtype', 'assignee_status', 'created_at', 'completed', 'completed_at', 'custom_fields', 'dependencies', 'dependents', 'due_on', 'due_at', 'followers', 'liked', 'likes', 'modified_at', 'name', 'html_notes', 'num_likes', 'projects', 'start_on', 'memberships', 'tags'),
				'expand' => array()
			),
			'sections' => array(
				'uri'    => $this->registry->getSetting('asana_sections'),
				'fields' => array('gid', 'project', 'name', 'created_at'),
				'expand' => array()
			),
			'projects' => array(
				'uri'    => $this->registry->getSetting('asana_projects'),
				'fields' => array('project_gid', 'owner_gid', 'workspace_gid', 'team_gid', 'name', 'current_status', 'due_date', 'start_on', 'created_at', 'modified_at', 'archived', 'public', 'members', 'followers', 'custom_fields', 'custom_field_settings', 'color', 'html_notes', 'layout'),
				'expand' => array()
			),
			'workspaces' => array(
				'uri'    => $this->registry->getSetting('asana_workspaces'),
				'fields' => array('gid', 'name', 'is_organization'),
				'expand' => array()
			),
			'teams' => array(
				'uri'    => $this->registry->getSetting('asana_teams'),
				'fields' => array('gid', 'name', 'html_description'),
				'expand' => array()
			),
			'users' => array(
				'uri'    => $this->registry->getSetting('asana_users'),
				'fields' => array('gid', 'name', 'email', 'workspaces'),
				'expand' => array()
			),
			'tags' => array(
				'uri'    => $this->registry->getSetting('asana_tags'),
				'fields' => array('gid', 'workspace', 'created_at', 'followers', 'name', 'color'),
				'expand' => array()
			),
			'custom_fields' => array(
				'uri'    => $this->registry->getSetting('asana_custom_fields'),
				'fields' => array('gid', 'name', 'resource_subtype', 'description', 'enum_options'),
				'expand' => array()
			)
		);

		$this->token = $this->registry->getSetting('asana_token');
	}

	/**
	 * Basic CURL request which connects to the Asana API and returns the result
	 *
	 * @param $endpoint string the endpoint being used
	 * @param $method string the addtional query string
	 * @return bool result
	 * @access protected
	 * @since 1.0.0
	*/
	protected function callGet( $endpoint, $method )
	{
		$out = array();

		$curl2 = curl_init();

		if ( $endpoint == 'next_page' )
		{
			$url = $this->apiURL . $method;
		}
		else
		{
			if ( count($this->endpoints[ $endpoint ]['expand']) > 0 )
			{
				$fields = (strlen($method) > 0 && ( strpos('?', $method) > 0 || substr($method,0,1) == '?' ) ? '&' : '?') . 'opt_expand=' . implode(',',$this->endpoints[ $endpoint ]['expand']);
			}
			else
			{
				$fields = (strlen($method) > 0 && strpos('?', $method) > 0 ? '&' : '?') . 'opt_fields=' . implode(',',$this->endpoints[ $endpoint ]['fields']);
			}

			$url = $this->apiURL . $this->endpoints[ $endpoint ]['uri'] . $method . $fields;
		}

		curl_setopt($curl2, CURLOPT_URL, $url);
		curl_setopt($curl2, CURLOPT_HTTPHEADER, array( "Content-Type: application/json", "Authorization: Bearer {$this->token}" ) );
		curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl2, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl2, CURLOPT_USERAGENT, $this->userAgent);
		$result = curl_exec($curl2);

		$httpCode = curl_getinfo($curl2, CURLINFO_HTTP_CODE);
		$this->httpCode = $httpCode;

		if ( $httpCode == 200 )
		{
			$result = json_decode( $result, TRUE );

			if ( is_array( $result ) && count( $result ) > 0 )
			{
				$out = $result;
			}
		}
		else
		{
			$this->display->addDebug( array( 'url' => $url, 'http_code' => $httpCode, 'result' => $result ) );
		}

		return $out;
	}

	/**
	 * Basic CURL post which connects to the Asana API and returns the result
	 *
	 * @param $endpoint string the endpoint being used
	 * @param $method string the addtional query string
	 * @return bool result
	 * @access protected
	 * @since 1.0.0
	*/
	protected function callPost( $endpoint, $method, $params )
	{
		$out = array();

		$curl2 = curl_init();
		$url = $this->apiURL . $this->endpoints[ $endpoint ]['uri'] . $method;
		$parameters = array();
		
		if ( is_array( $params ) )
		{
			foreach( $params as $key => $value )
			{
				$parameters[ $key ] = urlencode( $value );
			}
		}

		$parameters = implode("&", $parameters);;

		curl_setopt($curl2, CURLOPT_POST, true);
		curl_setopt($curl2, CURLOPT_POSTFIELDS, $parameters);

		curl_setopt($curl2, CURLOPT_URL, $url);
		curl_setopt($curl2, CURLOPT_HTTPHEADER, array( "Authorization: Bearer {$this->token}" ) );
		curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl2, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl2, CURLOPT_USERAGENT, $this->userAgent);
		$result = curl_exec($curl2);

		$httpCode = curl_getinfo($curl2, CURLINFO_HTTP_CODE);
		$this->httpCode = $httpCode;

		if ( $httpCode == 200 )
		{
			$result = json_decode( $result, TRUE );

			if ( is_array( $result ) && count( $result ) > 0 )
			{
				$out = $result;
			}
		}
		else
		{
			$this->display->addDebug( array( 'url' => $url, 'params' => $parameters, 'http_code' => $httpCode, 'result' => $result ) );
		}

		return $out;
	}

	/**
	 * Generate v4 UUID
	 * 
	 * Version 4 UUIDs are pseudo-random.
	 * @return string a pseudo-random GID
	 * @access protected
	 * @since 1.0.0
	 */
	public static function generateGID() 
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		// 32 bits for "time_low"
		mt_rand(0, 0xffff), mt_rand(0, 0xffff),
		// 16 bits for "time_mid"
		mt_rand(0, 0xffff),
		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		mt_rand(0, 0x0fff) | 0x4000,
		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		mt_rand(0, 0x3fff) | 0x8000,
		// 48 bits for "node"
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	/**
	 * Call the Asana API to retrieve the specified Menu Group
	 *
	 * @param $gid string the GID to query
	 * @return array the salesCategories
	 * @access public
	 * @since 1.0.0
	*/
	protected function getMenuGroup( $gid )
	{
		$out = array();
		$menuGroup = $this->callGet('config',"/menuGroups/{$gid}");
		
		if ( is_array( $menuGroup ) && isset( $menuGroup['items'] ) && is_array( $menuGroup['items'] ) )
		{
			$out = $menuGroup['items'];
		}
		
		return $out;
	}

	/**
	 * Call the Asana API to retrieve the specified Menu Group
	 *
	 * @param $gid int the number of results to return
	 * @oaram $page int the page number
	 * @param $lastModified string the date/time to return changes after in ISO-8601 format
	 * @return array the salesCategories
	 * @access protected
	 * @since 1.0.0
	*/
	protected function getMenuItems( $pageSize, $page=0, $lastModified="" )
	{
		$out = array();

		$params = "pageSize={$pageSize}" . ( $page > 0 ? "&page={$page}" : "" ) . ( $lastModified <> "" ? "&lastModified={$lastModified}" : "" );

		$menuItems = $this->callGet('config',"/menuItems?{$params}");

		if ( is_array( $menuItems ) && count( $menuItems ) > 0 )
		{
			$out = $menuItems;
		}

		return $out;
	}

	/**
	 * Call the Asana API to retrieve the specified order
	 *
	 * @param $gid string the date/time to return changes after is ISO-8601 format
	 * @return array the order GIDs
	 * @access protected
	 * @since 1.0.0
	*/
	protected function getOrder( $gid )
	{
		$out = array();
		$order = $this->callGet('orders',"/orders/{$gid}");

		if ( is_array( $order ) )
		{
			$out = $order;
		}

		return $out;
	}

	/**
	 * Call the Asana API to retrieve the specified orders
	 *
	 * @param $date string the date/time to return changes after in YYYYMMDD format
	 * @return array the order GIDs
	 * @access protected
	 * @since 1.0.0
	*/
	protected function getOrdersByDate( $date )
	{
		$out = array();

		$params = "businessDate={$date}";

		$orders = $this->callGet('orders',"/orders?{$params}");

		if ( is_array( $orders ) && count( $orders ) > 0 )
		{
			$out = $orders;
		}

		return $out;
	}

	/**
	 * Call the Asana API to retrieve the specified orders
	 *
	 * @param $start string the date/time to start the range in ISO-8601 format
	 * @param $end string the date/time to end the range in ISO-8601 format
	 * @return array the order GIDs
	 * @access protected
	 * @since 1.0.0
	*/
	protected function getOrdersBySpan( $start, $end )
	{
		$out = array();

		$params = "startDate={$start}&endDate={$end}";

		$orders = $this->callGet('orders',"/orders?{$params}");

		if ( is_array( $orders ) && count( $orders ) > 0 )
		{
			$out = $orders;
		}

		return $out;
	}

	/**
	 * Updates/adds the passed menu items into the database
	 *
	 * @return int the total number of processed items
	 * @access protected
	 * @since 1.0.0
	*/
	protected function processMenuItems( $data, $updateTimestamp = FALSE )
	{
		$itemIDs = array();
		$gids   = array();
		$newRows = array();
		$oldRows = array();
		$total = 0;

		foreach( $data as $row )
		{
			$itemID = intval($row['sku']);
			$text = $row['name'];
			$gid = $row['gid'];
			//$categoryID = $row[2];
			//$active = $row[3];
			//$price = $row[4];

			if ( $itemID <> 0 )
			{
				$itemIDs[] = $itemID;
			}

			$gids[] = $gid;
			$newRows[ $gid ] = array( 'menu_item_id' => $itemID, 'gid' => $gid, 'title' => $text);

			$total++;
		}

		$total = count( $gids );

		if ( count( $itemIDs) > 0 || count( $gids ) > 0 )
		{
			$this->DB->query("SELECT menu_item_id,asana_gid FROM menu_item WHERE menu_item_id IN('".implode("','",$itemIDs)."') OR asana_gid IN('".implode("','",$gids)."');");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					if ( isset( $row['asana_gid'] ) && strlen( $row['asana_gid'] ) > 0 )
					{
						$oldRows[ $row['asana_gid'] ] = $newRows[ $row['asana_gid'] ];
						unset( $newRows[ $row['asana_gid'] ] );
					}
					else if ( isset( $row['menu_item_id'] ) && $row['menu_item_id'] > 0 )
					{
						foreach( $newRows as $test )
						{
							if ( isset( $test['menu_item_id'] ) && $test['menu_item_id'] > 0 && $row['menu_item_id'] == $test['menu_item_id'] )
							{
								$oldRows[ $test['gid'] ] = $newRows[ $test['gid'] ];
								unset( $newRows[ $test['gid'] ] );
								break;
							}
						}
					}
				}
			}
		}

		if ( count( $newRows ) > 0 )
		{
			$query = "INSERT INTO menu_item (asana_gid,title".($updateTimestamp ? ",last_modified" : '').") VALUES ";

			foreach( $newRows as $row )
			{
				$query .= "('{$row['gid']}',\"{$row['title']}\"".($updateTimestamp ? ",NOW()" : '')."),";
			}

			$query = substr($query,0,-1) . ";";

			$this->DB->query( $query );
		}

		if ( count( $oldRows ) > 0 )
		{
			foreach( $oldRows as $row )
			{
				if ( isset( $row['menu_item_id'] ) && $row['menu_item_id'] > 0 )
				{
					$this->DB->query("UPDATE menu_item SET title = \"{$row['title']}\", asana_gid = '{$row['gid']}'".($updateTimestamp ? ", last_modified = NOW()" : '')." WHERE menu_item_id = '{$row['menu_item_id']}';");
				}
				else if ( isset( $row['gid'] ) && strlen( $row['gid'] ) > 0 )
				{
					$this->DB->query("UPDATE menu_item SET title = \"{$row['title']}\"".($updateTimestamp ? ", last_modified = NOW()" : '')." WHERE asana_gid = '{$row['gid']}';");
				}
			}
		}

		return $total;
	}

	/**
	 * Updates/adds the passed order gids into the database
	 *
	 * @return int the total number of processed items
	 * @access protected
	 * @since 1.0.0
	*/
	protected function processOrders( $data )
	{
		$newRows = array();
		$oldRows = array();
		$total = 0;
		$items = $this->cache->getCache('items');

		$orderGIDs     = array();
		$orders         = array();
		$checkGIDs     = array();
		$checks         = array();
		$selectionGIDs = array();
		$selections     = array();
		
		foreach( $data as $row )
		{
			if ( strlen( $row ) > 1 )
			{
				$order = $this->getOrder( $row );

				if ( is_array( $order ) && isset( $order['gid'] ) && $row = $order['gid'] )
				{
					$orderGID = $order['gid'];
					$void = ( $order['voided'] == 'true' ? 1 : 0 );
					$deleted = ( $order['deleted'] == 'true' ? 1 : 0 );
					$businessDate = date_create( $order['businessDate'] )->format("Y-m-d");
					$createdDate = date_create( $order['openedDate'] )->format("Y-m-d H:i:s");
					$closedDate = ( $order['closedDate'] <> '' ? date_create( $order['closedDate'] )->format("Y-m-d H:i:s") : '0000-00-00 00:00:00' );

					$orderGIDs[] = $orderGID;

					$orders[ $orderGID ] = array(
						'order_gid'    => $orderGID,
						'void'          => $void,
						'deleted'       => $deleted,
						'business_date' => $businessDate,
						'opened_date'   => $createdDate,
						'closed_date'   => $closedDate
					);

					if ( isset( $order['checks'] ) && is_array( $order['checks'] ) && count( $order['checks'] ) > 0 )
					{
						foreach( $order['checks'] as $check )
						{
							if ( is_array( $check ) && isset( $check['gid'] ) && $row = $check['gid'] )
							{
								$checkGID = $check['gid'];
								$void = ( $check['voided'] == 'true' ? 1 : 0 );
								$deleted = ( $check['deleted'] == 'true' ? 1 : 0 );
								$createdDate = date_create( $check['openedDate'] )->format("Y-m-d H:i:s");
								$closedDate = ( $check['closedDate'] <> '' ? date_create( $check['closedDate'] )->format("Y-m-d H:i:s") : '0000-00-00 00:00:00' );
								$tabName = $check['tabName'];
								$displayNumber = $check['displayNumber'];
								$matches = array();
								$appliedDiscounts = array();

								if ( preg_match("/(\*[0-9]+\*)/", $tabName, $matches ) > 0 )
								{
									$clubID = intval( trim( $matches[0], "*" ) );
								}
								else
								{
									$clubID = 0;
								}

								if ( is_array( $check['appliedDiscounts'] ) && count( $check['appliedDiscounts'] ) > 0 )
								{
									foreach( $check['appliedDiscounts'] as $discount )
									{
										$appliedDiscounts[ $discount['gid'] ] = $discount;
									}
								}

								$checkGIDs[] = $checkGID;

								$checks[ $checkGID ] = array(
									'check_gid'        => $checkGID,
									'order_gid'        => $orderGID,
									'void'              => $void,
									'deleted'           => $deleted,
									'opened_date'       => $createdDate,
									'closed_date'       => $closedDate,
									'tab_name'          => mysql_real_escape_string($tabName),
									'club_id'           => $clubID,
									'display_number'    => $displayNumber,
									'applied_discounts' => mysql_real_escape_string( serialize($appliedDiscounts) )
								);

								if ( isset( $check['selections'] ) && is_array( $check['selections'] ) && count( $check['selections'] ) > 0 )
								{
									foreach( $check['selections'] as $item )
									{
										if ( is_array( $item ) && isset( $item['gid'] ) && $row = $item['gid'] )
										{
											$selectionGID = $item['gid'];
											$itemGID = $item['item']['gid'];
											$categoryGID = $item['salesCategory']['gid'];
											$void = ( $item['voided'] == 'true' ? 1 : 0 );
											$createdDate = date_create( $item['createdDate'] )->format("Y-m-d H:i:s");
											$price = $item['price'] + $item['taxAmount'];
											$quantity = $item['quantity'];
											$appliedDiscounts = array();

											if ( is_array( $check['appliedDiscounts'] ) && count( $check['appliedDiscounts'] ) > 0 )
											{
												foreach( $check['appliedDiscounts'] as $discount )
												{
													$appliedDiscounts[ $discount['gid'] ] = $discount;
												}
											}

											$selectionGIDs[] = $selectionGID;

											$selections[ $selectionGID ] = array(
												'selection_gid'    => $selectionGID,
												'check_gid'        => $checkGID,
												'item_gid'         => $itemGID,
												'category_gid'     => $categoryGID,
												'void'              => $void,
												'created_date'      => $createdDate,
												'price'             => $price,
												'quantity'          => $quantity,
												'applied_discounts' => mysql_real_escape_string( serialize($appliedDiscounts) )
											);
										}
									}
								}
							}
						}
					}

					$total++;
				}
			}
		}

		$oldRows = array();
		$newRows = $orders;
		$gids   = $orderGIDs;

		if ( count( $gids ) > 0 )
		{
			$this->DB->query("SELECT order_gid FROM `order` WHERE order_gid IN('".implode("','",$gids)."');");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					if ( isset( $row['order_gid'] ) && strlen( $row['order_gid'] ) > 0 )
					{
						$oldRows[ $row['order_gid'] ] = $newRows[ $row['order_gid'] ];
						unset( $newRows[ $row['order_gid'] ] );
					}
				}
			}
		}

		if ( count( $newRows ) > 0 )
		{
			$query = "INSERT INTO `order` (order_gid,void,deleted,business_date,opened_date,closed_date) VALUES ";

			foreach( $newRows as $row )
			{
				$query .= "('{$row['order_gid']}','{$row['void']}','{$row['deleted']}','{$row['business_date']}','{$row['opened_date']}','{$row['closed_date']}'),";
			}

			$query = substr($query,0,-1) . ";";

			$this->DB->query( $query );
		}

		if ( count( $oldRows ) > 0 )
		{
			foreach( $oldRows as $row )
			{
				$this->DB->query("UPDATE `order` SET void = '{$row['void']}', deleted = '{$row['deleted']}', business_date = '{$row['business_date']}', opened_date = '{$row['opened_date']}', closed_date = '{$row['closed_date']}' WHERE order_gid = '{$row['order_gid']}';");
			}
		}

		$oldRows = array();
		$newRows = $checks;
		$gids   = $checkGIDs;

		if ( count( $gids ) > 0 )
		{
			$this->DB->query("SELECT check_gid FROM `check` WHERE check_gid IN('".implode("','",$gids)."');");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					if ( isset( $row['check_gid'] ) && strlen( $row['check_gid'] ) > 0 )
					{
						$oldRows[ $row['check_gid'] ] = $newRows[ $row['check_gid'] ];
						unset( $newRows[ $row['check_gid'] ] );
					}
				}
			}
		}

		if ( count( $newRows ) > 0 )
		{
			$query = "INSERT INTO `check` (check_gid,order_gid,void,deleted,opened_date,closed_date,tab_name,club_id,display_number,applied_discounts) VALUES ";

			foreach( $newRows as $row )
			{
				$query .= "('{$row['check_gid']}','{$row['order_gid']}','{$row['void']}','{$row['deleted']}','{$row['opened_date']}','{$row['closed_date']}',\"{$row['tab_name']}\",'{$row['club_id']}','{$row['display_number']}',\"{$row['applied_discounts']}\"),";
			}

			$query = substr($query,0,-1) . ";";

			$this->DB->query( $query );
		}

		if ( count( $oldRows ) > 0 )
		{
			foreach( $oldRows as $row )
			{
				$this->DB->query("UPDATE `check` SET order_gid = '{$row['order_gid']}', void = '{$row['void']}', deleted = '{$row['deleted']}', opened_date = '{$row['opened_date']}', closed_date = '{$row['closed_date']}', tab_name = \"{$row['tab_name']}\", club_id = '{$row['club_id']}', display_number = '{$row['display_number']}', applied_discounts = \"{$row['applied_discounts']}\" WHERE check_gid = '{$row['check_gid']}';");
			}
		}

		$oldRows = array();
		$newRows = $selections;
		$gids   = $selectionGIDs;

		if ( count( $gids ) > 0 )
		{
			$this->DB->query("SELECT selection_gid FROM `selection` WHERE selection_gid IN('".implode("','",$gids)."');");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					if ( isset( $row['selection_gid'] ) && strlen( $row['selection_gid'] ) > 0 )
					{
						$oldRows[ $row['selection_gid'] ] = $newRows[ $row['selection_gid'] ];
						unset( $newRows[ $row['selection_gid'] ] );
					}
				}
			}
		}

		if ( count( $newRows ) > 0 )
		{
			$query = "INSERT INTO `selection` (selection_gid,check_gid,item_gid,category_gid,void,created_date,price,quantity,applied_discounts) VALUES ";

			foreach( $newRows as $row )
			{
				$query .= "('{$row['selection_gid']}','{$row['check_gid']}','{$row['item_gid']}','{$row['category_gid']}','{$row['void']}','{$row['created_date']}','{$row['price']}','{$row['quantity']}',\"{$row['applied_discounts']}\"),";
			}

			$query = substr($query,0,-1) . ";";

			$this->DB->query( $query );
		}

		if ( count( $oldRows ) > 0 )
		{
			foreach( $oldRows as $row )
			{
				$this->DB->query("UPDATE `selection` SET check_gid = '{$row['check_gid']}', item_gid = '{$row['item_gid']}', category_gid = '{$row['category_gid']}', void = '{$row['void']}', created_date = '{$row['created_date']}', price = '{$row['price']}', quantity = '{$row['quantity']}', applied_discounts = \"{$row['applied_discounts']}\" WHERE selection_gid = '{$row['selection_gid']}';");
			}
		}

		$this->DB->query("SELECT `selection`.`selection_gid`, `selection`.`item_gid`, `selection`.`category_gid`, 
									IF(`selection`.`void` = 1 OR `check`.`void` = 1 OR `check`.`deleted` = 1 OR `order`.`void` = 1 OR `order`.`deleted` = 1,1,0) AS `void`, 
									`selection`.`created_date` AS `date_time`, `selection`.`quantity`, `check`.`club_id`, `order`.`business_date`
								FROM `selection`
									INNER JOIN `check` ON `selection`.`check_gid`=`check`.`check_gid`
									INNER JOIN `order` ON `check`.`order_gid`=`order`.`order_gid`
								WHERE `selection`.`rewarded` = 0 AND `check`.`closed_date` > 0 AND `check`.`club_id` > 0
								ORDER BY `selection`.`created_date` ASC");

		$closedTransactions = array();

		if ( $this->DB->getTotalRows() > 0 )
		{
			while( $r = $this->DB->fetchRow() )
			{
				$closedTransactions[ $r['selection_gid'] ] = array(
					'selection_gid' => $r['selection_gid'],
					'category_gid' => $r['category_gid'],
					'date_time' => $r['date_time'],
					'business_date' => $r['business_date'],
					'menu_id' => $items[ $r['item_gid'] ],
					'club_id' => $r['club_id'],
					'quantity' =>$r['quantity'],
					'void' => $r['void']
				);
			}
		}

		if ( count( $closedTransactions ) > 0 )
		{
			$query = "INSERT INTO `transaction` (selection_gid,category_gid,date_time,business_date,menu_id,club_id,quantity,void) VALUES ";
			$query2 = "UPDATE `selection` SET `rewarded` = 1 WHERE selection_gid IN(";

			foreach( $closedTransactions as $row )
			{
				$query .= "('{$row['selection_gid']}','{$row['category_gid']}','{$row['date_time']}','{$row['business_date']}','{$row['menu_id']}','{$row['club_id']}','{$row['quantity']}','{$row['void']}'),";
				$query2 .="'{$row['selection_gid']}',";
			}

			$query = substr($query, 0, -1) . ";";
			$query2 = substr($query2, 0, -1) . ");";

			$this->DB->query( $query );
			$this->DB->query( $query2 );
		}

		$this->DB->query("SELECT `selection`.`selection_gid`, `selection`.`item_gid`, `selection`.`category_gid`, 
									`selection`.`created_date` AS `date_time`, `check`.`club_id`, `selection`.`quantity`, `selection`.`void`
								FROM `selection`
									INNER JOIN `check` ON `selection`.`check_gid`=`check`.`check_gid`
									INNER JOIN `order` ON `check`.`order_gid`=`order`.`order_gid`
								WHERE `selection`.`rewarded` = 0 AND `check`.`closed_date` = 0 AND `check`.`club_id` > 0 AND `selection`.`void` = 0 AND `check`.`void` = 0 AND `check`.`deleted` = 0 AND `order`.`void` = 0 AND `order`.`deleted` = 0
								ORDER BY `selection`.`created_date` ASC");

		$pendingTransactions = array();

		if ( $this->DB->getTotalRows() > 0 )
		{
			while( $r = $this->DB->fetchRow() )
			{
				$pendingTransactions[ $r['selection_gid'] ] = array(
					'selection_gid' => $r['selection_gid'],
					'category_gid' => $r['category_gid'],
					'date_time' => $r['date_time'],
					'menu_id' => $items[ $r['item_gid'] ],
					'club_id' => $r['club_id'],
					'quantity' =>$r['quantity'],
					'void' => $r['void']
				);
			}
		}

		$this->DB->query("TRUNCATE `pending_transaction`;");

		if ( count( $pendingTransactions ) > 0 )
		{
			$query = "INSERT INTO `pending_transaction` (selection_gid,category_gid,date_time,menu_id,club_id,quantity,void) VALUES ";

			foreach( $pendingTransactions as $row )
			{
				$query .= "('{$row['selection_gid']}','{$row['category_gid']}','{$row['date_time']}','{$row['menu_id']}','{$row['club_id']}','{$row['quantity']}','{$row['void']}'),";
			}

			$query = substr($query, 0, -1) . ";";

			$this->DB->query( $query );
		}

		$this->cache->update('sold');

		return $total;
	}

	/**
	 * Processes transactions waiting to be assigned to a reward
	 *
	 * @return int the total number of processed transactions
	 * @access protected
	 * @since 1.0.0
	*/
	public function processRewards()
	{
		// Declaration for the module
		$transactions = array();
		$rewards = array();
		$processQueue = array();
		$clubIDs = array();
		$transactionUpdates = array();
		$rewardUpdates = array();
		$menuItems = array();
		$categories = $this->cache->getCache('categories');
		$catIDs = array();
		$excluded = array();

		foreach( $categories as $cat )
		{
			$catIDs[] = $cat['asana_gid'];
		}

		$catIDs = "'" . implode("','", $catIDs ) . "'";

		$this->DB->query(
			"SELECT t.*, m.points
				FROM transaction t
				LEFT JOIN reward r ON (t.reward_id = r.reward_id)
				INNER JOIN menu_item m ON (t.menu_id = m.menu_item_id)
				WHERE r.reward_id IS NULL AND t.void = 0 AND t.web_void = 0 AND t.excluded = 0 AND t.category_gid IN ({$catIDs})
				ORDER BY t.date_time, t.transaction_id
				LIMIT 0, 200;"
		);

		if ( $this->DB->getTotalRows() > 0 )
		{
			while( $r = $this->DB->fetchRow() )
			{
				$test = TRUE;

				if ( strtolower( date('l', strtotime( $r['business_date']))) == 'thursday' )
				{
					foreach( $categories as $cat )
					{
						if ( $r['category_gid'] == $cat['asana_gid'] && $cat['type'] == 'tap' )
						{
							$excluded[] = $r['transaction_id'];
							$test = FALSE;
						}
					}
				}

				if ( $test )
				{
					$transactions[ $r['transaction_id'] ] = $r;
				}

				$menuItems[ $r['menu_id'] ] = $r['points'];
			}
		}

		if ( count($excluded) > 0 )
		{
			foreach( $excluded as $tID )
			{
				$this->DB->query("UPDATE transaction SET excluded = 1 WHERE transaction_id = '{$tID}';");
			}
		}

		$this->DB->query(
			"SELECT r.*, t.menu_id, m.points AS menu_points
				FROM reward r
				LEFT JOIN transaction t ON (r.reward_id = t.reward_id)
				LEFT JOIN menu_item m ON (t.menu_id = m.menu_item_id)
				WHERE r.status = 0
				GROUP BY r.reward_id, r.club_id, t.menu_id, m.points
				ORDER BY r.club_id, r.reward_id"
		);

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

				if ( ! isset( $clubIDs[ $r['club_id'] ] ) )
				{
					$clubIDs[ $r['club_id'] ] = array(
						'reward_id'   => $r['reward_id'],
						'club_id'     => $r['club_id']
					);
				}

				$menuItems[ $r['menu_id'] ] = $r['menu_points'];

				if ( ! isset( $rewards[ $r['reward_id'] ]['beers'][ $r['menu_id'] ] ) )
				{
					$rewards[ $r['reward_id'] ]['beers'][ $r['menu_id'] ] = $r['menu_id'];
					$rewards[ $r['reward_id'] ]['points'] += $r['menu_points'];
				}
			}
		}

		if ( count( $transactions ) > 0 )
		{
			foreach( $transactions as $r )
			{
				if ( ! isset( $processQueue[ $r['club_id'] ] ) )
				{
					$processQueue[ $r['club_id'] ] = array();
				}

				if ( ! isset( $processQueue[ $r['club_id'] ][ $r['menu_id'] ] ) )
				{
					$processQueue[ $r['club_id'] ][ $r['menu_id'] ] = array();
				}

				$processQueue[ $r['club_id'] ][ $r['menu_id'] ][ $r['transaction_id'] ] = $r;
			}
		}

		if ( count( $processQueue ) > 0 )
		{
			foreach( $processQueue as $clubID => $menuItem )
			{
				if ( ! isset( $clubIDs[ $clubID ] ) )
				{
					$this->DB->query("INSERT INTO reward (club_id) VALUES ('{$clubID}');");
					$rID = $this->DB->getInsertID();

					$rewards[ $rID ] = array( 'reward_id' => $rID, 'club_id' => $clubID, 'beers' => array(), 'points' => 0 );
					$clubIDs[ $clubID ] = array( 'reward_id' => $rID, 'club_id' => $clubID );
				}

				$rID = $clubIDs[ $clubID ]['reward_id'];

				if ( is_array( $menuItem ) && count( $menuItem ) > 0 )
				{
					foreach( $menuItem as $menuID => $trans )
					{
						if ( isset( $rewards[ $rID ]['beers'][ $menuID ] ) )
						{
							if ( is_array( $trans ) && count( $trans ) > 0 )
							{
								foreach( $trans as $tID => $data )
								{
									$rewardUpdates[ $tID ] = $rID;
								}
							}
						}
						else if ( $rewards[ $rID ]['points'] < 53 )
						{
							$rewards[ $rID ]['beers'][ $menuID ] = $menuID;
							$rewards[ $rID ]['points'] += $menuItems[ $menuID ];

							if ( is_array( $trans ) && count( $trans ) > 0 )
							{
								foreach( $trans as $tID => $data )
								{
									$rewardUpdates[ $tID ] = $rID;
								}
							}
						}

						if ( $rewards[ $rID ]['points'] >= 53 )
						{
							$this->DB->query("UPDATE reward SET status = 1 WHERE reward_id = '{$rID}';");

							$carryOver = 0;

							if ( $rewards[ $rID ]['points'] > 53 )
							{
								$carryOver = $rewards[ $rID ]['points'] - 53;
							}

							$this->DB->query("INSERT INTO reward (club_id, carry_over) VALUES ('{$clubID}', '{$carryOver}');");
							$rID = $this->DB->getInsertID();

							$rewards[ $rID ] = array( 'reward_id' => $rID, 'club_id' => $clubID, 'beers' => array(), 'points' => 0 );
							$clubIDs[ $clubID ] = array( 'reward_id' => $rID, 'club_id' => $clubID );
						}
					}
				}
			}

			if ( count( $rewardUpdates ) > 0 )
			{
				foreach( $rewardUpdates as $tID => $rID )
				{
					$this->DB->query("UPDATE transaction SET reward_id = '{$rID}' WHERE transaction_id = '{$tID}';");
				}

				$this->DB->query("INSERT INTO logs (type,total,result,date_time) VALUES ('rewards'," .count($rewardUpdates). ",1,'".date("Y-m-d H:i:s")."');");
			}
		}

		$this->cache->update("rewards", TRUE);

		return count( $rewardUpdates );
	}

	/**
	 * Call the Asana API to retrieve all of Menu Items
	 *
	 * @return bool success
	 * @access public
	 * @since 1.0.0
	*/
	public function refreshAllMenuItems()
	{
		$out = 0;
		$count = 1;

		while( $items = $this->getMenuItems( 200, $count ) )
		{
			if ( !is_array($items) || count( $items ) == 0 )
			{
				break;
			}

			$this->processMenuItems( $items );
			$count++;
			$out += count( $items );
		}

		if ( $out > 0 )
		{
			$this->DB->query("INSERT INTO logs (type,total,result,date_time) VALUES ('items',{$out},1,'".date("Y-m-d H:i:s")."');");
		}

		$this->cache->update('items');

		return $out;
	}

	/**
	 * Call the Asana API to retrieve the specified day's Orders
	 *
	 * @param DateTime $date the date to poll
	 * @return int count of orders
	 * @access public
	 * @since 1.0.0
	*/
	public function refreshOrdersByDate( $date )
	{
		$orders = array();

		if ( $date instanceof DateTime)
		{
			$orders = $this->getOrdersByDate( $date->format('Ymd') );
			
			if ( is_array( $orders ) && count( $orders ) > 0 )
			{
				$this->processOrders( $orders );
			}
		}

		$count = count( $orders );

		if ( $count > 0 )
		{
			$this->DB->query("INSERT INTO logs (type,total,result,date_time) VALUES ('transactions',{$count},1,'".date("Y-m-d H:i:s")."');");
		}

		return $count;
	}

	/**
	 * Call the Asana API to retrieve today's Orders
	 *
	 * @return int count of orders
	 * @access public
	 * @since 1.0.0
	*/
	public function refreshOrdersToday()
	{
		$date = new DateTime();

		$orders = $this->getOrdersByDate( $date->format('Ymd') );

		if ( is_array( $orders ) && count( $orders ) > 0 )
		{
			$this->processOrders( $orders );
		}

		$count = count( $orders );

		if ( $count > 0 )
		{
			$this->DB->query("INSERT INTO logs (type,total,result,date_time) VALUES ('transactions',{$count},1,'".date("Y-m-d H:i:s")."');");
		}

		return $count;
	}

	/**
	 * Call the Asana API to retrieve yesterday's Orders
	 *
	 * @return int count of orders
	 * @access public
	 * @since 1.0.0
	*/
	public function refreshOrdersYesterday()
	{
		$date = new DateTime();
		$date->sub( new DateInterval('P1D') );

		$orders = $this->getOrdersByDate( $date->format('Ymd') );

		if ( is_array( $orders ) && count( $orders ) > 0 )
		{
			$this->processOrders( $orders );
		}

		$count = count( $orders );

		if ( $count > 0 )
		{
			$this->DB->query("INSERT INTO logs (type,total,result,date_time) VALUES ('transactions',{$count},1,'".date("Y-m-d H:i:s")."');");
		}

		return $count;
	}

	/**
	 * Poll the latest active beer items
	 *
	 * @return bool success
	 * @access public
	 * @since 1.0.0
	*/
	public function updateActiveMenuItems()
	{
		$out = 0;
		$cats = $this->cache->getCache('categories');
		$activeItems = array();

		$this->DB->query("UPDATE menu_item SET active = 0;");

		$updateSalesCategory = "UPDATE menu_item SET category_id = ";

		foreach( $cats as $cat )
		{
			if ( isset( $cat['menu_gid'] ) && strlen( $cat['menu_gid'] ) > 0 )
			{
				$catItems = array();

				$items = $this->getMenuGroup( $cat['menu_gid'] );

				if ( is_array( $items ) && count( $items ) > 0 )
				{
					foreach( $items as $item )
					{
						$activeItems[] = $item['gid'];
						$catItems[] = $item['gid'];
						$out++;
					}
				}

				if ( count( $catItems ) > 0 )
				{
					$this->DB->query( $updateSalesCategory . $cat['category_id'] . " WHERE asana_gid IN('".implode("','",$catItems)."');");
				}
			}
		}

		if ( count( $activeItems ) > 0 )
		{
			$this->DB->query("UPDATE menu_item SET active = 1 WHERE asana_gid IN('".implode("','",$activeItems)."');");
		}

		if ( $out > 0 )
		{
			$this->DB->query("INSERT INTO logs (type,total,result,date_time) VALUES ('prices',{$out},1,'".date("Y-m-d H:i:s")."');");
		}

		return $out;
	}

	/**
	 * Call the Asana API to retrieve the Sales Categories
	 *
	 * @return bool success
	 * @access public
	 * @since 1.0.0
	*/
	public function updateCategories()
	{
		$categoryIDs = array();
		$newRows = array();
		$oldRows = array();
		$dbRows = array();

		$data = $this->callGet('config','/salesCategories?pageSize=50');

		$count = count( $data );

		if ( is_array( $data ) && $count > 0 )
		{
			foreach( $data as $row )
			{
				$categoryID = $row['gid'];
				$categoryText = $row['name'];

				$categoryIDs[]  = $categoryID;
				$newRows[ $categoryID ] = array('gid' => $categoryID, 'name' => $categoryText);
			}

			$this->DB->query("SELECT * FROM menu_category;");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					$dbRows[ $row['category_id'] ] = $row;
					
					if ( isset( $row['category_id'] ) && isset( $newRows[ $row['asana_gid'] ] ) )
					{
						$oldRows[ $row['asana_gid'] ] = $newRows[ $row['asana_gid'] ];
						$oldRows[ $row['asana_gid'] ]['category_id'] = $row['category_id'];
						unset( $newRows[ $row['asana_gid'] ] );
					}
				}
			}

			if ( count( $newRows ) > 0 )
			{
				$this->DB->query(
					"SELECT IF( MAX(position), MAX(position) + 1, 1 ) as new_position FROM menu_category;"
				);

				$position = $this->DB->fetchRow();

				$this->DB->query(
					"SELECT IF( MAX(category_id), MAX(category_id) + 1, 1 ) as new_category_id FROM menu_category;"
				);

				$categoryID = $this->DB->fetchRow();

				$queryInsert = "INSERT INTO menu_category (category_id,title,asana_gid,active,type,position) VALUES ";
				$queryUpdate = "UPDATE menu_category SET asana_gid = ";
				
				foreach( $newRows as $row )
				{
					$check = false;
					
					foreach( $dbRows as $test )
					{
						if ( $test['asana_gid'] == '' && $test['title'] == $row['name'] )
						{
							$this->DB->query( $queryUpdate . "'{$row['gid']}' WHERE category_id={$test['category_id']};" );
							$check = true;
						}
					}

					if ( $check == false )
					{
						$queryInsert .= "('{$categoryID['new_category_id']}','{$row['name']}','{$row['gid']}',1,'na',{$position['new_position']}),";
						$categoryID['new_category_id'] += 1;
						$position['new_position'] += 1;
					}
				}
				
				$queryInsert = substr($queryInsert,0,-1) . ";";
				
				$this->DB->query( $queryInsert );
			}

			if ( count( $oldRows ) > 0 )
			{
				foreach( $oldRows as $row )
				{
					if ( $dbRows[ $row['category_id'] ]['title'] <> $row['name'] )
					{
						$this->DB->query("UPDATE menu_category SET title = '{$row['name']}' WHERE asana_gid = '{$row['gid']}';");
					}
				}
			}

			$this->DB->query("UPDATE menu_category SET active = 0;");
			$this->DB->query("UPDATE menu_category SET active = 1 WHERE asana_gid IN('". implode("','", $categoryIDs) ."');");

			$this->cache->update('categories');
		}

		return $count;
	}

	/**
	 * Call the Asana API to retrieve the Workspaces
	 *
	 * @return int count of retrieved items
	 * @access public
	 * @since 1.0.0
	*/
	public function updateWorkspaces()
	{
		$workspaceIDs = array();
		$newRows = array();
		$oldRows = array();
		$dbRows = array();
		$count = 0;

		$data = array();

		do
		{
			if ( isset($data['next_page']) && is_array($data['next_page']) )
			{
				$data = $this->callGet('next_page', $data['next_page']['path']);
			}
			else
			{
				$data = $this->callGet('workspaces','?limit=50');
			}

			if ( isset($data['data']) && is_array($data['data']) && count($data['data']) > 0 )
			{
				foreach( $data as $row )
				{
					$workspaceIDs[]  = $row['gid'];
					$newRows[ $discountID ] = array(
						'workspace_gid'   => $row['gid'],
						'name'            => $row['name'],
						'is_organization' => ( $row['is_organization'] ? 1 : 0 )
					);
				}

				$this->DB->query("SELECT * FROM workspace;");

				if ( $this->DB->getTotalRows() )
				{
					while( $row = $this->DB->fetchRow() )
					{
						$dbRows[ $row['workspace_gid'] ] = $row;

						if ( isset( $row['workspace_gid'] ) && isset( $newRows[ $row['workspace_gid'] ] ) )
						{
							$oldRows[ $row['workspace_gid'] ] = $newRows[ $row['workspace_gid'] ];
							unset( $newRows[ $row['workspace_gid'] ] );
						}
					}
				}

				if ( count( $newRows ) > 0 )
				{
					$query = "INSERT INTO workspace (workspace_gid,name,is_organization) VALUES ";

					foreach( $newRows as $row )
					{
						$query .= "('{$row['workspace_gid']}',\"{$row['name']}\",'{$row['is_organization']}'),";
					}

					$query = substr($query,0,-1) . ";";

					$this->DB->query( $query );
				}

				if ( count( $oldRows ) > 0 )
				{
					foreach( $oldRows as $row )
					{
						$this->DB->query("UPDATE workspace SET name = \"{$row['name']}\", is_organization = '{$row['is_organization']}', last_update = NOW() WHERE workspace_gid = '{$row['workspace_gid']}';");
					}
				}
			}
		} while( isset($data['next_page']) && is_array($data['next_page']) );

		return $count;
	}

	/**
	 * Call the Asana API to retrieve the latest Menu Items
	 *
	 * @return bool success
	 * @access public
	 * @since 1.0.0
	*/
	public function updateMenuItems()
	{
		$date = date("Y-m-d H:i:s");
		$time = strtotime($date);
		$time = $time - (20 * 60);
		$date = date("Y-m-d", $time)."T".date("H:i:s.000O", $time);

		$items = $this->getMenuItems( 200, 0, $date );

		$this->processMenuItems( $items, TRUE );

		$count = count( $items );

		if ( $count > 0 )
		{
			$this->DB->query("INSERT INTO logs (type,total,result,date_time) VALUES ('items',{$count},1,'".date("Y-m-d H:i:s")."');");
		}

		$this->cache->update('items');

		return $count;
	}

	/**
	 * Call the Asana API to retrieve the latest Orders
	 *
	 * @return bool success
	 * @access public
	 * @since 1.0.0
	*/
	public function updateOrders()
	{
		$date = date("Y-m-d H:i:s");
		$time = strtotime($date);
		$end = date("Y-m-d", $time)."T".date("H:i:s.000O", $time);
		$time = $time - (20 * 60);
		$start = date("Y-m-d", $time)."T".date("H:i:s.000O", $time);

		$orders = $this->getOrdersBySpan( $start, $end );

		if ( is_array( $orders ) && count( $orders ) > 0 )
		{
			$this->processOrders( $orders );
		}

		$count = count( $orders );

		if ( $count > 0 )
		{
			$this->DB->query("INSERT INTO logs (type,total,result,date_time) VALUES ('transactions',{$count},1,'".date("Y-m-d H:i:s")."');");
		}

		return $count;
	}
}