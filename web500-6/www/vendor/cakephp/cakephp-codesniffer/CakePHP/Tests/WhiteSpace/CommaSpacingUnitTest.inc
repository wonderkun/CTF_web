<?php
echo implode('', array_map('chr', array_map('hexdec',array_filter(explode($delimiter, $string)))));
echo implode('', array_map('chr', array_map('hexdec' , array_filter(explode($delimiter, $string)))));
echo implode('', array_map('chr', array_map('hexdec', array_filter(explode($delimiter, $string)))));
list(,, $extension, $filename) = array_values(pathinfo($filename));
