<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Spooler Transaction Processor
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

class SpoolerCheck extends Command
{
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
		$this->DB->query("SELECT * FROM logs WHERE date_time > DATE_ADD( NOW(), INTERVAL -6 HOUR);");

		if ( $this->DB->getTotalRows() )
		{
			print("OK");
		}
		else
		{
			$this->DB->query("SELECT * FROM user WHERE email_alerts = 1;");
			
			if ( $this->DB->getTotalRows() )
			{
				$emails = array();

				while( $row = $this->DB->fetchRow() )
				{
					$emails[] = $row['email'];
				}
				
				$to = implode(",", $emails);
				$headers = 'From: no-reply@stubclub53.com' . "\r\n" .
					'Reply-To: krocheck@simnaweb.com' . "\r\n" .
					'X-Mailer: PHP/' . phpversion();
				
				mail( $to, "Stub Club Down / Action Required", "Dear Administrator,\n\nTransaction processing for the Stub Club appears to be down.  No activity has been detected for at least the last 6 hours.  Please check and/or restart the Access database on the POS server.\n\nThanks,\nStub Club Web Server", $headers);
				
				print("ERROR: Alert sent");
			}
			else
			{
				print("ERROR: No accounts to send alert");
			}
		}
	}
}

?>