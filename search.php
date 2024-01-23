<?php
require_once 'php/utils.php';
require_once 'php/pons_api.php';

if(isset($_GET['dictionary'])) {
    $dictionary = $_GET['dictionary'];
} else {
    $dictionary = 'deen';
}

if(!isset($_GET['query'])) {
    error("A 'query' parameter must be set.", 400);
}

$query = $_GET['query'];

if($query == '') {
    error('The query must contain a word', 400);
}

if(isset($TYPO_DICT[$dictionary][$query])) {
    $query = $TYPO_DICT[$dictionary][$query];
}

$ponsAPIQuery = new PonsAPIQuery($query, $dictionary);
$result = $ponsAPIQuery->execute();

if($result->hasErrors()) {
    $errors = $result->getErrors();
    $error = array_pop($errors);
    error($error[0], $error[1], $query);
}

success($result, $query);
