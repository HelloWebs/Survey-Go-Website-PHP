<?php

// Things to notice:
// This script holds the sanitisation function that we pass all our user data to
// This script holds the validation functions that double-check our user data is valid
// if the data is valid return an empty string, if the data is invalid return a help message



// function to sanitise (clean) user data:
function sanitise($str, $connection)
{
	if (get_magic_quotes_gpc())
	{
		// just in case server is running an old version of PHP with "magic quotes" running:
		$str = stripslashes($str);
	}
	// escape any dangerous characters, e.g. quotes:
	$str = mysqli_real_escape_string($connection, $str);
	// ensure any html code is safe by converting reserved characters to entities:
	$str = htmlentities($str);
	// return the cleaned string:
	return $str;
}





// if the data is valid return an empty string, if the data is invalid return a help message
function validateString($field, $minlength, $maxlength) 
{
    if (strlen($field)<$minlength) 
    {
        // wasn't a valid length, return a help message:	
       // throw new Exception('Division by zero.');	//debugging, getting null somewhere?
        return "Minimum length: " . $minlength; 
    }
	elseif (strlen($field)>$maxlength) 
    { 
		// wasn't a valid length, return a help message:
        return "Maximum length: " . $maxlength; 
    }
	// data was valid, return an empty string:
    return ""; 
}

// if the email is valid return "" if its invalid return help message.
function validateEmail($field)
{
//TODO 	$field = filter_var($field,FILTER_)
    
    if(!filter_var($field,FILTER_VALIDATE_EMAIL))
    {
        return "Not a valid Email";     
    }
    return "";
}



// if the data is valid return an empty string, if the data is invalid return a help message
function validateInt($field, $min, $max) 
{ 
    // see PHP manual for more info on the options: http://php.net/manual/en/function.filter-var.php
    
    // it fails on 0 we have to set this check. idk why?
    if($field=="0"&& $min <= 0) return "";
    if($max < 0)    // no upper limit
	    $options = array("options" => array("min_range"=>$min));
    else
        $options = array("options" => array("min_range"=>$min,"max_range"=>$max));
	if (!filter_var($field, FILTER_VALIDATE_INT, $options)) 
    { 
		// wasn't a valid integer, return a help message:
        return "Not a valid number (must be whole and in the range: " . $min . " to " . $max . ")"; 
    }
	// data was valid, return an empty string:
    return ""; 
}

// all other validation functions should follow the same rule:
// if the data is valid return an empty string, if the data is invalid return a help message
// ...

function validateDate($field)
{
     $date = date_create_from_format("Y-m-d",$field);
    
    if($date)
    {
        return "";
     }
    else{
        return "Invalid date format<br>";
    }
    
}
?>