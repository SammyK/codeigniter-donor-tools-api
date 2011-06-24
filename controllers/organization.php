<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Organization extends CI_Controller
{
	/*
	 * Your API user must be set to "Administrator" to use get('organization')
	 */
	// Retrieve Organization
	public function index()
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Get this organization info
		$status = $this->donor_tools->get('organization');
		
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
	
	// Retrieve Organization Stats
	public function stats()
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Get this organization stats info
		$status = $this->donor_tools->get('organization-count');
		
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
	
	/*
	 * Your API user must be set to "Administrator" to use update()
	 */
	// Update Organization
	public function update()
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Start with an organization object
		$this->donor_tools->startData('organization');
		
		// Other Data
		$this->donor_tools->addData('name', 'My Organization');
		$this->donor_tools->addData('profile', 'Some description.');
		
		// UPDATE
		if( $this->donor_tools->update() )
		{
			echo '<h2>Updated Successfully!</h2>';
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