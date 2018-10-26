<?php
$noGood = empty ($thing);
$fail = include ('./inline_if_pass.php');
$fail = include_once ('./inline_if_pass.php');
$fail = require ('./inline_if_pass.php');
$fail = require_once ('./inline_if_pass.php');
$o = $obj->something ('testing');
fail_whale ();
