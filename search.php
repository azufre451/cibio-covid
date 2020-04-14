<?php


include('includes/app_include.php');

include('includes/PHPTAL-1.3.0/PHPTAL.php');


if (isSet($_GET['barcode']) || isSet($_POST['barcode']))
{

	if (isSet($_POST['barcode']))
	{
		//echo "A";
		$bcdLis = array();
		$bcd = explode(',',addslashes(trim($_POST['barcode'])));

		foreach($bcd as $elem)
		{
			//echo $elem;
			if ($elem != ''){
				$bcdLis[] = $elem;
			}
		}

		$bcdList = implode(',',$bcdLis);
	}

	elseif (isSet($_GET['barcode']))
	{
		$bcdList = addslashes($_GET['barcode']);
	}


	$res = mysql_query("SELECT * FROM samples WHERE barcode IN ('$bcdList') ");
	$samples = array();
	while ($ras = mysql_fetch_assoc($res))
	{
		$samples[$ras['barcode']] = $ras;
	}

	$res = mysql_query("SELECT * FROM estrazioni WHERE barcode IN ('$bcdList') ");
	$batches = array();
	while ($ras = mysql_fetch_assoc($res))
	{
		$batches[] = $ras;
	}
	                                  

	$res = mysql_query("SELECT * FROM pcr_plates WHERE barcode IN ('$bcdList') ORDER BY data_pcr ASC");
	$PCRs = array();
	while ($ras = mysql_fetch_assoc($res))
	{
		$PCRs[] = $ras;
	}

	$template = new PHPTAL('TEMPLATES/search_results.html');
	$template->searchKey = 'Barcode :: '. $bcdList;

	$template->samples = $samples;
	$template->batches = $batches;
	$template->PCRs = $PCRs;
	//print_r($PCRs);
}


elseif (isSet($_GET['plate']))
{
	$plate = $_GET['plate'];

	$res = mysql_query("SELECT * FROM samples WHERE barcode IN (SELECT barcode FROM pcr_plates WHERE plate = '$plate') ");
	$samples = array();
	while ($ras = mysql_fetch_assoc($res))
	{
		$samples[] = $ras;
	}

	$res = mysql_query("SELECT * FROM estrazioni WHERE  barcode IN (SELECT barcode FROM pcr_plates WHERE plate = '$plate') ");
	$batches = array();
	while ($ras = mysql_fetch_assoc($res))
	{
		$batches[] = $ras;
	}
	                                  

	$res = mysql_query("SELECT * FROM pcr_plates WHERE plate = '$plate' ORDER BY barcode ASC");
	$PCRs = array();
	while ($ras = mysql_fetch_assoc($res))
	{
		$PCRs[] = $ras;
	}

	$template = new PHPTAL('TEMPLATES/search_results.html');
	$template->searchKey = 'Plate di PCR :: '. $plate;

	$template->samples = $samples;
	$template->batches = $batches;
	$template->PCRs = $PCRs;

}


elseif (isSet($_POST['date']))
{
	$date = $_POST['date']; 
	$PCRs = array();                      

	$res = mysql_query("SELECT * FROM pcr_plates WHERE data_pcr = '$date' ORDER BY plate ASC, barcode ASC");
	
	while ($ras = mysql_fetch_assoc($res))
	{
		$ras['extractions'] = array();
		$PCRs[$ras['barcode']] = $ras;
	}

	$res = mysql_query("SELECT * FROM estrazioni WHERE barcode IN (SELECT barcode FROM pcr_plates WHERE data_pcr = '$date') ");
	while ($ras = mysql_fetch_assoc($res))
	{
		$PCRs[$ras['barcode']]['extractions'][] = $ras;
	}


	 
	$template = new PHPTAL('TEMPLATES/search_results_pcr.html');
	$template->searchKey = 'Data di PCR :: '. $date;
	$template->PCRs = $PCRs;

}




	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}

?>