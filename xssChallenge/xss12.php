<?php
    # the request
    $ch = curl_init($_GET["url"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    # get the content type
    $mime = array("application/octet-stream","application/postscript","application/x-cdf","application/x-compressed","application/x-zip-compressed","audio/basic","audio/wav","audio/x-aiff","video/avi","video/mpeg","video/x-msvideo","image/png","image/jpeg","image/gif");
    if (in_array(curl_getinfo($ch, CURLINFO_CONTENT_TYPE), $mime)) {
    header("Content-Type:".curl_getinfo($ch, CURLINFO_CONTENT_TYPE));
    //header("X-Content-Type-Options: nosniff");
    echo curl_exec($ch);
    }
    # output
    // text/html; charset=ISO-8859-1
?>
