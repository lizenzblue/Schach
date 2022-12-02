<?php

function saveDataGrid($contentForJSON){
    $finalData = json_encode($contentForJSON);  
    file_put_contents("dataGrid.json", $finalData);
}

/*
1 = Bauer
2 = Turm
3 = Reiter
4 = Springer
5 = Dame
6 = König
*/ 

function getFigureCodeByNumber($number) {
    $figures = [
        -1 => '&#9823',
        -2 => '&#9820',
        -3 => '&#9822',
        -4 => '&#9821',
        -5 => '&#9819',
        -6 => '&#9818',
        1 =>'&#9817',
        2 => '&#9814', 
        3 => '&#9816', 
        4 => '&#9815', 
        5 => '&#9813',  
        6 => '&#9812', 
    ];

    return $figures[$number];
}


function colorPlayfield($x, $y){
    if ($y % 2 == 0 && $x % 2 == 0) {
        $cellClass = 'cellBlack';
    } else if ($y % 2 == 0 && $x % 2 == 1) {
        $cellClass = 'cellWhite';
    } else if ($y % 2 == 1 && $x % 2 == 0) {
        $cellClass = 'cellWhite';
    } else {
        $cellClass = 'cellBlack';
    }
    return $cellClass;
}

function generateHTML($dataGrid){
    for($y = 0; $y < count($dataGrid); $y++){
        echo '<div class="row">';
        for($x = 0; $x < count($dataGrid[0]); $x++) {
            $figureValue = $dataGrid[$y][$x];
            if($figureValue > 0 || $figureValue <= -1){
                echo '<div class="' . colorPlayfield($x, $y) . '"> <div id="figure" class="figure">' . getFigureCodeByNumber($figureValue) . '</div></div>';
            } else {
                echo '<div class="' . colorPlayfield($x, $y) . '"></div>';
            }
        }
        echo '</div>';
    }
}

function moveFigure($oldX, $oldY, $newX, $newY, $dataGrid){ 
    $figureValue = $dataGrid[$oldY][$oldX];
    $dataGrid[$newY][$newX] = $figureValue;
    $dataGrid[$oldY][$oldX] = 0;
    return $dataGrid;
}

function checkIfFigurIsInWay($posX, $posY, $dataGrid, $figureValue){
    $fieldValue = $dataGrid[$posY][$posX];
    if($figureValue > 0){
        if($fieldValue > 0 && $fieldValue != 3){
            return true;
        } else {
            return false;
        }
    } elseif($figureValue < 0){
        if($fieldValue > 0 && $fieldValue != -3){
            return true;
        } else {
            return false;
        }
    }
}

function check($newX, $newY, $dataGrid){
    $coords = [$newX, $newY];
    $color = getCurrentColor();
    $enemypieces = getAllPiecesOfColor( $color * -1, $dataGrid);
        if(isUnderAttack($coords, $dataGrid, $enemypieces)){
            if($color == 1){
                echo "Black King check";
            } else {
                echo "White King check";
            }
            return true;
        }
    return false;
}

function checkIfRequestedMoveIsInAllowedMoves($requestedMove, $allowedMoves){
    if(in_array($requestedMove, $allowedMoves)){
        return true;
    } else {
        return false;
    }
}

function getFieldValue($x, $y, $dataGrid){
    $fieldValue = $dataGrid[$y][$x];
    return $fieldValue;
}

function getRequestMove($x, $y){
    $requestMove = [$x, $y];
    return $requestMove;
}
//---------------------------------------------------------------

