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
				$fields = (strlen($method) > 0 && ( strpos('?', $method) > 0 || substr($method,0,1) == '?' ) ? '&' : '?') . 'opt_fields=' . implode(',',$this->endpoints[ $endpoint ]['fields']);
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
	 * Get an array of the workspaces to create a dropdown or multi-select
	 *
	 * @return array the workspaces
	 * @access public
	 * @since 1.0.0
	 */
	public function getWorkspacesDropdown()
	{
		$out = array();
		$cache = $this->cache->getCache('workspaces');

		if ( count( $cache ) > 0 )
		{
			foreach( $cache as $item )
			{
				$out[ $item['workspace_gid'] ] = array( $item['workspace_gid'], $item['name'] );
			}
		}

		return $out;
	}

	/**
	 * Parse an Asana date to MySQL safe
	 *
	 * @param string $date the date parse
	 * @return string the parseddate
	 * @access protected
	 * @since 1.0.0
	 */
	protected function parseDate( $date )
	{
		$date = substr($date,0,10) . ' ' . substr($date,11,8);

		return date_create( $date )->format("Y-m-d H:i:s");
	}

	/**
	 * Call the Asana API to retrieve the Tags
	 *
	 * @return int count of retrieved items
	 * @access public
	 * @since 1.0.0
	*/
	public function updateTag()
	{
		$tagIDs = array();
		$newRows = array();
		$oldRows = array();
		$dbRows = array();
		$count = 0;
		$workspace = $this->registry->getSetting('asana_default');

		$data = array();

		do
		{
			if ( isset($data['next_page']) && is_array($data['next_page']) )
			{
				$data = $this->callGet('next_page', $data['next_page']['path']);
			}
			else
			{
				$data = $this->callGet('tags',"?limit=50&workspace={$workspace}");
			}

			if ( isset($data['data']) && is_array($data['data']) && count($data['data']) > 0 )
			{
				foreach( $data['data'] as $row )
				{
					$count++;
					$tagID = $row['gid'];
					$followers = array();

					if ( is_array($row['followers']) && count($row['followers']) > 0 )
					{
						foreach( $row['followers'] as $follower )
						{
							$folowers[] = $followers['gid'];
						}
					}

					$tagIDs[]  = $tagID;
					$newRows[ $tagID ] = array(
						'tag_gid'       => $tagID,
						'workspace_gid' => $workspace,
						'color'         => $row['color'],
						'created_at'    => $this->parseDate( $row['created_at'] ),
						'followers'     => mysql_real_escape_string( serialize($followers) ),
						'name'          => $row['name']
					);
				}

				$this->DB->query("SELECT * FROM tag;");

				if ( $this->DB->getTotalRows() )
				{
					while( $row = $this->DB->fetchRow() )
					{
						$dbRows[ $row['tag_gid'] ] = $row;

						if ( isset( $row['tag_gid'] ) && isset( $newRows[ $row['taggid'] ] ) )
						{
							$oldRows[ $row['tag_gid'] ] = $newRows[ $row['tag_gid'] ];
							unset( $newRows[ $row['tag_gid'] ] );
						}
					}
				}

				if ( count( $newRows ) > 0 )
				{
					$query = "INSERT INTO tag (tag_gid,workspace_gid,color,created_at,followers,name) VALUES ";

					foreach( $newRows as $row )
					{
						$query .= "('{$row['tag_gid']}','{$row['workspace_gid']}','{$row['color']}','{$row['created_at']}',\"{$row['followers']}\",\"{$row['name']}\"),";
					}

					$query = substr($query,0,-1) . ";";

					$this->DB->query( $query );
				}

				if ( count( $oldRows ) > 0 )
				{
					foreach( $oldRows as $row )
					{
						$this->DB->query("UPDATE tag SET workspace_gid = '{$row['workspace_gid']}', color = '{$row['color']}', created_at = '{$row['created_at']}', followers = \"{$row['followers']}\", name = \"{$row['name']}\", last_update = NOW() WHERE tag_gid = '{$row['tag_gid']}';");
					}
				}
			}
		} while( isset($data['next_page']) && is_array($data['next_page']) );

		$this->cache->update('tags');

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
				foreach( $data['data'] as $row )
				{
					$count++;
					$workspaceID = $row['gid'];

					$workspaceIDs[]  = $workspaceID;
					$newRows[ $workspaceID ] = array(
						'workspace_gid'   => $workspaceID,
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

		$this->cache->update('workspaces');

		return $count;
	}
}