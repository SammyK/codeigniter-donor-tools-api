<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Donations extends CI_Controller
{
	// Retrieve Donation(s)
	public function index( $id = 0 )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		if( $id > 0 )
		{
			// Get a specific donation
			$status = $this->donor_tools->get('donation', $id);
		}
		else
		{
			// Optional: What page we want
			$this->donor_tools->page(1);
			// Get a list of donations
			$status = $this->donor_tools->get('donations');
		}
		
		if( $status === TRUE )
		{
			// Data is returned as a SimpleXMLElement object
			$data = $this->donor_tools->getResponse();
			
			var_dump($data);
			
			// Show the data in a pretty format
			echo '<pre>';
			echo htmlspecialchars($this->donor_tools->formatXml($data));
			echo '</pre>';
			return;
		}
		
		echo '<h2>Fail!</h2>';
		// Get error
		echo '<p>' . $this->donor_tools->getError() . '</p>';
		// Show debug data
		$this->donor_tools->debug(TRUE);
	}
	
	// Create Donation
	public function create()
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Start with a donation object
		$this->donor_tools->startData('donation');
		
		// Donation Info
		$donation_info = array(
			'amount-in-cents'	=> 2000,
			'fund-id'			=> 0, // Change this
			'memo'				=> 'TPS Reports',
			);
		$this->donor_tools->addSet('split', $donation_info);
		
		// Other Data
		$this->donor_tools->addData('donation-type-id', 0); // Change this
		$this->donor_tools->addData('persona-id', 0); // Change this
		$this->donor_tools->addData('source-id', 0); // Change this
		$this->donor_tools->addData('transaction-id', '2160755875');
		
		// CREATE
		if( $this->donor_tools->create() )
		{
			$id = $this->donor_tools->getId();
			echo '<h2>Success! New Donation ID: ' . $id . '</h2>';
			return;
		}
		
		echo '<h2>Fail!</h2>';
		// Get error
		echo '<p>' . $this->donor_tools->getError() . '</p>';
		// Show debug data
		$this->donor_tools->debug(TRUE);
		echo '</html>';
	}
	
	// Update Donation
	public function update( $id )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Start with a donation object
		$this->donor_tools->startData('donation');
		
		// Donation Info
		$donation_info = array(
			'id'				=> 0, // This split ID (Change this or you'll get a 404 error)
			'memo'				=> 'My new memo',
			);
		$this->donor_tools->addSet('split', $donation_info);
		
		// Other Data
		$this->donor_tools->addData('transaction-id', '123');
		
		// UPDATE
		if( $this->donor_tools->update($id) )
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
	
	// Delete Donation
	public function delete( $id )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// DELETE
		if( $this->donor_tools->delete('donation', $id) )
		{
			echo '<h2>Deleted Successfully!</h2>';
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