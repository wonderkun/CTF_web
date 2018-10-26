<?php
//Fail
$isActive = !!$value;
$isActive = (boolean)$value;
$count = (integer)$someNumber;
$isActive = (Bool)$value;
$count = (Int)$someNumber;

//Pass
$isActive = (bool)$value;
$count = (int)$someNumber;
