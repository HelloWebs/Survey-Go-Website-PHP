<?php

// execute the header script:
require_once "header.php";

// default values we show in the form:
$username = "";
$password = "";
$email = "";
$firstname = "";
$surname = "";
$dob = "";
$phone_number = "";

// strings to hold any validation error messages:
$username_val = "";
$password_val = "";
$email_val = "";
$firstname_val = "";
$surname_val = "";
$dob_val = "";
$phone_number_val = "";

// should we show the signup form?:
$show_signup_form = false;
// message to output to user:
$message = "";

if (isset($_SESSION['loggedIn']))
{
	// user is already logged in, just display a message:
	echo "You are already logged in, please log out if you wish to create a new account<br>";
	
}
elseif (isset($_POST['username']))
{
	// user just tried to sign up:
	
	// connect directly to our database (notice 4th argument) we need the connection for sanitisation:
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	
	// if the connection fails, we need to know, so allow this exit:
	if (!$connection)
	{
		die("Connection failed: " . $mysqli_connect_error);
	}	
	
	// SANITISATION (see helper.php for the function definition)
	
	// take copies of the credentials the user submitted, and sanitise (clean) them:
	$username = sanitise($_POST['username'], $connection);
	$password = sanitise($_POST['password'], $connection);
	$firstname = sanitise($_POST['firstname'],$connection);
	$surname = sanitise($_POST['surname'],$connection);
	
	$dob = sanitise($_POST['dob'],$connection);
	$phone_number = sanitise($_POST['phone_number'],$connection);

	$email = sanitise($_POST['email'], $connection);


	// VALIDATION (see helper.php for the function definitions)
	
	// now validate the data (both strings must be between 1 and 16 characters long):
	// (reasons: we don't want empty credentials, and we used VARCHAR(16) in the database table for username and password)
    // firstname is VARCHAR(32) and lastname is VARCHAR(64) in the DB
    // email is VARCHAR(64) and telephone is VARCHAR(16) in the DB
	$username_val = validateString($username, 1, 16);
	$password_val = validateString($password, 1, 16);
	
	 //the following line will validate the email as a string, but maybe you can do a better job...
	$email_val = validateString($email, 1, 64);

	$firstname_val = validateString($firstname, 1, 25);
	$surname_val = validateString($surname, 1, 25);
	//see helper.php  
	$dob_val = validateDate($dob);
	// do a regex check to see if the phone number is all numbers and its length is less than 15
	if(preg_match("/[^0-9]/",$phone_number) && (strlen($phone_number) <= 15))
	{
		$phone_number_val = "Invalid Phone number";
	}
	// (username VARCHAR(16), password VARCHAR(16), firstname VARCHAR(25), surname VARCHAR(25),
	//  email VARCHAR(64), dob DATE,phone_number VARCHAR(15)

	$query = "Select username from users where username ='$username'";
	$result = mysqli_query($connection,$query);
	$n = mysqli_num_rows($result);
	if($n > 0) 
	{
		$username_val = $username_val." Username already taken!";
	}
	
	// concatenate all the validation results together ($errors will only be empty if ALL the data is valid):
	$errors = $username_val . $password_val . $email_val . $firstname_val . $surname_val . $dob_val . $phone_number_val;
	
	// check that all the validation tests passed before going to the database:
	if ($errors == "")
	{  
		$password = password_hash($password,PASSWORD_DEFAULT);
		// try to insert the new details:
		$query = "INSERT INTO users (username, password, firstname, surname, email, dob, phone_number) 
		VALUES ('$username', '$password','$firstname','$surname', '$email','$dob','$phone_number');";
		$result = mysqli_query($connection, $query);
		
		// no data returned, we just test for true(success)/false(failure):
		if ($result) 
		{
			// show a successful signup message:
			$message = "Signup was successful, please sign in<br>";
		} 
		else 
		{
			// show the form:
			$show_signup_form = true;
			// show an unsuccessful signup message:
			$message = "Sign up failed, please try again<br>";
		}
			
	}
	else
	{
		// validation failed, show the form again with guidance:
		$show_signup_form = true;
		// show an unsuccessful signin message:
		$message = "<br>Sign up failed, please check the errors shown above and try again<br>";
	}
	
	// we're finished with the database, close the connection:
	mysqli_close($connection);

}
else
{
	// just a normal visit to the page, show the signup form:
	$show_signup_form = true;
	
}

if ($show_signup_form)
{
// show the form that allows users to sign up
// Note we use an HTTP POST request to avoid their password appearing in the URL:	
echo <<<_END

<form action="sign_up.php" method="post">
  <table>
  <th colspan="2">Please enter your details below to sign up:     <th>

  <tr>
	  <td>Username: </td>
	  <td><input type="text"   name="username" maxlength="16" minlength="1" value="$username" required> $username_val</td>
 </tr>	
  <tr>
   <td>Email: </td>
  <td><input type="email" name="email" maxlength="64" value="$email" required> $email_val </td>
  </tr>
  
  <tr>
  	<td>Password: </td>
 	<td><input type="password" name="password" maxlength="16" value="$password" required> $password_val  </td>
  </tr>
 
  <tr>
	<td>Firstname:</td>
	<td><input type="text" name="firstname" minlength="1" maxlength="25" value="$firstname" required> $firstname_val  </td>
  </tr>
  <tr>
	<td>Surname </td>
	<td><input type="text" name="surname" minlength="1" maxlength="25"  value="$surname" required> $surname_val</td>
  </tr>
  <tr>
	<td>Date of Birth </td>
	<td><input type="date" name="dob" value="$dob" required> $dob_val</td>
  </tr>
  <tr>
	<td>Phone number 
	<td><input type="text" name="phone_number"value="$phone_number" minlength="1" maxlengths="15" required> $phone_number_val</td>
  </tr>
  </table>
  <br>
<input type="submit" value="SIGN UP">
</form>	
<br>
<a href="sign_in.php"> SIGN IN</a>
_END;
// (username VARCHAR(16), password VARCHAR(16), firstname VARCHAR(20), surname VARCHAR(25), 
// email VARCHAR(64), dob DATE,phone_number VARCHAR(15), PRIMARY KEY(username))";

}

// display our message to the user:
echo $message;

// finish off the HTML for this page:
require_once "footer.php";

?>