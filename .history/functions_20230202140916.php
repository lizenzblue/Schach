<?php

function translateChessfieldsToXAndYCoords($chessfieldCorrds, $moveToChessField, $dataGrid){
    $coords = [0, 0, 0, 0];
    $chessfield = (array) json_decode(file_get_contents("./dataForGame/schachcoords.json"));

    for ($y = 0; $y < count($chessfield); $y++) {
        for ($x = 0; $x < count($chessfield[0]); $x++) {
            if($chessfield[$y][$x] == $moveToChessField){
                $coords[0] = $x;
                $coords[1] = $y;
            }
            if($chessfield[$y][$x] == $chessfieldCorrds){
                $coords[2] = $x;
                $coords[3] = $y;
            }
        }
    }
    return $coords;
}

function getPossibleMovesToShowOnBoard($coords, $dataGrid){
    $figure = $dataGrid[$coords[3]][$coords[2]];
    var_dump($coords);
    if(abs($figure) == 1 || abs($figure) == 3 || abs($figure) == 6){
        $possibleMoves = getPossibleMovesWithVector($coords[0], $coords[1], $dataGrid, getMoveVectorsForFigure($figure), true);
    } else {
        $possibleMoves = getPossibleMovesWithVector($coords[0], $coords[1], $dataGrid, getMoveVectorsForFigure($figure));
    }
    return $possibleMoves;
}

function saveDataGrid($contentForJSON)
{
    $finalData = json_encode($contentForJSON);
    file_put_contents("./dataForGame/dataGrid.json", $finalData);
}

function colorPlayfield($x, $y)
{
    if ($y % 2 == 0 && $x % 2 == 0) {
        $cellClass = 'cellWhite';
    } else if ($y % 2 == 0 && $x % 2 == 1) {
        $cellClass = 'cellBlack';
    } else if ($y % 2 == 1 && $x % 2 == 0) {
        $cellClass = 'cellBlack';
    } else {
        $cellClass = 'cellWhite';
    }
    return $cellClass;
}

function generateHTML($dataGrid, $highlightCoords = []){
    for ($y = 0; $y < count($dataGrid); $y++) {
        echo '<div class="row">';
        for ($x = 0; $x < count($dataGrid[0]); $x++) {
            $figureValue = $dataGrid[$y][$x];
            $hightlightClass = '';

            if (in_array([$x, $y], $highlightCoords)) {
                $hightlightClass = 'markedField';
            }

            if ($figureValue > 0 || $figureValue <= -1) {
                echo '<div class="' . colorPlayfield($x, $y) . ' ' . $hightlightClass . '"> <div id="figure" class="figure">' . getFigureCodeByNumber($figureValue) . '</div></div>';
            } else {
                echo '<div class="' . colorPlayfield($x, $y) . ' ' . $hightlightClass . '"></div>';
            }
        }
        echo '</div>';
    }
}

function getFigureCodeByNumber($number)
{
    $figures = [
        -1 => '&#9823',
        -2 => '&#9820',
        -3 => '&#9822',
        -4 => '&#9821',
        -5 => '&#9819',
        -6 => '&#9818',
        1 => '&#9817',
        2 => '&#9814',
        3 => '&#9816',
        4 => '&#9815',
        5 => '&#9813',
        6 => '&#9812',
    ];

    return $figures[$number];
}

function moveFigure($oldX, $oldY, $newX, $newY, $dataGrid)
{
    $figureValue = $dataGrid[$oldY][$oldX];
    $dataGrid[$newY][$newX] = $figureValue;
    $dataGrid[$oldY][$oldX] = 0;
    return $dataGrid;
}

function checkIfFigurIsInWay($posX, $posY, $dataGrid, $figureValue)
{
    $fieldValue = $dataGrid[$posY][$posX];
    if ($figureValue > 0) {
        if ($fieldValue > 0 && $fieldValue != 3) {
            return true;
        } else {
            return false;
        }
    } elseif ($figureValue < 0) {
        if ($fieldValue < 0 && $fieldValue != -3) {
            return true;
        } else {
            return false;
        }
    }
}

function check($coords, $dataGrid, $color, $onlytesting = false)
{
    $enemyPieces = getAllPiecesOfColor($color * -1, $dataGrid);
    if (isUnderAttack($coords, $dataGrid, $enemyPieces)) {
        if(!$onlytesting){
            if ($color == -1) {
                echo "Black King check";
            } else {
                echo "White King check";
            }
        }
        return true;
    }
    return false;
}

