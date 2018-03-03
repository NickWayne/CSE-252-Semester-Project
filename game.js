function letterChoice(ele){
    var but = $(ele);
    console.log(but.children("h4").text());
    $.ajax({
        type: 'POST',
        url: "game.php",
        data: 'change=' + but.children("h4").text()
    });
    var text = $("#guessed").val();
    $("#guessed").val(text + but.children("h4").text());
    if($("#word").val().indexOf(but.children("h4").text()) > -1){
        $(".word_choice").empty();
        var missing = 0;
        for (var i=0; i < $("#word").val().length; i++) {
            console.log($("#word").val().charAt(i));
            if($("#word").val().charAt(i) == " "){
                $(".word_choice").append("<br>");
            }else if($("#guessed").val().indexOf($("#word").val().charAt(i)) > -1){
                $(".word_choice").append("<p style='display: inline;'>" + $("#word").val().charAt(i) + "</p>");
            }else{
                $(".word_choice").append("<p style='display: inline;'>_ </p>");
                missing += 1;
            }
        }
        if(missing == 0){
            $("#winner").removeClass("hidden");
            $.ajax({
                type: 'POST',
                url: "game.php",
                data: 'winner=' + but.children("h4").text()
            });
        }
    }else{
        $("#state").val(parseInt($("#state").val()) + 1);
        if($("#state").val() >= 6){
            $.ajax({
                type: 'POST',
                url: "game.php",
                data: 'gameOver=' + but.children("h4").text()
            });
            $("#gameover").removeClass("hidden");
        }
        $(".image").empty();
        $(".image").append("<img src='images/game" + $("#state").val() + ".png' style='display: block; margin: auto;'>")

    }
    but.prop("onclick", null);
    but.parent().appendTo(".chosen");
}

function loadGame(id){
    $.ajax({
        type: 'POST',
        url: "game.php",
        data: 'loadGame=' + id
    });
    window.location.href = 'http://waynens.cse252.spikeshroud.com/semester-project/game.php';
}
function deleteGame(id){
    $.ajax({
        type: 'POST',
        url: "game.php",
        data: 'deleteGame=' + id
    });
    window.location.href = 'http://waynens.cse252.spikeshroud.com/semester-project/history.php';
}

function getLeaderboard(num){
    $("#leaderboard").empty();
    $.ajax({
        type: 'POST',
        url: "leaderboard.php",
        data: 'sort=' + num
    });
    console.log("running" + num);
    window.location.href = 'http://waynens.cse252.spikeshroud.com/semester-project/leaderboard.php';
}
