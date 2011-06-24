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

The library is equipped to handle create (POST), retrieve (GET), update (PUT) and delete (DELETE)
requests to the DonorTools.com API.

First and foremost, you must load the library of course:

	$this->load->library('donor_tools');

{Add more here documentation}

For a full set of examples with more values and error handling, check out the files in the /controllers folder.

Enjoy!
[SammyK](http://sammyk.me/)
