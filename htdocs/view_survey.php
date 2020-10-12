<?php

require_once "header.php";
$errors="";
if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page. please <a href='sign_in.php'>click here.</a><br><br>";
}
else
{
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    if (!$connection)
    {
        die("Connection failed: " . $mysqli_connect_error);
    }
    
    if(isset($_POST['save']))
    {
      // this looks complex but its simple yet it took me a whole day. todo:remove comment
       
        // no user input should be trusted  
        $survey_id = sanitise($_POST['survey_save_id'], $connection);       
        $errors = validateInt($survey_id, 1, 999999);

        $posted_keys = array_keys ($_POST);     // get all fields that were submitted

        //get all the fields with 'question number+answer' format
        $answer_keys = preg_grep("/^[0-9]answer$/",$posted_keys);   // matches answer fields 
      //  var_dump($answer_keys);
        $answers[] = [];                      // empty array to hold all the answers
        //  is there a checkbox question?
        $checkbox[]=[];
        if(isset($_POST['checkbox']))          // check box is present, handle it differently
        {
            $checkbox = preg_grep("/^[0-9]answer[0-9]$/",$posted_keys); 
            if(!empty($checkbox)){

            
                foreach ($checkbox as &$value)  
                {   
                    $question_id = (explode('answer',$value));        // remove answer from so questionID+answer+checkedID
                  //  echo $value.":".$question_id[0];
                    $temp =  empty($answers[$question_id[0]]) ?"":  $answers[$question_id[0]];
                    $answers[$question_id[0]] = $temp."".sanitise($_POST[$value],$connection).","; // we want  answers[question_id] to user answer
                    $errors= $errors.validateString($_POST[$value],1,100);      // validate

                }                    

            }
        }

       // for($i=1; $i <= count($answer_keys);$i++)
       foreach ($answer_keys as &$value)  
       {   
           $question_id = explode('answer',$value);           //separate the question ID number 
          // echo "<br>".$value.":".$question_id[0].":".$_POST[$value];

           $answers[$question_id[0]] = sanitise($_POST[$value],$connection);    // user question id to set answer.
           $errors= $errors.validateString($_POST[$value],1,100);       // validationss
        }
        //var_dump($answers);

        //  did we get any errors?
        if($errors=="")
        {       
            //get the questions with the usual database connection
            $query = "SELECT * FROM questions WHERE survey_id={$survey_id};";
            $results = mysqli_query($connection, $query);
            $n = mysqli_num_rows($results);

            //check if the number of answers we got match the questions
            if((count($answer_keys)+count($checkbox)) < $n){
                echo "<br>Error missing answer. Please check ans try again.";
            }
            else
            {   // insert each of the answers in to the databse
                $inserted=true;
                for ($i = 0; $i < count($answers); $i++)
                {
                    $query = "INSERT INTO answers (survey_id,question_id,username,answer)
                    VALUES ($survey_id,$i,'{$_SESSION['username']}','$answers[$i],')"; 
                    if (mysqli_query($connection, $query)) 
                    {
                        echo "";
                    }
                    else 
                    {
                        $inserted=false;
                        die("Error inserting row: " . mysqli_error($connection));
                    }
                }
                if($inserted)
                {
                    $query = "SELECT * FROM surveys WHERE survey_id={$survey_id};";
                    $result = mysqli_query($connection, $query);            // execute query

                    //check how many results we got back
                    $n = mysqli_num_rows($result);
                    if ($n > 0) 
                    {    
                        $survey = mysqli_fetch_assoc($result); 
                        echo "<br>{$survey['end_message']}";
                    }
                }
            }
        }
        else
        {
            echo "Error validating details!<br>";
        }
    }
   
    // user should have the token for the survey they want to do
    if(isset($_GET['token']))
    {
        // clean and validate the user inputs
        $token = sanitise($_GET['token'], $connection);
        $errors = validateString($token, 1, 20);

        if($errors=="")
        {
            //connect and get survey
            $query = "SELECT * FROM surveys WHERE url='$token';";
            $result = mysqli_query($connection, $query);            // execute query

            //check how many results we got back
            $n = mysqli_num_rows($result);
            if ($n > 0) 
            {    
                $survey = mysqli_fetch_assoc($result); 
                //showe survey details
                echo "<h2>Title: {$survey['name']}</h2>";
                echo "<p><b>Instruction: </b>{$survey['description']}</p>";
                echo "Made by: ".$survey['username'];

                // if($survey['videolink']){

//                 echo <<<END
//                 <br>Please watch this video before <br>
//                 <iframe width="560" height="315" src="https://www.youtube.com/embed/dQw4w9WgXcQ >
//                     </iframe>                <br>
// END;
            
                // }

            } else{
                echo "Error!<br>".mysqli_error($connection);
            }

            // retreive the questions
            $query = "SELECT * FROM questions WHERE survey_id={$survey['survey_id']};";
            $results = mysqli_query($connection, $query);
    
            $n = mysqli_num_rows($results);

            if ($n > 0)
            {   
                $count = 1;         // keep track of the question count
                echo "<form action='view_survey.php' method='POST'>";
    
                while($row = mysqli_fetch_assoc($results))
                {
                    displayQuestionBox($row,$count);        // displayQuestionBox to display the question defined below
                    echo "<br>";
                    $count++;       

                }

                echo "<input type='hidden' name='survey_save_id' value='{$survey['survey_id']}'>";  // send the survey_id;
                echo "<input type='submit' name='save' value='Save'>";
                echo "</form";
            }
            else{
                echo "Empty!<br>".mysqli_error($connection);
            }
        }else
        {
            echo "Error invalid linasdk!<br>".mysqli_error($connection);;
        }
    }
    mysqli_close($connection);
    echo "<br><br><a href='./surveys_manage.php'>Goto Surveys</a>";

}
//todo: make thid use a form so that its content is transferable:
//done
function displayQuestionBox($row,$count)
{ 
    echo <<<END
    <fieldset style="width:60%">
        <table>
    
        <legend><b>Question $count</b></legend>
        <tr>
            <th><b>{$row['question']}</b></th>
        </tr>
END;
    if($row['type']=="radio")
    {
        echo "<tr><td>"; 
        $options = explode(',',$row['answer_options']);
        for($i = 0; $i < count($options);$i++)
        {
            echo "<input type='radio' name='{$row['question_id']}answer' required value='{$options[$i]}'> $options[$i]<br>";
        } 
        echo "</td></tr>";
    }
    if($row['type']=="check")
    {
        
        $options = explode(',',$row['answer_options']);
        echo "<tr><td>"; 
        echo "<input type='hidden' name='checkbox' value='none'>" ;
        for($i = 0; $i < count($options);$i++)
        {   
            echo "<input type='checkbox' name='{$row['question_id']}answer{$i}' value='{$options[$i]}'> $options[$i]<br>";
        }
        echo "</td></tr>";
    }
    if($row['type']=="text")
    {        
        echo "<tr><td>"; 
        echo "Answer: <input type='text' required maxlength='100' name='{$row['question_id']}answer' ";
        echo "</td></tr>";
    }
    if($row['type']=="number")
    {
        echo "<tr><td>";
        echo "Enter Number: <input type='number' name='{$row['question_id']}answer' required>";
        echo "</td></tr>";
    }

    if($row['type']=="drop")
    {
        echo "<tr><td>"; 
        $options = explode(',',$row['answer_options']);
        echo "<select required name='{$row['question_id']}answer'>";

        for($i = 0; $i < count($options);$i++)
        {
            echo " <option value='$options[$i]'>$options[$i]</option><br>";
        }
        echo "</select></td></tr>";   
    }
    
                            
    echo "</table></fieldset>";
    
}
require_once "footer.php";
?>
