<?php


include('includes/app_include.php');

include('includes/PHPTAL-1.3.0/PHPTAL.php');

$searchType = 'bad';

if (isSet($_GET['barcode']) || isSet($_POST['barcode']))
{
	$searchType = 'barcode';


	if (isSet($_POST['barcode']))
	{
		//echo "A";
		$bcdLis = array();
		$bcdLisReadable=array();
		$bcd = explode(',',addslashes(trim($_POST['barcode'])));

		foreach($bcd as $elem)
		{
			//echo $elem;
			if ($elem != ''){
				$bcdLis[] = $elem;
				$bcdLisReadable[] = $elem;
				
				// ADD this to be extra sure
				$bcdLis[] = '0'.$elem;
				$bcdLis[] = $elem.'01';
				$bcdLis[] = '0'.$elem.'01';

				$bcdLis[] = substr($elem,1,strlen($elem));
				$bcdLis[] = substr($elem,1,strlen($elem)).'01';
				
				if (isSet($_GET['partialmatch']))
				{
					//barcode ending with 0x
					$bcdLis[] = substr($elem,0,8);
				}
			}
		}

		$bcdList = implode("','",$bcdLis);
		$bcdReadableList = implode(", ",$bcdLisReadable);
	}

	elseif (isSet($_GET['barcode']))
	{
		$bcdList = addslashes($_GET['barcode']);
	}

	//echo $bcdList;
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

		if($ras['isControl'] == 1)
			$htmlClass='row_ctrl';
		else if($ras['esito_pcr'] == 'POSITIVO')
			$htmlClass='row_pos';
		else if($ras['esito_pcr'] == 'NEGATIVO')
			$htmlClass='row_neg';
		else if($ras['esito_pcr'] == 'RIPETERE ESTRAZIONE')
			$htmlClass='row_rep_ext';
		else if($ras['esito_pcr'] == 'RIPETERE PCR')
			$htmlClass='row_rep_pcr';
		else if($ras['esito_pcr'] == 'RIPETERE TAMPONE')
			$htmlClass='row_rep_tamp';
		else if($ras['esito_pcr'] == 'ERRORE COMPILAZIONE')
			$htmlClass='row_error';
		$ras['htmlClass'] = $htmlClass;
		$PCRs[] = $ras;
	}

	$template = new PHPTAL('TEMPLATES/search_results.html');
	$template->searchKey = 'Barcode :: '. $bcdReadableList;

	$template->samples = $samples;
	$template->batches = $batches;
	$template->PCRs = $PCRs;
	//print_r($PCRs);
}


elseif (isSet($_GET['plate']))
{
	$searchType = 'plate';

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
	$wellLayout=array();

	foreach(range(0,7) as $k)
	{
		$wellLayout[chr($k+65)] = array();
		
		foreach(range(1,12) as $i)
			$wellLayout[chr($k+65)][sprintf("%02d", $i)  ] = array('',array());

	}	


	while ($ras = mysql_fetch_assoc($res))
	{

		#if (!array_key_exists($ras['well'], $wellLayout))



		if($ras['isControl'] == 1)
			$htmlClass='row_ctrl';
		else if($ras['esito_pcr'] == 'POSITIVO')
			$htmlClass='row_pos';
		else if($ras['esito_pcr'] == 'NEGATIVO')
			$htmlClass='row_neg';
		else if($ras['esito_pcr'] == 'RIPETERE ESTRAZIONE')
			$htmlClass='row_rep_ext';
		else if($ras['esito_pcr'] == 'RIPETERE PCR')
			$htmlClass='row_rep_pcr';
		else if($ras['esito_pcr'] == 'RIPETERE TAMPONE')
			$htmlClass='row_rep_tamp';
		else if($ras['esito_pcr'] == 'ERRORE COMPILAZIONE')
			$htmlClass='row_error';
		$ras['htmlClass'] = $htmlClass;

		$PCRs[] = $ras;

		$wellLetter = substr($ras['well'],0,1);
		$wellNumber = (substr($ras['well'],1,2));

		if (!array_key_exists($wellLetter, $wellLayout)) {echo "ERRORE!"; exit;}
		$wellLayout[$wellLetter][$wellNumber] = array($htmlClass,$ras);

	}


	$od=array();
	

	ksort($wellLayout);
	foreach($wellLayout as $k=>$v)
	{
		ksort($v);
		$od[$k] = $v;
	}
	//print_r(array_keys($od)); exit;
	
	$template = new PHPTAL('TEMPLATES/search_results.html');
	$template->searchKey = 'Plate di PCR :: '. $plate;


	$template->samples = $samples;
	$template->batches = $batches;
	$template->PCRs = $PCRs;
	$template->wellLayout = $od;
//	print_r($od);exit;

}


