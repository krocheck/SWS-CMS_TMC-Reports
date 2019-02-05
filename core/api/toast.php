<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Untappd API Library
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
class ToastAPI extends Command
{
	protected $apiURL = "";
	protected $clientID = "";
	private $config = array(
		'sql_host'        => 'localhost',
		'sql_database'    => 'stubbysdev_toast1',
		'sql_user'        => 'stubbysdev_toast',
		'sql_pass'        => '6D%oZ^o?e^sp'
	);
	protected $endpoints = array();
	protected $httpCode;
	protected $location = "";
	private $toastDB;
	protected $token = "";
	protected $userAgent = "StubClub-API-1.0";

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
		$this->apiURL = $this->registry->getSetting('toast_url');
		$this->clientID = $this->registry->getSetting('toast_client_id');

		$this->config['toast_secret'] = urlencode("w8Qvq38y47eN&vbp@SZx");

		$this->endpoints = array(
			'auth'    => $this->registry->getSetting('toast_auth'),
			'orders'  => $this->registry->getSetting('toast_orders'),
			'config'  => $this->registry->getSetting('toast_config'),
			'crm'     => $this->registry->getSetting('toast_crm')
		);

		$this->location = $this->registry->getSetting('toast_location');
		$this->token = $this->registry->getSetting('toast_token');

