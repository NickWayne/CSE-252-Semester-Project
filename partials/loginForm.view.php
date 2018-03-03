<?php
require 'top.view.php';

foreach($messages as $message) {
    echo $message;
}

?>
<div class="well clearfix">
    <form action="login.php" method="POST">
        <h3>Login</h3>
        <label>Username: <input type="text" class="form-control" name="username"></label><br>
        <label>Password:<input type="password" class="form-control" name="password"></label><br />
        <input type="submit" name="login" value="Login">
        <input type="submit" name="register" value="Register New User">
    </form>
</div>

<?php
require 'bottom.view.php';
