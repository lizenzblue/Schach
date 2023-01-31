<?php

include_once "functions.php";

/*
1 = Bauer
2 = Turm
3 = Reiter
4 = Springer
5 = Dame
6 = König
*/

//---------------------------------------------------------------

$error = '';
$possibleMovesToShowOnBoard = [];

if (file_exists("./dataForGame/dataGrid.json")) {
    $dataGrid = json_decode(file_get_contents("./dataForGame/dataGrid.json"), true);
} else {
    $dataGrid = createDataGridForJSON();
}

if(isset($_POST["showMoves"])){
    $inputs = [(int)$_POST["oldx"], (int)$_POST["oldy"]];
    $possibleMovesToShowOnBoard = getPossibleMovesToShowOnBoard($inputs, $dataGrid);
}

if (isset($_POST["newGame"])) {
    $dataGrid = createDataGridForJSON();
    file_put_contents("./dataForGame/currentColor.json", json_encode(1));
    $dataForJSON =  json_encode([
        'countedMovesWhiteKing' => 0, 
        'countedMovesWhiteTurmOne' => 0,
        'countedMovesWhiteTurmTwo' => 0,
        'countedMovesBlackKing' => 0,
        'countedMovesBlackTurmOne' => 0,
        'countedMovesBlackTurmTwo' => 0, 
    ]);
    file_put_contents("./dataForGame/countedMovesForRochade.json", $dataForJSON);
}
if (isset($_POST["submit"])) {
    $inputs = [(int)$_POST["oldx"], (int)$_POST["oldy"], (int)$_POST["newx"], (int)$_POST["newy"]];
    $color = (int)getCurrentColor();
    if (!file_exists("./dataForGame/currentColor.json")) {
        file_put_contents("./dataForGame/currentColor.json", json_encode(1));
    }

    $piecesOfColor = getAllPiecesOfColor($color, $dataGrid);
    if ($dataGrid[$inputs[1]][$inputs[0]] > 0 && $color > 0 || $dataGrid[$inputs[1]][$inputs[0]] < 0 && $color < 0) {
        if (validation($inputs[0], $inputs[1], $inputs[2], $inputs[3], $dataGrid)) {
            if(checkForRochade([$inputs[0], $inputs[1]], [$inputs[2], $inputs[3]], $dataGrid)){
                $dataForExecution = rochade([$inputs[0], $inputs[1]], $dataGrid, [$inputs[2], $inputs[3]]);
                $dataGrid = executeRochade($dataForExecution, $dataGrid);
            } else {
                $dataGrid = moveFigure($inputs[0], $inputs[1], $inputs[2], $inputs[3], $dataGrid);
            }
            saveDataGrid($dataGrid);
            $color = $color * -1;
            file_put_contents("./dataForGame/currentColor.json", json_encode($color));
        } else {
            $error = 'Figurenzug ist nicht valide!';
        }
    } else {
        $error = 'Figurenfarbe ist nicht valide!';
    }
}
if(checkForCheckAndCheckMate($dataGrid)){
    echo '<script type="text/javascript">
    window.onload = function () { 
        alert("CHECKMATE"); 
        document.getElementById("newGameBtn").click();
    } 
</script>';
}
?>

<html>
<head>
    <style>
        body{
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #dddddd;
        }
        .figure {
            position: absolute;
            z-index: 99999;
            width: 40px;
            height: 40px;
            flex: 1;
            font-size: 30px;
            text-align: right;
            cursor: pointer;

        }        

        .cellWhite {
            position: relative;
            z-index: 4000;
            background-color: burlywood;
            width: 50px;
            height: 50px;
            font-size: 32px;
            text-align: center;
        }

        .cellBlack {
            position: relative;
            z-index: 4000;
            background-color: brown;
            width: 50px;
            height: 50px;
            font-size: 32px;
            text-align: center;
        }

        .markedField {
            background-color: rgba(0, 255, 0, 0.2);
        }

        .grid {
            box-shadow: 10px 5px 5px gray;
            display: block;
            border: solid 2px black;
            width: 400px;
            height: auto;
            margin: 0 auto;
        }

        .row {
            align-items: flex-start;
            display: flex;
            width: 400px;
            height: 50px;
        }

        .form {
            margin-left: 33.6%;
            margin-top: 2%;
            text-align: left;
            width: 500px;
        }

        .form input[type="number"] {
            width: 90px;
            height: 30px;
            font-size: 15px;
            padding: 5px;
            margin: 10px;
        }

        .form .btn {
            width: 100px;
            height: 30px;
            margin-left: 35px;
            margin-top: 10px;
            font-size: 16px;
        }

        .error {
            color: red;
            font-weight: bold;
            font-size: 20px;
            margin-top: 20px;
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="error">
    <?php
    echo $error;
    ?>
</div>
<div class="grid">
    <?php
    generateHTML($dataGrid, $possibleMovesToShowOnBoard);
    ?>
</div>
<div class="form">
    <form action="Schach.php" method="post">
        <input type="number" name="oldx" placeholder="altes X">
        <input type="number" name="oldy" placeholder="altes Y">
        <input type="number" name="newx" placeholder="neues X">
        <input type="number" name="newy" placeholder="neues Y">
        <input class="btn" type="submit" value="Submit" name="submit">
        <input id="newGameBtn" class="btn" type="submit" value="New Game" name="newGame">
        <input id="showMoves" class="btn" type="submit" value="Show Moves" name="showMoves">
    </form>
</div>
</body>
</html>
