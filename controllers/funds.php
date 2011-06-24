<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Funds extends CI_Controller
{
	// Retrieve Funds(s)
	public function index( $id = 0 )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		if( $id > 0 )
		{
			// Get a specific fund
			$status = $this->donor_tools->get('fund', $id);
		}
		else
		{
			// Optional: What page we want
			$this->donor_tools->page(1);
			// Get a list of funds
			$status = $this->donor_tools->get('funds');
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
	
	// Create Fund
	public function create()
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Start with a fund object
		$this->donor_tools->startData('fund');
		
		// Other Data
		$this->donor_tools->addData('name', 'A Name of a New Fund');
		$this->donor_tools->addData('description', '<p>I can use <acronym title="Hyper Text Markup Language">HTML</acronym> tags.</p>');
		$this->donor_tools->addData('excerpt', 'This is a short description of the fund for use on the index page.');
		$this->donor_tools->addData('published', TRUE);
		$this->donor_tools->addData('tax-deductible', TRUE);
		// Money
		$this->donor_tools->addMoney('goal', 15000.00);
		
		// CREATE
		if( $this->donor_tools->create() )
		{
			$id = $this->donor_tools->getId();
			echo '<h2>Success! New Fund ID: ' . $id . '</h2>';
			return;
		}
		
		echo '<h2>Fail!</h2>';
		// Get error
		echo '<p>' . $this->donor_tools->getError() . '</p>';
		// Show debug data
		$this->donor_tools->debug(TRUE);
	}
	
	// Update Fund
	public function update( $id )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Start with a fund object
		$this->donor_tools->startData('fund');
		
		// Other Data
		$this->donor_tools->addData('name', 'An Updated Fund Name');
		$this->donor_tools->addData('published', FALSE);
		
		// Money
		$this->donor_tools->addMoney('goal', 3100.25);
		
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
	
	// Delete Fund
	public function delete( $id )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Replacement fund ID (required or it won't work)
		$this->donor_tools->replacementFund(0); // Change this
		
		// DELETE
		if( $this->donor_tools->delete('fund', $id) )
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