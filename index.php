<?php
include('includes/app_include.php');

include('includes/PHPTAL-1.3.0/PHPTAL.php');


$template = new PHPTAL('TEMPLATES/index.htm');
$template->appVersion = $appVersion;
	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
	
	

?>