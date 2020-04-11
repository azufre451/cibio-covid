<?php


include('includes/app_include.php');

include('includes/PHPTAL-1.3.0/PHPTAL.php');

$bcd = $_POST['barcode'];

$res = mysql_query("SELECT * FROM samples WHERE barcode = '$bcd' ");
$samples = array();
while ($ras = mysql_fetch_assoc($res))
{
	$samples[] = $ras;
}

$res = mysql_query("SELECT * FROM estrazioni WHERE barcode = '$bcd' ");
$batches = array();
while ($ras = mysql_fetch_assoc($res))
{
	$batches[] = $ras;
}
                                  

$res = mysql_query("SELECT * FROM pcr_plates WHERE barcode = '$bcd' ORDER BY data_pcr ASC");
$PCRs = array();
while ($ras = mysql_fetch_assoc($res))
{
	$PCRs[] = $ras;
}

$template = new PHPTAL('TEMPLATES/search_results.html');
$template->samples = $samples;
$template->batches = $batches;
$template->PCRs = $PCRs;

$template->barcode = $bcd;


	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}

?>