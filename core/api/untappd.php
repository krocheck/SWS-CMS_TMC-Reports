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
class UntappdAPI extends Command
{
	protected $account = "";
	protected $apiURL = "";
	protected $httpCode;
	protected $readToken = "";
	protected $readWriteToken = "";
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
		$this->account = $this->registry->getSetting('untappd_account');
		$this->apiURL = $this->registry->getSetting('untappd_url');
		$this->readToken = $this->registry->getSetting('untappd_read_only');
		$this->readWriteToken = $this->registry->getSetting('untappd_read_write');
	}

	/**
	* Basic GET wrapper for non-oauth calls to the API
	*
	*/
	public function get($url, $params = array())
	{
		$out = "";

		if (sizeof($params) != 0)
		{
			$added = "&" . http_build_query($params);
		}
		
		if ($this->access_token == "")
		{
			if(stristr($url, '?') === FALSE)
			{
				$url = $this->apiBase . $url . "?client_id=".$this->client_id . "&client_secret=" . $this->client_secret . $added;
			}
			else
			{
				$url = $this->apiBase . $url . "&client_id=".$this->client_id . "&client_secret=" . $this->client_secret . $added;
			}
		}
		else
		{
			$url = $this->apiBase . $url . "?access_token=".$this->access_token . $added;
		}
		
		$response = $this->call($url, 'GET', $params);
		
		return json_decode($response);
	}

	/**
	* Basic GET wrapper for oauth calls to the API
	*
	*/
	public function post($url, $params = array())
	{
		
		if ($this->access_token == "")
		{
			if(stristr($url, '?') === FALSE)
			{
				$url = $this->apiBase . $url . "?client_id=".$this->client_id . "&client_secret=" . $this->client_secret;
			}
			else
			{
				$url = $this->apiBase . $url . "&client_id=".$this->client_id . "&client_secret=" . $this->client_secret;
			}
		}
		else
		{
			if(stristr($url, '?') === FALSE)
			{
				$url = $this->apiBase . $url . "?access_token=".$this->access_token;
			}
			else
			{
				$url = $this->apiBase . $url . "&access_token=".$this->access_token;
			}
		}
		
		$response = $this->call($url, 'POST', $params);
		return json_decode($response);
	}

	/**
	* Basic CURL request which connects to the Tumblr API URLs and returns the result
	*
	* @returns result from the URL call
	*/
	private function call($url, $method, $parameters)
	{
		$curl2 = curl_init();
		
		if ($method == "POST")
		{
			curl_setopt($curl2, CURLOPT_POST, true);
			curl_setopt($curl2, CURLOPT_POSTFIELDS, $parameters);
		}
		
		curl_setopt($curl2, CURLOPT_URL, $url);
		curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl2, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl2, CURLOPT_USERAGENT, $this->userAgent);
		$result = curl_exec($curl2);
		
		$HttpCode = curl_getinfo($curl2, CURLINFO_HTTP_CODE);
		$this->http_code = $HttpCode;
		return $result;
 	}
}