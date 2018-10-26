<?php

function myFunc( ) {}
function myFunc( $blah ) {}
function myFunc($blah , $blah2, $blah3 ) {}
function myFunc($blah,$blah2,$blah3) {}
function myFunc($blah,   $blah2,  $blah3) {}
function myFunc($blah='hello') {}
function myFunc(  $blah='hello'   , $blah2=   'hi',  $blah3   = 'yo' ) {}
function myFunc(PHP_CodeSniffer   $object, array  $array=array(), $blah3='yo') {}
function myFunc( PHP_CodeSniffer $object,  array $array=array(), $blah3='yo') {}
function myFunc(PHP_CodeSniffer $object, array $array=array(), $blah3='yo') {}
function myFunc( array  &$one,  array  &$two) {}
function myFunc( &$blah ) {}

function myFunc() {}
function myFunc($blah) {}
function myFunc($blah, $blah2, $blah3) {}
function myFunc($blah = 'hello') {}
function myFunc(&$blah) {}
function myFunc(PHP_CodeSniffer $object, array $array = array(), $blah3 = 'yo') {}
