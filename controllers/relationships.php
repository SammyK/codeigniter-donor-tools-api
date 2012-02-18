<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Relationships extends CI_Controller
{
	// There is no documention for this as of 2012-02-17
	
	// Retrieve Relationships (for a persona)
	public function index( $id = 0 )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Optional: What page we want
		$this->donor_tools->page(1);
		// Optional: Order the results
		$this->donor_tools->orderBy('added','ASC');
		
		if( $id > 0 )
		{
			// Get a list of relationships for a specific persona
			$status = $this->donor_tools->get('relationships', $id);
		}
		else
		{
			// Get a list of all relationships
			$status = $this->donor_tools->get('relationships-all');
		}
			
		if( $status === TRUE )
		{
			// Data is returned as a SimpleXMLElement object
			$data = $this->donor_tools->getResponse();
			
			//var_dump($data);
			
			foreach( $data->relationship as $rel )
			{
				echo '<pre>';
				echo 'Date: ' . $rel->{'created-at'} . "\n";
				echo 'Persona ID: ' . $rel->{'persona-id'} . "\n";
				echo 'Inverse Persona ID: ' . $rel->{'inverse-persona-id'} . "\n";
				echo 'Type ID: ' . $rel->{'relationship-type-id'} . "\n";
				echo '</pre>';
			}
			
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
	
	// Retrieve available fields for creating a new entry
	public function fields( $id )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Get field list
		$status = $this->donor_tools->get('relationship-new', $id);
		
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
	
	// Create Relationship
	public function create( $person_id, $inverse_person_id )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Start with a relationship object
		$this->donor_tools->startData('relationship');
		
		// Primary Person
		$this->donor_tools->addData('persona-id', $person_id);
		// Secondary Person
		$this->donor_tools->addData('inverse-persona-id', $inverse_person_id);
		// Relationship type (from primary person perspective)
		$this->donor_tools->addData('relationship-type-id', 25); // Change this (see possible values below)
		/*
		=== Relationship Types ===
		ID     Relationship Type  Complement
		20     Acquaintence       Acquaintence
		9      Ancestor           Descendent
		33     Aunt/Uncle         Nephew/Niece
		4      Child              Parent
		21     Colleague          Colleague
		36     Cousin             Cousin
		10     Descendent         Ancestor
		12     Employee           Employer
		11     Employer           Employee
		15     Fiancé (groom)     Fiancée (bride)
		16     Fiancée (bride)    Fiancé (groom)
		19     Friend             Friend
		8      Grandchild         Grandparent
		7      Grandparent        Grandchild
		1      Husband            Wife
		14     Mentee             Mentor
		13     Mentor             Mentee
		22     Neighbor           Neighbor
		34     Nephew/Niece       Aunt/Uncle
		3      Parent             Child
		27     Parish             Parishoner
		28     Parishoner         Parish
		43     Partner            Partner
		18     Sibling            Sibling
		25     Sponsor            Sponsoree
		26     Sponsoree          Sponsor
		17     Spouse             Spouse
		24     Student            Teacher
		23     Teacher            Student
		2      Wife               Husband
		*/
		
		// CREATE
		if( $this->donor_tools->create($person_id) )
		{
			$id = $this->donor_tools->getId();
			echo '<h2>Success! New Relationship ID: ' . $id . '</h2>';
			return;
		}
		
		echo '<h2>Fail!</h2>';
		// Get error
		echo '<p>' . $this->donor_tools->getError() . '</p>';
		// Show debug data
		$this->donor_tools->debug(TRUE);
	}
	
	// Update Relationship
	public function update( $person_id, $relationship_id )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// Start with a relationship object
		$this->donor_tools->startData('relationship');
		
		// New relationship type
		$this->donor_tools->addData('relationship-type-id', 12); // Change this (see possible vaules in create() above)
		
		// UPDATE
		if( $this->donor_tools->update($person_id, $relationship_id) )
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
	
	// Delete Relationship
	public function delete( $person_id, $relationship_id )
	{
		// Load Donortools.com Lib
		$this->load->library('donor_tools');
		
		// DELETE
		if( $this->donor_tools->delete('relationship', $person_id, $relationship_id) )
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