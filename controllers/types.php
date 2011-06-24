<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Types extends CI_Controller
{
	// Types
	public function index( $type = 'donation-types' )
	{
		// Only thess types are allowed
		switch( $type )
		{
			case 'donation-types':
			case 'name-types':
			case 'address-types':
			break;
		
			default:
			echo '<h2>Invalid type "' . $type . '"</h2>';
			return;
			break;
		}
		
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Get this type info
		if( $this->donor_tools->get($type) )
		{
			// Data is returned as a SimpleXMLElement object
			$data = $this->donor_tools->getResponse();
			
			var_dump($data);
			return;
		}
		
		echo '<h2>Fail!</h2>';
		// Get error
		echo '<p>' . $this->donor_tools->getError() . '</p>';
		// Show debug data
		$this->donor_tools->debug(TRUE);
	}
	
}

/* EOF */