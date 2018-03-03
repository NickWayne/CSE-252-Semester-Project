<?php

function isUsernameTaken($username)
{
    global $pdo;
    //this query gets a count of users who already have the provided username
    $query = "SELECT COUNT(*) FROM users WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    //return TRUE if there was a query error; this makes it seem like the user exists when it might not
    if ($count === false) {
        return true;
    }

    if ($count > 0) {
        return true;
    } else {
        return false;
    }
}

function createUser($username, $password, $firstname, $lastname, $rating, $wins, $losses, $total)
{
    if($username !== "" and $username !== "" and $firstname !== "" and $lastname !== ""){
        global $pdo;
        //Salt and hash the provided password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        //this query inserts the new user record into the table with the salted and hashed password
        $query = "INSERT INTO users (username, password, fname, lname, rating, wins, losses, total) VALUES (:username, :password, :first_name, :last_name, :rating, :wins, :losses, :total)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $passwordHash);
        $stmt->bindParam(":first_name", $firstname);
        $stmt->bindParam(":last_name", $lastname);
        $stmt->bindParam(":rating", $rating);
        $stmt->bindParam(":wins", $wins);
        $stmt->bindParam(":losses", $losses);
        $stmt->bindParam(":total", $total);

        return $stmt->execute();
    }else{
        return false;
    }

}

function checkAuth($username, $password)
{
    global $pdo;
//This query gets the password hash from the user table for the user attempting to login
    $query = "SELECT password,id FROM users WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    $result =$stmt->fetchAll();
    if ($result === false) {
        return false;
    }
    foreach($result as $row){
        $_SESSION['user_id'] = $row['id'];
        return password_verify($password, $row['password']);
    }
}

function logout()
{
    $_SESSION = array();
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
    session_destroy();
}

function updateMove($guessed, $letter, $state){
    global $pdo;
    $query = "INSERT INTO moves(game_id, guessed, guess, state) VALUES (:game_id, :guessed, :guess, :state)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":game_id", $_SESSION['game_id']);
    $stmt->bindParam(":guessed", $guessed);
    $stmt->bindParam(":guess", $letter);
    $stmt->bindParam(":state", $state);
    $stmt->execute();
}

function updateGame($guessed, $state, $finished, $win){
    global $pdo;
    if($finished == 1){
        if($win == 1){
            updateElo(1, $_SESSION['difficulty']);
            $query = "UPDATE users SET total = total + 1, wins = wins + 1 WHERE id = :user_id";
        }else{
            updateElo(-1, $_SESSION['difficulty']);
            $query = "UPDATE users SET total = total + 1, losses = losses + 1 WHERE id = :user_id";
        }
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":user_id", $_SESSION['user_id']);
        $stmt->execute();
    }
    $query = "UPDATE games SET guessed = :guessed ,state = :state, finished = :finished WHERE id = :game_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":game_id", $_SESSION['game_id']);
    $stmt->bindParam(":guessed", $guessed);
    $stmt->bindParam(":state", $state);
    $stmt->bindParam(":finished", $finished);
    $stmt->execute();
}

function makeStats(){
    global $pdo;
    $query = "SELECT fname, lname, rating, wins, losses, total FROM users WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    $stmt->execute();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<div class="col-sm-4">';
        echo '<h1>' . $row['fname'] . ' ' . $row['lname'] . '</h1>';
        echo '</div>';
        echo '<div class="col-sm-4">';
        if($row['total'] > 0){
            echo '<h1>Win %: ' . round(100*($row['wins']/floatval($row['total'])),2) . '%</h1>';
        }else{
            echo '<h1>Win %: 100%</h1>';
        }
        echo '<h1>Wins: ' . $row['wins'] . '</h1>';
        echo '<h1>losses: ' . $row['losses'] . '</h1>';
        echo '</div>';
        echo '<div class="col-sm-4">';
        echo '<h1>Rating: ' . $row['rating'] . '</h1>';
        echo '</div>';
    }
}


function updateElo($win, $diff){
    global $pdo;
    $query = "SELECT rating, wins, losses, total FROM users WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    $stmt->execute();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $change = max(-200, min(200,(floatval(($row['rating'] * (floatval($diff)/5) * ($row['wins']/$row['total'])))/3)*$win));
    }
    echo $change;
    $query = "UPDATE users SET rating = rating + :change WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    $stmt->bindParam(":change", $change);
    $stmt->execute();
}

function createGame($diff){
    global $pdo;
    $query = "SELECT name FROM words WHERE difficulty = :diff";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":diff", $diff);
    $stmt->execute();
    $words = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $query = "INSERT INTO games(user_id, finished, state, word, guessed) VALUES (:user_id, :finished, :state, :word, :guessed)";
    $stmt = $pdo->prepare($query);
    $state_finished = 1;
    $letter = "";
    $guessed = "";
    $state= 0;
    $word = $words[array_rand($words)];
    $_SESSION['difficulty'] = array("easy"=>1, "medium"=>2, "hard"=>3)[$diff];
    $_SESSION['state'] = 0;
    $_SESSION['guessed'] = "";
    $_SESSION['word'] = $word;
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    $stmt->bindParam(":finished", $state);
    $stmt->bindParam(":state", $state);
    $stmt->bindParam(":word", $word);
    $stmt->bindParam(":guessed", $wins);
    $stmt->execute();
    $_SESSION['game_id'] = $pdo->lastInsertId();
}

function loadGame($id){
    global $pdo;
    $query = "SELECT word, guessed, state FROM games WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $query = "SELECT difficulty FROM words WHERE word = :word";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":word", $row['word']);
        $stmt->execute();
        $diff = $stmt->fetchColumn();
        $_SESSION['difficulty'] = array("easy"=>1, "medium"=>2, "hard"=>3)[$diff];
        $_SESSION['state'] = $row['state'];
        $_SESSION['guessed'] = $row['guessed'];
        $_SESSION['word'] = $row['word'];
        $_SESSION['game_id'] = $id;
    }
}

