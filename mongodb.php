<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter MongoDB Active Record Library
 *
 * A library to interface with the NoSQL database MongoDB. For more information see http://www.mongodb.org
 *
 * @package		CodeIgniter
 * @author		Alex Bilbie | www.alexbilbie.com | alex@alexbilbie.com
 * @copyright	Copyright (c) 2010, Alex Bilbie.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://alexbilbie.com/code/
 * @version		Version 0.2.2
 */
class mongo_db {
	
	protected $CI;

	private $connection;
	private $db;
	
	private $select = array();
	private $where = array();
	private $limit = NULL;
	private $offset = NULL;
	private $sort = array();
	
	/* Constuct function
	 *
	 * Checks that the Mongo PECL library is installed and enabled
	 *
	 */
	function __construct()
	{
		if(!class_exists('Mongo'))
		{
			show_error('It looks like the MongoDB PECL extension isn\'t installed or enabled', 500);
			return;
		}
		
		$this->CI =& get_instance();
		
		// Attempt to connect
		$this->connect();
	}
	
	/* Connect function
	 *
	 * Connect to a Mongo database
	 *
	 * Usage: $this->mongo_db->connect();
	 */ 
	private function connect()
	{
		$this->CI->config->load('mongodb');
		
		$host = $this->CI->config->item('mongo_host');
		$port = $this->CI->config->item('mongo_port');
		$db = $this->CI->config->item('mongo_db');
		$username = $this->CI->config->item('mongo_username');
		$password = $this->CI->config->item('mongo_password');
		
		if($host == "" || $port == "")
		{
			show_error('No host or port configured to connect to MongoDB', 500);
			return;	
		}
		
		if(!empty($db))
		{
			$this->db = $db;
		}
		else
		{
			show_error('No Mongo database selected', 500);
		}
		
		$auth = '';
		if($username !== "" && $password !== "")
		{
			$auth = "{$username}:{$password}@";
		}
		
		$connection_string = "mongodb://{$auth}{$host}:{$port}/$db";

		// Make the connection
		try
		{
			$this->connection = new Mongo($connection_string);
		}
		catch(Exception $e)
		{
			show_error('Unable to connect to MongoDB. Please check your host, port, username and password settings.', 500);
		}
		
		return $this;
	}
	
	//! Get Functions
	
	/* Select function
	 *
	 * Select specific fields from a document
	 *
	 * Usage: $this->mongo_db->select(array('foo','bar'))->get('foobar');
	 */ 
	function select($what = array())
	{
		if(is_array($what) && count($what) > 0)
		{
			$this->select = $what;
		}
		elseif($what !== "")
		{
			$this->select = array();
			$this->select[] = $what;
		}
		
		return $this;
	}
	
	/* Where function
	 *
	 * Get documents where something
	 *
	 * Usage: $this->mongo_db->where(array('foo' => 1))->get('foobar');
	 */ 
	function where($where = array())
	{
		$this->where = $where;
		return $this;
	}
	
	/* Where_in function
	 *
	 * Get documents where something is in an array of something
	 *
	 * Usage: $this->mongo_db->where_in('foo', array(1,2,3))->get('foobar');
	 */ 
	function where_in($what = "", $in = array())
	{
		$this->_where_init($what);
		
		$this->where[$what]['$in'] = $in;
		return $this;
	}
	
	/* Where_in function
	 *
	 * Get documents where something is in all of an array of something
	 *
	 * Usage: $this->mongo_db->where_in_all('foo', array(1,2,3))->get('foobar');
	 */
	function where_in_all($what = "", $in = array())
	{
		$this->_where_init($what);
		
		$this->where[$what]['$all'] = $in;
		return $this;
	}
	
	/* Where_not_in function
	 *
	 * Get documents where something is not in an array of something
	 *
	 * Usage: $this->mongo_db->where_not_in('foo', array(1,2,3))->get('foobar');
	 */
	function where_not_in($what = "", $in)
	{
		$this->_where_init($what);
		
		$this->where[$what]['$nin'] = $in;
		return $this;
	}
	
	/* Where_gt function
	 *
	 * Get documents where something is greater than something
	 *
	 * Usage: $this->mongo_db->where_gt('foo', 1)->get('foobar');
	 */
	function where_gt($what, $gt)
	{
		$this->_where_init($what);
		
		$this->where[$what]['$gt'] = $gt;
		return $this;
	}
	
