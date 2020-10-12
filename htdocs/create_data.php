<?php
// read in the details of our MySQL server:
require_once "credentials.php";

// We'll use the procedural (rather than object oriented) mysqli calls

// connect to the host:
$connection = mysqli_connect($dbhost, $dbuser, $dbpass);

// exit the script with a useful message if there was an error:
if (!$connection){	die("Connection failed: " .mysqli_connect_error($connection));}
  
// build a statement to create a new database:
$sql = "CREATE DATABASE IF NOT EXISTS " . $dbname;

// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) 
{
	echo "Database created successfully, or already exists<br>";
} 
else
{
	die("Error creating database: " . mysqli_error($connection));
}

// connect to our database:
mysqli_select_db($connection, $dbname);



///////////////////////////////////////////
////////////// DROP TABLE   //////////////
///////////////////////////////////////////

// if there's an old version of our table, then drop it:
$sql = "DROP TABLE IF EXISTS answers";

// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) 
{
	echo "Dropped existing table: answers<br>";
} else {	
	die("Error checking for existing table: answers " . mysqli_error($connection));
}	

//Questions

$sql = "DROP TABLE IF EXISTS questions";

// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) {
	echo "Dropped existing table: questions<br>";
}
else {	
	die("Error checking for existing table:questions " . mysqli_error($connection));
}



//ANSWERS
// if there's an old version of our table, then drop it:
	$sql = "DROP TABLE IF EXISTS answers";
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) 
	{
		echo "Dropped existing table: answers<br>";
	} 
	else 
	{	
		die("Error checking for existing table: answers " . mysqli_error($connection));
	}
	// if there's an old version of our table, then drop it:
$sql = "DROP TABLE IF EXISTS surveys";

// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) 
{
	echo "Dropped existing table: surveys<br>";
} 
else 
{	
	die("Error checking for existing table: surveys " . mysqli_error($connection));
}


	//USERS
// if there's an old version of our table, then drop it:
	$sql = "DROP TABLE IF EXISTS users";

	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) 
	{
		echo "Dropped existing table: users<br>";
	} 
	else 
	{	
		die("Error checking for existing table: suers " . mysqli_error($connection));
	}









///////////////////////////////////////////
////////////// USERS TABLE   //////////////
///////////////////////////////////////////



// make our table:
$sql = "CREATE TABLE users (username VARCHAR(16), password VARCHAR(255), firstname VARCHAR(25), surname VARCHAR(25), email VARCHAR(64), dob DATE,phone_number VARCHAR(15), PRIMARY KEY(username))";


//username password firstname surname email dob phone
// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) 
{
	echo "Table created successfully: users<br>";
}
else 
{
	die("Error creating table: users " . mysqli_error($connection));
}

// put some data in our table:

$usernames[] = 'a'; $passwords[] = 'test'; $emails[] = 'a@alphabet.test.com';
$firstname[] = "a"; $surname[]="lastA";  $dob[]='1999-12-09'; $phone_number[]="0787943718";

$usernames[] = 'barrym'; $passwords[] = 'letmein'; $emails[] = 'barry@m-domain.com'; 
$firstname[] = "barry"; $surname[]="ve"; $dob[]='1999-10-10'; $phone_number[]="07884953718";

$usernames[] = 'admin'; $passwords[] = 'secret'; $emails[] = 'webmaster@mandy-g.co.uk';
$firstname[] = "secret"; $surname[]="ve";  $dob[]='1999-10-10'; $phone_number[]="07884953718";

$usernames[] = 'timmy'; $passwords[] = 'secret95'; $emails[] = 'timmy@lassie.com';
$firstname[] = "timmy"; $surname[]="somthing";  $dob[]='1999-10-10'; $phone_number[]="0788434578";

$usernames[] = 'briang'; $passwords[] = 'password'; $emails[] = 'brian@quahog.gov';
$firstname[] = "brain"; $surname[]="ng";  $dob[]='1989-10-10'; $phone_number[]="0787943718";



$usernames[] = 'b'; $passwords[] = 'test'; $emails[] = 'b@alphabet.test.com';
$firstname[] = "b"; $surname[]="ng";  $dob[]='1999-04-09'; $phone_number[]="0787943718";

$usernames[] = 'c'; $passwords[] = 'test'; $emails[] = 'c@alphabet.test.com';
$firstname[] = "c"; $surname[]="ng";  $dob[]='1999-12-09'; $phone_number[]="0787943718";

$usernames[] = 'd'; $passwords[] = 'test'; $emails[] = 'd@alphabet.test.com';
$firstname[] = "d"; $surname[]="ng";  $dob[]='1959-10-10'; $phone_number[]="0787943718";


// loop through the arrays above and add rows to the table:
for ($i=0; $i<count($usernames); $i++)
{
	//username password firstname surname email dob phone
	$password = password_hash($passwords[$i],PASSWORD_DEFAULT);
	$sql = "INSERT INTO users (username, password, firstname, surname, email, dob, phone_number)
	 VALUES ('$usernames[$i]', '$password','$firstname[$i]','$surname[$i]','$emails[$i]','$dob[$i]','$phone_number[$i]')";
	//username password firstname surname email dob phone

	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) 
	{
		echo "row inserted<br>";
	}
	else 
	{
		die("Error inserting row: " . mysqli_error($connection));
	}
}





/////<-- survey
/*
survey_id <PK>
username <FK>				
name
description  
*/



// make our table:
$sql = "CREATE TABLE surveys (
	survey_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	username VARCHAR(16),
	name VARCHAR(64) NOT NULL,
	url VARCHAR(20) NOT NULL,
	description VARCHAR(125),
	end_message VARCHAR(125),
	FOREIGN KEY (username) REFERENCES users(username)
	ON UPDATE CASCADE ON DELETE SET NULL
	);";
	//FOREIGN KEY (PersonID) REFERENCES Persons(PersonID)