function getCountedMoves(){
    return (array) json_decode(file_get_contents("./dataForGame/countedMovesForRochade.json"));
}

function detectTurmForRochade($posKing, $moveToCoords){
    $color = getCurrentColor();
    $returnValue = "";
    if($moveToCoords[0] < $posKing[0]){
        ($color == 1) ?  $returnValue = "countedMovesWhiteTurmOne" : $returnValue = "countedMovesBlackTurmOne";
    } else {
        ($color == 1) ?  $returnValue = "countedMovesWhiteTurmTwo" : $returnValue = "countedMovesBlackTurmTwo";
    }
    return $returnValue;
}

function checkIfFieldsBetweenAreEmpty($coordsFigureOne, $coordsFigureTwo, $moveVector, $dataGrid){
    while($coordsFigureOne[0] != $coordsFigureTwo[0]){
        $coordsFigureOne[0] += $moveVector[0];

        if($coordsFigureOne[0] == 0 ||$coordsFigureOne[0] == 7){
            break;
        }
        if($dataGrid[$coordsFigureOne[1]][$coordsFigureOne[0]] != 0){
            return false;
        }
    }
    return true;
}

function checkForRochade($oldCoords, $newCoords, $dataGrid){
    $returnValue = true;
    if($dataGrid[$oldCoords[1]][$oldCoords[0]] != 6 || $dataGrid[$oldCoords[1]][$oldCoords[0]] != -6){
        $returnValue = false;
    }
    $fieldsmoved = 0;
    ($oldCoords[0] > $newCoords[0]) ? $fieldsmoved = $oldCoords[0] - $newCoords[0] : $fieldsmoved = $newCoords[0] - $oldCoords[0];
    ($fieldsmoved == 2) ? $returnValue = true : $returnValue = false;
    return $returnValue;
}

function rochade($posKing, $dataGrid, $kingMoveCoords){ 
    $posRock = [];
    $pieces =  getAllPiecesOfColor(getCurrentColor(), $dataGrid); 
    $color = getCurrentColor();
    $assingKings = [
        1 => "countedMovesWhiteKing",
        -1 => "countedMovesBlackKing",
    ];
    $detectedFigures = [
        "king" => $assingKings[$color],
        "rock" => detectTurmForRochade($posKing, $kingMoveCoords),
    ];
    $possibilityRochade = true; 

    $moveVectorBetweenKingAndRock = [];

    if($posKing[0] > $kingMoveCoords[0]){
        $moveVectorBetweenKingAndRock = [-1, 0];
    } else {
        $moveVectorBetweenKingAndRock = [1, 0];
    }

    
    if(checkForCheckAndCheckMate($dataGrid)){

        $possibilityRochade = false;
    }

    if(!(strpos($detectedFigures["king"], "White") && strpos($detectedFigures["rock"], "White") || 
    strpos($detectedFigures["king"], "Black") && strpos($detectedFigures["rock"], "Black"))){

        $possibilityRochade = false;
    }

    $countedMoves = getCountedMoves();

    if($countedMoves[$detectedFigures["king"]] != 0 || $countedMoves[$detectedFigures["rock"]] != 0){

        $possibilityRochade = false;
    } 

    if(strpos($detectedFigures["rock"], "One")){
        ($color == 1) ? $posRock = [0, 7] : $posRock = [0, 0];
    } else {
        ($color == 1) ? $posRock = [7, 7] : $posRock = [7, 0];
    }
    if(!checkIfFieldsBetweenAreEmpty($posKing, $posRock, $moveVectorBetweenKingAndRock, $dataGrid)){
        $possibilityRochade = false;
    }
    $coords = $posKing;
    for ($x=0; $x < 1; $x++) { 
        $coords[0] += $moveVectorBetweenKingAndRock[0];
        if(isUnderAttack($coords, $dataGrid, getAllPiecesOfColor($color * -1, $dataGrid))){
            $possibilityRochade = false;
        }
    }

    $result = [
        "possibility" => $possibilityRochade,
        "rock" => $detectedFigures["rock"],
        "king" => $detectedFigures["king"],
        "rockCoords" => $posRock,
        "kingcoords" => $posKing,
        "kingMoveCoords" => $kingMoveCoords,
    ];

    return $result;
}

