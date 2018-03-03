<?php
    session_start();
    if($_SESSION['auth'] !== true){
        header('Location: http://' . $_SERVER['HTTP_HOST'] . '/semester-project/login.php');
    }
    require 'db.php';
    require 'functions.php';
    if(!isset($_SESSION['guessed'])){
        $_SESSION['guessed'] = "";
    }
    if(!isset($word)){
        $word= "";
    }
    if(!isset($letter)){
        $letter = "";
    }
    if(!isset($state)){
        $state = 0;
    }
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "<br />";
        die();
    }
    if(isset($_POST['gameOver']) && !empty($_POST['gameOver'])) {
        updateGame($_SESSION['guessed'], $_SESSION['state'], 1, 0);
    }
    if(isset($_POST['winner']) && !empty($_POST['winner'])) {
        updateGame($_SESSION['guessed'], $_SESSION['state'], 1, 1);
    }
    if(isset($_POST['change']) && !empty($_POST['change'])) {
        $letter = $_POST['change'];
        makeMove($letter, $_SESSION['word'], $_SESSION['guessed'], $_SESSION['state']);
    }
    if(isset($_POST['loadGame']) && !empty($_POST['loadGame'])) {
        $id = $_POST['loadGame'];
        loadGame($id);
    }
    if(isset($_POST['deleteGame']) && !empty($_POST['deleteGame'])) {
        $id = $_POST['deleteGame'];
        delteGame($id);
    }
    if(isset($_GET['newGame']) && !empty($_GET['newGame'])) {
        createGame($_GET['newGame']);
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
        <div class="jumbotron hidden" id="gameover">
            <h1>Game Over :(</h1>
        </div>
        <div class="jumbotron hidden" id="winner">
            <h1>You Win! :)</h1>
        </div>
        <input type="hidden" id="word" value="<?=$_SESSION['word']?>">
        <input type="hidden" id="guessed" value="<?=$_SESSION['guessed']?>">
        <input type="hidden" id="state" value="<?=$_SESSION['state']?>">
        <div class="col-sm-12">
            <div class="well clearfix">
                <div class="word_choice">
                    <?php
                        $word = preg_replace("/[ ]/","!",$_SESSION['word']);
                        foreach(str_split($word) as $value){
                    		if($value === "!"){
                    			echo "<br>";
                    		}else if(strpos($_SESSION['guessed'], $value) !== false){
                    			echo "<p style='display: inline;'>" . $value . "</p>";
                    		}else{
                                echo "<p style='display: inline;'>_ </p>";
                            }
                    	}
                     ?>
                </div>
                <div class="image">
                    <?php
                        echo "<img src='images/game" . $_SESSION['state'] . ".png' style='display: block; margin: auto;'>";
                     ?>
                </div>
                <div class="chosen">
                    <h2>Chosen</h2>
                    <?php
                        if(strlen($_SESSION['guessed']) > 1){
                            $stringParts = str_split($_SESSION['guessed']);
                            sort($stringParts);
                            $guessed = implode('', $stringParts);
                            foreach(str_split($guessed) as $character){
                                echo "<div class='col-sm-1' id=" . $character . ">";
                                echo "<div class='well' onClick='letterChoice(this)'>";
                                echo "<h4 class='text-center'>" . $character . "</h4>";
                                echo "</div>";
                                echo "</div>";
                            }
                        }
                     ?>
                </div>
                <div class="choices">
                    <br><br><h2>Choices</h2>
                    <?php
                        $possible_choices = "abcdefghijklmnopqrstuvwxyz";
                        $possible_choices = str_replace(str_split($_SESSION['guessed']),"",$possible_choices);
                        foreach(str_split($possible_choices) as $character){
                            echo "<div class='col-sm-1' id=" . $character . ">";
                            echo "<div class='well' onClick='letterChoice(this)'>";
                            echo "<h4 class='text-center'>" . $character . "</h4>";
                            echo "</div>";
                            echo "</div>";
                        }
                     ?>
                </div>
        </div>
        <script src="game.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    </body>
</html>
