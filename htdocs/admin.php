<?php

// Things to notice:
// You need to add code to this script to implement the admin functions and features


// execute the header script:
require_once "header.php";
$user = "";
$errors="";
$show_form =false;

$username = "";
$password = "";
$email = "";
$firstname = "";
$surname = "";
$dob = "";
$phone_number = "";
	
//admin update validation
$username_val = "";
$password_val = "";
$email_val = "";
$firstname_val = "";
$surname_val = "";
$dob_val = "";
$phone_number_val = "";


$message = "";
$profile= array();


if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
}
else
{
	// only display the page content if this is the admin account (all other users get a "you don't have permission..." message):
	if ($_SESSION['username'] == "admin")
	{
		$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
		if (!$connection)	{die("Connection failed: " . $mysqli_connect_error);}


		if(isset($_POST['delete']))
		{

			//admin wants to delete
			$user = $_POST['user'];					// get the id 
			$user = sanitise($user,$connection);	// clean + remove malicous input + escap any html
			$errors = validateString($user,1,16);	// check username length
			
			if($errors=="")
			{
				// no errors found
				
				$query= "DELETE FROM users WHERE username = '{$user}'";

				if ( mysqli_query($connection, $query))
				{ 
					echo "<br>User '$user' deleted successfully<br>";
				}else
				{
					echo mysqli_error($connection);
				}

			}
		}
		elseif(isset($_POST['edit']))	// admin  wants to edit a user
		{
			// admin wants 
			$user = $_POST['user'];				// get the id 
			$user = sanitise($user,$connection);	// clean + remove malicous input + escap any html
			$errors = validateString($user,1,16);	// check username length
			
			if($errors=="")
			{
				$query = "SELECT * FROM users WHERE username='$user'";
	
				// this query can return data ($result is an identifier):
				$result = mysqli_query($connection, $query);
				
				// how many rows came back? (can only be 1 or 0 because username is the primary key in our table):
				$n = mysqli_num_rows($result);
					
				// if there was a match then extract their profile data:
				if ($n > 0)
				{
					// get the database resulset as associated array
					$profile = mysqli_fetch_assoc($result);		// global variable
					// extract their profile data for use in the HTML:
				}
				$show_form =true;

			}
			

		}
		elseif(isset($_POST['update']))
		{
			// clean + remove malicous input + escap any html = prevent xss cross site and sql injection
			$username = sanitise($_POST['username'], $connection);//
			$orig_username = sanitise($_POST['original_user'], $connection);	// grab original username so if admin has changed username we would know
			$password = sanitise($_POST['password'], $connection);
			$firstname = sanitise($_POST['firstname'],$connection);
			$surname = sanitise($_POST['surname'],$connection);
			
			$dob = sanitise($_POST['dob'],$connection);
			$phone_number = sanitise($_POST['phone_number'],$connection);

			$email = sanitise($_POST['email'], $connection);

			// check type of input and length
			$username_val = validateString($username, 1, 16);
			$password_val = validateString($password, 1, 255);
			//check if its a valid email
			$email_val = validateEmail($email);			

			$firstname_val = validateString($firstname, 1, 25);
			$surname_val = validateString($surname, 1, 25);
			//see helper.php  
			$dob_val = validateDate($dob);
			// do a regex check to see if the phone number has anything that isn't anumber and its length is less than 15
			if(preg_match("/[^0-9]/",$phone_number) && (strlen($phone_number) <= 15))
			{	
				$phone_number_val = "Invalid Phone number";
			}
		
			if(strcmp($orig_username, $username) != 0)			//Has user changed the username?
			{												// they have, see if its not taken already
				$query = "Select username from users where username ='$username'";
				$result = mysqli_query($connection,$query);
				$n = mysqli_num_rows($result);
				if($n > 0) 
				{
					$username_val = $username_val." Username already taken!";
				}
			}
			//compile the errors
			$errors = $username_val . $password_val . $email_val . $firstname_val . $surname_val . $dob_val . $phone_number_val;

			if ($errors == "")
			{  
				// we should hash password  before storing. password should never be in plain text
				$password = password_hash($password,PASSWORD_DEFAULT);	

					// try to insert the new details:
				$query = "UPDATE users set
				username='$username', password ='$password', firstname='$firstname', surname='$surname', 
				email ='$email', dob='$dob', phone_number='$phone_number' WHERE username='$orig_username';";
				
				$result = mysqli_query($connection, $query);
				echo mysqli_error($connection);
				// no data returned, we just test for true(success)/false(failure):
				if ($result) 
				{
					// show a successful update
					$message = "<h4>Detail updated successfull</h4><br>";

					//hide the form
					$show_form = false;
				} 
				else // error has occured with database
				{	// show the update form again
					
					$query = "SELECT * FROM users WHERE username='$user'";
	
					// this query can return data ($result is an identifier):
					$result = mysqli_query($connection, $query);
					
					// how many rows came back? (can only be 1 or 0 because username is the primary key in our table):
					$n = mysqli_num_rows($result);
						
					// if there was a match then extract their profile data:
					if ($n > 0)
					{
						// use the identifier to fetch one row as an associative array (elements named after columns):
						$profile = mysqli_fetch_assoc($result);
						// extract their profile data for use in the HTML:
					}
					$show_form = true;

					// show an unsuccessful update message
					$message = "Failed please try again<br>";
				}
			}	
		}
		elseif(isset($_POST['addnew']))		// admin wants to add new user
		{
			// clean + remove malicous input + escap any html
			$username = sanitise($_POST['new_username'], $connection);
			$password = sanitise($_POST['new_password'], $connection);
			$firstname = sanitise($_POST['new_firstname'],$connection);
			$surname = sanitise($_POST['new_surname'],$connection);

			$password_val = validateString($password, 1, 16);		// yes that right 255 because password_hash() needs it.

			$dob = sanitise($_POST['new_dob'],$connection);
			$phone_number = sanitise($_POST['new_phone_number'],$connection);

			$email = sanitise($_POST['new_email'], $connection);

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

			// check if the user name  already exists
			$query = "Select username from users where username ='$username'";
			$result = mysqli_query($connection,$query);
			$n = mysqli_num_rows($result);
			if($n > 0) 
			{
				$username_val = $username_val." Username already taken!";
			}
			
			$errors = $username_val . $password_val . $email_val . $firstname_val . $surname_val . $dob_val . $phone_number_val;
	
			// check that all the validation tests passed before going to the database:
			if ($errors == "")
			{  	
				// we should hash password  before storing. password should never be in plain text

				$password = password_hash($password,PASSWORD_DEFAULT);

				$query = "INSERT INTO users (username, password, firstname, surname, email, dob, phone_number) 
				VALUES ('$username', '$password','$firstname','$surname', '$email','$dob','$phone_number');";
				$result = mysqli_query($connection, $query);
				
				// no data returned, we just test for true(success)/false(failure):
				if ($result) 
				{
					// show a successful signup message:
					$message = "User '$username' added successfully!<br>";
				} 
				else 
				{
					// show an unsuccessful signup message:
					$message = "Sign up failed, please try again<br>";
				}
			}


		}

		// check for a row in our profiles table with a matching username:
		$query = "SELECT * FROM users";
	
		// this query can return data ($result is an identifier):
		$result = mysqli_query($connection, $query);
		
		// how many rows came back? (can only be 1 or 0 because username is the primary key in our table):
		$n = mysqli_num_rows($result);

		echo "<table>";
		echo "
		<th>Username</th> 
		<th>Email</th> 
		<th>Password</th> 
		<th>Firstname</th> 
		<th>Surname</th> 	
		<th>Date of Birth</th> 
		<th>Phone Number</th>
		<th>Delete /  Edit</th>";

		if (!($n > 0))
		{
			echo "error!<br>";
		}
		else
		{
			// use the identifier to fetch one row as an associative array (elements named after columns):
			while(	$row = mysqli_fetch_assoc($result))
			{	
				echo <<<END
				<tr>
				<td>{$row['username']}</td>
				<td>{$row['email']}</td>
				<td>{hidden}</td>
				<td>{$row['firstname']}</td>
				<td>{$row['surname']}</td>
				<td>{$row['dob']}</td>
				<td>{$row['phone_number']}</td>
				<td>
					<form action="admin.php" method="post">
					<input type="hidden" name="user" value="{$row['username']}" >
					<input type="submit" name="delete" value="DELETE">
					<input type="submit" name="edit" value="EDIT ACCOUNT">
				</form>
				</td>
				<!--<td>
					<form action="surveys_manage.php" method="get">
					<input type="hidden" name="user" value="{$row['username']}" >
					<input type="submit" name="surveys" value="SURVEYS">
					</form>
				</td> --!>
				</tr>
END;
			}
		}
		
			echo <<<END
			<form action="admin.php" method="POST">
			<tr>
				<td><input type="text" size="7"  placeholder="Username" name="new_username" maxlength="16" minlength="1" value="{$username}" required></td> $username_val
				<td><input type="email"  size="15" placeholder="Email" name="new_email" maxlength="64" value="$email" required>  </td>$email_val
				<td><input type="password" size="10" placeholder="Password" name="new_password" maxlength="16" value="" required> </td> $password_val 
				<td><input type="text"  size="10" placeholder="First Name" name="new_firstname" minlength="1" maxlength="25" value="$firstname" required>  </td> $firstname_val
				<td><input type="text"  size="10" placeholder="Last Name" name="new_surname" minlength="1" maxlength="25"  value="$surname" required> </td>$surname_val
				<td><input type="date"  size="8" name="new_dob" value="$dob" required> </td>$dob_val
				<td><input type="text"  size="13" placeholder="Phone Number" name="new_phone_number"value="$phone_number" minlength="1" maxlength="15" required> </td>$phone_number_val
				<td><input type="submit" name="addnew"  value="ADD USER"></td>
				</tr>
				</form>
END;
			echo "</table>";
	
	
	echo $errors;
	echo $message;
	// we're finished with the database, close the connection:
	mysqli_close($connection);
	


	if ($show_form && !empty($profile))
	{	//todo remove the password field admin shouldn't be allowed to view it
			// display their profile data:	
			echo <<<_END
			<form action="admin.php" method="post">
			<table>
			<th colspan='2' style='text-align:left'>Change user details:</th>
	
			<tr>
				<td>Username</td>		
				<td><input type="text" name="username" maxlength="16" minlength="1" value="{$profile['username']}" required>$username_val</td>
			</tr>	
			<tr>
				<td>Email</td> 
				<td><input type="email" name="email" maxlength="64" value="{$profile['email']}" required> $email_val </td>
			</tr>
			<tr>
				<td>Password</td>
				<td><input type="password" name="password" value="{$profile['password']}"> $password_val </td>
			</tr> 
			<tr>
				<td>Firstname</td> 
				<td><input type="text" name="firstname" minlength="1" maxlength="25" value="{$profile['firstname']}" required>$firstname_val </td>
			</tr>
			<tr>
				<td>Surname</td>
				<td><input type="text" name="surname" minlength="1" maxlength="25"  value="{$profile['surname']}" required>$surname_val</td>
			</tr>
			<tr>
				<td>Date of Birth</td> 
				<td><input type="date" name="dob" value="{$profile['dob']}" required>$dob_val</td>
			</tr>
			<tr>
				<td>Phone Number</td> 
				<td><input type="text" name="phone_number"value="{$profile['phone_number']}" minlength="1" maxlength="15" required> <br></td>
			</tr>
			</table>
			<br>
			<input type="hidden" name="original_user" value="{$profile['username']}" >
			<input style="margin-left:15px;" type="submit" name="update" value="Update">
			<br>
		</form>
	
_END;
	}
}else
	// looks like a normal user found admin page
	{echo "You dont have permission to view this page";}

}	

// finish off the HTML for this page:
require_once "footer.php";
?>