<?php

//The error span for when passwords do not match on the register form
$passwordMatchError = <<<EOD
<span>The provided passwords do not match.</span>
EOD;

//The error span for when a username from the register form already exists
$usernameTakenError = <<<EOD
<span>The username is already taken.</span>
EOD;

//The error span for any generic errors that occur when the INSERT fails
$registrationError = <<<EOD
<span>There was an error registering your account.  Please try again.</span>
EOD;

//The error span for any a login error
$loginError = <<<EOD
<span>The provided credentials are incorrect.</span>
EOD;

//The success span for when the user account is successfully created
$userCreated = <<<EOD
<span>You've successfully created your account.  Proceed with login.</span>
EOD;