	/* Where_gte function
	 *
	 * Get documents where something is greater than or equal to something
	 *
	 * Usage: $this->mongo_db->where_gte('foo', 1)->get('foobar');
	 */
	function where_gte($what, $gte)
	{
		$this->_where_init($what);
		
		$this->where[$what]['$gte'] = $gte;
		return $this;
	}
	
	/* Where_lt function
	 *
	 * Get documents where something is lee than something
	 *
	 * Usage: $this->mongo_db->where_lt('foo', 1)->get('foobar');
	 */
	function where_lt($what, $lt)
	{
		$this->_where_init($what);
		
		$this->where[$what]['$lt'] = $lt;
		return $this;
	}
	
	/* Where_lte function
	 *
	 * Get documents where something is less than or equal to something
	 *
	 * Usage: $this->mongo_db->where_lte('foo', 1)->get('foobar');
	 */
	function where_lte($what, $lte)
	{
		$this->_where_init($what);
		
		$this->where[$what]['$lte'] = $lte;
		return $this;
	}
	
	/* Where_lte function
	 *
	 * Get documents where something is not equal to something
	 *
	 * Usage: $this->mongo_db->where_not_equal('foo', 1)->get('foobar');
	 */
	function where_not_equal($what, $to)
	{
		$this->_where_init($what);
		
		$this->where[$what]['$ne'] = $to;
		return $this;
	}
	
	/* Order_by function
	 *
	 * Order documents by something ascending (1) or descending (-1)
	 *
	 * Usage: $this->mongo_db->order_by('foo', 1)->get('foobar');
	 */
	function order_by($what, $order = "ASC")
	{
		if($order = "ASC"){ $order = 1; }
		elseif($order = "DESC"){ $order = -1; }
		$this->sort[] = array($what => $order);
		return $this;
	}
	
	/* Limit function
	 *
	 * Limit the returned documents by something (and optionally an offset)
	 *
	 * Usage: $this->mongo_db->limit(5,5)->get('foobar');
	 */
	function limit($limit = NULL, $offset = NULL)
	{
		if($limit !== NULL && is_numeric($limit) && $limit >= 1)
		{
			$this->limit = $limit;
		}
		
		if($offset !== NULL && is_numeric($offset) && $offset >= 1)
		{
			$this->offset = $offset;
		}
		
		return $this;
	}
	
	/* Get_where function
	 *
	 * Get documents where something
	 *
	 * Usage: $this->mongo_db->get_where('foobar', array('foo' => 'bar'));
	 */
	function get_where($collection = "", $where = array())
	{
		return $this->where($where)->get($collection);
	}
	
	/* Get function
	 *
	 * Get documents from a collection
	 *
	 * Usage: $this->mongo_db->get('foobar');
	 */
	function get($collection = "")
	{
		if($collection !== "")
		{
			$results = array();
			$i = 0;
						
			// Initial query
			$documents = $this->connection->{$this->db}->{$collection}->find($this->where);
			
			// Limit the results
			if($this->limit !== NULL)
			{
				$documents = $documents->limit($this->limit);
			}
			
			// Offset the results
			if($this->offset !== NULL)
			{
				$documents = $documents->skip($this->offset);
			}
			
			// Get the results
			while($documents->hasNext())
			{
				$document = $documents->getNext();
				if($this->select !== NULL && count($this->select) > 0)
				{
					foreach($this->select as $s)
					{
						if(isset($document[$s])){
							$results[$i][$s] = $document[$s];
						}
					}
				}
				else
				{
					$results[$i] = $document;
				}
				$i++;
			}

			return $results;
		}
		
		else
		{
			show_error('No Mongo collection selected to query', 500);
		}	
	}
	
	/* Count function
	 *
	 * Count the number of documents
	 *
	 * Usage: $this->mongo_db->where(array('foo' => 'bar'))->count('foobar');
	 */
	function count($collection = "")
	{
		if($collection !== "")
		{			
			// Initial query
			$documents = $this->connection->{$this->db}->{$collection}->find($this->where);
			
			// Limit the results
			if($this->limit !== NULL)
			{
				$documents = $documents->limit($this->limit);
			}
			
			// Offset the results
			if($this->offset !== NULL)
			{
				$documents = $documents->skip($this->offset);
			}
			
			$this->_clear();
			return $documents->count();
		}
		
		else
		{
			$this->_clear();
			show_error('No Mongo collection selected', 500);
		}
	}
		
