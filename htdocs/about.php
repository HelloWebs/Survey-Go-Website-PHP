<?php

// execute the header script:
require_once "header.php";

echo "Hi,<br>";
echo "This is a web survey platform! <br> You can create surveys and share them with your friends or colleages!<br>You have full control over the survey.";
echo "<br> Here is a survey you can complete: <br>";
echo "<a href='./view_survey.php?token=customercode'> Customber feedback: view_survey.php?token=customercode</a>";
// finish of the HTML for this page:
require_once "footer.php";

?>