		$this->toastDB = new Database( $this->registry, $this->config, 'toast' );
	}

	/**
	 * CURL authentication scheme
	 *
	 * @return bool result
	 * @access private
	 * @since 1.0.0
	 */
	private function auth()
	{
		$out = false;

		$curl2 = curl_init();
		$url = $this->apiURL . $this->endpoints['auth'];
		$parameters = "grant_type=client_credentials&client_id={$this->clientID}&client_secret={$this->config['toast_secret']}";

		curl_setopt($curl2, CURLOPT_POST, true);
		curl_setopt($curl2, CURLOPT_POSTFIELDS, $parameters);

		curl_setopt($curl2, CURLOPT_URL, $url);
		curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl2, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl2, CURLOPT_USERAGENT, $this->userAgent);
		$result = curl_exec($curl2);

		$httpCode = curl_getinfo($curl2, CURLINFO_HTTP_CODE);
		$this->http_code = $httpCode;

		if ( $httpCode == 200 )
		{
			$result = json_decode( $result, TRUE );

			if ( is_array( $result ) & isset( $result['access_token'] ) )
			{
				$this->token = $result['access_token'];
				$this->registry->updateSetting( 'toast_token', $this->token );
				$out = TRUE;
			}
		}
		else
		{
			$this->display->addDebug( array( 'url' => $url, 'params' => $parameters, 'http_code' => $httpCode, 'result' => $result ) );
		}

		return $out;
 	}

	/**
	 * Basic CURL request which connects to the Toast API and returns the result
	 *
	 * @param $endpoint string the endpoint being used
	 * @param $method string the addtional query string
	 * @param $break bool whether or not to break recurrsion
	 * @return bool result
	 * @access protected
	 * @since 1.0.0
	*/
	protected function callGet( $endpoint, $method, $break = FALSE )
	{
		$out = array();

		$curl2 = curl_init();
		$url = $this->apiURL . $this->endpoints[ $endpoint ] . $method;

		curl_setopt($curl2, CURLOPT_URL, $url);
		curl_setopt($curl2, CURLOPT_HTTPHEADER, array( "Content-Type: application/json", "Authorization: Bearer {$this->token}", "Toast-Restaurant-External-ID: {$this->location}" ) );
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
		else if ( $httpCode == 401 && $break == FALSE )
		{
			if ( $this->auth() )
			{
				$out = $this->callGet( $endpoint, $method, TRUE );
			}
		}
		else
		{
			$this->display->addDebug( array( 'url' => $url, 'http_code' => $httpCode, 'result' => $result ) );
		}

		return $out;
	}

	/**
	 * Basic CURL post which connects to the Toast API and returns the result
	 *
	 * @param $endpoint string the endpoint being used
	 * @param $method string the addtional query string
	 * @param $break bool whether or not to break recurrsion
	 * @return bool result
	 * @access protected
	 * @since 1.0.0
	*/
	protected function callPost( $endpoint, $method, $params, $break = FALSE )
	{
		$out = array();

		$curl2 = curl_init();
		$url = $this->apiURL . $this->endpoints[ $endpoint ] . $method;
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
		curl_setopt($curl2, CURLOPT_HTTPHEADER, array( "Authorization: Bearer {$this->token}", "Toast-Restaurant-External-ID: {$this->location}" ) );
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
		else if ( $httpCode == 401 && $break == FALSE )
		{
			if ( $this->auth() )
			{
				$out = $this->callGet( $endpoint, $method, $params, TRUE );
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
	 * @return string a pseudo-random GUID
	 * @access protected
	 * @since 1.0.0
	 */
	public static function generateGUID() 
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
	 * Call the Toast API to retrieve the specified Menu Group
	 *
	 * @param $guid string the GUID to query
	 * @return array the salesCategories
	 * @access public
	 * @since 1.0.0
	*/
	protected function getMenuGroup( $guid )
	{
		$out = array();
		$menuGroup = $this->callGet('config',"/menuGroups/{$guid}");
		
		if ( is_array( $menuGroup ) && isset( $menuGroup['items'] ) && is_array( $menuGroup['items'] ) )
		{
			$out = $menuGroup['items'];
		}
		
		return $out;
	}

	/**
	 * Call the Toast API to retrieve the specified Menu Group
	 *
	 * @param $guid int the number of results to return
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
	 * Call the Toast API to retrieve the specified order
	 *
	 * @param $guid string the date/time to return changes after is ISO-8601 format
	 * @return array the order GUIDs
	 * @access protected
	 * @since 1.0.0
	*/
	protected function getOrder( $guid )
	{
		$out = array();
		$order = $this->callGet('orders',"/orders/{$guid}");

		if ( is_array( $order ) )
		{
			$out = $order;
		}

		return $out;
	}

	/**
	 * Call the Toast API to retrieve the specified orders
	 *
	 * @param $date string the date/time to return changes after in YYYYMMDD format
	 * @return array the order GUIDs
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
	 * Call the Toast API to retrieve the specified orders
	 *
	 * @param $start string the date/time to start the range in ISO-8601 format
	 * @param $end string the date/time to end the range in ISO-8601 format
	 * @return array the order GUIDs
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
		$guids   = array();
		$newRows = array();
		$oldRows = array();
		$total = 0;

		foreach( $data as $row )
		{
			$itemID = intval($row['sku']);
			$text = $row['name'];
			$guid = $row['guid'];
			//$categoryID = $row[2];
			//$active = $row[3];
			//$price = $row[4];

			if ( $itemID <> 0 )
			{
				$itemIDs[] = $itemID;
			}

			$guids[] = $guid;
			$newRows[ $guid ] = array( 'menu_item_id' => $itemID, 'guid' => $guid, 'title' => $text);

			$total++;
		}

		$total = count( $guids );

		if ( count( $itemIDs) > 0 || count( $guids ) > 0 )
		{
			$this->DB->query("SELECT menu_item_id,toast_guid FROM menu_item WHERE menu_item_id IN('".implode("','",$itemIDs)."') OR toast_guid IN('".implode("','",$guids)."');");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					if ( isset( $row['toast_guid'] ) && strlen( $row['toast_guid'] ) > 0 )
					{
						$oldRows[ $row['toast_guid'] ] = $newRows[ $row['toast_guid'] ];
						unset( $newRows[ $row['toast_guid'] ] );
					}
					else if ( isset( $row['menu_item_id'] ) && $row['menu_item_id'] > 0 )
					{
						foreach( $newRows as $test )
						{
							if ( isset( $test['menu_item_id'] ) && $test['menu_item_id'] > 0 && $row['menu_item_id'] == $test['menu_item_id'] )
							{
								$oldRows[ $test['guid'] ] = $newRows[ $test['guid'] ];
								unset( $newRows[ $test['guid'] ] );
								break;
							}
						}
					}
				}
			}
		}

		if ( count( $newRows ) > 0 )
		{
			$query = "INSERT INTO menu_item (toast_guid,title".($updateTimestamp ? ",last_modified" : '').") VALUES ";

			foreach( $newRows as $row )
			{
				$query .= "('{$row['guid']}',\"{$row['title']}\"".($updateTimestamp ? ",NOW()" : '')."),";
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
					$this->DB->query("UPDATE menu_item SET title = \"{$row['title']}\", toast_guid = '{$row['guid']}'".($updateTimestamp ? ", last_modified = NOW()" : '')." WHERE menu_item_id = '{$row['menu_item_id']}';");
				}
				else if ( isset( $row['guid'] ) && strlen( $row['guid'] ) > 0 )
				{
					$this->DB->query("UPDATE menu_item SET title = \"{$row['title']}\"".($updateTimestamp ? ", last_modified = NOW()" : '')." WHERE toast_guid = '{$row['guid']}';");
				}
			}
		}

		return $total;
	}

	/**
	 * Updates/adds the passed order guids into the database
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

		$orderGUIDs     = array();
		$orders         = array();
		$checkGUIDs     = array();
		$checks         = array();
		$selectionGUIDs = array();
		$selections     = array();
		
		foreach( $data as $row )
		{
			if ( strlen( $row ) > 1 )
			{
				$order = $this->getOrder( $row );

				if ( is_array( $order ) && isset( $order['guid'] ) && $row = $order['guid'] )
				{
					$orderGUID = $order['guid'];
					$void = ( $order['voided'] == 'true' ? 1 : 0 );
					$deleted = ( $order['deleted'] == 'true' ? 1 : 0 );
					$businessDate = date_create( $order['businessDate'] )->format("Y-m-d");
					$createdDate = date_create( $order['openedDate'] )->format("Y-m-d H:i:s");
					$closedDate = ( $order['closedDate'] <> '' ? date_create( $order['closedDate'] )->format("Y-m-d H:i:s") : '0000-00-00 00:00:00' );

					$orderGUIDs[] = $orderGUID;

					$orders[ $orderGUID ] = array(
						'order_guid'    => $orderGUID,
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
							if ( is_array( $check ) && isset( $check['guid'] ) && $row = $check['guid'] )
							{
								$checkGUID = $check['guid'];
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
										$appliedDiscounts[ $discount['guid'] ] = $discount;
									}
								}

								$checkGUIDs[] = $checkGUID;

								$checks[ $checkGUID ] = array(
									'check_guid'        => $checkGUID,
									'order_guid'        => $orderGUID,
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
										if ( is_array( $item ) && isset( $item['guid'] ) && $row = $item['guid'] )
										{
											$selectionGUID = $item['guid'];
											$itemGUID = $item['item']['guid'];
											$categoryGUID = $item['salesCategory']['guid'];
											$void = ( $item['voided'] == 'true' ? 1 : 0 );
											$createdDate = date_create( $item['createdDate'] )->format("Y-m-d H:i:s");
											$price = $item['price'] + $item['taxAmount'];
											$quantity = $item['quantity'];
											$appliedDiscounts = array();

											if ( is_array( $check['appliedDiscounts'] ) && count( $check['appliedDiscounts'] ) > 0 )
											{
												foreach( $check['appliedDiscounts'] as $discount )
												{
													$appliedDiscounts[ $discount['guid'] ] = $discount;
												}
											}

											$selectionGUIDs[] = $selectionGUID;

											$selections[ $selectionGUID ] = array(
												'selection_guid'    => $selectionGUID,
												'check_guid'        => $checkGUID,
												'item_guid'         => $itemGUID,
												'category_guid'     => $categoryGUID,
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
		$guids   = $orderGUIDs;

		if ( count( $guids ) > 0 )
		{
			$this->toastDB->query("SELECT order_guid FROM `order` WHERE order_guid IN('".implode("','",$guids)."');");

			if ( $this->toastDB->getTotalRows() )
			{
				while( $row = $this->toastDB->fetchRow() )
				{
					if ( isset( $row['order_guid'] ) && strlen( $row['order_guid'] ) > 0 )
					{
						$oldRows[ $row['order_guid'] ] = $newRows[ $row['order_guid'] ];
						unset( $newRows[ $row['order_guid'] ] );
					}
				}
			}
		}

		if ( count( $newRows ) > 0 )
		{
			$query = "INSERT INTO `order` (order_guid,void,deleted,business_date,opened_date,closed_date) VALUES ";

			foreach( $newRows as $row )
			{
				$query .= "('{$row['order_guid']}','{$row['void']}','{$row['deleted']}','{$row['business_date']}','{$row['opened_date']}','{$row['closed_date']}'),";
			}

			$query = substr($query,0,-1) . ";";

			$this->toastDB->query( $query );
		}

		if ( count( $oldRows ) > 0 )
		{
			foreach( $oldRows as $row )
			{
				$this->toastDB->query("UPDATE `order` SET void = '{$row['void']}', deleted = '{$row['deleted']}', business_date = '{$row['business_date']}', opened_date = '{$row['opened_date']}', closed_date = '{$row['closed_date']}' WHERE order_guid = '{$row['order_guid']}';");
			}
		}

		$oldRows = array();
		$newRows = $checks;
		$guids   = $checkGUIDs;

		if ( count( $guids ) > 0 )
		{
			$this->toastDB->query("SELECT check_guid FROM `check` WHERE check_guid IN('".implode("','",$guids)."');");

			if ( $this->toastDB->getTotalRows() )
			{
				while( $row = $this->toastDB->fetchRow() )
				{
					if ( isset( $row['check_guid'] ) && strlen( $row['check_guid'] ) > 0 )
					{
						$oldRows[ $row['check_guid'] ] = $newRows[ $row['check_guid'] ];
						unset( $newRows[ $row['check_guid'] ] );
					}
				}
			}
		}

		if ( count( $newRows ) > 0 )
		{
			$query = "INSERT INTO `check` (check_guid,order_guid,void,deleted,opened_date,closed_date,tab_name,club_id,display_number,applied_discounts) VALUES ";

			foreach( $newRows as $row )
			{
				$query .= "('{$row['check_guid']}','{$row['order_guid']}','{$row['void']}','{$row['deleted']}','{$row['opened_date']}','{$row['closed_date']}',\"{$row['tab_name']}\",'{$row['club_id']}','{$row['display_number']}',\"{$row['applied_discounts']}\"),";
			}

			$query = substr($query,0,-1) . ";";

			$this->toastDB->query( $query );
		}

		if ( count( $oldRows ) > 0 )
		{
			foreach( $oldRows as $row )
			{
				$this->toastDB->query("UPDATE `check` SET order_guid = '{$row['order_guid']}', void = '{$row['void']}', deleted = '{$row['deleted']}', opened_date = '{$row['opened_date']}', closed_date = '{$row['closed_date']}', tab_name = \"{$row['tab_name']}\", club_id = '{$row['club_id']}', display_number = '{$row['display_number']}', applied_discounts = \"{$row['applied_discounts']}\" WHERE check_guid = '{$row['check_guid']}';");
			}
		}

		$oldRows = array();
		$newRows = $selections;
		$guids   = $selectionGUIDs;

		if ( count( $guids ) > 0 )
		{
			$this->toastDB->query("SELECT selection_guid FROM `selection` WHERE selection_guid IN('".implode("','",$guids)."');");

			if ( $this->toastDB->getTotalRows() )
			{
				while( $row = $this->toastDB->fetchRow() )
				{
					if ( isset( $row['selection_guid'] ) && strlen( $row['selection_guid'] ) > 0 )
					{
						$oldRows[ $row['selection_guid'] ] = $newRows[ $row['selection_guid'] ];
						unset( $newRows[ $row['selection_guid'] ] );
					}
				}
			}
		}

		if ( count( $newRows ) > 0 )
		{
			$query = "INSERT INTO `selection` (selection_guid,check_guid,item_guid,category_guid,void,created_date,price,quantity,applied_discounts) VALUES ";

			foreach( $newRows as $row )
			{
				$query .= "('{$row['selection_guid']}','{$row['check_guid']}','{$row['item_guid']}','{$row['category_guid']}','{$row['void']}','{$row['created_date']}','{$row['price']}','{$row['quantity']}',\"{$row['applied_discounts']}\"),";
			}

			$query = substr($query,0,-1) . ";";

			$this->toastDB->query( $query );
		}

		if ( count( $oldRows ) > 0 )
		{
			foreach( $oldRows as $row )
			{
				$this->toastDB->query("UPDATE `selection` SET check_guid = '{$row['check_guid']}', item_guid = '{$row['item_guid']}', category_guid = '{$row['category_guid']}', void = '{$row['void']}', created_date = '{$row['created_date']}', price = '{$row['price']}', quantity = '{$row['quantity']}', applied_discounts = \"{$row['applied_discounts']}\" WHERE selection_guid = '{$row['selection_guid']}';");
			}
		}

		$this->toastDB->query("SELECT `selection`.`selection_guid`, `selection`.`item_guid`, `selection`.`category_guid`, 
									IF(`selection`.`void` = 1 OR `check`.`void` = 1 OR `check`.`deleted` = 1 OR `order`.`void` = 1 OR `order`.`deleted` = 1,1,0) AS `void`, 
									`selection`.`created_date` AS `date_time`, `selection`.`quantity`, `check`.`club_id`, `order`.`business_date`
								FROM `selection`
									INNER JOIN `check` ON `selection`.`check_guid`=`check`.`check_guid`
									INNER JOIN `order` ON `check`.`order_guid`=`order`.`order_guid`
								WHERE `selection`.`rewarded` = 0 AND `check`.`closed_date` > 0 AND `check`.`club_id` > 0
								ORDER BY `selection`.`created_date` ASC");

		$closedTransactions = array();

		if ( $this->toastDB->getTotalRows() > 0 )
		{
			while( $r = $this->toastDB->fetchRow() )
			{
				$closedTransactions[ $r['selection_guid'] ] = array(
					'selection_guid' => $r['selection_guid'],
					'category_guid' => $r['category_guid'],
					'date_time' => $r['date_time'],
					'business_date' => $r['business_date'],
					'menu_id' => $items[ $r['item_guid'] ],
					'club_id' => $r['club_id'],
					'quantity' =>$r['quantity'],
					'void' => $r['void']
				);
			}
		}

		if ( count( $closedTransactions ) > 0 )
		{
			$query = "INSERT INTO `transaction` (selection_guid,category_guid,date_time,business_date,menu_id,club_id,quantity,void) VALUES ";
			$query2 = "UPDATE `selection` SET `rewarded` = 1 WHERE selection_guid IN(";

			foreach( $closedTransactions as $row )
			{
				$query .= "('{$row['selection_guid']}','{$row['category_guid']}','{$row['date_time']}','{$row['business_date']}','{$row['menu_id']}','{$row['club_id']}','{$row['quantity']}','{$row['void']}'),";
				$query2 .="'{$row['selection_guid']}',";
			}

			$query = substr($query, 0, -1) . ";";
			$query2 = substr($query2, 0, -1) . ");";

			$this->DB->query( $query );
			$this->toastDB->query( $query2 );
		}

		$this->toastDB->query("SELECT `selection`.`selection_guid`, `selection`.`item_guid`, `selection`.`category_guid`, 
									`selection`.`created_date` AS `date_time`, `check`.`club_id`, `selection`.`quantity`, `selection`.`void`
								FROM `selection`
									INNER JOIN `check` ON `selection`.`check_guid`=`check`.`check_guid`
									INNER JOIN `order` ON `check`.`order_guid`=`order`.`order_guid`
								WHERE `selection`.`rewarded` = 0 AND `check`.`closed_date` = 0 AND `check`.`club_id` > 0 AND `selection`.`void` = 0 AND `check`.`void` = 0 AND `check`.`deleted` = 0 AND `order`.`void` = 0 AND `order`.`deleted` = 0
								ORDER BY `selection`.`created_date` ASC");

		$pendingTransactions = array();

		if ( $this->toastDB->getTotalRows() > 0 )
		{
			while( $r = $this->toastDB->fetchRow() )
			{
				$pendingTransactions[ $r['selection_guid'] ] = array(
					'selection_guid' => $r['selection_guid'],
					'category_guid' => $r['category_guid'],
					'date_time' => $r['date_time'],
					'menu_id' => $items[ $r['item_guid'] ],
					'club_id' => $r['club_id'],
					'quantity' =>$r['quantity'],
					'void' => $r['void']
				);
			}
		}

		$this->DB->query("TRUNCATE `pending_transaction`;");

		if ( count( $pendingTransactions ) > 0 )
		{
			$query = "INSERT INTO `pending_transaction` (selection_guid,category_guid,date_time,menu_id,club_id,quantity,void) VALUES ";

			foreach( $pendingTransactions as $row )
			{
				$query .= "('{$row['selection_guid']}','{$row['category_guid']}','{$row['date_time']}','{$row['menu_id']}','{$row['club_id']}','{$row['quantity']}','{$row['void']}'),";
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
			$catIDs[] = $cat['toast_guid'];
		}

		$catIDs = "'" . implode("','", $catIDs ) . "'";

		$this->DB->query(
			"SELECT t.*, m.points
				FROM transaction t
				LEFT JOIN reward r ON (t.reward_id = r.reward_id)
				INNER JOIN menu_item m ON (t.menu_id = m.menu_item_id)
				WHERE r.reward_id IS NULL AND t.void = 0 AND t.web_void = 0 AND t.excluded = 0 AND t.category_guid IN ({$catIDs})
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
						if ( $r['category_guid'] == $cat['toast_guid'] && $cat['type'] == 'tap' )
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
	 * Call the Toast API to retrieve all of Menu Items
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
	 * Call the Toast API to retrieve the specified day's Orders
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
	 * Call the Toast API to retrieve today's Orders
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
	 * Call the Toast API to retrieve yesterday's Orders
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
			if ( isset( $cat['menu_guid'] ) && strlen( $cat['menu_guid'] ) > 0 )
			{
				$catItems = array();

				$items = $this->getMenuGroup( $cat['menu_guid'] );

				if ( is_array( $items ) && count( $items ) > 0 )
				{
					foreach( $items as $item )
					{
						$activeItems[] = $item['guid'];
						$catItems[] = $item['guid'];
						$out++;
					}
				}

				if ( count( $catItems ) > 0 )
				{
					$this->DB->query( $updateSalesCategory . $cat['category_id'] . " WHERE toast_guid IN('".implode("','",$catItems)."');");
				}
			}
		}

		if ( count( $activeItems ) > 0 )
		{
			$this->DB->query("UPDATE menu_item SET active = 1 WHERE toast_guid IN('".implode("','",$activeItems)."');");
		}

		if ( $out > 0 )
		{
			$this->DB->query("INSERT INTO logs (type,total,result,date_time) VALUES ('prices',{$out},1,'".date("Y-m-d H:i:s")."');");
		}

		return $out;
	}

	/**
	 * Call the Toast API to retrieve the Sales Categories
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
				$categoryID = $row['guid'];
				$categoryText = $row['name'];

				$categoryIDs[]  = $categoryID;
				$newRows[ $categoryID ] = array('guid' => $categoryID, 'name' => $categoryText);
			}

			$this->DB->query("SELECT * FROM menu_category;");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					$dbRows[ $row['category_id'] ] = $row;
					
					if ( isset( $row['category_id'] ) && isset( $newRows[ $row['toast_guid'] ] ) )
					{
						$oldRows[ $row['toast_guid'] ] = $newRows[ $row['toast_guid'] ];
						$oldRows[ $row['toast_guid'] ]['category_id'] = $row['category_id'];
						unset( $newRows[ $row['toast_guid'] ] );
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

				$queryInsert = "INSERT INTO menu_category (category_id,title,toast_guid,active,type,position) VALUES ";
				$queryUpdate = "UPDATE menu_category SET toast_guid = ";
				
				foreach( $newRows as $row )
				{
					$check = false;
					
					foreach( $dbRows as $test )
					{
						if ( $test['toast_guid'] == '' && $test['title'] == $row['name'] )
						{
							$this->DB->query( $queryUpdate . "'{$row['guid']}' WHERE category_id={$test['category_id']};" );
							$check = true;
						}
					}

					if ( $check == false )
					{
						$queryInsert .= "('{$categoryID['new_category_id']}','{$row['name']}','{$row['guid']}',1,'na',{$position['new_position']}),";
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
						$this->DB->query("UPDATE menu_category SET title = '{$row['name']}' WHERE toast_guid = '{$row['guid']}';");
					}
				}
			}

			$this->DB->query("UPDATE menu_category SET active = 0;");
			$this->DB->query("UPDATE menu_category SET active = 1 WHERE toast_guid IN('". implode("','", $categoryIDs) ."');");

			$this->cache->update('categories');
		}

		return $count;
	}

	/**
	 * Call the Toast API to retrieve the Discounts
	 *
	 * @return bool success
	 * @access public
	 * @since 1.0.0
	*/
	public function updateDiscounts()
	{
		$discountIDs = array();
		$newRows = array();
		$oldRows = array();
		$dbRows = array();

		$data = $this->callGet('config','/discounts?pageSize=50');

		$count = count( $data );

		if ( is_array( $data ) && $count > 0 )
		{
			foreach( $data as $row )
			{
				$discountID = $row['guid'];
				$discountText = $row['name'];
				$active = ( $row['active'] ? 1 : 0 );
				$amount = $row['amount'];
				$percentage = $row['percentage'];
				$type = $row['type'];
				$selectionType = $row['selectionType'];

				$discountIDs[]  = $discountID;
				$newRows[ $discountID ] = array(
					'toast_guid' => $discountID,
					'title' => $discountText,
					'active' => $active,
					'amount' => $amount,
					'percentage' => $percentage,
					'type' => $type,
					'selection_type' => $selectionType
				);
			}

			$this->DB->query("SELECT * FROM discount;");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					$dbRows[ $row['toast_guid'] ] = $row;

					if ( isset( $row['toast_guid'] ) && isset( $newRows[ $row['toast_guid'] ] ) )
					{
						$oldRows[ $row['toast_guid'] ] = $newRows[ $row['toast_guid'] ];
						unset( $newRows[ $row['toast_guid'] ] );
					}
				}
			}

			if ( count( $newRows ) > 0 )
			{
				$query = "INSERT INTO discount (toast_guid,title,active,amount,percentage,type,selection_type,exclude) VALUES ";

				foreach( $newRows as $row )
				{
					$query .= "('{$row['toast_guid']}',\"{$row['title']}\",'{$row['active']}','{$row['amount']}','{$row['percentage']}','{$row['type']}','{$row['selection_type']}',0),";
				}

				$query = substr($query,0,-1) . ";";

				$this->DB->query( $query );
			}

			if ( count( $oldRows ) > 0 )
			{
				foreach( $oldRows as $row )
				{
					$this->DB->query("UPDATE discount SET title = \"{$row['title']}\", active = '{$row['active']}', amount = '{$row['amount']}', percentage = '{$row['percentage']}', type = '{$row['type']}', selection_type = '{$row['selection_type']}' WHERE toast_guid = '{$row['toast_guid']}';");
				}
			}

			$this->cache->update('discounts');
		}

		return $count;
	}

	/**
	 * Call the Toast API to retrieve the latest Menu Items
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
	 * Call the Toast API to retrieve the latest Orders
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