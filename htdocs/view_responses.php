<?php
require_once "header.php";


echo <<<END
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

    // Load in google charts
   
  
    </script>
END;
$errors="";
if (!isset($_SESSION['loggedIn']))
{
    // user isn't logged in, display a message saying they must be:
    echo "You must be logged in to view this page.<br>";
}
elseif(isset($_POST['export']))
{
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    if (!$connection)
    {
        die("Connection failed: " . $mysqli_connect_error);
    }

    $survey_id = sanitise($_POST['survey_id'], $connection);
    $export = sanitise($_POST['export'], $connection);      // what format user wants

    $errors = validateInt($survey_id, 0, -1);           //-1 indicate no limit just check its an int
    $errors = $errors.validateString($export,1,5);
    if($errors=="")
    {
        //connect and get survey
        $query = "SELECT * FROM answers WHERE survey_id='$survey_id';";
        $answers = mysqli_query($connection, $query);            // execute query

        //check how many answerss we got back
        $n = mysqli_num_rows($answers);
        // we'll store all reponses here
        $all_question_responses=[];
        if ($n > 0) 
        {    
            while($row = mysqli_fetch_assoc($answers) )
            $all_question_responses[] = $row;       // get all answer relating to survey
      
            
            if($export=='csv')      // user wants  a csv
            {
                $fp = fopen('file.csv', 'w');           // open file.csv create if not exist
            
                foreach ($all_question_responses as $fields) 
                {
                    fputcsv($fp, $fields);      // capture the data row by row
                } 
                header( 'Location: ./file.csv' );   // download the files
             }
            else
            {   // not csv  = json
                echo "<textarea rows='40%' cols='100%'>".json_encode($all_question_responses)."</textarea>";    //  display the responses as json

                echo "<br>Exported successfully!";
            }
        }else{
            echo "Error!sd";
        }

    }
    else{
     echo "Error! ".$errors;
    }

}
else
{
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    if (!$connection)
    {
        die("Connection failed: " . $mysqli_connect_error);
    }

    if(isset($_POST['survey_id']))
    {   
        // clean and validate the user inputs
        $survey_id = sanitise($_POST['survey_id'], $connection);
        $errors = validateInt($survey_id, 0, -1);
        if($errors=="")
        {
            //connect and get survey
            $query = "SELECT * FROM surveys WHERE survey_id='$survey_id';";
            $result = mysqli_query($connection, $query);            // execute query

            //check how many results we got back
            $n = mysqli_num_rows($result);
            if ($n > 0) 
            {    
                $survey = mysqli_fetch_assoc($result); 
                //showe survey details
        
                echo "<h2>Survey Analysis</h2>";
                echo "<p>Title: {$survey['name']}</p>";
                echo "<form action='view_responses.php' method='POST'>
                    <input type='hidden' name='survey_id' value='$survey_id'>
                    <button type='submit' name='export' value='json'>Export as JSON</button>
                    <button type='submit' name='export' value='csv'>Export as CSV</button>
                    </form>";

            } else
            {
                echo "Error!<br>".mysqli_error($connection);
            }

            // retreive the questions
            $query = "SELECT * FROM questions WHERE survey_id={$survey['survey_id']};";
            $results = mysqli_query($connection, $query);
    
            $n = mysqli_num_rows($results);

            if ($n > 0)
            {   
                $count = 1;         // keep track of the question count
              //  echo "<form action='view_survey.php' method='POST'>";
    
                while($row = mysqli_fetch_assoc($results))
                {
                    // query - get responses to question
                    $query = "SELECT * FROM answers WHERE survey_id='{$survey['survey_id']}' and question_id='{$row['question_id']}';"; 
                    $answer_results = mysqli_query($connection,$query);
                    // get the number of responsses
                    $number_of_reponses = mysqli_num_rows($answer_results); 

                   // echo "<div>";
                    echo <<<END
                    <div style=";margin:10px;width:40%">
                    <fieldset style="width:100%;">
                        <table>
                    
                        <legend><b>Question $count</b></legend>
                        <tr>
                            <th><b>{$row['question']}</b></th>
                        </tr>
                        <tr>
                            <td>Number of responses: $number_of_reponses</td>
                        </tr>
END;
                    echo "<tr><td>";
                    $answers_all = "";
                    while($responses = mysqli_fetch_assoc($answer_results)     )
                    {
                        $answers_all = $answers_all.$responses['answer'];       // compile all the answer ever provided for a question
                    }


                    if(!empty($row['answer_options']))
                        {
                            $options = explode(',',$row['answer_options']); // separate the answer choices in array
                            echo "<br>All answers: ".$answers_all;
                            for($i = 0; $i < count($options);$i++)  // go through choices 
                            {     
                                echo "<tr><td>";  
                                echo "Response <input type='text' disabled value='$options[$i]'><br>";    // show which choice it  is 
                                echo "Chosen ".substr_count($answers_all, $options[$i].",")." times";   // go throught the compiled answers to see if there is a match
                                echo "</td></tr>";
                            }
                          
                            //set up the google charts
                            // source: developer.google.com
                            echo <<<END
                            <script type="text/javascript">
                            google.charts.load('current', {'packages':['corechart']});
                            google.charts.setOnLoadCallback(drawChart);
                            function drawChart() {
                                // Create the data table.
                                var data = new google.visualization.DataTable();
                                data.addColumn('string', 'Responses');
                                data.addColumn('number', 'No. of times chosen');
                                data.addRows([
END;
                                for($i = 0; $i < count($options);$i++)
                                {  
                                    if(($i+1) >= count($options))
                                    {
                                        echo "['{$options[$i]}',".substr_count($answers_all, $options[$i])."]";
                                    }
                                    else
                                    echo "['{$options[$i]}',".substr_count($answers_all, $options[$i])."],";

                                }   
                                echo "]);";
                            echo <<<END
                            
                            var options = {'title':'{$row['question']}',
                                'width':400,
                                'height':300};

                            var chart = new google.visualization.PieChart(document.getElementById('chart_div{$row['question_id']}'));
                            chart.draw(data, options);
                            }
                            </script>
END;
                            }
                            
                        
                        else    // if the question doesn't have choices then just display don't count
                        {
                            echo "Reponses: ".$answers_all;
                        }
                        
                        
                                        
                    echo "</table></fieldset></div>";
                    echo "<div style='float:right;clear:right width:30%;' id='chart_div{$row['question_id']}'></div>";
            

                    echo "<br>";
                    $count++;       

                }

                // echo "<input type='hidden' name='survey_save_id' value='{$survey['survey_id']}'>";  // send the survey_id;
                // echo "<input type='submit' name='save' value='Save'>";
                // echo "</form";
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

require_once "footer.php";
?>