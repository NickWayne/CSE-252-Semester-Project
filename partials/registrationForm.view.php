<?php

require 'top.view.php';

foreach($messages as $message) {
    echo $message;
}

?>
<div class="well clearfix">
    <form action="login.php" method="POST">
        <h3>Create Account</h3>
        <label>Username: <input type="text" class="form-control" name="username"></label><br>
        <label>First name: <input type="text" class="form-control" name="fname"></label><br>
        <label>Last name: <input type="text" class="form-control" name="lname"></label><br>
        <label>Password:<input type="password" class="form-control" name="password"></label><br />
        <label>Retype Password:<input type="password" class="form-control" name="password2"></label><br />
        <input type="submit" name="signup" value="Register">
    </form>
</div>

<?php
require 'bottom.view.php';
