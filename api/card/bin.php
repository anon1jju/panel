<?php
require_once "fungsi.php";
header('Content-Type: application/json');
$bin = $_GET['bin'];

$randuser = random_user_agent();

print_r(check_bin($bin, $randuser));