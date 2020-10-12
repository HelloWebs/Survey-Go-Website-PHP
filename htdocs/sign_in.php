<?php

// execute the header script:
require_once "header.php";

// default values we show in the form:
$username = "";
$password = "";
// strings to hold any validation error messages:
$username_val = "";
$password_val = "";

// should we show the signin form:
$show_signin_form = false;
// message to output to user:
$message = "";

if (isset($_SESSION['loggedIn']))
{
	// user is already logged in, just display a message:
	echo "You are already logged in, please log out first.<br>";

}
elseif (isset($_POST['username']))
{
	// user has just tried to log in:
	
	// connect directly to our database (notice 4th argument) we need the connection for sanitisation:
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	
	// if the connection fails, we need to know, so allow this exit:
	if (!$connection)
	{
		die("Connection failed: " . $mysqli_connect_error);
	}	
	
	// SANITISATION (see helper.php for the function definition)
	
	// take copies of the credentials the user submitted and sanitise (clean) them:
	$username = sanitise($_POST['username'], $connection);
	$password = sanitise($_POST['password'], $connection);
	
	// VALIDATION (see helper.php for the function definitions)
	
	// now validate the data (both strings must be between 1 and 16 characters long):
	// (reasons: we don't want empty credentials, and we used VARCHAR(16) in the database table)
	$username_val = validateString($username, 1, 16);
	$password_val = validateString($password, 1, 16);
	
	// concatenate all the validation results together ($errors will only be empty if ALL the data is valid):
	$errors = $username_val . $password_val;
	
	// check that all the validation tests passed before going to the database:
	if ($errors == "")
	{
		$sql = "SELECT * FROM users WHERE username = '{$username}';";
		$results  = mysqli_query($connection,$sql);
		$n = mysqli_num_rows($results);
		if($n > 0)
		{
			$row = mysqli_fetch_assoc($results);
			$hashed_password = $row['password'];
			if (password_verify($password, $hashed_password))
			{
				// set a session variable to record that this user has successfully logged in:
				$_SESSION['loggedIn'] = true;
				// and copy their username into the session data for use by our other scripts:
				$_SESSION['username'] = $username;
				
				// show a successful signin message:
				$message = "Hi, $username, you have successfully logged in, please <a href='about.php'>click here</a><br>";
			}
			else
			{
				// no matching credentials found so redisplay the signin form with a failure message:
				$show_signin_form = true;
				// show an unsuccessful signin message:
				$message = "Sign in failed, please try again<br>";
			}
			
		}
		else
		{
			// validation failed, show the form again with guidance:
			$show_signin_form = true;
			// show an unsuccessful signin message:
			$message = "Sign in failed, please check the errors shown above and try again<br>";
		
		}
	}	
	else
	{
		// validation failed, show the form again with guidance:
		$show_signin_form = true;
		// show an unsuccessful signin message:
		$message = "Sign in failed, please check the errors shown above and try again<br>";
	}
	
	// we're finished with the database, close the connection:
	mysqli_close($connection);

}
else
{
	// user has arrived at the page for the first time, just show them the form:
	
	// show signin form:
	$show_signin_form = true;
}

if ($show_signin_form)
{
// show the form that allows users to log in
// Note we use an HTTP POST request to avoid their password appearing in the URL:
echo <<<_END
<form action="sign_in.php" method="post">
<table>

<th colspan="2">  Please enter your username and password:<th>
<tr>
	<td>Username:</td>
	<td><input type="text" name="username" maxlength="16" value="$username" required> </td>$username_val
</tr>
<tr>
	<td>Password: </td>
  	<td><input type="password" name="password" maxlength="16"  required></td> $password_val
</tr>
</table>
<br>	
  <input type="submit" value="SIGN IN">

</form>
_END;



}
//(username VARCHAR(16), password VARCHAR(16), firstname VARCHAR(20), 
//surname VARCHAR(25), email VARCHAR(64), dob DATE,phone_number VARCHAR(15)

// display our message to the user:
echo $message;

// finish off the HTML for this page:
require_once "footer.php";
?>