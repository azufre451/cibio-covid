<?php

session_start();


include('includes/app_include.php');
include('includes/PHPTAL-1.3.0/PHPTAL.php');


error_reporting(E_ALL);
ini_set('display_errors', 1);


if( isSet ($_GET['dologin']))
{
	if($_POST['username'] == $OPT_refertUSR && $_POST['password'] == md5($OPT_refertPWD))
    {
		$_SESSION['username'] = 'dma'; 
    }
    header("Location:index.php");
}

else
	$template = new PHPTAL('TEMPLATES/login.html');



try{
	echo $template->execute();
}
catch (Exception $e){ echo $e;}

?>
