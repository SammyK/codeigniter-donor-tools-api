<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sources extends CI_Controller
{
	// Retrieve Source(s)
	public function index( $id = 0 )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		if( $id > 0 )
		{
			// Get a specific source
			$status = $this->donor_tools->get('source', $id);
		}
		else
		{
			// Optional: What page we want
			$this->donor_tools->page(1);
			// Get a list of sources
			$status = $this->donor_tools->get('sources');
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
	
	// Create Source
	public function create()
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Start with a source object
		$this->donor_tools->startData('source');
		
		// Other Data
		$this->donor_tools->addData('name', 'A New Source Of Income');
		$this->donor_tools->addData('description', 'This is a short description of the source.');
		
		// CREATE
		if( $this->donor_tools->create() )
		{
			$id = $this->donor_tools->getId();
			echo '<h2>Success! New Source ID: ' . $id . '</h2>';
			return;
		}
		
		echo '<h2>Fail!</h2>';
		// Get error
		echo '<p>' . $this->donor_tools->getError() . '</p>';
		// Show debug data
		$this->donor_tools->debug(TRUE);
	}
	
	// Update Source
	public function update( $id )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Start with a source object
		$this->donor_tools->startData('source');
		
		// Other Data
		$this->donor_tools->addData('name', 'An Updated Source Name!');
		
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
	
	// Delete Source
	public function delete( $id )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Replacement source ID (required or it won't work)
		$this->donor_tools->replacementSource(0); // Change this
		
		// DELETE
		if( $this->donor_tools->delete('source', $id) )
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