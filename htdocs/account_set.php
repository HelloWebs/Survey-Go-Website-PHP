<?php
require_once "header.php";

//  form values	
$username = "";
$password = "";
$email = "";
$firstname = "";
$surname = "";
$dob = "";
$phone_number = "";

// strings to hold any validation error messages:
$username_val="";
$password_val="";
$email_val="";
$firstname_val="";
$surname_val="";
$dob_val="";
$phone_number_val="";
 
// should we show the set profile form?:
$show_account_form = false;
// message to output to user:
$message = "";

if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
}
elseif (isset($_POST['email']))
{
	// user just tried to update their profile
	//connect to the database with values provided by credentials
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	
	// if the connection fails, we need to know, so allow this exit:
	if (!$connection)
	{
		die("Connection failed: " . $mysqli_connect_error);
	}

			$username = sanitise($_POST['username'], $connection);//
			$orig_username =  $_SESSION["username"];	// grab original username so if has changed username we would know
			// $password = sanitise($_POST['password'], $connection);
			$firstname = sanitise($_POST['firstname'],$connection);
			$surname = sanitise($_POST['surname'],$connection);
			
			$dob = sanitise($_POST['dob'],$connection);
			$phone_number = sanitise($_POST['phone_number'],$connection);

			$email = sanitise($_POST['email'], $connection);

			$username_val = validateString($username, 1, 16);
			$email_val = validateEmail($email);	

			$firstname_val = validateString($firstname, 1, 25);
			$surname_val = validateString($surname, 1, 25);
			//see helper.php  
			$dob_val = validateDate($dob);
			// do a regex check to see if the phone number has anything that isn't anumber and its length is less than 15
			if(preg_match("/[^0-9]+/",$phone_number) && (strlen($phone_number) <= 15))
			{	
				$phone_number_val = "Invalid Phone number";
			}
		
			if(strcmp($orig_username, $username) != 0)			//Has user changed the username?
			{												// they have, see if its not taken already
				$query = "Select username from users where username ='$username'";		// check if the user name is an existing username
				$result = mysqli_query($connection,$query);
				$n = mysqli_num_rows($result);
				
				if($n > 0) 			//1 if there is  already one	
				{
					$username_val = $username_val." Username already taken!";
				}
			}

			$errors = $username_val . $email_val . $firstname_val . $surname_val . $dob_val . $phone_number_val;

			if ($errors == "")
			{  	// no errors
				// try to insert the new details:

				$query = "UPDATE users set
				username='$username', firstname='$firstname', surname='$surname', 
				email ='$email', dob='$dob', phone_number='$phone_number' WHERE username='$orig_username';";
				
				$result = mysqli_query($connection, $query);		// execute
				echo mysqli_error($connection);

				// no data returned, we just test for true(success)/false(failure):
				if ($result) 
				{
					// show a successful update
					$message = "<h4>Detail updated successfull</h4><br>";
					$_SESSION['username']= $username;
					//hide the form
					$show_account_form = false;
				} 
				else // error has occured with database
				{	// show the update form again
					
					$show_account_form = true;
					// show an unsuccessful update message
					$message = "Failed please try again<br>";
				}
			}	
			else{

				$show_account_form = true;

				// show an unsuccessful update message
				$message = "Failed please see errors and try again<br>".$errors;
			
			}

	// we're finished with the database, close the connection:.
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
		$username =  $row['username'];
		//$password =  $row['password'];
		$email =  $row['email'];
		$firstname =  $row['firstname'];
		$surname =  $row['surname'];
		$dob =  $row['dob'];
		$phone_number =  $row['phone_number'];



	}
	
	// show the set profile form:
	$show_account_form = true;
	
	// we're finished with the database, close the connection:
	mysqli_close($connection);
	
}

if ($show_account_form)
{
	// use the identifier to fetch one row as an associative array (elements named after columns):
		// display their profile data
		echo <<<_END
		<form action="account_set.php" method="post">
		<table>
		<th colspan='2' style='text-align:left'>Here are you account details:</th>

		<tr>
			<td>Username</td>		
			<td><input type="text" name="username" maxlength="16" minlength="1" value='{$_SESSION['username']}'" required>$username_val</td>
		</tr>	
		<tr>
			<td>Email</td> 
			<td>  <input type="email" name="email" maxlength="64" value="$email" required> $email_val </td>
		</tr>
		<tr>
			<td>Password</td>
			 
			<td><a href="set_password.php">Change Password</a></td>
		</tr>
		<tr>
			<td>Firstname</td> 
			<td>  <input type="text" name="firstname" minlength="1" maxlength="25" value="$firstname" required> $firstname_val </td>
		</tr>
		<tr>
			<td>Surname</td>
			<td>  <input type="text" name="surname" minlength="1" maxlength="25"  value="$surname" required> $surname_val</td>
		</tr>
		<tr>
			<td>Date of Birth</td> 
			<td>  <input type="date" name="dob" value="$dob" required>$dob_val</td>
		</tr>
		<tr>
			<td>Phone Number</td> 
			<td>  <input type="number" name="phone_number" value="$phone_number" minlength="1" maxlength="15" required> $phone_number_val <br></td>
		</tr>
		</table>
		<br>

		<input style="margin-left:15px;" type="submit" name="update  "value="Update">
		<br>
	</form>
	<br>
_END;

}

// display our message to the user:
echo $message;
echo "<br><a href='account.php'>Account</a>";
require_once "footer.php";
?>