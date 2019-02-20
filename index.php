<?php
include 'auto.php';
$module = filter_input(INPUT_GET, 'module');
$page = filter_input(INPUT_GET, 'page');
$module = $module ? $module : 'index';
$page = $page ? $page : 'index';

ini_set("display_errors", "On");
error_reporting(E_ALL);   
define("DEFAULT_PAGE", realpath(__DIR__."/app/handler/index/index.php"));

function handurl($module,$page){
	$path = realpath(__DIR__. "/app/handler/".$module."/".$page.".php");
	if(file_exists($path)){
		return $path;
	}else{
		return realpath(__DIR__. "/app/handler/index/index.php");
	}
}



function outputjson($json, $header = NULL, $exit = TRUE) {
    $data = is_string($json) ? $json : json_encode($json);
    if (is_array($header)) {
        foreach ($header as $h) {
            header($h);
        }
    } elseif (is_string($header)) {
        header($header);
    }
    header("Content-Type: application/json; charset=UTF-8");
    echo $data;
    if ($exit) {
        exit;
    }
}



$path = handurl($module,$page);
//print_r($path);
include_once $path;
$spa = "\\app\\handler\\$module\\executeRequest";
$spa();
