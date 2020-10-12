<?php

// execute the header script:
require_once "header.php";

// default values we show in the form:


    
// strings to hold any validation error messages:
$password_val = "";
$new_password_val = "";
 
 
// should we show the set profile form?:
$show_account_form = false;
// message to output to user:
$message = "";

if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
}
elseif (isset($_POST['password']))
{
	// user just tried to update their profile
	
	// connect directly to our database (notice 4th argument) we need the connection for sanitisation:
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	
	// if the connection fails, we need to know, so allow this exit:
	if (!$connection)
	{
		die("Connection failed: " . $mysqli_connect_error);
	}
		
	$password = sanitise($_POST['oldpassword'], $connection);

	$new_password = sanitise($_POST['newpassword'],$connection);
	$password_val= validateString($password,1,16);
	$new_password_val= validateString($new_password,1,16);
	
	$errors = "";
	
	// check that all the validation tests passed before going to the database:
	if ($errors == "")
	{		
		// read their username from the session:
		$username = $_SESSION["username"];

		// check to see if this user already had a favourite:
		$query = "SELECT password FROM users WHERE username='$username' ";
		
		// this query can return data ($result is an identifier):
		$result = mysqli_query($connection, $query);
		
		// how many rows came back? (can only be 1 or 0 because username is the primary key in our table):
		$n = mysqli_num_rows($result);
			
		// if there was a match then UPDATE their profile data, otherwise INSERT it:
		if ($n > 0)
		{	

			//compare the password with the one in the db
			$row = mysqli_fetch_assoc($result);
			$currentPassword= $row['password'];

			if(password_verify($new_password,$currentPassword))	// verify the password with hashed password
			{
				$password = password_hash($newPassword,PASSWORD_DEFAULT);	// hash the new password

				$query = "UPDATE users SET password='$password' WHERE username='$username'";
				$result = mysqli_query($connection, $query);	
			}
			else{
				$password_val = "Error current password incorrect";
			}

		}
	

		// no data returned, we just test for true(success)/false(failure):
		if ($result) 
		{
			// show a successful update message:
			$message = "Profile successfully updated<br>";
		} 
		else
		{
			// show the set profile form:
			$show_account_form = true;
			// show an unsuccessful update message:
			$message = "Update failed<br>";
		}
	}
	else
	{
		// validation failed, show the form again with guidance:
		$show_account_form = true;
		// show an unsuccessful update message:
		$message = "Update failed, please check the errors above and try again<br>";
	}
	
	// we're finished with the database, close the connection:
	mysqli_close($connection);

}
else
{
	// arrived at the page for the first time, show any data already in the table:
	
	// read the username from the session:
	$username = $_SESSION["username"];
	
	// now read their profile data from the table...
	
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
	if ($n > 0)
	{
		// use the identifier to fetch one row as an associative array (elements named after columns):
		$row = mysqli_fetch_assoc($result);
		// extract their profile data for use in the HTML:
	

	}
	
	// show the set profile form:
	$show_account_form = true;
	
	// we're finished with the database, close the connection:
	mysqli_close($connection);
	
}

if ($show_account_form)
{
	// use the identifier to fetch one row as an associative array (elements named after columns):
		$row = mysqli_fetch_assoc($result);
		// display their profile data:	
		echo <<<_END
		<form action="account_set.php" method="post">
		<table>
		
		<tr>
			<td>Old Password</td>
			 
			<td><form action="account.php" method="post"> 
			<input type="text" name="oldpassword"></form></td>
		</tr>
		
		<tr>
			<td>New Password</td>
			 
			<td><form action="account.php" method="post"> 
			<input type="text" value="" name="newpassword"></form></td>
		</tr>
		<input type="submit" name="password" value="Change">
		</form>
	
_END;

}

// display our message to the user:
echo $message;

// finish of the HTML for this page:
require_once "footer.php";
?>