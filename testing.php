<?php
include_once "functions.php";

function testColorPlayField(){
   if(colorPlayfield(0, 6) == 'cellBlack'){
      return "Das Feld ist Schwarz";
   }
   return "Das Feld ist nicht schwarz";
}

//-----------------Test 1: Check and Checkmate----------------
function testCheckFunction(){
   $onlyCheck = [
      [0, 0, 0, 0, 0, -1, 0, 0],
      [0, 0, 0, 0, -1, -1, 0, 0],
      [0, 0, 0, 1, 0, 0, 0, 0],
      [0, 0, 0, 0, 0, 3, 0, 0],
      [0, -6, 0, 1, 0, 0, 0, 0],
      [0, 0, 1, 0, 1, 0, 0, 0],
      [0, 0, 0, 0, 0, 0, 0, 2],
      [0, 0, 0, 0, 0, 0, 0, 0],
  ];
   if(!checkForCheckAndCheckMate($onlyCheck, true)){
      return true;
   }
   return false;
}

function testCheckmateFunction(){
   $checkmate = [
      [5, 5, 5, 0, 0, 1, 0, 0],
      [0, 0, 0, 0, 1, 1, 0, 0],
      [0, 0, 0, -1, 0, 0, 0, 0],
      [0, 0, 0, 0, 0, -3, 0, 0],
      [0, 0, 0, -1, 0, 0, 0, 0],
      [0, 0, 0, 0, -1, 0, 0, 0],
      [0, -6, 0, 0, 0, 0, 0, 2],
      [0, 0, 0, 0, 0, 0, 0, 0],
   ];
   if(checkForCheckAndCheckMate($checkmate, true)){
      return true;
   }
   return false;
}

//------------------------------------------------------------

//-----------------Test 2: getAllPiecesOfColor----------------

function testGetPieces(){
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
   $result = [
      "status" => true,
      "messages" => []
   ];
   $colors = [1, -1];
   $randNumber = rand(0, 15);
   foreach($colors as $color){
      $pieces = getAllPiecesOfColor($color, $dataGrid);
      foreach($pieces as $key => $piece){
         if($color == 1){
            if($piece["fieldValue"] < 0){
               $result["messages"][] =   "Error at " . $key;
               $result["status"] = false;
               break;
            }
         } else {
            if($piece["fieldValue"] > 0){
               $result["status"] = false;
               break;
            }
         }
      }
      if(sizeof($pieces) != 16){
         $result["status"] = false;
      }
   }
   return $result;
}
//------------------------------------------------------------

//-----------------Test 3: Test calculation of possible Moves----------------

function testMoveCalculation(){
   $moveVectors = [[0, 1], [0, -1], [1, 0], [-1, 0]];
   $dataGrid = [
      [0, 0, 0, 0, 0, 0, 0, 0],
      [0, 0, 0, 0, 0, 0, 0, 0],
      [0, 0, 0, 0, 0, 0, 0, 0],
      [0, 0, 0, 0, 0, 0, 0, 0],
      [0, 0, 0, 5, 0, 0, 0, 0],
      [0, 0, 0, 0, 0, 0, 0, 0],
      [0, 0, 0, 0, 0, 0, 0, 0],
      [0, 0, 0, 0, 0, 0, 0, 0],
  ];
   $possibleMoves = getPossibleMovesWithVector(3, 4, $dataGrid, $moveVectors, true);
   $expectedMoves = [[3, 5], [3, 3], [4, 4], [2, 4]];
   if(count($expectedMoves) > count($possibleMoves) || count($expectedMoves) < count($possibleMoves)){
      return false;
   }
   foreach($possibleMoves as $key => $value){
      if(!in_array($value, $expectedMoves)){
         return false;
      }
   }
   return true;
}

function printErrorMessages($arrayOfErrors){
   foreach($arrayOfErrors as $errorMessage){
      echo "\e[31m" . $errorMessage . "\e[0m" . PHP_EOL;
   }
}


function printOutTestresults($numberOfTest, $failedOrPassed = false){
   $sapiType = php_sapi_name();
   ($failedOrPassed) ? $failedOrPassed = "passed" : $failedOrPassed = "failed";
   if($sapiType == "cli"){
      $color = "";
      ($failedOrPassed == "passed") ? $color = "92" : $color = "31";
      echo "\e[". $color ."mTest" . " " . $numberOfTest ." ". $failedOrPassed . "\e[0m" . PHP_EOL;
   } else {
      echo "<style>
      .failedTest {
         color: red;
      }
      .passedTest{
         color: green;
      }
   </style>";
      $class = "";
      ($failedOrPassed == "passed") ? $class = "'passedTest'" : $class = "'failedTest'";
      echo "<p class=". $class . "> Test " . $numberOfTest . " " .  $failedOrPassed . "</p>";
   }
}

function startTestting(){
   if(testCheckFunction() && testCheckmateFunction()){
      printOutTestresults(1, true);
   } else {
      printOutTestresults(1);
   }
   $resultTestTwo = testGetPieces();
   if($resultTestTwo["status"]){
      printOutTestresults(2, true);
   } else {
      printOutTestresults(2);
   }
   if(testMoveCalculation()){
      printOutTestresults(3, true);
   } else {
      printOutTestresults(3);
   }
}

startTestting();