function validation($oldX, $oldY, $newX, $newY, $dataGrid, $simulateMove = false){

    if($newX > 7 && $newX < 0 && $newY > 7 && $newY < 0){
        return false;
    }

    $absoluteFieldValue = abs($dataGrid[$oldY][$oldX]);
    $onlyOneTime = false;
    $allFigureMoveVectors = [
          2 =>  [[0,1],[0,-1],[1,0],[-1,0]], // Turm
          3 =>  [[1,2],[-1,2],[1,-2],[-1,-2], [-2, 1], [-2,-1], [2, 1], [2, -1]], // Reiter
          4 =>  [[1,1],[1,-1],[-1,-1],[-1,1]], // Bishop
          5 =>  [[1,1],[1,-1],[-1,-1],[-1,1],[0,1],[0,-1],[1,0],[-1,0]] // queen
        ];

    switch ($absoluteFieldValue) {
        case 1:
            if(bauerMoveValid($oldX, $oldY, $newX, $newY, $dataGrid)  ){
                return true;
            }
            return false;
        case 6:
            if(koenigMoveValid($oldX, $oldY, $newX, $newY, $dataGrid, $simulateMove)){
                return true;
            }
            return false;
        case 3:
            $onlyOneTime = true;
            // break fehlt, weil der Reiter ein special case ist und $onlyOneTime nur für ihn veraendert werden muss
            // PS: Idee kam von Ralf
        default:
            if($absoluteFieldValue == 3){
                $onlyOneTime = true;
            }
            if(moveValid($oldX, $oldY, $newX, $newY, $dataGrid, $onlyOneTime, $allFigureMoveVectors[$absoluteFieldValue])){
                return true;
            }
            return false;
    }
}

function bauernThow($x, $y, $throwMoveVectors, $dataGrid, $color){
    $possibelThrows = [];
    $posx = $x + $throwMoveVectors[0];
    $posy = $y + $throwMoveVectors[1];
    $fieldValue = getFieldValue($posx, $posy, $dataGrid);
    $possibelThrow = [$posx, $posy];
    if ($color == 1 && $fieldValue < 0){
        $possibelThrows[] = $possibelThrow;
    } elseif($color == -1 && $fieldValue > 0){
        $possibelThrows[] = $possibelThrow;
    }
    return $possibelThrows;
}

function bauerMoveValid($oldX, $oldY, $newX, $newY, $dataGrid){
    $moveVector = [[0,1], [0,-1], [0,2],[0,-2]];
    $throwMoveVectorsBlack = [[1,1],[-1,1]];
    $throwMoveVectorsWhite = [[1,-1],[-1,-1]];
    $possibleMoves = [];
    $requestMoves = [$newX, $newY];
    $fieldValue = getFieldValue($oldX, $oldY, $dataGrid);
    if($fieldValue == 1){
        $possibleMoves = array_merge($possibleMoves, bauernThow($oldX, $oldY, $throwMoveVectorsWhite[0] ,$dataGrid, 1));
        $possibleMoves = array_merge($possibleMoves, bauernThow($oldX, $oldY, $throwMoveVectorsWhite[1] ,$dataGrid, 1));
        if($oldY == 6){
            $possibleMoves = array_merge($possibleMoves, calculateMoves($oldX, $oldY, $moveVector[3] ,$dataGrid, true));
        }
        $possibleMoves = array_merge($possibleMoves, calculateMoves($oldX, $oldY, $moveVector[1] ,$dataGrid, true));
       
    } elseif($fieldValue == -1) {
        $possibleMoves = array_merge($possibleMoves, bauernThow($oldX, $oldY, $throwMoveVectorsBlack[0] ,$dataGrid, 1));
        $possibleMoves = array_merge($possibleMoves, bauernThow($oldX, $oldY, $throwMoveVectorsBlack[1] ,$dataGrid, 1));
        if($oldY == 1){       
            $possibleMoves = array_merge($possibleMoves, calculateMoves($oldX, $oldY, $moveVector[2] ,$dataGrid, true));
        }
        $possibleMoves = array_merge($possibleMoves, calculateMoves($oldX, $oldY, $moveVector[0] ,$dataGrid, true));
    } else {
        return false;
    }
    return checkIfRequestedMoveIsInAllowedMoves($requestMoves, $possibleMoves);
}

