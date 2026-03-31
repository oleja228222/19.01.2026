<?php
require_once 'config.php';
$auth = new Auth();
$auth->logout();
redirect(SITE_URL);