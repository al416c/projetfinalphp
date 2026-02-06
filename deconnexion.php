<?php
require_once 'config/init.php';

$_SESSION = [];
session_destroy();

redirect('index.php');