function koenigMoveValid($oldX, $oldY, $newX, $newY, $dataGrid, $simulateMove){
    $moveVector = [[0,1],[0,-1],[1,0],[-1,0], [1,1], [1,-1], [-1,-1], [-1,1]];
    $possibleMoves = getPossibleMovesWithVector($oldX, $oldY, $dataGrid, $moveVector, true);
    $enemyPieces = getAllPiecesOfColor(getCurrentColor() * - 1, $dataGrid);

    if (!$simulateMove) {
        foreach($possibleMoves as $i => $possibleMove) {
            if(isUnderAttack($possibleMove, $dataGrid, $enemyPieces)){
                unset($possibleMoves[$i]);
            }
        }
    }

    $requestMoves = getRequestMove($newX, $newY);
    return checkIfRequestedMoveIsInAllowedMoves($requestMoves, $possibleMoves);
}

function moveValid($oldX, $oldY, $newX, $newY, $dataGrid, $onlyOneTime, $moveVectors){
    return checkIfRequestedMoveIsInAllowedMoves(getRequestMove($newX, $newY), getPossibleMovesWithVector($oldX, $oldY, $dataGrid, $moveVectors, $onlyOneTime));
}

function isUnderAttack($targets, $grid, $enemyPieces) {
    $targetX = $targets[0];
    $targetY = $targets[1];
    foreach ($enemyPieces as $enemyPiece) {
        $pieceX = $enemyPiece["x"];
        $pieceY = $enemyPiece["y"];
        if (validation($pieceX, $pieceY, $targetX, $targetY, $grid, true)) {
            return true;
        }
    }
    return false;
}

function getAllPiecesOfColor($color, $dataGrid) {
    $pieces = [];
    for($i = 0; $i < count($dataGrid); $i++){
        for($j = 0; $j < count($dataGrid[0]); $j++){
            $fieldValue = $dataGrid[$i][$j];
            if($color == 1){
                if ($fieldValue > 0) {
                    $pieces[] = [
                        "x" => $j,
                        "y" => $i,
                        "fieldValue" => $fieldValue,
                    ];
                }
            } else {
                if ($fieldValue < 0 && $color < 0) {
                    $pieces[] = [
                        "x" => $j,
                        "y" => $i,
                        "fieldValue" => $fieldValue,
                    ];
                }
            }
        }
    }
    return $pieces;
}

function getPossibleMovesWithVector($oldX, $oldY, $dataGrid, $moveVector, $oneTimeOnly){
    $possibleMoves = [];
    foreach($moveVector as $value => $key){
        $possibleMoves = array_merge($possibleMoves, calculateMoves($oldX, $oldY, $key ,$dataGrid, $oneTimeOnly));
    }
    return $possibleMoves;
}

function calculateMoves($x, $y, $directionVector ,$dataGrid, $oneTimeOnly = false){
    $posX = $x + $directionVector[0];
    $posY = $y + $directionVector[1];
    $possibleMoves = [];
    $figureValue = $dataGrid[$x][$y];
    while($posX >= 0 && $posX < 8 && $posY >= 0 && $posY < 8) {
        if(checkIfFigurIsInWay($posX, $posY, $dataGrid, $figureValue) == true){
                break;
        } else {
            $possibleMoves[] = [$posX, $posY];
            $posX += $directionVector[0];
            $posY += $directionVector[1];
        }
        if ($oneTimeOnly == true) {
            break;
        }
    }
    return $possibleMoves;    
}

//---------------------------------------------------------------

function fancy_dump($var) {
    echo '<div style="background: grey; color: #fff; padding: 0 20px; overflow: hidden;">';
    if (!is_scalar($var)) {
        if (is_array($var)) {
            echo array_dump($var);
        }
    } else {
        $typeOfVar = gettype($var);
        echo '<pre>' . $var . ' &#8594; ' . $typeOfVar . '</pre>';
    }
    echo '</div>';
}

function array_dump($array) {
    echo '<ul>';
    foreach($array as $key => $value) {
        echo '<li>';
        if (is_array($value)) {
            echo array_dump($value);
        } else {
            echo $value;
        }
        echo '</li>';
    }
    echo '</ul>';
}

