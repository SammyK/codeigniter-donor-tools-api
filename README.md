Codeigniter DonorTools.com API Integration
==========================================

A library that allows you to manage nearly every aspect of your donor tools account.

Requires [Philip Sturgeon's](http://philsturgeon.co.uk/) [cURL lib](http://getsparks.org/packages/curl/show) (included).

Installation
------------

1. Copy the /config/donor_tools.php file into your application's config/ folder and make sure to change the values!
2. Copy /libraries/Donor_tools.php and /libraries/Curl.php into your application's libraries/ folder.

Usage
-----

The library is equipped to handle Create (POST), Retrieve (GET), Update (PUT) and Delete (DELETE) or
"CRUD" requests to the DonorTools.com API.

### Initialization

First and foremost, you must load the library of course:

	$this->load->library('donor_tools');

As with most libraries, you can send in an array of config data.

### Retrieve (GET) Data

One of the most straight-forward calls is a GET. I could get a list of personas (or "donors"):

	$this->donor_tools->get('personas');

This method will return TRUE on success and FALSE on failure. If TRUE, the response will be waiting
for you in a nice SimpleXMLElement object:

	$data = $this->donor_tools->getResponse();

If FALSE, there will be an error waiting for you:

	$error = $this->donor_tools->getError();

Note that "personas" is the name of the root tag that we receive as seen 
[in the documentation](http://www.donortools.com/userguide/api/personas). With that in mind if I
wanted to get a list of funds, I would consult the
[documentation on funds](http://www.donortools.com/userguide/api/03-funds) and see that the root tag
is "funds" so in order to run a get request, I use the root tag name:

	$this->donor_tools->get('funds');

If I wanted a specific fund, I would use the singular version of the root tag name "fund" and pass
get() the fund's ID:

	$this->donor_tools->get('fund', 123);

### Create (POST) Data

Before we can just start adding data to the object willy-nilly, we need to instantiate the type of
data we are going to send. We'll use the singular form of the type of data we want to send. For
example a persona:

	$this->donor_tools->startData('persona');

Now we can start adding whatever tag we like. There are are three ways to do this.

#### General Data

Most data can be added to our object using addData(). The library relies on the naming schema of the
XML DOM so that future changes to the API can be accommodated without changing the library. For
instance, if in the XML you need to send a tag <foo>, you simply add your data like so:

	$this->donor_tools->addData('foo', 'Bar');

It will be helpful to consult the [Donor Tools API Documentation](http://www.donortools.com/userguide/api/)
to see all the possible tag names for each data type. So since we're working with a persona, we
could add a birth date:

	$this->donor_tools->addData('birth-date', '1984-02-13');

Or maybe some tags:

	$this->donor_tools->addData('tag-list', array('Tag One', 'Tag Two'));

#### Set Data

Personas can have multiple names, emails, addresses and phone numbers associated with them.
Therefore we must treat each of those data differently. We could add a name:

	$this->donor_tools->addSet('name', array(
		'first-name' => 'Some',
		'last-name' => 'Name',
		));

Or even two!

	$this->donor_tools->addSet('name', array(
		'first-name' => 'Another',
		'last-name' => 'Name',
		));

Notice that for the set name, we're using the singular form. The library will automatically
"pluralize" the name for the tag. One more example - an email:

	$this->donor_tools->addSet('email-address', array('email-address' => 'test@example.com'));

#### Money

Since money has it's own "Money" attribute and we don't want to get a zip code confused with a money
type, it has its own method:

	$this->donor_tools->addMoney('goal', 1500.00);

Obviously personas don't have a money data type, but funds do!

Now that we have all the data we want to add, we send the create request:

	$this->donor_tools->create();

The create() method will return TRUE on success and FALSE on failure. We get our response or error 
the same way we did with the get() method:

	$data = $this->donor_tools->getResponse();

Or on error:

	$error = $this->donor_tools->getError();

### Update (PUT) Data

Update works just like create() in that we need to initialize our data object with startData(), we
need to add data to our object with addData(), addSet() or addMoney() and then we can send the update
request with the id of the entry we are updating:

	$this->donor_tools->update(123);

### Delete (DELETE) Data

Deleting data is almost as simple as get(). All you need is the id of the entry you want to delete
and the type of object:

	this->donor_tools->delete('persona', 123);

There are two instances where this is slightly more complicated in that funds and sources need to
have replacements. So we need to add the replacement id's before sending the request:

	$this->donor_tools->replacementFund(65);
	$this->donor_tools->delete('fund', 123);

Or for sources

	$this->donor_tools->replacementSource(19);
	$this->donor_tools->delete('source', 1234);

For a full set of examples with more values and error handling, check out the files in the /controllers folder.

Enjoy!
[SammyK](http://sammyk.me/)