//username password firstname surname email dob phone
// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) 
{
	echo "Table created successfully: surveys<br>";
}
else 
{
	die("Error creating table:  " . mysqli_error($connection));
}

$names[] = "Customer survey";
$names[] = "Favourite Animal";
// $url[] =  bin2hex(openssl_random_pseudo_bytes(5));
$url[] =  "customercode";// token needs stay the same
$url[] = bin2hex(openssl_random_pseudo_bytes(5));	// this one can change every run

$description[] = "Customer satisfaction";
$description[] = "People\'s favourite animal";

for ($i=0; $i<count($names); $i++)
{

	$sql = "INSERT INTO surveys (username,url, name, description,end_message)
	 VALUES ('a','$url[$i]','$names[$i]', '$description[$i]','Thanks your completing the survey!')";
	//username password firstname surname email dob phone

	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) 
	{
		echo "row inserted<br>";
	}
	else 
	{
		die("Error inserting row: " . mysqli_error($connection));
	}
}





/*
<-- questions -->
question_id <PK>
question 
type 
answer_options 
*/

// if there's an old version of our table, then drop it:


// make our table:
$sql = "CREATE TABLE questions (
	survey_id BIGINT UNSIGNED REFERENCES surveys(survey_id) ON UPDATE CASCADE ON DELETE SET NULL,
	question_id BIGINT UNSIGNED ,

	question VARCHAR(90) NOT NULL,
	type VARCHAR(15) NOT NULL,
	answer_options VARCHAR(200),

    PRIMARY KEY (survey_id, question_id)
	);";
	//FOREIGN KEY (PersonID) REFERENCES Persons(PersonID)


//username password firstname surname email dob phone
// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) 
{
	echo "Table created successfully: questions<br>";
}
else 
{
	die("Error creating table: u" . mysqli_error($connection));
}




$question[] = "What do you normally use our service for?";
$type[] = "drop";
$answer_options1[] = "I dont use";
$answer_options1[] = "Professional";
$answer_options1[] = "Personal";
$answers_options[] = implode(",",$answer_options1);

$question[] = "How satified where you with your recent usage?";
$type[] = "radio";
$answer_options[] = "Satisfied";
$answer_options[] = "Neither";
$answer_options[] = "Dissatisfied";
$answers_options[] = implode(",",$answer_options);

$question[] = "How long have your been with us in months?: ";
$type[] = "number";
$answer_options = [];
$answers_options[] = implode(",",$answer_options);

$question[] = "Check which service you are interested in?";
$type[] = "check";
$answer_options[] = "Surveys";
$answer_options[] = "Accounts";
$answer_options[] = "Admin permission";
$answers_options[] = implode(",",$answer_options);

$question[] = "Please leave any comments below or n/a: ";
$type[] = "text";
$answer_options = [];
$answers_options[] = implode(",",$answer_options);

//$question[] = "Enter your phone number if you want us to get back. 00 if you don'
for ($i=0; $i<count($question); $i++)
{
	$sql = "INSERT INTO questions (survey_id,question_id, question, type, answer_options)
	 VALUES ('1','$i','$question[$i]', '$type[$i]','$answers_options[$i]')";
	//username password firstname surname email dob phone

	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) 
	{
		echo "row inserted<br>";
	}
	else 
	{
		die("Error inserting row: " . mysqli_error($connection));

	}
}




/*
<-- answers -->
question_id<FK>
answer
*/


// make our table:
$sql = "CREATE TABLE answers (
	survey_id BIGINT UNSIGNED REFERENCES questions(survey_id) ON UPDATE CASCADE ON DELETE SET NULL,
	question_id BIGINT UNSIGNED REFERENCES questions(question_id) ON UPDATE CASCADE ON DELETE SET NULL,
	username VARCHAR(16),
	answer VARCHAR(100)
	);";

// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) 
{
	echo "Table created successfully: Answers<br>";
}
else 
{
	die("Error creating table: " . mysqli_error($connection));
}

$answer[] = "Neither";
$answer[] = "Satisfied";
$answer[] = "Satisfied";
$username[] ="admin";
$username[] ="a";
$username[] ="admin";


$query[] = "INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,0,'a','I dont use,')";
$query[] = "INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,1,'a','Satisfied,')";
$query[] = "INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,2,'a','12435,')";
$query[] = "INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,3,'a','Accounts,Admin permission,,')";
$query[] = "INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,4,'a','comment,')";
$query[] = "INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,0,'a','I dont use,')";
$query[] = "INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,1,'a','Satisfied,')";
$query[] = "INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,2,'a','12435,')";
$query[] = "INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,3,'a','Accounts,Admin permission,,')";
$query[] = "INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,4,'a','comment,')";
$query[] ="INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,0,'barrym','Professional,')";
$query[] ="INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,1,'barrym','Satisfied,')";
$query[] ="INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,2,'barrym','12,')";
$query[] ="INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,3,'barrym','Surveys,,')";
$query[] ="INSERT INTO answers (survey_id,question_id,username,answer) VALUES (1,4,'barrym','212,')";





for ($i=0; $i<count($query); $i++)
{

	if (mysqli_query($connection, $query[$i])) 
	{
		echo "row inserted<br>";
	}
	else 
	{
		die("Error inserting row: " . mysqli_error($connection));
	}
}




echo "<br><h2>Please go <a href='sign_up.php'>here</a></h2>";
echo "<h4>username:a <br> password:test</h4>";



// we're finished, close the connection:
mysqli_close($connection);
?>