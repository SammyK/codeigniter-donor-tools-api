<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Soft_credits extends CI_Controller
{
	// Soft Credits
	public function index( $id = 0 )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		if( $id > 0 )
		{
			// Get a soft credit for a specific persona
			$status = $this->donor_tools->get('soft-credits-persona', $id);
		}
		else
		{
			// Optional: What page we want
			$this->donor_tools->page(1);
			// Get a list of soft credits
			$status = $this->donor_tools->get('soft-credits');
		}
		
		if( $status === TRUE )
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