function executeRochade($dataOfFigures, $dataGrid){
    $arrayOfRockMoveToCoords = [
        "countedMovesWhiteTurmOne" => [3, 7],
        "countedMovesWhiteTurmTwo" => [5, 7],
        "countedMovesBlackTurmOne" => [3, 0],
        "countedMovesBlackTurmTwo" => [5, 0],
    ];
    $color = getCurrentColor();
    $rockMoveToCoords = $arrayOfRockMoveToCoords[$dataOfFigures["rock"]];
    $kingMoveToCoords = $dataOfFigures["kingMoveCoords"];
    $kingcoords = $dataOfFigures["kingcoords"];
    $rockCoords = $dataOfFigures["rockCoords"];
    $dataGrid[$rockCoords[1]][$rockCoords[0]] = 0;
    $dataGrid[$kingcoords[1]][$kingcoords[0]] = 0;

    if($color ==  1){
        $dataGrid[$kingMoveToCoords[1]][$kingMoveToCoords[0]] = 6;
        $dataGrid[$rockMoveToCoords[1]][$rockMoveToCoords[0]] = 2;
    } else {
        $dataGrid[$kingMoveToCoords[1]][$kingMoveToCoords[0]] = -6;
        $dataGrid[$rockMoveToCoords[1]][$rockMoveToCoords[0]] = -2;
    }
    return $dataGrid;
}

function checkMate($posKing, $dataGrid)
{
    $possibleMoves = koenigMoveValid($posKing[0], $posKing[1], 0, 0, $dataGrid, false, true);

    if (empty($possibleMoves)) {
        if (!checkIfFigureCanSaveKing($posKing, $dataGrid)) {
            return true;
        }
    }
    return false;
}

function checkIfFigureCanSaveKing($coordsOfKing, $dataGrid)
{
    $color = getCurrentColor();
    $enemyPieces = getAllPiecesOfColor($color * -1, $dataGrid);
    $playerPieces = getAllPiecesOfColor($color, $dataGrid);
    $attackerPos = isUnderAttack($coordsOfKing, $dataGrid, $enemyPieces, true);
    $moveVector = [$coordsOfKing[0] - $attackerPos["x"], $coordsOfKing[1] - $attackerPos["y"]];
    ($moveVector[0] < 0) ? $moveVector[0] = -1 : $moveVector[0] = 1;
    ($moveVector[1] < 0) ? $moveVector[1] = -1 : $moveVector[1] = 1;
    $posX = $attackerPos["x"];
    $posY = $attackerPos["y"];

    $fieldsBetweenKingAndAttacker = getPossibleMovesWithVector($posX, $posY, $dataGrid, [$moveVector], false);

    array_pop($fieldsBetweenKingAndAttacker);

    foreach ($fieldsBetweenKingAndAttacker as $coordinate) {
        foreach ($playerPieces as $playerPiece) {
            if (validation($playerPiece["x"], $playerPiece["y"], $coordinate[0], $coordinate[1], $dataGrid)) {
                return true;
            }
        }
    }
    return false;
}

function checkForCheckAndCheckMate($dataGrid, $onlyTestting = false)
{
    $posKing = [];
    $color = getCurrentColor();
    $playerPieces = getAllPiecesOfColor($color, $dataGrid);
    foreach ($playerPieces as $playerPiece) {
        if ($playerPiece["fieldValue"] == 6 || $playerPiece["fieldValue"] == -6) {
            $posKing = [$playerPiece["x"], $playerPiece["y"]];
        }
    }

    if (check($posKing, $dataGrid, $color, $onlyTestting)) {
        if (checkMate($posKing, $dataGrid)) {
            return true;
        }
    }
    return false;
}

function checkIfRequestedMoveIsInAllowedMoves($requestedMove, $allowedMoves)
{
    if (in_array($requestedMove, $allowedMoves)) {
        return true;
    } else {
        return false;
    }
}

function getFieldValue($x, $y, $dataGrid)
{
    $fieldValue = $dataGrid[$y][$x];
    return $fieldValue;
}

function getRequestMove($x, $y)
{
    $requestMove = [$x, $y];
    return $requestMove;
}

