<?php
require_once 'config/init.php';

// Destroy session and redirect
session_destroy();
redirect('index.php');
