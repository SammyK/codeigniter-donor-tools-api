<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Personas extends CI_Controller
{
	// Retrieve Persona(s)
	public function index( $id = 0 )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		if( $id > 0 )
		{
			// Get a specific persona
			$status = $this->donor_tools->get('persona', $id);
		}
		else
		{
			// Optional: What page we want
			$this->donor_tools->page(1);
			// Optional: Order the results
			$this->donor_tools->orderBy('added','ASC');
			// Get a list of personas
			$status = $this->donor_tools->get('personas');
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
	
	// Create Persona
	public function create()
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Start with a persona object
		$this->donor_tools->startData('persona');
		
		// Name
		$name_one = array(
			'first-name'		=> 'New',
			'last-name'			=> 'Guy',
			);
		$this->donor_tools->addSet('name', $name_one);
		
		// Email
		$email_one = array(
			'email-address'		=> 'test@example.com',
			);
		$this->donor_tools->addSet('email-address', $email_one);
		
		// US Address
		$address_one = array(
			'street-address'	=> '123 Example St.',
			'city'				=> 'San Jose',
			'state'				=> 'CA',
			'postal-code'		=> '90210',
			'country'			=> 'US',
			);
		$this->donor_tools->addSet('address', $address_one);
		
		// Icelandic Address
		$address_two = array(
			'street-address' => 'Nýlendugata 61',
			'city' => 'Reykjavik',
			'state' => 'Capitol Reg',
			'postal-code' => '101',
			'country' => 'IS',
			);
		$this->donor_tools->addSet('address', $address_two);
		
		// Phone
		$phone_one = array(
			'phone-number'		=> '555-111-1234',
			);
		$this->donor_tools->addSet('phone-number', $phone_one);
		
		// Tags
		$this->donor_tools->addData('tag-list', array('Tag One', 'Tag Two'));
		
		// Other Data
		$this->donor_tools->addData('birth-date', '1984-02-13');
		$this->donor_tools->addData('salutation', 'Hey Dude!');
		
		// CREATE
		if( $this->donor_tools->create() )
		{
			$id = $this->donor_tools->getId();
			echo '<h2>Success! New Persona ID: ' . $id . '</h2>';
			return;
		}
		
		echo '<h2>Fail!</h2>';
		// Get error
		echo '<p>' . $this->donor_tools->getError() . '</p>';
		// Show debug data
		$this->donor_tools->debug(TRUE);
	}
	
	// Update Persona
	public function update( $id )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Start with a persona object
		$this->donor_tools->startData('persona');
		
		// New Address
		/*
		 * The address ID ("id" below) is different from the persona ID.
		 * You'll get a 404 error if this is wrong, so make sure to update
		 * it with the correct value and uncomment it. If you leave it out,
		 * it will add the address as a new address under this persona
		 */
		$address_one = array(
			//'id'				=>	0, // Change this and uncomment to edit an existing address
			'street-address'	=> '444 New Home Lane',
			'city'				=> 'Lexington',
			'state'				=> 'KY',
			'postal-code'		=> '40502',
			'country'			=> 'US',
			);
		$this->donor_tools->addSet('address', $address_one);
		
		// Other Data
		$this->donor_tools->addData('salutation', 'Hey New Guy!');
		$this->donor_tools->addData('salutation-formal', 'Mr. Example');
		
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
	
	// Delete Persona
	public function delete( $id )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// DELETE
		if( $this->donor_tools->delete('persona', $id) )
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