	//! Insert functions
	
	/* Insert function
	 *
	 * Insert a new document into a collection
	 *
	 * Usage: $this->mongo_db->insert('foobar', array('foo' => 'bar'));
	 */
	function insert($collection = "", $insert = array())
	{
		if($collection == "")
		{
			show_error("No Mongo collection selected to insert into", 500);
		}
		
		if(count($insert) == 0 || !is_array($insert))
		{
			show_error("Nothing to insert into Mongo collection or insert is not an array", 500);
		}
		
		return $this->connection->{$this->db}->{$collection}->insert($insert);
	}
	
	//! Update functions
	
	/* Update function
	 *
	 * Update a single document in a collection
	 *
	 * Usage: $this->mongo_db->where(array('foo' => 'bar'))->update('foobar', array('foo' => 'foobar'));
	 */
	function update($collection = "", $update = array())
	{
		if($collection == "")
		{
			show_error("No Mongo collection selected to insert into", 500);
		}
		
		if(count($update) == 0 || !is_array($update))
		{
			show_error("Nothing to update in Mongo collection or update is not an array", 500);
		}
		
		$update_result = $this->connection->{$this->db}->{$collection}->update($this->where, array('$set' => $update));
		$this->_clear();
		return $update_result;
	}
	
	/* Update function
	 *
	 * Update a all documents in a collection
	 *
	 * Usage: $this->mongo_db->where(array('foo' => 'bar'))->update('foobar', array('foo' => 'foobar'));
	 */
	function update_all($collection = "", $update = array())
	{
		if($collection == "")
		{
			show_error("No Mongo collection selected to insert into", 500);
		}
		
		if(count($update) == 0 || !is_array($update))
		{
			show_error("Nothing to update in Mongo collection or update is not an array", 500);
		}
		
		$update_result = $this->connection->{$this->db}->{$collection}->update($this->where, array('$set' => $update), array('multiple'=>TRUE));
		$this->_clear();
		return $update_result;
	}
	
	//! Delete functions
	
	/* Delete function
	 *
	 * Delete a single document in a collection
	 *
	 * Usage: $this->mongo_db->delete('foobar', array('foo' => 'foobar'));
	 */
	function delete($collection = "", $delete = array())
	{
		if($collection == "")
		{
			show_error("No Mongo collection selected to insert into", 500);
		}
		
		if(count($delete) == 0 || !is_array($delete))
		{
			show_error("Nothing to delete from Mongo collection or delete is not an array", 500);
		}
		
		if(isset($delete["_id"]))
		{
			if(gettype($delete["_id"] == "string"))
			{
				$delete["_id"] = new MongoID($delete["_id"]);
			}
		}
		
		return $this->connection->{$this->db}->{$collection}->remove($delete, array('justOne'=>TRUE));
	}
	
	/* Delete function
	 *
	 * Delete all documents in a collection
	 *
	 * Usage: $this->mongo_db->delete('foobar', array('foo' => 'foobar'));
	 */
	function delete_all($collection = "", $delete = array())
	{
		if($collection == "")
		{
			show_error("No Mongo collection selected to insert into", 500);
		}
		
		if(count($delete) == 0 || !is_array($delete))
		{
			show_error("Nothing to delete from Mongo collection or delete is not an array", 500);
		}
		
		if(isset($delete["_id"]))
		{
			if(gettype($delete["_id"] == "string"))
			{
				$delete["_id"] = new MongoID($delete["_id"]);
			}
		}
		
		return $this->connection->{$this->db}->{$collection}->remove($delete);
	}
		
	
	
	
	/*
	 * Internal function to clear params so there are no conflicts
	 */
	private function _clear()
	{
		$this->select = array();
		$this->where = array();
		$this->limit = NULL;
		$this->offset = NULL;
		$this->sort = array();
	}
	
	/*
	 * Internal function to initialise parameters for where calls
	 */
	private function _where_init($what)
	{
		if(!isset($this->where[$what]))
		{
			$this->where[$what] = array();
		}
	}

}