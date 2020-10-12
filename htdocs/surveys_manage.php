<?php

// Things to notice:
// This is the page where each user can MANAGE their surveys

// execute the header script:
require_once "header.php";


if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
}
else
{
	if(isset($_POST['delete'])){	
		$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
		if (!$connection)	{die("Connection failed: " . $mysqli_connect_error);}
		
		$survey_id = $_POST['survey_id'];
		$survey_id = sanitise($survey_id,$connection);
		$errors = validateInt($survey_id,1,99999999);	
			
		if($errors=="")
		{
			$query= "DELETE FROM surveys WHERE username = '{$_SESSION['username']}' and survey_id='{$survey_id}';";

			if ( mysqli_query($connection, $query))
			{ 
				echo "<br>Survey '{$survey_id}' deleted successfully<br><br>";
			}else
			{
				echo mysqli_error($connection);
			}

		}else{
			echo "<br> Error has occured.";
		}
	}


	//create edit analyse and delete survery
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

	// if the connection fails, we need to know, so allow this exit:
	if (!$connection){die("Connection failed: " . $mysqli_connect_error);}
	
	$query = "SELECT * FROM surveys WHERE username='{$_SESSION['username']}'";
	echo "Hello, ".$_SESSION['username'].".<br><br>";


	$result = mysqli_query($connection,$query);
	$n = mysqli_num_rows($result);
	echo <<<END
		Here are your surveys:<br>
		<table>
			<tr>
				<th>Survery Name</th>
				<th>Survery Description</th>
				<th>sharable URL</th>	
				<th>Number of responses</th>	
				<th colspan="3">Action</th><th></th>
				
			
			</tr>
END;
	if($n > 0)
	{
		while($survey = mysqli_fetch_assoc($result))
		{	
			$query = "SELECT * FROM answers WHERE survey_id='{$survey['survey_id']}' and question_id='0';";
			$answer_results = mysqli_query($connection,$query);
			$n = mysqli_num_rows($answer_results);
			echo <<<END
			<tr>
				<td>{$survey['name'] }</td>
				<td>{$survey['description']}</td>
				<td><a href="view_survey.php?token={$survey['url']}">/view_survey.php?token={$survey['url']}</a></td>
				<td>$n</td>
				<td>
				<form action="view_survey.php" method="GET">
					<input type="hidden" name="token" value="{$survey['url']}">
					<input type="submit" name="view" value="View">
				</form>
				</td>
				<td>
				<form action="surveys_manage.php" method="post">
					<input type="hidden" name="survey_id" value="{$survey['survey_id']}">
					<button onclick="return confirm('Are you sure you want to delete?');" type="submit" name="delete">Delete</button>
				</form>
				</td>
				<td>
				<form action="edit_survey.php" method="post">
					<input type="hidden" name="survey_id" value="{$survey['survey_id']}">
					<input type="submit" name="edit" value="Edit">
				</form>
				</td>
				<td>
				<form action="view_responses.php" method="post">
					<input type="hidden" name="survey_id" value="{$survey['survey_id']}">
					<input type="submit" name="responses" value="View Responses">
				</form>
				</td>
			</tr>

END;
		}	
	}
	echo "</table>	<br>";
	echo<<<END
	<form action='create_survey.php' method='get'>
	<input type='submit' value='Create New Survey'>
	</form>
END;

    
}

// finish off the HTML for this page:
require_once "footer.php";

?>