<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter MongoDB Library
 *
 * A library to interface with the NoSQL database MongoDB. For more information see http://www.mongodb.org
 *
 * @package		CodeIgniter
 * @author		Alex Bilbie | www.alexbilbie.com | alex@alexbilbie.com
 * @copyright	Copyright (c) 2010, Alex Bilbie.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://alexbilbie.com/code/
 * @version		Version 0.1
 */
 
 /**
 *	Usage
 *	
 *	Connect to a MongoDB instance
 *		$this->mongodb->connect('localhost', 27017);
 *
 *	Select a database
 *		$this->mongodb->db('dbname');
 *
 *	Select a collection
 *		$this->mongodb->collection('collection name');
 *
 *	Insert a document into a collection (returns the insert ID)
 *		$this->mongodb->insert( array('name' => 'Alex Bilbie', 'email' => 'alex@alexbilbie.com', 'age' => 20) );
 *
 *	Get documents (where email = 'alex@alexbilbie.com')
 *		$this->mongodb->get( array('email' => 'alex@alexbilbie.com') );
 *
 *	Get documents (where age is greater than 19)
 *		$this->mongodb->get( array('age' => array('$gt' => 19) );
 *
 *	Get documents (where email = 'alex@alexbilbie.com' AND age is greater than 19
 *		$this->mongodb->get( array('email' => 'alex@alexbilbie.com', 'age' => array('$gt' => 19) );
 *
 *	Update a single document (where email = 'alex@alexbilbie.com')
 *		$this->mongodb->update( array('email' => 'alex@alexbilbie.com'), array('age' => 21), array('multiple' => FALSE) );
 *
 *	Update multiple documents (where age = 20)
 *		$this->mongodb->update( array('age' => 20), array('age' => 21) );
 *
 *	Delete a single document (where email = 'alex@alexbilbie.com')
 *		$this->mongodb->delete( array('email' => 'alex@alexbilbie.com') );
 *
 *	Delete multiple documents (where age = 20)
 *		$this->mongodb->delete( array('age' => 20), TRUE );
 *		
 */
 
class mongodb {

	private $host = 'localhost';
	private $port = 27017;
	
	private $connection;
	
	var $ci;

	/**
	 * mongodb function.
	 * 
	 * @access public
	 * @return void
	 */
	function mongodb()
	{
		$this->__construct();
	}
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	function __construct()
	{	
		// Get instance of CI super object
		$this->CI =& get_instance();	
	}
	
	/**
	 * Function to connect to a MongoDB.
	 * 
	 * @access public
	 * @param mixed $host. (default: FALSE)
	 * @param mixed $port. (default: FALSE)
	 * @return $this
	 */
	function connect($host = FALSE, $port = FALSE)
	{
	
		if($host)
		{
			$this->host = $host;
		}
		
		if($port)
		{
			$this->port = $port;
		}
		
		$this->connection = new Mongo( $host . ":" . $port ) or show_error( "Failed to connect to MongoDB on {$host}:{$port}", 500 );
		
		return $this;
		
	}
	
	/**
	 * Function to select a database.
	 * 
	 * @access public
	 * @param mixed $db. (default: NULL)
	 * @return $this
	 */
	function db( $db = NULL )
	{
	
		if( $db == NULL )
		{
			show_error( "No MongoDB database selected", 500 );
			return;
		}
	
		$this->connection->selectDB( $db );
		
		return $this;
	
	}
	
	/**
	 * Function to select a connection.
	 * 
	 * @access public
	 * @param mixed $collection. (default: NULL)
	 * @return $this
	 */
	function collection( $collection = NULL )
	{
	
		if( $collection == NULL )
		{
			show_error( "No MongoDB collection selected", 500 );
			return;
		}
		
		$this->connection->selectCollection( $collection );
		
		return $this;
	
	}
	
	/**
	 * Function to insert a document into a collection.
	 * 
	 * @access public
	 * @param array $insert. (default: array())
	 * @param mixed $safe. (default: TRUE)
	 * @return void
	 */
	function insert( $insert = array(), $safe = TRUE )
	{
		if( !is_array( $insert ) && count( $insert ) == 0 )
		{
			show_error( "MongoDB insert value is empty or not an array", 500 );
			return;
		}
		
		$this->connection->insert( $insert, $safe );
		
		return $insert['_id'];
	}
	
	/**
	 * Function to get documents.
	 * 
	 * @access public
	 * @param array $filter. (default: array())
	 * @return void
	 */
	function get( $filter = array() )
	{
		if( !is_array( $filter) )
		{
			show_error( "MongoDB get filter not an array", 500 );
			return;
		}
		
		return $this->connection->find( $filter );
		
	}
	
	/**
	 * Function to update document(s).
	 * 
	 * @access public
	 * @param array $filter. (default: array())
	 * @param array $updates. (default: array())
	 * @param array $options. (default: array('multiple')
	 * @return void
	 */
	function update( $filter = array(), $updates = array(), $options = array('multiple' => FALSE) )
	{
		if( !is_array( $filter ) )
		{
			show_error( "MongoDB update filter value not an array", 500 );
			return;
		}
		
		if( !is_array( $updates ) && count( $updates ) == 0 )
		{
			show_error( "MongoDB update value is empty or not an array", 500 );
			return;
		}
		
		if( !is_array( $options ) && count( $options ) == 0 )
		{
			show_error( "MongoDB options value is empty or not an array", 500 );
			return;
		}
		
		$this->connection->update( $filter, $updates, $options );
		
	}
	
	/**
	 * Function to delete document(s).
	 * 
	 * @access public
	 * @param array $filter. (default: array())
	 * @param mixed $multiple. (default: FALSE)
	 * @return void
	 */
	function delete( $filter = array(), $multiple = FALSE )
	{
		if( !is_array( $filter ) )
		{
			show_error( "MongoDB delete filter is not an array", 500 );
			return;
		}
		
		if( !is_bool($multiple) )
		{
			show_error( "MongoDB delete multiple option is not TRUE or FALSE", 500 );
			return;
		}
		
		$this->connection->remove( $filter, $multiple );
	}

}