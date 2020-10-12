<?php

// Things to notice:
// This script is called by every other script (via require_once)
// It begins the HTML output, with the customary tags, that will produce each of the pages on the web site
// It starts the session and displays a different set of menu links depending on whether the user is logged in or not...
// ... And, if they are logged in, whether or not they are the admin
// It also reads in the credentials for our database connection from credentials.php

// database connection details:
require_once "credentials.php";

// our helper functions:
require_once "helper.php";

// start/restart the session:
session_start();

if (isset($_SESSION['loggedIn']))
{
	// THIS PERSON IS LOGGED IN
	// show the logged in menu options:

echo <<<_END
<!DOCTYPE html>
<html>
	<head><title>A Survey Website</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="js/script.js"></script>
	<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
<div id = "container">
<center>
<h1>Survey Website</h1>

<a href='about.php'>About</a> ||
<a href='account.php'>My Account</a> ||
<a href='surveys_manage.php'>My Surveys</a> ||
<a href='competitors.php'>Design and Analysis</a> ||
<a href='sign_out.php'>Sign Out ({$_SESSION['username']})</a>

_END;
	// add an extra menu option if this was the admin:
	if ($_SESSION['username'] == "admin")
	{
		echo " |||| <a href='admin.php'>Admin Tools</a>";
	}
echo "<hr></center>";

}
else
{
	// THIS PERSON IS NOT LOGGED IN
	// show the logged out menu options:
	


echo <<<_END
<!DOCTYPE html>	
<html>
<head><title>A Survey Website</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="jquery-3.3.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
<div id = "container">
<center>
<h1>Survey Website</h1>

<a href='about.php'>About</a> ||
<a href='sign_up.php'>Sign Up</a> ||
<a href='sign_in.php'>Sign In</a>
<hr>
</center>

<br>
<br>
_END;
}
?>