function trackTurmMoves($coords){
    $coordsOfRocksAndKings = [[0, 7], [7, 7], [0, 0], [7, 0], [4, 7], [4, 0]];
    $figureThatMoves = "";
    $arrayToAsignFigure = [
        //------------Rocks-------------
        0 => "countedMovesWhiteTurmOne",
        1 => "countedMovesWhiteTurmTwo",
        2 => "countedMovesBlackTurmOne",
        3 => "countedMovesBlackTurmTwo",
        //------------Kings-------------
        4 => "countedMovesWhiteKing",
        5 => "countedMovesBlackKing",
    ];
    foreach($coordsOfRocksAndKings as $key => $coordsOfFigure){
        if($coordsOfFigure == $coords){
             $figureThatMoves = $arrayToAsignFigure[$key];
             $jsonData = getCountedMoves();
             $jsonData[$figureThatMoves] = 1;
             file_put_contents("./dataForGame/countedMovesForRochade.json", json_encode($jsonData));
        }
    }
}

//---------------------------------------------------------------

function getMoveVectorsForFigure($figure, $throw=false){
    $allFigureMoveVectors = [
        -1 => [[0, 1], [0, 2]], // BlackRock
        1 => [[0, -1], [0, -2]], // WhiteRock
        2 => [[0, 1], [0, -1], [1, 0], [-1, 0]], // Turm
        3 => [[1, 2], [-1, 2], [1, -2], [-1, -2], [-2, 1], [-2, -1], [2, 1], [2, -1]], // Reiter
        4 => [[1, 1], [1, -1], [-1, -1], [-1, 1]], // Bishop
        5 => [[1, 1], [1, -1], [-1, -1], [-1, 1], [0, 1], [0, -1], [1, 0], [-1, 0]], // queen
        6 => [[0, 1], [0, -1], [1, 0], [-1, 0], [1, 1], [1, -1], [-1, -1], [-1, 1]] //king
    ];
    $throwVectorsForRock = [
        -1 => [[1, 1], [-1, 1]],
        1 => [[1, -1], [-1, -1]],
    ];
    if($throw){
        return $throwVectorsForRock[$figure];
    } 
    return $allFigureMoveVectors[$figure];
}

function stopFiguresFromMovingIfKingIsCheck($coords, $dataGrid){
    if(check())
}

function validation($oldX, $oldY, $newX, $newY, $dataGrid, $simulateMove = false)
{

    if ($newX > 7 || $newX < 0 || $newY > 7 || $newY < 0) {
        return false;
    }

    $absoluteFieldValue = abs($dataGrid[$oldY][$oldX]);
    $onlyOneTime = false;

    if(!$absoluteFieldValue == 6){
        $coordsForFunc = [
            "x" => $oldX,
            "y" => $oldY,
        ];
        stopFiguresFromMovingIfKingIsCheck([$oldX, $oldY], $dataGrid);
    }


    switch ($absoluteFieldValue) {
        case 1:
            return validateBauernMoves([$oldX, $oldY], [$newX, $newY], $dataGrid, $simulateMove);
        case 6:
            $koenigMoveTrueOrFalse = koenigMoveValid($oldX, $oldY, $newX, $newY, $dataGrid, $simulateMove);
            if($koenigMoveTrueOrFalse && !$simulateMove){
                trackTurmMoves([$oldX, $oldY]);
            }
            return $koenigMoveTrueOrFalse;
        case 2:
            $turmMoveTrueOrFalse = moveValid($oldX, $oldY, $newX, $newY, $dataGrid, $onlyOneTime, getMoveVectorsForFigure($absoluteFieldValue));
            if($turmMoveTrueOrFalse && !$simulateMove){
                trackTurmMoves([$oldX, $oldY]);
            }
            return $turmMoveTrueOrFalse;
        case 3:
            $onlyOneTime = true;
            return moveValid($oldX, $oldY, $newX, $newY, $dataGrid, $onlyOneTime, getMoveVectorsForFigure($absoluteFieldValue));
        default:
            return moveValid($oldX, $oldY, $newX, $newY, $dataGrid, $onlyOneTime, getMoveVectorsForFigure($absoluteFieldValue));
    }
}

