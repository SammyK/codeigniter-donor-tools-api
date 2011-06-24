<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Donortools.com API Integration
 *
 * For Donortools.com API integration
 *
 * @package        	CodeIgniter
 * @subpackage    	Libraries
 * @category    	Libraries
 * @author		SammyK (http://sammyk.me/)
 */

class Donor_tools
{
    private $CI;						// CodeIgniter instance

    private $api_username = '';			// API Login username
    private $api_password = '';			// API Password
    private $api_url = '';				// Where we postin' to?
	private $api_url_actions = array(	// All the URL's that let us do stuff
		'funds' => '/settings/funds.xml',
		'fund' => '/settings/funds/%s.xml',
		'sources' => '/settings/sources.xml',
		'source' => '/settings/sources/%s.xml',
		'donations' => '/donations.xml',
		'donation' => '/donations/%s.xml',
		'organization' => '/settings/organization.xml',
		'personas' => '/personas.xml',
		'persona' => '/personas/%s.xml',
		// GET only
		'organization-count' => '/dashboard/count.xml', // Not actual tag name
		'soft-credits' => '/soft_credits.xml',
		'soft-credits-persona' => '/personas/%s/soft_credits.xml', // Not actual tag name
		'donation-types' => '/settings/donation_types.xml',
		'name-types' => '/settings/name_types.xml',
		'address-types' => '/settings/address_types.xml',
		);
	
    private $current_page;				// Page we are on
    private $per_page;					// How many entries per page
    private $total_entries;				// Total number of entries
	
	// URL key/value pairs
    private $pair_page;					// The page that we want to pull
    private $pair_sort_order;			// Sort order (personas only)
    private $pair_sort_direction;		// Sort direction (personas only)
    private $pair_funds_include_donors;	// Include donors in fund listing?
    private $pair_replacement_fund_id;	// When deleting fund, we need a replacement
    private $pair_replacement_source_id;// When deleting source, we need a replacement
	
    private $type;						// The current type we are working with
    private $send_data;					// The data that we will be sending (SimpleXMLElement object)
	
	/*
	 * If your installation of cURL works without the "CURLOPT_SSL_VERIFYHOST"
	 * and "CURLOPT_SSL_VERIFYPEER" options disabled, then remove them
	 * from the array below for better security.
	 */
    private $curl_options = array(		// Additional cURL Options
		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_TIMEOUT => 60,
		);
    private $curl_headers = array(		// Headers that we always want to send
		'Connection: close',	// Without this, server defaults to a "Keep-alive"
								// return header on POST and waits until timeout.
								// Dunno why this is default for the serer...
		);
    private $curl_send_data_headers = array(	// Additional headers for POST/PUT
		'Content-type: application/xml',
		);
	
    private $response;					// Response from donortools.com
	
    private $error;						// Error to show to the user
	
	public function __construct( $config = array() )
	{
		$this->CI =& get_instance();
		
		// Load config file
		$this->CI->config->load('donor_tools', TRUE);
		
		// Pull the config into scope
		foreach( $this->CI->config->item('donor_tools') as $key => $value )
		{
			if( isset($this->$key) )
			{
				$this->$key = $value;
			}
		}

		// Inline config
		$this->initialize($config);
		
		// Load cURL lib
		$this->CI->load->library('curl');
		
		// For word stemming
		$this->CI->load->helper('inflector');
	}

	// Initialize the lib config
	public function initialize( $config )
	{
		foreach( $config as $key => $value )
		{
			if( isset($this->$key) )
			{
				$this->$key = $value;
			}
		}
	}
	
	// Initialize the send data
	public function startData( $type )
	{
		if( !isset($this->api_url_actions[$type]) )
		{
			$this->error = 'Invalid request "' . $type . '"';
			return FALSE;
		}
		
		$this->type = $type;
		
		$this->send_data = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $this->type . '></' . $this->type . '>');
	}
	
	// Add general data
	public function addData( $key, $value )
	{
		$this->_addData( $key, $value, $this->send_data );
	}
	
	// Add money data
	public function addMoney( $key, $value )
	{
		$this->send_data->addChild($key, $value)->addAttribute('type','Money');
	}
	
	// Add data to a node
	private function _addData( $key, $value, &$node )
	{
		if( is_array($value) )
		{
			$new_node = $this->_getNode($key, array('key' => 'type', 'value' => 'array'));
			$this->_addArray($value, $new_node, $key);
		}
		elseif( is_bool($value) )
		{
			$node->addChild($key, $value ? 'true' : 'false')->addAttribute('type','boolean');
		}
		elseif( is_null($value) )
		{
			$node->addChild($key)->addAttribute('nil','true');
		}
		elseif( is_int($value) )
		{
			$node->addChild($key, $value)->addAttribute('type','integer');
		}
		else
		{
			$node->addChild($key, $this->_escape($value));
		}
	}
	
	// Escape a value
	private function _escape( $value )
    {
		return htmlspecialchars($value, NULL, 'UTF-8');
    }
	
	// Add "meta" data
	// (DEPRECIATED - DO NOT USE)
	public function addMeta( $data )
	{
		$node = $this->send_data->addChild('meta', $this->_escape($data));
		$node->addAttribute('type','yaml');
		
		if( is_null($data) )
		{
			$node->addAttribute('nil','true');
		}
	}
	
	// Add a set of data
	public function addSet( $name, $data )
	{
		$node = $this->_getNode(plural($name, TRUE), array('key' => 'type', 'value' => 'array'));
		
		$new_node = $node->addChild($name);
		
		foreach( $data as $key => $value )
		{
			$this->_addData($key, $value, $new_node);
		}
	}
	
	// Try to find a node, if not exists, create it
	private function _getNode( $name, $attr_data = NULL )
	{
		foreach( $this->send_data->children() as $child )
		{
			if( $name == $child->getName() )
			{
				return $child;
			}
		}
		
		// Not found, create the node
		$node = $this->send_data->addChild($name);
		
		if( is_array($attr_data) )
		{
			$node->addAttribute($attr_data['key'],$attr_data['value']);
		}
		
		return $node;
	}
	
	// Recursively add an array to the send data
	private function _addArray( $data, &$node, $key_alias = '' )
	{
		foreach( $data as $key => $value )
		{
			$key = is_numeric($key) ? $key_alias : $key;
			
			if( is_array($value) )
			{
				$subnode = $node->addChild($key);
				$subnode->addAttribute('type','array');
				$this->_addArray($value, $subnode, $key);
			}
			else
			{
				$this->_addData($key, $value, $node);
			}
		}
	}
	
	// GET a request
	public function get( $type, $id = 0 )
	{
		$url = $this->generateUrl($type, $id);
		if( $url === FALSE ) return FALSE;
		
		$this->type = $type;
		
		$this->CI->curl->create($url);
		$this->CI->curl->http_login($this->api_username, $this->api_password);
		
		foreach( $this->getHeaders('GET') as $header )
		{
			$this->CI->curl->http_header($header);
		}
		
		$this->CI->curl->options($this->curl_options);
		
		return $this->parseResponse($this->CI->curl->execute());
	}
	
	// POST a request
	public function create()
	{
		$url = $this->generateUrl(plural($this->type, TRUE));
		if( $url === FALSE ) return FALSE;
		
		$this->CI->curl->create($url);
		$this->CI->curl->http_login($this->api_username, $this->api_password);
		
		foreach( $this->getHeaders('POST') as $header )
		{
			$this->CI->curl->http_header($header);
		}
		
		// Get the XML to send
		$send_xml = $this->getSendXml();
		
		$len = strlen($send_xml);
		$this->CI->curl->http_header('Content-Length: ' . $len);
		
		/*
		 * Very helpful debugging info if you need it
		 */
		//$f = fopen('request.txt', 'w');
		//$this->curl_options[CURLOPT_VERBOSE] = 1;
		//$this->curl_options[CURLOPT_STDERR] = $f;
		
		// POST data (as XML)
		$this->CI->curl->post($send_xml, $this->curl_options);
		
		$response = $this->CI->curl->execute();
		
		//fclose($f);

		return $this->parseResponse($response);
	}
	
	// PUT a request
	public function update( $id = 0 )
	{
		$url = $this->generateUrl($this->type, $id);
		if( $url === FALSE ) return FALSE;
		
		$this->CI->curl->create($url);
		$this->CI->curl->http_login($this->api_username, $this->api_password);
		
		foreach( $this->getHeaders('PUT') as $header )
		{
			$this->CI->curl->http_header($header);
		}
		
		// Get the XML to send
		$send_xml = $this->getSendXml();
		
		$len = strlen($send_xml);
		$this->CI->curl->http_header('Content-Length: ' . $len);
		
		// POST data (as XML)
		$this->CI->curl->put($send_xml, $this->curl_options);
		
		if( $this->CI->curl->execute() )
		{
			if( $this->CI->curl->info['http_code'] == '200' )
			{
				return TRUE;
			}
		}

		$this->error = 'There was a problem while trying to update entry #' . $id . ' on DonorTools.com. Please try again.';
		return FALSE;
	}
	
	// DELETE a request
	public function delete( $type, $id )
	{
		$url = $this->generateUrl($type, $id);
		if( $url === FALSE ) return FALSE;
		
		$this->CI->curl->create($url);
		$this->CI->curl->http_login($this->api_username, $this->api_password);
		
		foreach( $this->getHeaders('DELETE') as $header )
		{
			$this->CI->curl->http_header($header);
		}
		
		// POST data (as XML)
		$this->CI->curl->delete(array(), $this->curl_options);
		
		if( $this->CI->curl->execute() )
		{
			if( $this->CI->curl->info['http_code'] == '200' )
			{
				return TRUE;
			}
		}

		$this->error = 'There was a problem while trying to delete entry #' . $id . ' on DonorTools.com. Please try again.';
		return FALSE;
	}
	
	// Parse the response back from DonorTools.com
	public function parseResponse( $response )
	{
		if( $response === FALSE )
		{
			$this->error = 'There was a problem while contacting DonorTools.com. Please try again.';
			return FALSE;
		}
		elseif( is_string($response) )
		{
			$res = simplexml_load_string($response);
			if( $res !== FALSE )
			{
				// Get pagination attributes
				foreach( $res->attributes() as $key => $value )
				{
					switch( $key )
					{
						case 'current_page':
						case 'per_page':
						case 'total_entries':
						$this->{$key} = $value;
						break;
					}
				}
				$this->response = $res;
				return TRUE;
			}
		}
		
		$this->error = 'Received an unknown response from the DonorTools.com. Please try again.';
		return FALSE;
	}
	
	// Generate the URL that we'll be using for the API
	public function generateUrl( $type, $id = 0 )
	{
		if( !isset($this->api_url_actions[$type]) )
		{
			$this->error = 'Invalid request "' . $type . '"';
			return FALSE;
		}
		
		// Do we need to append any key/value pairs?
		$pairs = array();
		if( isset($this->pair_page) )
		{
			$pairs['page'] = $this->pair_page;
		}
		if( isset($this->pair_sort_order) && isset($this->pair_sort_direction) )
		{
			$pairs['order'] = $this->pair_sort_order;
			$pairs['direction'] = $this->pair_sort_direction;
		}
		if( isset($this->pair_funds_include_donors) )
		{
			$pairs['include_donors'] = 'true';
		}
		if( isset($this->pair_replacement_fund_id) )
		{
			$pairs['replacement_fund_id'] = $this->pair_replacement_fund_id;
		}
		if( isset($this->pair_replacement_source_id) )
		{
			$pairs['replacement_source_id'] = $this->pair_replacement_source_id;
		}
		
		$append = count($pairs) > 0 ? '?' . http_build_query($pairs, NULL, '&') : '';
		
		return $this->api_url . sprintf($this->api_url_actions[$type], $id) . $append;
	}
	
	// Get the headers we'll need for the request
	public function getHeaders( $method )
	{
		switch( $method )
		{
			case 'POST':
			case 'PUT':
			return array_merge($this->curl_headers, $this->curl_send_data_headers);
			break;
		}
		
		return $this->curl_headers;
	}
	
	// Set the page to show
	public function page( $page )
	{
		$this->pair_page = $page;
	}
	
	// Set the order by
	public function orderBy( $name, $dir = 'ASC' )
	{
		$this->pair_sort_order = $name;
		$this->pair_sort_direction = $dir;
	}
	
	// Include donors in funds list
	public function includeDonors()
	{
		$this->pair_funds_include_donors = TRUE;
	}
	
	// Set the replacement fund id
	public function replacementFund( $fund_id )
	{
		$this->pair_replacement_fund_id = $fund_id;
	}
	
	// Set the replacement source id
	public function replacementSource( $source_id )
	{
		$this->pair_replacement_source_id = $source_id;
	}
	
	// Get the raw response
	public function getResponse()
	{
		return $this->response;
	}
	
	// Get the raw send data
	public function getSendData()
	{
		return $this->send_data;
	}
	
	// Get the XML we want to send
	private function getSendXml()
	{
		return $this->send_data->asXML();
	}
	
	// Get the error text
	public function getError()
	{
		return $this->error;
	}
	
	/*
	 * Pagination
	 */
	// Get the current page
	public function getCurrentPage()
	{
		return $this->current_page;
	}
	
	// Get the total entries
	public function getTotalEntries()
	{
		return $this->total_entries;
	}
	
	// Get the # of entries per page
	public function getPerPage()
	{
		return $this->per_page;
	}
	
	// Calculate the total number of pages
	public function getTotalPages()
	{
		if( isset($this->total_entries) && isset($this->per_page) )
		{
			return ceil($this->total_entries / $this->per_page);
		}
		
		return 0;
	}
	
	// Get next page or 0 if no next page
	public function getNextPage()
	{
		if( isset($this->total_entries) && isset($this->per_page) && isset($this->current_page) )
		{
			return ($this->current_page < $this->getTotalPages()) ? $this->current_page + 1 : 0;
		}
		
		return 0;
	}
	
	// Get the id of the last created entry
	public function getId()
	{
		return isset($this->response->id) ? (int)$this->response->id : 0;
	}
	
	// Show debug info
	public function debug( $show_curl_debug = FALSE )
	{
		echo '<h1>DonorTools.com API Debug Info</h1>';
		$url = $this->CI->curl->debug_request();
		echo '<h3>URL: ' . $url['url'] . '</h3>';
		
		if( !empty($this->error) )
		{
			echo '<p>' . $this->error . '"</p>';
		}
		
		if( isset($this->send_data) )
		{
			echo '<h1>Send Data For "' . $this->type . '"</h1>';
			echo '<pre>';
			echo htmlspecialchars($this->formatXml($this->send_data), ENT_QUOTES, 'UTF-8');
			echo '</pre>';
		}
		
		if( isset($this->response) )
		{
			echo '<h1>Response For "' . $this->type . '"</h1>';
			echo '<pre>';
			echo htmlspecialchars($this->formatXml($this->response), ENT_QUOTES, 'UTF-8');
			echo '</pre>';
		}
		
		if( $show_curl_debug )
		{
			echo '<h1>cURL Debug Data</h1>';
			$this->CI->curl->debug();
		}
	}
	
	// Format XML nicely
	public function formatXml( $xml )
	{
		$dom = new DOMDocument('1.0');
		$dom->preserveWhiteSpace = FALSE;
		$dom->formatOutput = TRUE;
		$dom->loadXML($xml->asXML());
		return $dom->saveXML();
	}
	
	// Reset everything so we can go again
	public function clear()
	{
		$this->current_page = NULL;
		$this->per_page = NULL;
		$this->total_entries = NULL;
		
		$this->pair_page = NULL;
		$this->pair_sort_order = NULL;
		$this->pair_sort_direction = NULL;
		$this->pair_funds_include_donors = NULL;
		$this->pair_replacement_fund_id = NULL;
		$this->pair_replacement_source_id = NULL;
		
		$this->type = NULL;
		$this->send_data = NULL;
		
		$this->response = NULL;
		$this->error = NULL;
	}

}

/* EOF */