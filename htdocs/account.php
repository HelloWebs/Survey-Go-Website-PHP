<?php

// Things to notice:
// The main job of this script is to execute a SELECT statement to find the user's profile information (then display it)

// execute the header script:
require_once "header.php";



if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
}
else
{    
    // user is already logged in, read their username from the session:
	$username = $_SESSION["username"];
	
	// now read their account data from the table...
	// connect directly to our database (notice 4th argument):
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	
	// if the connection fails, we need to know, so allow this exit:
	if (!$connection)
	{
		die("Connection failed: " . $mysqli_connect_error);
	}
	
	// check for a row in our profiles table with a matching username:
	$query = "SELECT * FROM users WHERE username='$username'";
	
	// this query can return data ($result is an identifier):
	$result = mysqli_query($connection, $query);
	
	// how many rows came back? (can only be 1 or 0 because username is the primary key in our table):
	$n = mysqli_num_rows($result);
		
	// if there was a match then extract their profile data:

	echo "<table class='table'>";
	echo "<th colspan='2' style='text-align:left'>Here are you account details:</th>";
	if ($n > 0)
	{
		// use the identifier to fetch one row as an associative array (elements named after columns):
		$row = mysqli_fetch_assoc($result);
		// display their profile data:
		echo "<tr><td>Username</td> <td>{$row['username']}</td><tr>";
		echo "<tr><td>Email</td> <td>{$row['email']}</td><tr>";
//		echo "<tr><td>Password</td> <td>{$row['password']}</td><tr>";
		echo "<tr><td>Firstname</td> <td>{$row['firstname']}</td><tr>";
		echo "<tr><td>Surname</td> <td>{$row['surname']}</td><tr>";
		echo "<tr><td>Date of Birth</td> <td>{$row['dob']}</td><tr>";
		echo "<tr><td>Phone Number</td> <td>{$row['phone_number']}</td><tr>";
		echo "</table>";

		echo <<<_end
		<br>
		<form action="account_set.php" method="post">
		<input type="submit" name="update "value="Update">
		</form>
_end;
	//username email
//password firstname surname  dob phone
 

	}
	else
	{
		// no match found, prompt user to set up their profile:
		echo "You still need to set up a profile!<br>";
	}
	
	// we're finished with the database, close the connection:
	mysqli_close($connection);
		
}

// finish off the HTML for this page:
require_once "footer.php";
?>