function delteGame($id){
    global $pdo;
    $query = "DELETE FROM moves WHERE game_id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $query = "DELETE FROM games WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
}

function makeMove($letter, $word, $guessed, $state){
    $_SESSION['guessed'] = $guessed . $letter;
    if(strpos($word, $letter) === false){
        $_SESSION['state'] = $state + 1;
    }
    updateMove($_SESSION['guessed'], $letter, $_SESSION['state']);
    updateGame($_SESSION['guessed'], $_SESSION['state'], 0, 0);
    if($state >= 6){
        endGame();
    }
}

function getHistory(){
    global $pdo;
    $query = "SELECT id, finished, word, guessed, state FROM games WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    $stmt->execute();
    $num = 1;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if($row['finished'] == 1){
            if($row['state'] == 6){
                echo "<div class='well clearfix' style='background-color: #ffb3ba'>";
            }else{
                echo "<div class='well clearfix' style='background-color: #baffc9'>";
            }

        }else{
            echo "<div class='well clearfix'>";
        }
        echo '<h1>Game #' . $num . '</h1>';
        if($row['finished'] == 1){
            echo '<h1>Word: ' . $row['word'] . '</h1>';
        }
        echo '<h1>guessed: ' . $row['guessed'] . '</h1>';
        echo "<img src='images/game" . $row['state'] . ".png' style='display: block; margin: auto;'>";
        if($row['finished'] == 0){
            echo '<button type="button" class="btn btn-secondary" onclick="loadGame(' . $row['id'] . ')">Load Game</button>';
        }
        echo '<button type="button" class="btn btn-danger" onclick="deleteGame(' . $row['id'] . ')">delete Game(will not affect win/loss)</button>';


        echo "</div>";
        $num++;
    }
}

function drawLeaderboardButtons($sort){
    echo '<div class="row">';
    echo '<div class="col-sm-4">';
    if($sort == 1){
        echo '<button type="button" class="btn btn-block btn-secondary" onclick="getLeaderboard(4)">Rating <span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></span></button>';
    }else if($sort == 4){
        echo '<button type="button" class="btn btn-block btn-secondary" onclick="getLeaderboard(1)">Rating <span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span></button>';
    }else{
        echo '<button type="button" class="btn btn-block btn-secondary" onclick="getLeaderboard(1)">Rating</button>';
    }
    echo '</div>';
    echo '<div class="col-sm-4">';
    if($sort == 2){
        echo '<button type="button" class="btn btn-block btn-secondary" onclick="getLeaderboard(5)">win % <span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></button>';
    }else if($sort == 5){
        echo '<button type="button" class="btn btn-block btn-secondary" onclick="getLeaderboard(2)">win % <span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></button>';
    }else{
        echo '<button type="button" class="btn btn-block btn-secondary" onclick="getLeaderboard(2)">win %</button>';
    }
    echo '</div>';
    echo '<div class="col-sm-4">';
    if($sort == 3){
        echo '<button type="button" class="btn btn-block btn-secondary" onclick="getLeaderboard(6)">total <span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></button>';
    }else if($sort == 6){
        echo '<button type="button" class="btn btn-block btn-secondary" onclick="getLeaderboard(3)">total <span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></button>';
    }else{
        echo '<button type="button" class="btn btn-block btn-secondary" onclick="getLeaderboard(3)">total</button>';
    }
    echo '</div>';
    echo '</div>';
}


function drawLeaderboard($users){
    foreach($users as $row){
        echo '<div class="well">';
        echo '<div class="row">';
        echo '<div class="col-sm-6">';
        echo '<h1>' . $row['username'] . '</h1>';
        echo '<h3> Rating:</h3>';
        echo '<h3>' . $row['rating'] . '</h3>';
        echo '</div>';
        echo '<div class="col-sm-6">';
        if($row['total'] > 0){
            echo '<h1>' . round(100*($row['wins']/floatval($row['total'])),2) . '% Win</h1>';
        }else{
            echo '<h1>100% Win</h1>';
        }
        echo '<h3>' . $row['total'] . ' Total</h3>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}

function getLeaderboard($num){
    global $pdo;
    $query = "SELECT username, rating, wins, losses, total FROM users WHERE 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll();
    $sortArray = array();

    foreach($users as $user){
        foreach($user as $key=>$value){
            if(!isset($sortArray[$key])){
                $sortArray[$key] = array();
            }
            $sortArray[$key][] = $value;
        }
    }
    drawLeaderboardButtons($num);
    switch($num){
        case 0:
            drawLeaderboard($users);
            break;
        case 1:
            $orderby = "rating";
            array_multisort($sortArray[$orderby],SORT_DESC,$users);
            drawLeaderboard($users);
            break;
        case 2:
            $orderby = "wins";
            array_multisort($sortArray[$orderby],SORT_ASC,$users);
            drawLeaderboard($users);
            break;
        case 3:
            $orderby = "total";
            array_multisort($sortArray[$orderby],SORT_DESC,$users);
            drawLeaderboard($users);
            break;
        case 4:
            $orderby = "rating";
            array_multisort($sortArray[$orderby],SORT_ASC,$users);
            drawLeaderboard($users);
            break;
        case 5:
            $orderby = "wins";
            array_multisort($sortArray[$orderby],SORT_DESC,$users);
            drawLeaderboard($users);
            break;
        case 6:
            $orderby = "total";
            array_multisort($sortArray[$orderby],SORT_ASC,$users);
            drawLeaderboard($users);
            break;
    }

}
