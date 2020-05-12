<?php

session_start();


include('includes/app_include.php');
include('includes/PHPTAL-1.3.0/PHPTAL.php');


error_reporting(E_ALL);
ini_set('display_errors', 1);


function tempdir() {
	$tempfile=tempnam(sys_get_temp_dir(),'');
	if (file_exists($tempfile)) { unlink($tempfile); }
	mkdir($tempfile);
	if (is_dir($tempfile)) { return $tempfile; }
}

function cmd_exec($cmd, &$stdout, &$stderr)
{
	$outfile = tempnam(".", "cmd");
	$errfile = tempnam(".", "cmd");
	$descriptorspec = array(
		0 => array("pipe", "r"),
		1 => array("file", $outfile, "w"),
		2 => array("file", $errfile, "w")
	);
	$proc = proc_open($cmd, $descriptorspec, $pipes);
   
	if (!is_resource($proc)) return 255;

	fclose($pipes[0]);	//Don't really want to give any input

	$exit = proc_close($proc);
	$stdout = file($outfile);
	$stderr = file($errfile);

	unlink($outfile);
	unlink($errfile);
	return $exit;
}

$additionalParams = '';
$stderr = NULL;
$stdout = NULL;

if( isSet ($_GET['dologin']))
{

	if($_POST['username'] == $OPT_refertUSR && $_POST['password'] == md5($OPT_refertPWD))
    {
		$_SESSION['username'] = 'dma'; 
    }

}
if( isSet( $_FILES["fileToUpload"]) )
{
    if(!isSet($_SESSION['username'])){
        exit;
    } ;

	$template = new PHPTAL('TEMPLATES/load_procedure.html');
	$tempFolder = tempdir();	 

	$uploadType=$_POST['uploadtype'];
	

	if(isSet($_POST['replaceData']) && $_POST['replaceData'] == 'replaceData')
	{
		$additionalParams.='--replace_data';
	}

	$target_file = str_replace(' ','_',$tempFolder .'/'. basename($_FILES["fileToUpload"]["name"]));

	if(isset($_POST["submit"]))
	{
		 if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
			

			$fbasename = basename($_FILES["fileToUpload"]["name"]);

		 	if($uploadType == 'analisi' && strpos ( $fbasename,'_analisi.xlsm') !== false ){
				#echo "U";


				cmd_exec("/var/www/html/anaconda3/bin/python backend/extr_data_analysis.py --data_folder $tempFolder $additionalParams", $stdout,$stderr);

				$template->stdout = implode('<br/>',$stdout);
				$template->stderr = implode('<br/>',$stderr);
				$template->loadedFile = $fbasename;
				$template->iplate = str_replace('_analisi.xlsm','',$fbasename);
			}
			elseif($uploadType == 'estrazioni' && strpos($fbasename,'.xlsm') !== false)
			{   
				cmd_exec("/var/www/html/anaconda3/bin/python backend/extr_estrazioni.py --extr_folder $tempFolder", $stdout, $stderr);

				$template->stdout = implode('<br/>',$stdout);
				$template->stderr = implode('<br/>',$stderr);
				$template->loadedFile = $fbasename;
			}
			elseif($uploadType == 'ministero' && strpos($fbasename,'.xlsx') !== false)
			{
				$retval = cmd_exec("/var/www/html/anaconda3/bin/python backend/ministero.py --minfile $target_file", $stdout, $stderr);
				
				if(!$retval){


				header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
				header('Cache-Control: no-store, no-cache, must-revalidate');
				header('Cache-Control: post-check=0, pre-check=0', false);
				header('Cache-Control: private');
				header('Pragma: no-cache');
				header("Content-Transfer-Encoding: binary");
				header("Content-type: application/vnd.ms-excel");

				$tit = "ministero_".str_replace('.xlsx','.csv',$fbasename);
				header("Content-disposition: attachment; filename=\"{$tit}\"");
				echo(implode('',$stdout));

				exit;

				}
				else{
					$template->stdout = '';
					$template->stderr = implode('<br/>',$stderr);
					$template->loadedFile = $fbasename;
				}
			}
			else{
				echo "Tipo di upload non concesso! Forse hai selezionato il tipo file sbagliato? ( $uploadType , $fbasename ".($uploadType == 'analisiBF').")";
				unlink($target_file);
				rmdir($tempFolder);
				exit;
			}




  		} else {echo "Errore nel caricamento del file! >> " . $_FILES["fileToUpload"]["error"]; exit;	}

		unlink($target_file);
		rmdir($tempFolder);   
	}


}
else
{
	$template = new PHPTAL('TEMPLATES/upload_results.html');
}

if(isSet($_SESSION['username'])){
    $template->login=true;
}

try{
	echo $template->execute();
}
catch (Exception $e){ echo $e;	 }

?>