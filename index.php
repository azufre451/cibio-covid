<?php
session_start();

include('includes/app_include.php');
include('includes/PHPTAL-1.3.0/PHPTAL.php');

if( isSet ($_GET['dologin']))
{

	if($_POST['username'] == $OPT_refertUSR && $_POST['password'] == md5($OPT_refertPWD))
    {
		$_SESSION['username'] = 'dma'; 
    }

}

$template = new PHPTAL('TEMPLATES/index.htm');
$template->appVersion = $appVersion;
if(isSet($_SESSION['username'])) $template->loginOk = 1;


	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
	
	

?>