<?php
session_start();
session_destroy();
header('Location: /job-portal/index.php');
exit;
