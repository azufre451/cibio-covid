<?

include('includes/app_include.php');

include('includes/PHPTAL-1.3.0/PHPTAL.php');


$template = new PHPTAL('TEMPLATES/search_barcode_interactive.html');
$template->appVersion = $appVersion;
 try 
	  {
	  	echo $template->execute();
	  }
	  	catch (Exception $e){
			echo $e;
	  }
?>