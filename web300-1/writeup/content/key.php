<?php
function get_key() {
	return file_get_contents('/etc/key');
}
function get_indentify(){
    return file_get_contents('/etc/indentify');
}
?>