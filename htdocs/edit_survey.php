<?php

// execute the header script:
require_once "header.php";

// default values we show in the form:

$new_question = "";
$new_type = "";
$new_reponse_options = "";

$new_question_val = "";
$new_type_val = "";
$new_reponse_options_val = "";

$edit_question = "";
$edit_type = "";
$edit_reponse_options = "";

$edit_question_val ="";
$edit_response_type_val ="";
$edit_responses_val ="";
// strings to hold any validation error messages:
$name_val = "";
$description_val = "";
$end_message_val = "";

// should we show the set question form?:
$show_main_question_form = true;
$show_question_form = false;

$question_details = array();
$survey_details = array();

// message to output to user:
$message = "";

if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
}
else
{
      // connect directly to our database (notice 4th argument) we need the connection for sanitisation:
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    // if the connection fails, we need to know, so allow this exit:
    if (!$connection)
    {
        die("Connection failed: " . $mysqli_connect_error);
    }
   
    if (isset($_POST['editsurvey']))
    {
        //Get and sanitise 
        $survey_id= sanitise($_POST['survey_id'], $connection);
        $new_name = sanitise($_POST['new_name'], $connection);
        $new_description = sanitise($_POST['new_description'], $connection);
        $new_end_msg = sanitise($_POST['new_message'], $connection);
        //validation
        $name_val = validateString($new_name, 1, 64);
	    $description_val = validateString($new_description, 1, 125);
        $end_message_val = validateString($new_end_msg, 1, 125);
        
        $survey_id_val = validateInt($survey_id, 1, 99999999);

        $errors = $name_val.$description_val.$end_message_val.$survey_id_val;
        // check that all the validation tests passed before going to the database:
        if ($errors == "")
        {		
            $query = "UPDATE surveys SET name ='$new_name',
                description ='$new_description',
                end_message='$new_end_msg'
                WHERE username='{$_SESSION['username']}' and survey_id='$survey_id';";
            
            // this query can return data ($result is an identifier):
            $result = mysqli_query($connection, $query);
            // we just test for true(success)/false(failure):
            if ($result) 
            {
                // show a successful update message:
                echo "Survey successfully updated<br>";
            } 
            else
            {
                echo "Update failed<br>".mysqli_error($connection);
            }
        }
        else
        {
            $echo = "Update failed, please check the errors below and try again<br>";
        }
    }

    // user wants to edit a questions
    elseif (isset($_POST['editquestion']))
    {
        $survey_id = sanitise($_POST['survey_id'], $connection);
        $question_id= sanitise($_POST['question_id'],$connection);

        $errors = validateInt($survey_id,-1,-1);
        $errors = validateInt($question_id,-1,-1);// -1 no limit

        if($errors==""){
            $query = "SELECT * FROM questions where survey_id ='{$survey_id}' and question_id='$question_id';";
            $result = mysqli_query($connection, $query);
            // how many rows came back? 
            $n = mysqli_num_rows($result);

            if ($n > 0)
            {
                // use the identifier to fetch one row as an associative array (elements named after columns):
                $question_details = mysqli_fetch_assoc($result);
            
            } 
            $show_question_form = true;
        }
        else
        {
            echo "Error has occured!<br>.".$errors;
        }

    }elseif(isset($_POST['updatequestion']))
    {
        // get composite keys for query later
        $survey_id = sanitise($_POST['survey_id'], $connection);
        $question_id= sanitise($_POST['question_id'],$connection);

        // validate int will return error message,
        $errors = validateInt($survey_id,-1,-1);        //only if system error or malicous input should trigger an error here
        $errors = validateInt($question_id,-1,-1);

        //sanitise using helper function, prevents malicous input
        $edit_question = sanitise($_POST['question'], $connection);
        $edit_type = sanitise($_POST['response_type'], $connection);
        $edit_reponse_options = sanitise($_POST['responses'], $connection);
        
        //validate the input, should store so they can displayed to user
        $edit_question_val = validateString($edit_question, 1, 90);
        $edit_response_type_val = validateString($edit_type, 1, 50 );
        $edit_responses_val = validateString($edit_reponse_options, 0, 200);

        $edit_reponse_options = str_replace("\\r\\n",',', $edit_reponse_options); // replace the new lines with comma to use as list.
        $edit_reponse_options = explode(',', $edit_reponse_options);    // array is easier to filter so convert it
        $edit_reponse_options = array_filter(array_unique($edit_reponse_options));    // only unique and non empty values should be the reponse
        $edit_reponse_options = implode(',', $edit_reponse_options);            // convert back to string
        //sum up the errors from validation, used to check if there were any errors
        $errors = $errors.$edit_question_val.$edit_response_type_val.$edit_responses_val;

        if ($errors == "")      // check if errors var has errors
	    {
            // user update query to set all the new changes
            $query = "UPDATE questions SET question = '$edit_question', type = '$edit_type',
            answer_options='$edit_reponse_options' WHERE survey_id='$survey_id' and question_id='$question_id'";

            //run query and store result should be true or 1 for success
            $result = mysqli_query($connection, $query);    

            if ($result) //success?
		    {
                echo "Question updated Successfully<br>";
                $show_question_form = false;
            }
            else{
                $query = "SELECT * FROM questions where survey_id ='{$survey_id}' and question_id='$question_id';";
                $result = mysqli_query($connection, $query);
                // how many rows came back? 
                $n = mysqli_num_rows($result);

                if ($n > 0)
                {
                    // use the identifier to fetch one row as an associative array (elements named after columns):
                    $question_details = mysqli_fetch_assoc($result);
                    $show_question_form = true;

                } 
			    // show an unsuccessful update message:
		    	echo "Update failed<br>".mysqli_error($connection);
            }
        }else{
            $show_question_form=true;
            echo "Error input validation failed. please check error messages";
        }


    }
    elseif(isset($_POST['addquestion']))
    {
        $survey_id = sanitise($_POST['survey_id'], $connection);

        // validate int will return error message,
        $errors = validateInt($survey_id,-1,-1);        //only if system error or malicous input should trigger an error here

        //sanitise using helper function, prevents malicous input
        $new_question = sanitise($_POST['new_question'], $connection);
        $new_type = sanitise($_POST['new_response_type'], $connection);
        $new_reponse_options = sanitise($_POST['new_responses'], $connection);
        
        //validate the input, should store so they can displayed to user
        $new_question_val = validateString($new_question, 1, 90);
        $new_response_type_val = validateString($new_type, 1, 50 );
        $new_responses_val = validateString($new_reponse_options, 0, 200);

        $new_reponse_options = str_replace("\\r\\n",',', $new_reponse_options); // replace the new lines with comma to use as list.
        $new_reponse_options = explode(',', $new_reponse_options);    // array is easier to filter so convert it
        $new_reponse_options = array_filter(array_unique($new_reponse_options));    // only unique and non empty values should be the reponse
        $new_reponse_options = implode(',', $new_reponse_options);            // convert back to string
        //sum up the errors from validation, used to check if there were any errors
        $errors = $errors.$new_question_val.$new_response_type_val.$new_responses_val;

        if ($errors == "")      // check if errors var has errors
	    {
            if(!isset($_SESSION['question_last_added']))
            {
                $_SESSION['question_last_added']= 0;
            }
            $query = "INSERT INTO questions (survey_id,question_id, question, type, answer_options) 
            VALUES ($survey_id,{$_SESSION['question_last_added']} ,'$new_question', '$new_type','$new_reponse_options')";

            //run query and store result should be true or 1 for success
            $result = mysqli_query($connection, $query);    

            if ($result) //success?
		    {
                echo "Question added Successfully<br>";
            }
            else{
			    // show an unsuccessful update message:
		    	echo "Update failed see errors below<br>".mysqli_error($connection);
            }
        }else{
            echo "Error input validation failed. please check error messages";
        }

    }
    elseif(isset($_POST['deletequestion']))
    {
        // get composite keys for query later
        $survey_id = sanitise($_POST['survey_id'], $connection);
        $question_id= sanitise($_POST['question_id'],$connection);

        // validate int will return error message,
        $errors = validateInt($survey_id,-1,-1);        //only if system error or malicous input should trigger an error here
        $errors =$errors.validateInt($question_id,-1,-1);
        if ($errors == "")  {
            $query= "DELETE FROM questions WHERE survey_id = '{$survey_id}' and question_id = '{$question_id}';";

            if ( mysqli_query($connection, $query))
            { 
                echo "<br>Question deleted successfully<br>";
            }else
            {
                echo "Error: ".mysqli_error($connection);
            }

        }
        
    }
    if(isset($_POST['survey_id'])) 
    {

        $survey_id = sanitise($_POST['survey_id'],$connection);
        $errors = validateInt($survey_id,0,999999999);
        if($errors=="")
        {
            $query = "SELECT * FROM surveys where survey_id ='$survey_id'";
            $result = mysqli_query($connection, $query);
            if(mysqli_num_rows($result)>0)
            {
                $survey_details = mysqli_fetch_assoc($result);
            }

            echo "<h2>Edit survey details</h2>";
            echo "<p>Use fields below to make any changes to the surveys</p>";
            echo <<<END
            <fieldset style="width:60%">
                <legend><b>Survey Details</b></legend>
                <form action="edit_survey.php" method="post">
                    <label for="iname">Survey Name</label><br>
                    <input type="text" placeholder="Enter survey name..." name="new_name" id="iname" maxlength="64" minlength="1"  size="30" required value="{$survey_details['name']}"> $name_val<br>

                    <label for="idesc">Description:</label><br>
                    <textarea name="new_description" id="idesc" maxlength="125" required placeholder="Enter a description for this survey..." rows="4" cols="50">{$survey_details['description']}</textarea>$description_val<br>
                
                    <label for="imsg">Message to user at the end:</label><br>
                    <textarea name="new_message" id="imsg" maxlength="125" required rows="4" cols="50">{$survey_details['end_message']}</textarea>$end_message_val<br>
                    
                    <input type="hidden" name="survey_id" value='{$survey_id}'>
                    <button type="submit" name="editsurvey">Save changes</button>
                </form>
                </fieldset>
                <h4>Unique URL: <a href="view_survey.php?token={$survey_details['url']}">/view_survey.php?token={$survey_details['url']}</a></h4>
END;
            if ($show_question_form && !empty($question_details))
            {	//todo remove the password field admin shouldn't be allowed to view it: done
                        // display their profile data:	
                echo <<<_END
                <br>
                <fieldset style="width:60%;color:red">
                <legend><b>Question Details</b></legend>
                <form action="edit_survey.php" method="post">
                    <table>
                        <th>Edit Questions<th>
                        <tr>
                            <td>Question: </td>
                        </tr>
                        <tr>
                            <td><input style="padding: 4px;" type="text" size="60" value="{$question_details['question']}" name="question" maxlength="90" minlength="1" required> $edit_question_val</td>
                        </tr>	
                        <tr>
                            <td>Question Type </td>
                        </tr>
                        <tr>
                            <td>
                                <select name="response_type" id="qtype" value="{$question_details['type']}" required>$edit_response_type_val
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
                            <td><textarea name="responses" class="qoptions" rows="8" cols="50">{$question_details['answer_options']}</textarea></td>  $edit_responses_val
                        </tr>
                    </table>
                    <br>
                    <input type="hidden" name="question_id" value="{$question_details['question_id']}" >
                    <input type="hidden" name="survey_id" value="{$question_details['survey_id']}">
                    <input type="submit" name="updatequestion" value="Done!">
                </form>	
                
                {$message}<br>
                </fieldset>
_END;
                
            }
            else
            {
                $query = "SELECT * FROM questions where survey_id ='{$survey_id}'";
                $result = mysqli_query($connection, $query);
                $n = mysqli_num_rows($result);
                $_SESSION['question_last_added']= $n;
                if($n >0)
                {
                    echo "<h2>Edit Survey Questions</h2>
                        <fieldset style='width:70%'>
                        <legend><b>Question Details</b></legend>
                        <table>
                        <tr>
                            <th>Question </th> 
                            <th>Type</th> 
                            <th>Answers</th>
                            <th>Actions</th>
                        </tr>";
                    while($row = mysqli_fetch_assoc($result))
                    {	
                        echo <<<END
                        <tr>
                            <td>{$row['question']}</td>
                            <td>{$row['type']}</td>
                            <td>{$row['answer_options']}</td>
                            <td>
                                <form action="edit_survey.php" method="post">
                                    <input type="hidden" name="question_id" value="{$row['question_id']}" >
                                    <input type="hidden" name="survey_id" value="{$row['survey_id']}">
                                    <input type="submit" name="deletequestion" value="DELETE">
                                    <input type="submit" name="editquestion" value="EDIT">
                                </form>
                            </td>	
                        </tr>		
END;
                    }
                }
            echo "</table>";
       

            echo <<<END
            <form action="edit_survey.php" method="POST">
            <table>
            
            Use fields below to add new question to the survey:
            <tr>
            <th>Question</td>
            <th> Type </td>
            <th>Responses</td>
            </tr>
            <tr>
            <td><input type="text" size="40" value="$new_question" name="new_question"  maxlength="90" minlength="1" required> $new_question_val</td>
            <td>
                <select value="$new_type" name="new_response_type" id="qtype" required>$new_type_val
                    <option value="radio">Radio Button</option>
                    <option value="check">Check Box</option>
                    <option value="text">Text Field</option>
                    <option value="number">Number</option>
                    <option value="drop">Drop Down</option>
                </select>
            </td> 
            <td>
                <textarea name="new_responses" class="qoptions" rows="4" cols="50" placeholder="Enter responses separared by comma" required>$new_reponse_options</textarea>$new_reponse_options_val
            </td>  
            <input type="hidden" name="survey_id" value="{$survey_id}">
            <td><input type="submit" name="addquestion"  value="Add Question"></td>
            </tr>
            </form>
END;
            echo "</table></fieldset>"; 
        
            

        }
      }
    }
    // we're finished with the database, close the connection:
    mysqli_close($connection);
    echo "<a href='./surveys_manage.php'>Goto Surveys</a>";
}

?>