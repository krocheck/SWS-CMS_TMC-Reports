<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Error class to record problems and stop the program if needed
 * Last Updated: $Date: 2010-06-29 14:04:05 -0500 (Tue, 29 Jun 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Core
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 28 $
 */

class Error
{
	/**
	 * The application registry library
	 *
	 * @access protected
	 * @var Registry
	 * @since 1.0.0
	 */
	protected $registry;
	/**
	 * The error codes brought in from the error lang file
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $errorCodes;
	/**
	 * The errors that will be printed for the user
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $loggedErrors;
	/**
	 * The language code used for displaying the errors in the correct language.  Default is 'en'.
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $languageCode = 'en';

	/**
	 * Constructor that loads the registry
	 *
	 * @param Registry $registry the main program registry
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct( Registry $registry )
	{
		$this->registry = $registry;
		
		$this->registry->getDisplay()->addDebug( "Error Library Loaded" );
		
		$this->loadErrors();
	}

	/**
	 * Constructor that loads the registry
	 *
	 * @param mixed $log the addition to the log
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addErrorLog( $log )
	{
		$this->loggedErrors[] = $log;
	}

	/**
	 * Returns the logged errors
	 *
	 * @return array the errors
	 * @access public
	 * @since 1.0.0
	 */
	public function getErrors()
	{
		return $this->loggedErrors;
	}

	/**
	 * Gets the errors codes from the language file
	 *
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function loadErrors()
	{
		if ( file_exists( SWS_LANG_PATH . strtolower($this->languageCode) . '.error.php' ) )
		{
			require( SWS_LANG_PATH . strtolower($this->languageCode) . '.error.php' );
			
			$this->registry->getDisplay()->addDebug( "Error Codes Loaded: {$this->languageCode}" );
		}
		
		if ( isset( $errorCodes ) && is_array( $errorCodes ) && count( $errorCodes ) > 0 )
		{
			$this->errorCodes = $errorCodes;
		}
		else
		{
			echo( 'FATAL ERROR!  Please notify the administrator at:  temp@localhost' );
			exit;
		}
	}

	/**
	 * Log an error for print and save to database if needed
	 *
	 * @param string $errorCode the error key
	 * @param bool $save saves the error information to the database if true, DEFAULT is FALSE
	 * @param array $extra additional information to save with log
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function logError( $errorCode, $save = FALSE, $extra = array() )
	{
		$this->loggedErrors[] = $this->errorCodes[ $errorCode ];
		
		if ( $save )
		{
			$this->saveError( $errorCode, $extra );
		}
	}

	/**
	 * Stops and prints an error screen
	 *
	 * @param string $errorCode the error key
	 * @param bool $save saves the error information to the database if true, DEFAULT is FALSE
	 * @param array $extra additional information to save with log
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function raiseError( $errorCode, $save = FALSE, $extra = array() )
	{
		$this->registry->getDisplay()->setContent( $this->errorCodes[ $errorCode ] );

		if ( $save )
		{
			$this->saveError( $errorCode, $extra );
		}
		
		if ( $errorCode == 'not_found' )
		{
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		}
		
		$this->registry->getDisplay()->doError();
	}

	/**
	 * Saves the error information to the database for review later
	 *
	 * @param string $errorCode the error key
	 * @param array $extra additional information to save with log
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function saveError( $errorCode, $extra = array() )
	{
		if ( is_object( $this->registry->getDB() ) )
		{
			$valueArray = array(
				'user_id'    => is_object( $this->registry->getUser() ) ? $this->registry->getUser()->getID() : 0,
				'session_id' => is_object( $this->registry->getUser() ) && is_object( $this->registry->getUser()->getSession() ) ? $this->registry->getUser()->getSessionID() : 0,
				'error_code' => $errorCode,
				'server'     => $_SERVER,
				'input'      => $this->registry->getInputs()
			);
			
			if ( is_array( $extra ) && count( $extra ) > 0 )
			{
				$valueArray['extra'] = $extra;
			}
			
			$value = serialize( $valueArray );
			
			$key = date('Y-m-d H:i:s');
			
			if ( is_object( $this->registry->getUser() ) && $this->registry->getUser()->getID() > 0 )
			{
				$this->registry->getDB()->query(
					"INSERT INTO metadata (module, id, meta_key, meta_value) VALUES ('error', '{$this->registry->getUser()->getID()}', '{$key}', '{$value}');"
				);
			}
			else
			{
				$this->registry->getDB()->query(
					"INSERT INTO metadata (module, meta_key, meta_value) VALUES ('error', '{$key}', '{$value}');"
				);
			}
		}
	}

	/**
	 * Sets the errors to display in the user's preferred language
	 *
	 * @param string $code the language code
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setLanguageCode( $code )
	{
		$this->languageCode = $code;
		
		$this->loadCodes();
	}
}

?>