function bauerMoveCalculation($moveVectors, $oldCoords, $dataGrid)
{
    $calculatedMoves = [];
    foreach ($moveVectors as $value) {
        $calculatedMoves = array_merge($calculatedMoves, calculateMoves($oldCoords[0], $oldCoords[1], $value, $dataGrid, true));
    }
    return $calculatedMoves;
}

function bauernThowCalculation($throwMoveVectors, $oldCoords, $dataGrid)
{
    $calculatedThrows = [];
    foreach ($throwMoveVectors as $value) {
        $calculatedThrows = array_merge($calculatedThrows, calculateMoves($oldCoords[0], $oldCoords[1], $value, $dataGrid, true));
    }
    return $calculatedThrows;
}

function filteringMovesForWhite($oldCoords, $possibleMoves, $dataGrid)
{
    foreach ($possibleMoves as $key => $value) {
        if ($oldCoords[0] != $value[0] && $oldCoords[1] != $value[1]) {
            if (getFieldValue($value[0], $value[1], $dataGrid) >= 0) {
                unset($possibleMoves[$key]);
            }
        }
        if (abs($oldCoords[1] - $value[1]) == 2 && $oldCoords[1] != 6) {
            unset($possibleMoves[$key]);
        }
    }
    return $possibleMoves;
}

function filteringMovesForBlack($oldCoords, $possibleMoves, $dataGrid)
{
    foreach ($possibleMoves as $key => $value) {
        if ($oldCoords[0] != $value[0] && $oldCoords[1] != $value[1]) {
            if (getFieldValue($value[0], $value[1], $dataGrid) <= 0) {
                unset($possibleMoves[$key]);
            }
        }
        if (abs($oldCoords[1] - $value[1]) == 2 && $oldCoords[1] != 1) {
            unset($possibleMoves[$key]);
        }
    }
    return $possibleMoves;
}

function validateBauernMoves($oldCoords, $newCoords, $dataGrid, $simulateMove)
{
    $fieldValue = $dataGrid[$oldCoords[1]][$oldCoords[0]];
    $color = getCurrentColor();
    $possibleMoves = [];
    if ($simulateMove) {
        $possibleMoves = array_merge($possibleMoves, bauernThowCalculation(getMoveVectorsForFigure($fieldValue, true), $oldCoords, $dataGrid));
        if (in_array($newCoords, $possibleMoves)) {
            return true;
        }
        return false;
    }
    $possibleMoves = array_merge($possibleMoves, bauerMoveCalculation(getMoveVectorsForFigure($fieldValue), $oldCoords, $dataGrid));
    $possibleMoves = array_merge($possibleMoves, bauernThowCalculation(getMoveVectorsForFigure($fieldValue, true), $oldCoords, $dataGrid));

    //--------------------------------special validation due to the color--------------------------------
    switch ($color) {
        case 1:
            $possibleMoves = filteringMovesForWhite($oldCoords, $possibleMoves, $dataGrid);
            break;
        case -1:
            $possibleMoves = filteringMovesForBlack($oldCoords, $possibleMoves, $dataGrid);
            break;
    }
    //---------------------------------------------------------------------------------------------------

    //--------------------------------basic validation--------------------------------
    foreach ($possibleMoves as $key => $value) {
        if ($oldCoords[0] == $value[0] && $oldCoords[1] != $value[1]) {
            if (getFieldValue($value[0], $value[1], $dataGrid) != 0) {
                unset($possibleMoves[$key]);
            }
        }
    }
    //-------------------------------------------------------------------------------

    return checkIfRequestedMoveIsInAllowedMoves($newCoords, $possibleMoves);
}

function koenigMoveValid($oldX, $oldY, $newX, $newY, $dataGrid, $simulateMove, $onlyGiveBackPossibleMoves = false)
{
    $moveVector = getMoveVectorsForFigure(6);
    $possibleMoves = getPossibleMovesWithVector($oldX, $oldY, $dataGrid, $moveVector, true);
    $enemyPieces = getAllPiecesOfColor(getCurrentColor() * -1, $dataGrid);
    if ($onlyGiveBackPossibleMoves) {
        foreach ($possibleMoves as $i => $possibleMove) {
            if (isUnderAttack($possibleMove, $dataGrid, $enemyPieces)) {
                unset($possibleMoves[$i]);
            }
        }
        return $possibleMoves;
    }
    if (!$simulateMove) {
        if(checkForRochade([$oldX, $oldY], [$newX, $newY], $dataGrid)){
            $possibilityAndDataOfRochade = rochade([$oldX, $oldY], $dataGrid, [$newX, $newY]);
            return $possibilityAndDataOfRochade["possibility"];
        }
        foreach ($possibleMoves as $i => $possibleMove) {
            if (isUnderAttack($possibleMove, $dataGrid, $enemyPieces)) {
                unset($possibleMoves[$i]);
            }
        }
    }
    $requestMoves = getRequestMove($newX, $newY);
    return checkIfRequestedMoveIsInAllowedMoves($requestMoves, $possibleMoves);
}