elseif (isSet($_POST['date']))
{
	$searchType = 'date';

	$date = $_POST['date']; 
	$PCRs = array();       
	$esitiTracker = array();

	$res = mysql_query("SELECT * FROM pcr_plates WHERE data_pcr = '$date' ORDER BY plate ASC, barcode ASC");
	
	while ($ras = mysql_fetch_assoc($res))
	{
		$ras['extractions'] = array();
		

		
		if($ras['isControl'] == 1)
			$htmlClass='row_ctrl';
		else if($ras['esito_pcr'] == 'POSITIVO')
			$htmlClass='row_pos';
		else if($ras['esito_pcr'] == 'NEGATIVO')
			$htmlClass='row_neg';
		else if($ras['esito_pcr'] == 'RIPETERE ESTRAZIONE')
			$htmlClass='row_rep_ext';
		else if($ras['esito_pcr'] == 'RIPETERE PCR')
			$htmlClass='row_rep_pcr';
		else if($ras['esito_pcr'] == 'RIPETERE TAMPONE')
			$htmlClass='row_rep_tamp';
		else if($ras['esito_pcr'] == 'ERRORE COMPILAZIONE')
			$htmlClass='row_error';
		else $htmlClass='';
		$ras['htmlClass'] = $htmlClass;

		if($ras['esito_pcr'] != 'CONTROLLO')
		{
			if (!array_key_exists($ras['esito_pcr'], $esitiTracker))
				$esitiTracker[$ras['esito_pcr']] = 1;
			else
				$esitiTracker[$ras['esito_pcr']] += 1;
		}

		if(!array_key_exists($ras['barcode'], $PCRs))
			$PCRs[$ras['barcode']] = array();	
		$PCRs[$ras['barcode']][] = $ras;
	}
/*
	$res = mysql_query("SELECT * FROM estrazioni WHERE barcode IN (SELECT barcode FROM pcr_plates WHERE data_pcr = '$date') ");
	while ($ras = mysql_fetch_assoc($res))
	{



		 foreach ( $PCRs[$ras['barcode']] as $bc=>$bca)
		 	$bca['extractions'][] = $ras;

	}
*/

	
	$template = new PHPTAL('TEMPLATES/search_results_pcr.html');
	$template->searchKey = 'Data di PCR :: '. $date;
	$template->PCRs = $PCRs;
	$template->esitiTracker=$esitiTracker;
	
}

$template->appVersion = $appVersion;


	if (isSet($_GET['json']) || isSet($_POST['json'])) {
		header('Content-type: application/json');
		if ($searchType == 'barcode' || $searchType == 'plate') {
			$output = json_encode([
				'samples' => $samples,
				'batches' => $batches,
				'PCRs'    => $PCRs
			]);
	  } elseif ($searchType == 'date') {
			$output = json_encode([
				'PCRs'         => $PCRs,
				'esitiTracker' => $esitiTracker
			]);
		} else {
			$output = json_encode([
				'error' => 'Bad criteria: "' . $searchType . '"'
			]);
		}
	} else {
	  try 
	  {
	  	$output = $template->execute();
	  }
	  	catch (Exception $e){
			$output = $e;
	  }
	}

	echo $output;
	
	

?>
