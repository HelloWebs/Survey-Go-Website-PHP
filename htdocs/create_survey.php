<?php
require_once "header.php";


$name ="";
$description = "";
$name_val = "";
$description_val = "";
$completed_msg_val = "";

$show_surveys_forms = false;
$show_answers_form = false;

$question[]= array();
$type[] = []; 
$answer_options[] = [];
$question_val="";
$response_type_val ="";
$responses_val = "";



if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
}
elseif(isset($_POST['addsurvey']))
{ 
    // allow user to crate their own surveys
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    if (!$connection)
    {
        die("Connection failed: " . $mysqli_connect_error);
    }
    $name = sanitise($_POST['sname'], $connection);
    $description = sanitise($_POST['sdescription'], $connection);
    $completed_msg = sanitise($_POST['smessage'], $connection);

    $name_val = validateString($name, 1, 64);
	$description_val = validateString($description, 1, 125);
	$completed_msg_val = validateString($completed_msg, 1, 125);

    $errors = $name_val.$description_val.$completed_msg_val;
    if ($errors == "")
	{
        $url= bin2hex(openssl_random_pseudo_bytes(5));
        $sql = "INSERT INTO surveys (username,url, name, description,end_message) 
        VALUES ('{$_SESSION['username']}','$url','$name', '$description','$completed_msg')";

        if (mysqli_query($connection, $sql))
        {
            echo "Survey added<br><br> ";
            $_SESSION['survey_last_added'] =  mysqli_insert_id($connection);
            $show_surveys_forms = false;
            $show_answers_form = true;
            $_SESSION['question_last_added'] = 0;// reset the coun

        }
        else 
        {
            die("Error adding survey: " . mysqli_error($connection));
        }
    }
    else
	{
		// validation failed, show the form again with guidance:
		$show_surveys_forms = true;
		// show an unsuccessful signin message:
		$message = "<br>Error adding survey, please check the errors shown above and try again<br>";
    }
}
elseif(isset($_POST['addquestion']))
{

    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    if (!$connection)
    {
        die("Connection failed: " . $mysqli_connect_error);
    }
    $question = sanitise($_POST['question'], $connection);
    
    $response_type = sanitise($_POST['response_type'], $connection);
    $responses = sanitise($_POST['responses'], $connection);

    $question_val = validateString($question, 1, 90);
    $response_type_val = validateString($response_type, 1, 50 );
    $responses_val = validateString($responses, 0, 200);
    

    $responses = str_replace("\\r\\n",',', $responses);
    $responses = explode(',', $responses);
    $responses = array_filter(array_unique($responses));    // only unique and non empty values should be the reponse
    $responses = implode(',', $responses);


    $errors = $question_val.$response_type_val.$responses_val;
    if ($errors == "")
	{
        if(!isset($_SESSION['question_last_added']))
        {
            $_SESSION['question_last_added'] = 0;
        }
        
        $sql = "INSERT INTO questions (survey_id,question_id, question, type, answer_options) VALUES ({$_SESSION['survey_last_added']},{$_SESSION['question_last_added']} ,'$question', '$response_type','$responses')";

        // no data returned, we just test for true(success)/false(failure):
            if (mysqli_query($connection, $sql)) 
            {
				// keep track of how many question we have on added
                $_SESSION['question_last_added'] = $_SESSION['question_last_added'] +1;
                $show_surveys_forms = false;
                $show_answers_form = true;
            }
            else 
            {
                die("Error inserting row: " . mysqli_error($connection));

            }
        
        }
        else{
            echo "Error validation failed! See errors!";
            $show_answers_form = true;

        }
}
else
{
    $show_surveys_forms = true;
}

echo "<h3> Enter survey details below: </h3>";

if($show_surveys_forms)
{
    echo <<<END
    <fieldset style="width:60%">
    <legend><b>Survey Details</b></legend>
        <form action="#" method="post">
            <label for="iname">Survey Name</label><br>
            <input type="text" placeholder="Enter survey name..." name="sname" id="iname" maxlength="64" minlength="1"  size="30" required> $name_val<br>
        
            <label for="idesc">Description:</label><br>
            <textarea name="sdescription" id="idesc" maxlength="125" required placeholder="Enter a description for this survey..." rows="4" cols="50"></textarea> $description_val<br>
            <br>
            <label for="imsg">Message to user when completed:</label><br>
            <textarea name="smessage" id="imsg" maxlength="125" required rows="4" cols="50">Thank you completing this survey! </textarea>$completed_msg_val<br>
            <br>
            <button type="submit" name="addsurvey">Add Survey</button>
        </form>
    </fieldset>
END;
}
elseif($show_answers_form)
{
    $query = "SELECT * FROM surveys where survey_id ='{$_SESSION['survey_last_added']}'";
	$result = mysqli_query($connection, $query);
    if(mysqli_num_rows($result)>0)
    {
        $row = mysqli_fetch_assoc($result);
    }
  echo <<<END
    
    <label for="iname">Survey Name</label><br>
    <input type="text" id="iname" disabled size="30" value="{$row['name']}"><br>
    <label for="idesc">Description:</label><br>
    <textarea id="idesc" maxlength="125" disabled rows="4" cols="50">{$row['description']}</textarea><br>

    <h4>Unique URL: <a href="view_survey.php?token={$row['url']}">/view_survey.php?token={$row['url']}</a></h4>
END;

    $query = "SELECT * FROM questions where survey_id ='{$_SESSION['survey_last_added']}'";
	$result = mysqli_query($connection, $query);
	$n = mysqli_num_rows($result);
    if($n >0)
    {
        echo "<table>";
        echo "<tr>
		<th>Question </th> 
		<th>Type</th> 
        <th>Answers</th></tr>";
        while($row = mysqli_fetch_assoc($result))
        {	
            echo <<<END
            <tr>
                <td>{$row['question']}</td>
                <td>{$row['type']}</td>
                <td>{$row['answer_options']}</td>
END;
              
        echo"</tr>";

        }
        echo "</table>";
    }
    echo <<<_END
    <br>
    <fieldset style="width:60%">
        <legend>Answers</legend>
        <form action="create_survey.php" method="post">
            <table>
                <th colspan="2">Please add Questions:   <th>
                <tr>
                    <td>Question: </td>
                </tr>
                <tr>
                    <td><input style="padding: 4px;" type="text" size="60"  name="question" maxlength="90" minlength="1" required> $question_val</td>
                </tr>	
                <tr>
                    <td>Question Type </td>
                </tr>
                <tr>
                    <td>
                        <select name="response_type" id="qtype" required>$response_type_val
                            <option value="radio">Radio Button</option>
                            <option value="check">Check Box</option>
                            <option value="text">Text Field</option>
                            <option value="number">Number</option>
                            <option value="drop">Drop Down</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="qoptions">Answer responses: <br>Separate response by a <b>new line or comma</b>, leave blank for any user input/response.<br> e.g. "Yes,No" or "Agree,Disagree"</td>
                </tr>
                <tr>  
                    <td><textarea name="responses"class="qoptions" rows="8" cols="50"></textarea></td>  $responses_val
                </tr>
            </table>
            <br>
            <input type="submit" name="addquestion" value="ADD QUESTION">
        </form>	

        <br>
    </fieldset>
_END;

echo "<br><a href='surveys_manage.php'>Save and go home</a>";

}


    
require_once "footer.php";

?>