function moveValid($oldX, $oldY, $newX, $newY, $dataGrid, $onlyOneTime, $moveVectors)
{
    return checkIfRequestedMoveIsInAllowedMoves(getRequestMove($newX, $newY), getPossibleMovesWithVector($oldX, $oldY, $dataGrid, $moveVectors, $onlyOneTime));
}

function isUnderAttack($targets, $grid, $enemyPieces, $saveKing = false)
{
    $targetX = $targets[0];
    $targetY = $targets[1];
    foreach ($enemyPieces as $enemyPiece) {
        $pieceX = $enemyPiece["x"];
        $pieceY = $enemyPiece["y"];
        if (validation($pieceX, $pieceY, $targetX, $targetY, $grid, true)) {
            if ($saveKing) {
                return $enemyPiece;
            }
            return true;
        }
    }
    return false;
}

function getAllPiecesOfColor($color, $dataGrid)
{
    $pieces = [];
    for ($i = 0; $i < count($dataGrid); $i++) {
        for ($j = 0; $j < count($dataGrid[0]); $j++) {
            $fieldValue = $dataGrid[$i][$j];
            if ($color == 1) {
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

function getPossibleMovesWithVector($oldX, $oldY, $dataGrid, $moveVectors, $oneTimeOnly = false)
{
    $possibleMoves = [];
    foreach ($moveVectors as $key) {
        $possibleMoves = array_merge($possibleMoves, calculateMoves($oldX, $oldY, $key, $dataGrid, $oneTimeOnly));
    }
    return $possibleMoves;
}

function calculateMoves($x, $y, $directionVector, $dataGrid, $oneTimeOnly = false)
{
    $posX = $x + $directionVector[0];
    $posY = $y + $directionVector[1];
    $possibleMoves = [];
    $figureValue = $dataGrid[$y][$x];
    while ($posX >= 0 && $posX < 8 && $posY >= 0 && $posY < 8) {
        if (checkIfFigurIsInWay($posX, $posY, $dataGrid, $figureValue) == true) {
            break;
        } else {
            $possibleMoves[] = [$posX, $posY];
            $posX += $directionVector[0];
            $posY += $directionVector[1];
        }
        if ($oneTimeOnly) {
            break;
        }
    }
    return $possibleMoves;
}

//---------------------------------------------------------------

function fancy_dump($var)
{
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

function array_dump($array)
{
    echo '<ul>';
    foreach ($array as $key => $value) {
        echo '<li>';
        if (is_array($value)) {
            echo array_dump($value);
        } else {
            echo $key . " => " . $value;
        }
        echo '</li>';
    }
    echo '</ul>';
}

//---------------------------------------------------------------

function createDataGridForJSON()
{
    $dataGrid = [
        [-2, -3, -4, -5, -6, -4, -3, -2],
        [-1, -1, -1, -1, -1, -1, -1, -1],
        [0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0],
        [1, 1, 1, 1, 1, 1, 1, 1],
        [2, 3, 4, 5, 6, 4, 3, 2],
    ];
    $dataGrid = [
        [-2, 0, 0, 0, -6, 0, 0, -2],
        [0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0],
        [2, 0, -4, 0, 6, 0, 0, 2],
    ];
    file_put_contents("./dataForGame/dataGrid.json", json_encode($dataGrid));
    return $dataGrid;
}

function getCurrentColor()
{
    return file_get_contents("./dataForGame/currentColor.json");
}

function isCoordinateInPieces($pieceX, $pieceY, $piecesOfColor)
{
    $wantToMovePiece = ["x" => $pieceX, "y" => $pieceY];
    if (in_array($wantToMovePiece, $piecesOfColor)) {
        return true;
    }
    return false;
}