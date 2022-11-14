<?php

function saveDataGrid($contentForJSON){
    $finalData = json_encode($contentForJSON);  
    file_put_contents("dataGrid.json", $finalData);
}

function writeToDataGrid($x, $y, $figure){
    $dataGrid[$x][$y] = $figure;
    saveDataGrid($dataGrid);
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

function placeFigures($arrayPos, $figurType){
    $htmlElement = '<div class="figureWhite">'. $figurType[$arrayPos] .'</div>';
    return $htmlElement;
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
    //echo '<script src="./index.js"></script>';
    //echo '<script>addEventlistenertoObjects();</script>';
}

function moveFigure($oldX, $oldY, $newX, $newY, $dataGrid){ 
    $figureValue = $dataGrid[$oldY][$oldX];
    $dataGrid[$newY][$newX] = $figureValue;
    $dataGrid[$oldY][$oldX] = 0;
    return $dataGrid;
}

function checkIfFigurIsInWay($posX, $posY, $dataGrid){
    $fieldValue = $dataGrid[$posY][$posX];
    if($fieldValue != 0){
        return true;
    } else {
        return false;
    }
}

function checkIfRequestedMoveIsInAllowedMoves($requestedMove, $allowedMoves){
    if(in_array($requestedMove, $allowedMoves)){
        return true;
    } else {
        return false;
    }
}
//---------------------------------------------------------------

function validation($oldX, $oldY, $newX, $newY, $dataGrid){
    if($newX > 7 || $newX < 0 || $newY > 7 || $newY < 0){
        return false;
    } else {
        $fieldValue = $dataGrid[$oldY][$oldX];
        $moveToFieldValue = $dataGrid[$newY][$newX];
        if($moveToFieldValue > 0 || $moveToFieldValue < 0){
            return false;
        } else {
            switch($fieldValue){
                case 1:
                case -1:
                    if(bauerMoveValid($oldY, $newY, $fieldValue) == true){
                        return true;
                    } else {return false;}
                break;
                case 2:
                case -2:
                    if(turmMoveValid($oldX, $oldY, $newX, $newY, $dataGrid) == true){
                        return true;
                    } else {return false;}
                break;
                case 3:
                case -3:
                    if(reiterMoveValid($oldX, $oldY, $newX, $newY) == true){
                        return true;
                    } else {return false;}
                break;
                case 4:
                case -4:
                    if(bishopMoveValid($oldX, $oldY, $newX, $newY, $dataGrid) == true){
                        return true;
                    } else {return false;}
                break;
                case 5:
                case -5:
                    if(queenMoveValid($oldX, $oldY, $newX, $newY, $dataGrid) == true){
                        return true;
                    } else {return false;}
            }
        }
    }
}

function bauerMoveValid($oldY, $newY, $fieldValue){
    if($fieldValue > 0){
        if($newY - $oldY == -1){
            return true;
        }elseif($newY - $oldY == -2 && $oldY == 6){
            return true;
        }
    } elseif($fieldValue < 0){
        if($newY - $oldY == 1){
            return true;
        }elseif($newY - $oldY == 2 && $oldY == 1){
            return true;
        }
    }
    return false;
}

function getValidCoordinatesForTurm($kindOfMove, $x, $y, $dataGrid){
    switch($kindOfMove){
        case 1:
            $moves = [];
            for($i = 0; $i < 2; $i++){
                switch($i){
                    case 0:
                        $directionX = -1;
                        $posX = $x + $directionX;
                        $posY = $y;
                    
                        while($posX >= 0 && $posX < 8) {
                            if(checkIfFigurIsInWay($posX, $posY, $dataGrid) == true){
                                break;
                            } else {
                                array_push($moves,[$posX, $posY]);
                                $posX += $directionX;
                            }
                        }
                    
                        return $moves;
                    break;
                    case 1:
                        $directionX = 1;
                        $posX = $x + $directionX;
                        $posY = $y;
                    
                        while($posX >= 0 && $posX < 8) {
                            if(checkIfFigurIsInWay($posX, $posY, $dataGrid) == true){
                                break;
                            } else {
                                array_push($moves,[$posX, $posY]);
                                $posX += $directionX;
                            }
                        }
                    
                        return $moves;
                    break;
                }
            }
        break;
        case 2:
            $moves = [];
            for($i = 0; $i < 2; $i++){
                switch($i){
                    case 0:
                        $directionY = -1;
                        $posX = $x;
                        $posY = $y + $directionY;
                    
                    
                        while($posY >= 0 && $posY < 8) {
                            if(checkIfFigurIsInWay($posX, $posY, $dataGrid) == true){
                                break;
                            } else {
                                array_push($moves, [$posX, $posY]);
                                $posY += $directionY;
                            }
                        }
                    break;
                    case 1:
                        $directionY = 1;
                        $posX = $x;
                        $posY = $y + $directionY;
                    
                    
                        while($posY >= 0 && $posY < 8) {
                            if(checkIfFigurIsInWay($posX, $posY, $dataGrid) == true){
                                break;
                            } else {
                                array_push($moves, [$posX, $posY]);
                                $posY += $directionY;
                            }
                        }
                    break;
                }
            }
            return $moves;
        break;
    }
}

function turmMoveValid($oldX, $oldY, $newX, $newY, $dataGrid){
    if($oldX == $newX && $oldY != $newY){
        $moves = getValidCoordinatesForTurm(2, $oldX, $oldY, $dataGrid);
        $requestedMove = [$newX, $newY];
        return checkIfRequestedMoveIsInAllowedMoves($requestedMove, $moves);
    } elseif($oldX != $newX && $oldY == $newY){
        $moves = getValidCoordinatesForTurm(1, $oldX, $oldY, $dataGrid);
        $requestedMove = [$newX, $newY];
        return checkIfRequestedMoveIsInAllowedMoves($requestedMove, $moves);
    } else {
        return false;
    }
}

function reiterMoveValid($oldX, $oldY, $newX, $newY){
    if($newY - $oldY == 2 || $newY - $oldY == -2 || $newX - $oldX == 2 || $newX - $oldX == -2){
        if($newY - $oldY == 1 || $newY - $oldY == -1 || $newX - $oldX == 1 || $newX - $oldX == -1){
            return true;
        }
    } elseif ($newY - $oldY == 1 || $newY - $oldY == -1 || $newX - $oldX == 1 || $newX - $oldX == -1) {
        if($newY - $oldY == 2 || $newY - $oldY == -2 || $newX - $oldX == 2 || $newX - $oldX == -2){
            return true;
        }
    }
    return false;
}

function getValidCoordinatesForQueenShort($x, $y, $dataGrid) {
    $moves = getValidCoordinatesForBishop($x, $y, 1, -1, $dataGrid);
    $moves = array_merge($moves, getValidCoordinatesForBishop($x, $y, -1, -1, $dataGrid));
    $moves = array_merge($moves, getValidCoordinatesForBishop($x, $y, -1, 1, $dataGrid));
    $moves = array_merge($moves, getValidCoordinatesForBishop($x, $y, 1, 1, $dataGrid));
    $moves = array_merge($moves, getValidCoordinatesForBishop($x, $y, 0, -1, $dataGrid));
    $moves = array_merge($moves, getValidCoordinatesForBishop($x, $y, 0, 1, $dataGrid));
    $moves = array_merge($moves, getValidCoordinatesForBishop($x, $y, 1, 0, $dataGrid));
    $moves = array_merge($moves, getValidCoordinatesForBishop($x, $y, -1, 0, $dataGrid));
}

function getValidCoordinatesForBishop($x, $y, $i, $j, $dataGrid) {
    $posX = $x + $i;
    $posY = $y + $j;

    $moves = [];

    while($posX >= 0 && $posX < 8 && $posY >= 0 && $posY < 8) {
        if(checkIfFigurIsInWay($posX, $posY, $dataGrid) == true){
            break;
        } else {
            $moves[] = [$posX, $posY];
            $posX += $i;
            $posY += $j;
        }
    }    

    return $moves;
}

function bishopMoveValid($oldX, $oldY, $newX, $newY, $dataGrid){
    $moves = getValidCoordinatesForBishop($oldX, $oldY, 1, 1, $dataGrid);
    $moves = array_merge($moves, getValidCoordinatesForBishop($oldX, $oldY, 1, -1, $dataGrid));
    $moves = array_merge($moves, getValidCoordinatesForBishop($oldX, $oldY, -1, 1, $dataGrid));
    $moves = array_merge($moves, getValidCoordinatesForBishop($oldX, $oldY, -1, -1, $dataGrid));
    $requestedMove = [$newX, $newY];
    return checkIfRequestedMoveIsInAllowedMoves($requestedMove, $moves);
}

function getValidCoordinatesForQueen($kindOfMove, $x, $y, $dataGrid){
    switch($kindOfMove){
        case 1:
            $moves = [];
            for($i = 0; $i < 2; $i++){
                switch($i){
                    case 0:
                        $directionY = 1;
                        $posX = $x;
                        $posY = $y + $directionY;
                    
                    
                        while($posY >= 0 && $posY < 8) {
                            if(checkIfFigurIsInWay($posX, $posY, $dataGrid) == true){
                                break;
                            } else {
                                array_push($moves, [$posX, $posY]);
                                $posY += $directionY;
                            }
                        }
                    break;
                    case 1:
                        $directionY = -1;
                        $posX = $x;
                        $posY = $y + $directionY;
                    
                    
                        while($posY >= 0 && $posY < 8) {
                            if(checkIfFigurIsInWay($posX, $posY, $dataGrid) == true){
                                break;
                            } else {
                                array_push($moves, [$posX, $posY]);
                                $posY += $directionY;
                            }
                        }
                    break;
                }
            }
            return $moves;
            break;
        case 2:
            $moves = [];
            for($i = 0; $i < 2; $i++){
                switch($i){
                    case 0:
                        $directionX = 1;
                        $posX = $x + $directionX;
                        $posY = $y;
                    
                    
                        while($posX >= 0 && $posX < 8) {
                            if(checkIfFigurIsInWay($posX, $posY, $dataGrid) == true){
                                break;
                            } else {
                                array_push($moves, [$posX, $posY]);
                                $posX += $directionX;
                            }
                        }
                    break;
                    case 1:
                        $directionX = -1;
                        $posX = $x + $directionX;
                        $posY = $y;
                    
                    
                        while($posX >= 0 && $posX < 8) {
                            if(checkIfFigurIsInWay($posX, $posY, $dataGrid) == true){
                                break;
                            } else {
                                array_push($moves, [$posX, $posY]);
                                $posX += $directionX;
                            }
                        }
                    break;
                }
            }
            return $moves;
            break;
        case 3:
            $moves = [];
            for($i = 0; $i < 4; $i++){
                switch($i){
                    case 0:
                        $directionX = 1;
                        $directionY = 1;
                        $posX = $x + $directionX;
                        $posY = $y + $directionY;
                    
                    
                        while($posX >= 0 && $posX < 8 && $posY >= 0 && $posY < 8) {
                            if(checkIfFigurIsInWay($posX, $posY, $dataGrid) == true){
                                break;
                            } else {
                                array_push($moves, [$posX, $posY]);
                                $posX += $directionX;
                                $posY += $directionY;
                            }

                        }
                    break;
                    case 1:
                        $directionX = 1;
                        $directionY = -1;
                        $posX = $x + $directionX;
                        $posY = $y + $directionY;
                    
                    
                        while($posX >= 0 && $posX < 8 && $posY >= 0 && $posY < 8) {
                            if(checkIfFigurIsInWay($posX, $posY, $dataGrid) == true){
                                break;
                            } else {
                                array_push($moves, [$posX, $posY]);
                                $posX += $directionX;
                                $posY += $directionY;
                            }
                        }
                    break;
                    case 2:
                        $directionX = -1;
                        $directionY = 1;
                        $posX = $x + $directionX;
                        $posY = $y + $directionY;
                    
                    
                        while($posX >= 0 && $posX < 8 && $posY >= 0 && $posY < 8) {
                            if(checkIfFigurIsInWay($posX, $posY, $dataGrid) == true){
                                break;
                            } else {
                                array_push($moves, [$posX, $posY]);
                                $posX += $directionX;
                                $posY += $directionY;
                            }
                        }
                    break;
                    case 3:
                        $directionX = -1;
                        $directionY = -1;
                        $posX = $x + $directionX;
                        $posY = $y + $directionY;
                    
                    
                        while($posX >= 0 && $posX < 8 && $posY >= 0 && $posY < 8) {
                            if(checkIfFigurIsInWay($posX, $posY, $dataGrid) == true){
                                break;
                            } else {
                                array_push($moves, [$posX, $posY]);
                                $posX += $directionX;
                                $posY += $directionY;
                            }
                        }
                    break;
                }
            }
            return $moves;
            break;
        default:
        return false;
    }
}

function checkKindOfMove($oldX, $oldY, $newX, $newY){
    if($newX == $oldX && $newY != $oldY){
        return 1;
    } elseif($newX != $oldX && $newY == $oldY){
        return 2;
    } elseif($newX != $oldX && $newY != $oldY){
        return 3;
    } else {
        return 4;
    }
}

function queenMoveValid($oldX, $oldY, $newX, $newY, $dataGrid){
    $moves = getValidCoordinatesForQueen(checkKindOfMove($oldX, $oldY, $newX, $newY), $oldX, $oldY, $dataGrid);
    $requestedMove = [$newX, $newY];
    return checkIfRequestedMoveIsInAllowedMoves($requestedMove, $moves);
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
    file_put_contents("dataGrid.json", json_encode($dataGrid));
    return $dataGrid;
}

//---------------------------------------------------------------

//$whiteFigures = ["U+2656", "U+2658", "U+2657", "U+2655", "U+2654", "U+2657", "U+2658", "U+2656"];
if(file_exists("dataGrid.json")){
    $dataGrid = json_decode(file_get_contents("dataGrid.json"), true);
} else {
    $dataGrid = createDataGridForJSON();
}

if(isset($_POST["newGame"])){
   $dataGrid = createDataGridForJSON();
}

if(isset($_POST["submit"])){
    $inputs = [(int) $_POST["oldx"], (int) $_POST["oldy"], (int) $_POST["newx"], (int) $_POST["newy"]];
    if(validation($inputs[0], $inputs[1], $inputs[2], $inputs[3], $dataGrid) == true){
        $dataGrid = moveFigure($inputs[0], $inputs[1], $inputs[2], $inputs[3], $dataGrid);
        saveDataGrid($dataGrid);
    }
}

/*

        [-2,-3,-4,-5,-6,-4,-3,-2],
        [-1,-1,-1,-1,-1,-1,-1,-1],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,0,0],
        [4,0,0,0,4,0,0,0],
        [1,4,1,4,1,1,1,1],
        [2,3,4,5,6,4,3,2],



*/

//$dataGrid = moveFigure(3, 6, 3, 4, $dataGrid);

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
        </style>
    </head>
    <body>
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