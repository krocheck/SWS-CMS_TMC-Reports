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
		require_once( SWS_VENDOR_PATH . 'autoload.php' );

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
				'fields' => array(),
				'expand' => array('gid', 'owner', 'workspace', 'team', 'name', 'current_status', 'due_date', 'start_on', 'created_at', 'modified_at', 'archived', 'public', 'members', 'followers', 'custom_fields', 'custom_field_settings', 'color', 'html_notes', 'layout')
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
				$fields = (strlen($method) > 0 && ( strpos($method, '?') > 0 || substr($method,0,1) == '?' ) ? '&' : '?') . 'opt_expand=' . implode(',',$this->endpoints[ $endpoint ]['expand']);
			}
			else
			{
				$fields = (strlen($method) > 0 && ( strpos($method, '?') > 0 || substr($method,0,1) == '?' ) ? '&' : '?') . 'opt_fields=' . implode(',',$this->endpoints[ $endpoint ]['fields']);
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
	 * Get an array of the custom fields to create a dropdown or multi-select
	 *
	 * @return array the custom fields
	 * @access public
	 * @since 1.0.0
	 */
	public function getFieldsDropdown()
	{
		$out = array();
		$cache = $this->cache->getCache('fields');

		if ( count( $cache ) > 0 )
		{
			foreach( $cache as $item )
			{
				$out[ $item['field_gid'] ] = array( $item['field_gid'], $item['name'] );
			}
		}

		return $out;
	}

	/**
	 * Get an array of the projects to create a dropdown or multi-select
	 *
	 * @return array the workspaces
	 * @access public
	 * @since 1.0.0
	 */
	public function getProjectsDropdown()
	{
		$out = array();
		$cache = $this->cache->getCache('projects');

		if ( count( $cache ) > 0 )
		{
			foreach( $cache as $item )
			{
				$out[ $item['project_gid'] ] = array( $item['project_gid'], $item['name'] );
			}
		}

		return $out;
	}

	/**
	 * Get an array of the tags to create a dropdown or multi-select
	 *
	 * @return array the tags
	 * @access public
	 * @since 1.0.0
	 */
	public function getTagsDropdown()
	{
		$out = array();
		$cache = $this->cache->getCache('tags');

		if ( count( $cache ) > 0 )
		{
			foreach( $cache as $item )
			{
				$out[ $item['tag_gid'] ] = array( $item['tag_gid'], $item['name'] );
			}
		}

		return $out;
	}

	/**
	 * Get an array of the teams to create a dropdown or multi-select
	 *
	 * @return array the teams
	 * @access public
	 * @since 1.0.0
	 */
	public function getTeamsDropdown()
	{
		$out = array();
		$cache = $this->cache->getCache('teams');

		if ( count( $cache ) > 0 )
		{
			foreach( $cache as $item )
			{
				$out[ $item['team_gid'] ] = array( $item['team_gid'], $item['name'] );
			}
		}

		return $out;
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
	 * Get an array of the users to create a dropdown or multi-select
	 *
	 * @return array the users
	 * @access public
	 * @since 1.0.0
	 */
	public function getUsersDropdown()
	{
		$out = array();
		$cache = $this->cache->getCache('users');

		if ( count( $cache ) > 0 )
		{
			foreach( $cache as $item )
			{
				$out[ $item['user_gid'] ] = array( $item['user_gid'], $item['name'] );
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
	 * Call the Asana API to retrieve the Custom Fields
	 *
	 * @return int count of retrieved items
	 * @access public
	 * @since 1.0.0
	*/
	public function updateFields()
	{
		$fieldIDs = array();
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
				$data = $this->callGet('custom_fields',"/{$workspace}/custom_fields?limit=100");
			}

			if ( isset($data['data']) && is_array($data['data']) && count($data['data']) > 0 )
			{
				foreach( $data['data'] as $row )
				{
					$count++;
					$fieldID = $row['gid'];
					$options = array();

					if ( is_array($row['enum_options']) && count($row['enum_options']) > 0 )
					{
						foreach( $row['enum_options'] as $r )
						{
							$options[$r['gid']] = array(
								'color'   => $r['color'],
								'enabled' => $r['enabled'],
								'name'    => $r['name'],
							);
						}
					}

					$fieldIDs[]  = $fieldID;
					$newRows[ $fieldID ] = array(
						'field_gid'        => $fieldID,
						'name'             => $row['name'],
						'resource_subtype' => $row['resource_subtype'],
						'enum_options'     => mysqli_real_escape_string( $this->DB->getConnection(), serialize($options) )
					);
				}
			}
		} while( isset($data['next_page']) && is_array($data['next_page']) );

		if ( $count > 0 )
		{
			$this->DB->query("SELECT * FROM custom_field;");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					$dbRows[ $row['field_gid'] ] = $row;

					if ( isset( $row['field_gid'] ) && isset( $newRows[ $row['field_gid'] ] ) )
					{
						$oldRows[ $row['field_gid'] ] = $newRows[ $row['field_gid'] ];
						unset( $newRows[ $row['field_gid'] ] );
					}
				}
			}

			if ( count( $newRows ) > 0 )
			{
				$query = "INSERT INTO custom_field (field_gid,resource_subtype,enum_options,name) VALUES ";

				foreach( $newRows as $row )
				{
					$query .= "('{$row['field_gid']}','{$row['resource_subtype']}',\"{$row['enum_options']}\",\"{$row['name']}\"),";
				}

				$query = substr($query,0,-1) . ";";

				$this->DB->query( $query );
			}

			if ( count( $oldRows ) > 0 )
			{
				foreach( $oldRows as $row )
				{
					$this->DB->query("UPDATE custom_field SET resource_subtype = '{$row['resource_subtype']}', enum_options = \"{$row['enum_options']}\", name = \"{$row['name']}\", last_update = NOW() WHERE field_gid = '{$row['field_gid']}';");
				}
			}

			// Cleanup orphans
			$this->DB->query("DELETE FROM custom_field WHERE field_gid NOT IN(".implode(',',$fieldIDs).");");
		}

		$this->cache->update('fields');

		return $count;
	}

	/**
	 * Call the Asana API to retrieve the Projects
	 *
	 * @return int count of retrieved items
	 * @access public
	 * @since 1.0.0
	*/
	public function updateProjects()
	{
		$projectIDs = array();
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
				$data = $this->callGet('projects',"?limit=100&workspace={$workspace}");
			}

			if ( isset($data['data']) && is_array($data['data']) && count($data['data']) > 0 )
			{
				foreach( $data['data'] as $row )
				{
					$count++;
					$projectID = $row['gid'];
					$currentStatus = array();
					$followers = array();
					$members = array();
					$fields = array();

					if ( is_array($row['current_status']) && count($row['current_status']) > 0 )
					{
						$currentStatus = $row['current_status'];
					}

					if ( is_array($row['followers']) && count($row['followers']) > 0 )
					{
						foreach( $row['followers'] as $follower )
						{
							$followers[] = $follower['gid'];
						}
					}

					if ( is_array($row['members']) && count($row['members']) > 0 )
					{
						foreach( $row['members'] as $member )
						{
							$members[] = $member['gid'];
						}
					}

					if ( is_array($row['custom_field_settings']) && count($row['custom_field_settings']) > 0 )
					{
						foreach( $row['custom_field_settings'] as $field )
						{
							$fields[] = $field['custom_field']['gid'];
						}
					}

					$projectIDs[]  = $projectID;
					$newRows[ $projectID ] = array(
						'project_gid'           => $projectID,
						'owner_gid'             => $row['owner']['gid'],
						'workspace_gid'         => $workspace,
						'team_gid'              => $row['team']['gid'],
						'name'                  => $row['name'],
						'current_status'        => mysqli_real_escape_string( $this->DB->getConnection(), serialize($currentStatus) ),
						'due_date'              => $row['due_date'],
						'start_on'              => $row['start_on'],
						'created_at'            => $this->parseDate( $row['created_at'] ),
						'modified_at'           => $this->parseDate( $row['modified_at'] ),
						'archived'              => ( $row['archived'] ? 1 : 0 ),
						'public'                => ( $row['public'] ? 1 : 0 ),
						'members'               => mysqli_real_escape_string( $this->DB->getConnection(), serialize($members) ),
						'followers'             => mysqli_real_escape_string( $this->DB->getConnection(), serialize($followers) ),
						'custom_fields'         => mysqli_real_escape_string( $this->DB->getConnection(), serialize($row['custom_fields']) ),
						'custom_field_settings' => mysqli_real_escape_string( $this->DB->getConnection(), serialize($fields) ),
						'color'                 => $row['color'],
						'html_notes'            => mysqli_real_escape_string( $this->DB->getConnection(), substr($row['html_notes'],6,strlen($row['html_notes'])-7))
					);
				}
			}
		} while( isset($data['next_page']) && is_array($data['next_page']) );

		if ( $count > 0 )
		{
			foreach( $projectIDs as $projectID )
			{
				$newRows[ $projectID ]['sections'] = mysqli_real_escape_string( $this->DB->getConnection(), serialize($this->updateSections($projectID)));
				$newRows[ $projectID ]['tasks']    = mysqli_real_escape_string( $this->DB->getConnection(), serialize($this->updateTasks($projectID)));
			}

			$this->DB->query("SELECT * FROM project;");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					$dbRows[ $row['project_gid'] ] = $row;

					if ( isset( $row['project_gid'] ) && isset( $newRows[ $row['project_gid'] ] ) )
					{
						$oldRows[ $row['project_gid'] ] = $newRows[ $row['project_gid'] ];
						unset( $newRows[ $row['project_gid'] ] );
					}
				}
			}

			if ( count( $newRows ) > 0 )
			{
				$query = "INSERT INTO project (project_gid,owner_gid,workspace_gid,team_gid,name,current_status,due_date,start_on,created_at,modified_at,archived,public,members,followers,custom_fields,custom_field_settings,color,html_notes,sections,tasks) VALUES ";

				foreach( $newRows as $row )
				{
					$query .= "('{$row['project_gid']}','{$row['owner_gid']}','{$row['workspace_gid']}','{$row['team_gid']}',\"{$row['name']}\",\"{$row['current_status']}\",'{$row['due_date']}','{$row['start_on']}','{$row['created_at']}','{$row['modified_at']}','{$row['archived']}','{$row['public']}',\"{$row['members']}\",\"{$row['followers']}\",\"{$row['custom_fields']}\",\"{$row['custom_field_settings']}\",'{$row['color']}',\"{$row['html_notes']}\",\"{$row['sections']}\",\"{$row['tasks']}\"),";
				}

				$query = substr($query,0,-1) . ";";

				$this->DB->query( $query );
			}

			if ( count( $oldRows ) > 0 )
			{
				foreach( $oldRows as $row )
				{
					$this->DB->query("UPDATE project SET project_gid = '{$row['project_gid']}', owner_gid = '{$row['owner_gid']}', workspace_gid = '{$row['workspace_gid']}', team_gid = '{$row['team_gid']}', name = \"{$row['name']}\", current_status = \"{$row['current_status']}\", due_date = '{$row['due_date']}', start_on = '{$row['start_on']}', created_at = '{$row['created_at']}', modified_at = '{$row['modified_at']}', archived = '{$row['archived']}', public = '{$row['public']}', members = \"{$row['members']}\", followers = \"{$row['followers']}\", custom_fields = \"{$row['custom_fields']}\", custom_field_settings = \"{$row['custom_field_settings']}\", color = '{$row['color']}', html_notes = \"{$row['html_notes']}\", sections = \"{$row['sections']}\", tasks = \"{$row['tasks']}\", last_update = NOW() WHERE project_gid = '{$row['project_gid']}';");
				}
			}

			// Cleanup orphans
			$this->DB->query("DELETE FROM project WHERE project_gid NOT IN(".implode(',',$projectIDs).");");
		}

		$this->cache->update('projects');

		return $count;
	}

	/**
	 * Call the Asana API to retrieve the Sections of a Project
	 *
	 * @param string $project the project id to scan
	 * @return array the gids retrieved
	 * @access public
	 * @since 1.0.0
	*/
	public function updateSections($project)
	{
		$sectionIDs = array();
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
				$data = $this->callGet('sections',"/{$project}/sections?limit=100");
			}

			if ( isset($data['data']) && is_array($data['data']) && count($data['data']) > 0 )
			{
				foreach( $data['data'] as $row )
				{
					$count++;
					$sectionID = $row['gid'];

					$sectionIDs[]  = $sectionID;
					$newRows[ $sectionID ] = array(
						'section_gid' => $sectionID,
						'project_gid' => $project,
						'name'        => $row['name'],
						'created_at'  => $this->parseDate( $row['created_at'] )
					);
				}
			}
		} while( isset($data['next_page']) && is_array($data['next_page']) );

		if ( $count > 0 )
		{
			$this->DB->query("SELECT * FROM section WHERE project_gid = '{$project}';");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					$dbRows[ $row['section_gid'] ] = $row;

					if ( isset( $row['section_gid'] ) && isset( $newRows[ $row['section_gid'] ] ) )
					{
						$oldRows[ $row['section_gid'] ] = $newRows[ $row['section_gid'] ];
						unset( $newRows[ $row['section_gid'] ] );
					}
				}
			}

			if ( count( $newRows ) > 0 )
			{
				$query = "INSERT INTO section (section_gid,project_gid,name,created_at) VALUES ";

				foreach( $newRows as $row )
				{
					$query .= "('{$row['section_gid']}','{$row['project_gid']}',\"{$row['name']}\",'{$row['created_at']}'),";
				}

				$query = substr($query,0,-1) . ";";

				$this->DB->query( $query );
			}

			if ( count( $oldRows ) > 0 )
			{
				foreach( $oldRows as $row )
				{
					$this->DB->query("UPDATE section SET project_gid = '{$row['project_gid']}', name = \"{$row['name']}\", created_at = '{$row['created_at']}', last_update = NOW() WHERE section_gid = '{$row['section_gid']}';");
				}
			}
		}

		return $sectionIDs;
	}

	/**
	 * Call the Asana API to retrieve the Tags
	 *
	 * @return int count of retrieved items
	 * @access public
	 * @since 1.0.0
	*/
	public function updateTags()
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
				$data = $this->callGet('tags',"?limit=100&workspace={$workspace}");
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
							$followers[] = $follower['gid'];
						}
					}

					$tagIDs[]  = $tagID;
					$newRows[ $tagID ] = array(
						'tag_gid'       => $tagID,
						'workspace_gid' => $workspace,
						'color'         => $row['color'],
						'created_at'    => $this->parseDate( $row['created_at'] ),
						'followers'     => mysqli_real_escape_string( $this->DB->getConnection(), serialize($followers) ),
						'name'          => $row['name']
					);
				}
			}
		} while( isset($data['next_page']) && is_array($data['next_page']) );

		if ( $count > 0 )
		{
			$this->DB->query("SELECT * FROM tag;");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					$dbRows[ $row['tag_gid'] ] = $row;

					if ( isset( $row['tag_gid'] ) && isset( $newRows[ $row['tag_gid'] ] ) )
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

			// Cleanup orphans
			$this->DB->query("DELETE FROM tag WHERE tag_gid NOT IN(".implode(',',$tagIDs).");");
		}

		$this->cache->update('tags');

		return $count;
	}

	/**
	 * Call the Asana API to retrieve the Sections of a Project
	 *
	 * @param string $project the project id to scan
	 * @return array the gids retrieved
	 * @access public
	 * @since 1.0.0
	*/
	public function updateTasks($project)
	{
		$taskIDs = array();
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
				$data = $this->callGet('tasks',"/{$project}/tasks?limit=100");
			}

			if ( isset($data['data']) && is_array($data['data']) && count($data['data']) > 0 )
			{
				foreach( $data['data'] as $row )
				{
					$count++;
					$taskID = $row['gid'];
					$fields = array();
					$dependencies = array();
					$dependents = array();
					$followers = array();
					$likes = array();
					$projects = array();
					$tags = array();

					if ( is_array($row['custom_fields']) && count($row['custom_fields']) > 0 )
					{
						foreach( $row['custom_fields'] as $r )
						{
							switch($r['resource_subtype'])
							{
								case 'number': $fields[$r['gid']] = $r['number_value'];
									break;
								case 'text': $fields[$r['gid']] = $r['text_value'];
									break;
								case 'enum': $fields[$r['gid']] = $r['enum_value']['gid'];
									break;
							}
						}
					}

					if ( is_array($row['dependencies']) && count($row['dependencies']) > 0 )
					{
						foreach( $row['dependencies'] as $dependency )
						{
							$dependencies[] = $dependency['gid'];
						}
					}

					if ( is_array($row['dependents']) && count($row['dependents']) > 0 )
					{
						foreach( $row['dependents'] as $dependent )
						{
							$dependents[] = $dependent['gid'];
						}
					}

					if ( is_array($row['followers']) && count($row['followers']) > 0 )
					{
						foreach( $row['followers'] as $follower )
						{
							$followers[] = $follower['gid'];
						}
					}

					if ( is_array($row['likes']) && count($row['likes']) > 0 )
					{
						foreach( $row['likes'] as $like )
						{
							$likes[] = $like['gid'];
						}
					}

					if ( is_array($row['likes']) && count($row['likes']) > 0 )
					{
						foreach( $row['likes'] as $like )
						{
							$likes[] = $like['gid'];
						}
					}

					if ( is_array($row['projects']) && count($row['projects']) > 0 )
					{
						foreach( $row['projects'] as $project )
						{
							$projects[] = $project['gid'];
						}
					}

					if ( is_array($row['tags']) && count($row['tags']) > 0 )
					{
						foreach( $row['tags'] as $tag )
						{
							$tags[] = $tag['gid'];
						}
					}

					$taskIDs[]  = $taskID;
					$newRows[ $taskID ] = array(
						'task_gid'         => $taskID,
						'parent_gid'       => (is_array($row['parent']) ? $row['parent']['gid'] : ''),
						'assignee_gid'     => (is_array($row['assignee']) ? $row['assignee']['gid'] : ''),
						'workspace_gid'    => $workspace,
						'resource_subtype' => $row['resource_subtype'],
						'assignee_status'  => $row['assignee_status'],
						'created_at'       => $this->parseDate( $row['created_at'] ),
						'completed'        => ( $row['completed'] ? 1 : 0 ),
						'completed_at'     => $this->parseDate( $row['completed_at'] ),
						'custom_fields'    => mysqli_real_escape_string( $this->DB->getConnection(), serialize($fields) ),
						'dependencies'     => mysqli_real_escape_string( $this->DB->getConnection(), serialize($dependencies) ),
						'dependents'       => mysqli_real_escape_string( $this->DB->getConnection(), serialize($dependents) ),
						'due_on'           => $row['due_on'],
						'due_at'           => $this->parseDate( $row['due_at'] ),
						'followers'        => mysqli_real_escape_string( $this->DB->getConnection(), serialize($followers) ),
						'liked'            => ( $row['liked'] ? 1 : 0 ),
						'likes'            => mysqli_real_escape_string( $this->DB->getConnection(), serialize($likes) ),
						'modified_at'      => $this->parseDate( $row['modified_at'] ),
						'name'             => $row['name'],
						'html_notes'       => mysqli_real_escape_string( $this->DB->getConnection(), substr($row['html_notes'],6,strlen($row['html_notes'])-7) ),
						'num_likes'        => $row['num_likes'],
						'projects'         => mysqli_real_escape_string( $this->DB->getConnection(), serialize($projects) ),
						'start_on'         => $row['start_on'],
						'memberships'      => mysqli_real_escape_string( $this->DB->getConnection(), serialize($row['memberships']) ),
						'tags'             => mysqli_real_escape_string( $this->DB->getConnection(), serialize($tags) )
					);
				}
			}
		} while( isset($data['next_page']) && is_array($data['next_page']) );

		if ( $count > 0 )
		{
			$this->DB->query("SELECT * FROM task WHERE task_gid IN('".implode("','",$taskIDs)."');");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					$dbRows[ $row['task_gid'] ] = $row;

					if ( isset( $row['task_gid'] ) && isset( $newRows[ $row['task_gid'] ] ) )
					{
						$oldRows[ $row['task_gid'] ] = $newRows[ $row['task_gid'] ];
						unset( $newRows[ $row['task_gid'] ] );
					}
				}
			}

			if ( count( $newRows ) > 0 )
			{
				$query = "INSERT INTO task (task_gid,parent_gid,assignee_gid,workspace_gid,resource_subtype,assignee_status,created_at,completed,completed_at,custom_fields,dependencies,dependents,due_on,due_at,followers,liked,likes,modified_at,name,html_notes,num_likes,projects,start_on,memberships,tags) VALUES ";

				foreach( $newRows as $row )
				{
					$query .= "('{$row['task_gid']}','{$row['parent_gid']}','{$row['assignee_gid']}','{$row['workspace_gid']}','{$row['resource_subtype']}',\"{$row['assignee_status']}\",'{$row['created_at']}','{$row['completed']}','{$row['completed_at']}',\"{$row['custom_fields']}\",\"{$row['dependencies']}\",\"{$row['dependents']}\",'{$row['due_on']}','{$row['due_at']}',\"{$row['followers']}\",'{$row['liked']}',\"{$row['likes']}\",'{$row['modified_at']}',\"{$row['name']}\",\"{$row['html_notes']}\",'{$row['num_likes']}',\"{$row['projects']}\",'{$row['start_on']}',\"{$row['memberships']}\",\"{$row['tags']}\"),";
				}

				$query = substr($query,0,-1) . ";";

				$this->DB->query( $query );
			}

			if ( count( $oldRows ) > 0 )
			{
				foreach( $oldRows as $row )
				{
					$this->DB->query("UPDATE task SET parent_gid = '{$row['parent_gid']}', assignee_gid = '{$row['assignee_gid']}', workspace_gid = '{$row['workspace_gid']}', resource_subtype = '{$row['resource_subtype']}', assignee_status = \"{$row['assignee_status']}\", created_at = '{$row['created_at']}', completed = '{$row['completed']}', completed_at = '{$row['completed_at']}', custom_fields = \"{$row['custom_fields']}\", dependencies = \"{$row['dependencies']}\", dependents = \"{$row['dependents']}\", due_on = '{$row['due_on']}', due_at = '{$row['due_at']}', followers = \"{$row['followers']}\", liked = '{$row['liked']}', likes = \"{$row['likes']}\", modified_at = '{$row['modified_at']}', name = \"{$row['name']}\", html_notes = \"{$row['html_notes']}\", num_likes = '{$row['num_likes']}', projects = \"{$row['projects']}\", start_on = '{$row['start_on']}', memberships = \"{$row['memberships']}\", tags = \"{$row['tags']}\", last_update = NOW() WHERE task_gid = '{$row['task_gid']}';");
				}
			}
		}

		return $taskIDs;
	}

	/**
	 * Call the Asana API to retrieve the Teams
	 *
	 * @return int count of retrieved items
	 * @access public
	 * @since 1.0.0
	*/
	public function updateTeams()
	{
		$teamIDs = array();
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
				$data = $this->callGet('teams',"/{$workspace}/teams?limit=100");
			}

			if ( isset($data['data']) && is_array($data['data']) && count($data['data']) > 0 )
			{
				foreach( $data['data'] as $row )
				{
					$count++;
					$teamID = $row['gid'];

					$teamIDs[]  = $teamID;
					$newRows[ $teamID ] = array(
						'team_gid'          => $teamID,
						'html_description' => mysqli_real_escape_string( $this->DB->getConnection(), substr($row['html_description'],6,strlen($row['html_description'])-7) ),
						'name'             => $row['name']
					);
				}
			}
		} while( isset($data['next_page']) && is_array($data['next_page']) );

		if ( $count > 0 )
		{
			$this->DB->query("SELECT * FROM team;");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					$dbRows[ $row['team_gid'] ] = $row;

					if ( isset( $row['team_gid'] ) && isset( $newRows[ $row['team_gid'] ] ) )
					{
						$oldRows[ $row['team_gid'] ] = $newRows[ $row['team_gid'] ];
						unset( $newRows[ $row['team_gid'] ] );
					}
				}
			}

			if ( count( $newRows ) > 0 )
			{
				$query = "INSERT INTO team (team_gid,html_description,name) VALUES ";

				foreach( $newRows as $row )
				{
					$query .= "('{$row['team_gid']}',\"{$row['html_description']}\",\"{$row['name']}\"),";
				}

				$query = substr($query,0,-1) . ";";

				$this->DB->query( $query );
			}

			if ( count( $oldRows ) > 0 )
			{
				foreach( $oldRows as $row )
				{
					$this->DB->query("UPDATE team SET html_description = \"{$row['html_description']}\", name = \"{$row['name']}\", last_update = NOW() WHERE team_gid = '{$row['team_gid']}';");
				}
			}

			// Cleanup orphans
			$this->DB->query("DELETE FROM team WHERE team_gid NOT IN(".implode(',',$teamIDs).");");
		}

		$this->cache->update('teams');

		return $count;
	}

	/**
	 * Call the Asana API to retrieve the Users
	 *
	 * @return int count of retrieved items
	 * @access public
	 * @since 1.0.0
	*/
	public function updateUsers()
	{
		$userIDs = array();
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
				$data = $this->callGet('users',"?limit=100&workspace={$workspace}");
			}

			if ( isset($data['data']) && is_array($data['data']) && count($data['data']) > 0 )
			{
				foreach( $data['data'] as $row )
				{
					$count++;
					$userID = $row['gid'];
					$workspaces = array();

					if ( is_array($row['workspaces']) && count($row['workspaces']) > 0 )
					{
						foreach( $row['workspaces'] as $workspace )
						{
							$workspaces[] = $workspace['gid'];
						}
					}

					$userIDs[]  = $userID;
					$newRows[ $userID ] = array(
						'user_gid'   => $userID,
						'email'      => $row['email'],
						'name'       => $row['name'],
						'workspaces' => mysqli_real_escape_string( $this->DB->getConnection(), serialize($workspaces) )
					);
				}
			}
		} while( isset($data['next_page']) && is_array($data['next_page']) );

		if ( $count > 0 )
		{
			$this->DB->query("SELECT * FROM asana_user;");

			if ( $this->DB->getTotalRows() )
			{
				while( $row = $this->DB->fetchRow() )
				{
					$dbRows[ $row['user_gid'] ] = $row;

					if ( isset( $row['user_gid'] ) && isset( $newRows[ $row['user_gid'] ] ) )
					{
						$oldRows[ $row['user_gid'] ] = $newRows[ $row['user_gid'] ];
						unset( $newRows[ $row['user_gid'] ] );
					}
				}
			}

			if ( count( $newRows ) > 0 )
			{
				$query = "INSERT INTO asana_user (user_gid,email,name,workspaces) VALUES ";

				foreach( $newRows as $row )
				{
					$query .= "('{$row['user_gid']}',\"{$row['email']}\",\"{$row['name']}\",\"{$row['workspaces']}\"),";
				}

				$query = substr($query,0,-1) . ";";

				$this->DB->query( $query );
			}

			if ( count( $oldRows ) > 0 )
			{
				foreach( $oldRows as $row )
				{
					$this->DB->query("UPDATE asana_user SET email = \"{$row['email']}\", name = \"{$row['name']}\", workspaces = \"{$row['workspaces']}\", last_update = NOW() WHERE user_gid = '{$row['user_gid']}';");
				}
			}
		}

		$this->cache->update('users');

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
				$data = $this->callGet('workspaces','?limit=100');
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
			}
		} while( isset($data['next_page']) && is_array($data['next_page']) );

		if ( $count > 0 )
		{
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

			// Cleanup orphans
			$this->DB->query("DELETE FROM workspace WHERE workspace_gid NOT IN(".implode(',',$workspaceIDs).");");
		}

		$this->cache->update('workspaces');

		return $count;
	}
}