<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Database library that handles all the good stuff
 * Last Updated: $Date: 2010-07-02 09:29:16 -0500 (Fri, 02 Jul 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Core
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 31 $
 */

class Database
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
	 * The database connection
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $connectionID;
	/**
	 * The last query run as a shortcut for retrieval without providing the ID
	 *
	 * @access protected
	 * @var resource
	 * @since 1.0.0
	 */
	protected $queryID;
	/**
	 * Array of queries run for debugging
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $queries = array();
	/**
	 * Flag for a SQL transaction in progress
	 *
	 * @access protected
	 * @var bool
	 * @since 1.0.0
	 */
	protected $transaction = FALSE;
	/**
	 * Array of errors used to detect problems during transactions
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $errors;

	/**
	 * Constructor that loads the registry
	 *
	 * @param Registry $registry the main program registry
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct( Registry $registry, array $config, $type = 'core' )
	{
		$this->registry = $registry;
		
		$this->registry->getDisplay()->addDebug( "Database Library Loaded: {$type}" );
		
		$this->setupDB( $config['sql_host'], $config['sql_user'], $config['sql_pass'], $config['sql_database'] );
	}

	public function addDebug( $theQuery )
	{
		$this->queries[ microtime() ] = $theQuery;
	}

	/**
	 * Begins a new transaction if one is not already in progress
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function begin()
	{
		if ( ! $this->transaction )
		{
			$this->query("BEGIN");
			
			if ( ! mysqli_error( $this->connectionID ) )
			{
				$this->transaction = TRUE;
				$this->errors      = array();
			}
		}
	}

	/**
	 * Returns true if errors were found during the transaction
	 *
	 * @return bool
	 * @access public
	 * @since 1.0.0
	 */
	public function checkForErrors()
	{
		$out = FALSE;

		if ( $this->transaction && count( $this->errors ) > 0 )
		{
			$out = TRUE;
		}

		return $out;
	}

	/**
	 * Closes the connection to the database
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function close()
	{
		if ( is_object( $this->connectionID ) )
		{
			mysqli_close( $this->connectionID );
		}
	}

	/**
	 * Commits the transaction if one is in progress
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function commit()
	{
		$out = FALSE;

		if ( $this->transaction )
		{
			$this->query("COMMIT");
			
			$this->transaction = FALSE;
			$out = TRUE;
		}

		return $out;
	}

	/**
	 * Retrieve row from database
	 *
	 * @param resource $queryID Query result id
	 * @return array|void Result set array, or void
	 * @access public
	 * @since 1.0.0
	 */
	public function fetchRow( $queryID = NULL )
	{
		if ( ! $queryID )
		{
			$queryID = $this->queryID;
		}
		
		try
		{
			$recordRow = mysqli_fetch_array($queryID, MYSQLI_ASSOC);
		}
		catch (Exception e)
		{
			debug_print_backtrace();
		}
		
		return $recordRow;
	}

	/**
	 * Get the connection
	 *
	 * @return array the connection
	 * @access public
	 * @since 1.0.0
	 */
	public function getConnection()
	{
		return $this->connectionID;
	}

	/**
	 * Retrieve the auto_increment ID from a query
	 *
	 * @return int the id
	 * @access public
	 * @since 1.0.0
	 */
	public function getInsertID()
	{
		$recordRow = mysqli_insert_id($this->connectionID);
		
		return $recordRow;
	}

	/**
	 * Get the queries run
	 *
	 * @return array the queries
	 * @access public
	 * @since 1.0.0
	 */
	public function getQueries()
	{
		return $this->queries;
	}

	/**
	 * Retrieve number of rows in result set
	 *
	 * @param resource $queryID Query result id
	 * @return int number of rows in result set
	 * @access public
	 * @since 1.0.0
	 */
	public function getTotalRows( resource $queryID=NULL )
	{
		if ( ! $queryID )
		{
			$queryID = $this->queryID;
		}
		
		return mysqli_num_rows( $queryID );
	}

	/**
	 * Runs a query with the SQL provided
	 *
	 * @param string $theQuery the query
	 * @return resource queryID for use with nested loops
	 * @access public
	 * @since 1.0.0
	 */
	public function query( $theQuery )
	{
		//-----------------------------------------
		// Run the query
		//-----------------------------------------
		
		//if ( DEBUG )
		//{
			$this->registry->getDB()->addDebug($theQuery);
		//}
		
		$this->queryID = mysqli_query( $this->connectionID, $theQuery );
		
		if ( $this->transaction )
		{
			$test = mysqli_error();
			
			if ( $test != '' )
			{
				$this->errors[] = $test;
			}
		}
		
		//if ( DEBUG )
		//{
			$error = mysqli_error( $this->connectionID );
			$errno = mysqli_errno( $this->connectionID );
			
			if ( strlen( $error ) > 0 )
			{
				$this->registry->display->addDebug( $errno . ": " . $error );
			}
		//}
		
		return $this->queryID;
	}

	/**
	 * Rolls back the transaction if one is in progress
	 *
	 * @return bool
	 * @access public
	 * @since 1.0.0
	 */
	public function rollBack()
	{
		$out = FALSE;

		if ( $this->transaction )
		{
			$this->query("ROLLBACK");
			
			$this->transaction = FALSE;
			$out = TRUE;
		}

		return $out;
	}

	
	/**
	 * Sets up the database connection and selects the table
	 *
	 * @param string $host the SQL hostname
	 * @param string $user the SQL username
	 * @param string $pass the SQL password
	 * @param string $db the SQL database
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function setupDB( $host, $user, $pass, $db )
	{
		$this->connectionID = mysqli_connect(
			$host,  //host
			$user,  //user
			$pass   //pass
		);
		
		if ( ! $this->connectionID )
		{
			$this->registry->getError()->raiseError( 'db_connect_fail', FALSE );
		}
		else if ( ! mysqli_select_db( $this->connectionID, $db ) )
		{
			$this->registry->getError()->raiseError( 'db_database_fail', FALSE );
		}
	}
}

?>