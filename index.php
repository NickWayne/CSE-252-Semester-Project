<?php
    session_start();
    if($_SESSION['auth'] !== true){
        header('Location: http://' . $_SERVER['HTTP_HOST'] . '/semester-project/login.php');
    }
    require 'db.php';
    require 'functions.php';
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "<br />";
        die();
    }

?>
<!doctype html>
<html lang="en">
    <head>
        <title>Hangman With Friends</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
    </head>
    <body>
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="index.php">Hangman With Friends</a>
                </div>
                <div class="collapse navbar-collapse" id="myNavbar">
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">New Game <span class="caret"></span></a>
                          <ul class="dropdown-menu">
                            <li><a href="game.php?newGame=easy">Easy</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="game.php?newGame=medium">Medium</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="game.php?newGame=hard">Hard</a></li>
                          </ul>
                        </li>
                        <li><a class="nav-item nav-link" href="history.php">History</a></li>
                        <li><a class="nav-item nav-link" href="leaderboard.php">Leaderboard</a></li>
                        <?php
                            if($_SESSION['auth'] === true){
                                echo '<li><a class="nav-item nav-link" href="login.php?logout=1">Logout</a></li>';
                            }else{
                                echo '<li><a class="nav-item nav-link" href="login.php">Login</a></li>';
                            }
                         ?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="col-sm-12">
            <div class="well clearfix">
                <h1>Welcome to the best hangman experience on the web</h1>
                <img src="images/game6.png" style="display: block; margin: auto;">
        </div>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    </body>
</html>