//---------------------------------------------------------------

function createDataGridForJSON(){
    $dataGrid = [
        [-2,-3,-4,-5,-6,-4,-3,-2],
        [-1,-1,-1,-1,-1,-1,-1,-1],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [1,1,1,1,1,1,1,1],
        [2,3,4,5,6,4,3,2],
    ];
    $dataGrid = [
        [0,0,0,0,-6,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,1,0,0,0],
        [0,0,0,0,0,3,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
    ];
    file_put_contents("dataGrid.json", json_encode($dataGrid));
    return $dataGrid;
}

function getCurrentColor(){
    return file_get_contents("currentColor.json");
}

function isCoordinateInPieces($pieceX, $pieceY, $piecesOfColor){
    $wantToMovePiece = ["x"=>$pieceX, "y"=>$pieceY];
    if(in_array($wantToMovePiece, $piecesOfColor)){
        return true;
    }
    return false;
}

//---------------------------------------------------------------

$error = '';

if(file_exists("dataGrid.json")){
    $dataGrid = json_decode(file_get_contents("dataGrid.json"), true);
} else {
    $dataGrid = createDataGridForJSON();
}

if(isset($_POST["newGame"])){
   $dataGrid = createDataGridForJSON();
    file_put_contents("currentColor.json", json_encode(1));
}

if(isset($_POST["submit"])){
    $inputs = [(int) $_POST["oldx"], (int) $_POST["oldy"], (int) $_POST["newx"], (int) $_POST["newy"]];
    $color = (int)getCurrentColor();
    if(!file_exists("currentColor.json")){
        file_put_contents("currentColor.json", json_encode(1));
    }

    $piecesOfColor = getAllPiecesOfColor($color, $dataGrid);
    if($dataGrid[$inputs[1]][$inputs[0]] > 0 && $color > 0 || $dataGrid[$inputs[1]][$inputs[0]] < 0 && $color < 0){
        if(validation($inputs[0], $inputs[1], $inputs[2], $inputs[3], $dataGrid)){
            $dataGrid = moveFigure($inputs[0], $inputs[1], $inputs[2], $inputs[3], $dataGrid);
            saveDataGrid($dataGrid);
            $color = $color * -1;
            file_put_contents("currentColor.json", json_encode($color));
        } else {
            $error = 'Figurenzug ist nicht valide!';
        }
    } else {
        $error = 'Figurenfarbe ist nicht valide!';
    }
}
?>

<html>
    <head>
        <style>
            .figure{
                position: absolute;
                z-index: 99999;
                width: 40px;
                height: 40px;
                flex: 1;
                font-size: 30px;
                text-align: right;
                cursor: pointer;
            }
            .cellWhite{
                position: relative;
                z-index: 4000;
                background-color: brown;
                width: 50px;
                height: 50px;
                font-size: 32px;
                text-align: center;
            }
            .cellBlack{
                position: relative;
                z-index: 4000;
                background-color: burlywood;
                width: 50px;
                height: 50px;
                font-size: 32px;
                text-align: center;
            }
            .grid{
                display: block;
                border: solid 2px black;
                width: 400px;
                height: auto;
                margin: 0 auto;
            }
            .row{
                align-items: flex-start;
                display: flex;
                 width: 400px;
                height: 50px;
            }
            .form{
                margin: 2%;
            }
            .form .btn{
                width: 70px;
                height: 23px;
            }
            .error {
                color: RED;
                font-weight: 700;
                width: 100%;
                font-size: 24px;
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
            generateHTML($dataGrid);
        ?> 
        </div>
        <div class="form">
            <form action="test.php" method="post">
                <input type="number" name="oldx" placeholder="altes X">
                <input type="number" name="oldy" placeholder="altex Y">
                <input type="number" name="newx" placeholder="neuex X">
                <input type="number" name="newy" placeholder="neues Y">
                <input class="btn" type="submit" value="Submit" name="submit">
                <input class="btn" type="submit" value="New Game" name="newGame">
            </form>
        </div>
    </body>
</html>
