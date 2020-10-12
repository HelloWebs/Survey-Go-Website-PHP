# Survey Go Website PHP
 Survey demonstration site 
 
<Jamshid Nazari> - 2018/19

<a href="Surveyandgo.freecluster.eu">Live Link: Surveyandgo.freecluster.eu</a>

Use details below for admin access

Admin access
-------------- 
username: admin
password: secret

test user access
--------------
username: a
password: test

DOCUMENTATION:

All user input are sanitised and validated.

User Accounts
--------------
User can create an account through sign_up.php
It will add their credentials to the Database

User can Sign In to their account through sign_in.php
which will authenticate and set session variable.

The user can view their account details in account.php
and change it in account_set.php.
Password change link in account.php uses set_password.php to change the password

User will be signed out and sessions expired through sign_out.php

Admin can view, change and delete user details in admin.php but no other user can view this page.


Survey Management
--------------------
User can create a survey through the surveys_manage.php page which directs to create_survey.php
They can also add questions and question details to it.

User can also edit the survey and questions details in the surveys_manage.php which directs to edit_survey.php

User can complete a survey provided they have a token for it or they created.
public surveys link/ token are displayed in about.php and index.php which means it can be done by any user.


Survey Results
--------------------
view_responses.php statistics of the site:number of users taken survey, respones for each question and question field .
Graphs and charts displays creates a summery of data.


Files
-------------
- about.php
Displays about info

- account_set.php
Updates user's account details

- admin.php
Shows admin page and has code for admin functions such as view, delete, add, edit users details

- create_data.php
Creates a database structure the website can use

- create_survey.php
Enables user to create surveys and add questions and question details to survey and pushes it to database. Generates a sharable link to survey

- credentials.php
Store database credentials 

- edit_survey.php
Allows user to change their existing surveys and add ,eddit or remove questions

-footer.php
closes html tags and displays footer

- header.php
Displays the opeing html tag and imports the scripts and css

- helper.php
Contains functions used through out the website to validate forms and sanitise user input

- index.php
Main page 

- script.js
Contains java script

set_password.php
-verifies and hashes the password if old password is correct

- sign_in.php
Authenticates use and sets the session variable

- sign_out.php
Deauthenticates the user and signs them out

-sign_up.php
Allows user to create an account 

- surveys_manage.php
Displays all of the user surveys

-view_responses.php
Displays the survey responses and responses and number of responses for each question.
Allows the responses to be exported

-view_survey.php
Display the survey to a user to fill out and submit



Database structure (ERD):
---------------------

<-- users -->
username <PK>
password (hashed)
firstname 
surname   
email
dob
phone_number



<-- surveys -->
survey_id <PK>
username <FK>
name
description
end_message


<-- questions -->
question_id <PK>
survey_id <FK>
question 
type 
answer_options 

<-- answers -->
survey_id<FK>
question_id <FK>
username
answer
