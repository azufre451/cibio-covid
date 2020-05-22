<?php
error_reporting(E_ERROR);
include('includes/conf.php');

$appVersion = "1.0.5.2";

function mysql_query($q){return Database::query($q);}
function mysql_fetch_array($q){return mysqli_fetch_array($q);}
function mysql_fetch_assoc($q){return mysqli_fetch_assoc($q);}
function mysql_error(){return mysqli_error(Database::$link);}
function mysql_affected_rows(){return mysqli_affected_rows(Database::$link);}

class Database
{
	public static $link;

	public static function tdbConnect($db_Host,$db_User, $db_Pass,$db_Name,$db_Port)
	{	

		self::$link=mysqli_connect($db_Host,$db_User, $db_Pass,$db_Name,$db_Port);
	}
	
	public static function tdbClose()
	{
		mysqli_close(self::$link);
	}

	public static function query($query)
	{ 
		$QR = mysqli_query(self::$link,$query);
		if(mysqli_error(self::$link))
			echo mysqli_error(self::$link);
		return $QR;
	}
}


$fluor2colors	=	array('FAM' => '#36a723','HEX' => '#2e69d5','Cy5' => '#b71656','Texas Red' => '#ff2f0e');
$plotOptions	=	array('bosphore'=> array(
								'max_pcr_cycles' => 40,
								'plotlabels' => implode(',',range(1,40)),
								'plotBaseLines' => array(
														array('value'=>'100','color'=>$fluor2colors['HEX'],'label' => 'HEX Threshold'),
														array('value'=>'50','color'=>$fluor2colors['Cy5'],'label' => 'Cy5/FAM Threshold')
														)
								),
							'realstar'=> array(
								'max_pcr_cycles' => 45,
								'plotlabels' => implode(',',range(1,45)),
								'plotBaseLines' => array(
														array('value'=>'150','color'=>$fluor2colors['HEX'],'label' => 'HEX Threshold'),
														array('value'=>'50','color'=>$fluor2colors['Cy5'],'label' => 'Cy5 Threshold'),
														array('value'=>'100','color'=>$fluor2colors['FAM'],'label' => 'FAM Threshold')
														)
								),
							'liferiver'=> array(
								'max_pcr_cycles' => 45,
								'plotlabels' => implode(',',range(1,45)),
								'plotBaseLines' => array(
														array('value'=>'100','color'=>$fluor2colors['HEX'],'label' => 'HEX Threshold'),
														array('value'=>'100','color'=>$fluor2colors['Cy5'],'label' => 'Cy5 Threshold'),
														array('value'=>'100','color'=>$fluor2colors['FAM'],'label' => 'FAM Threshold'),
														array('value'=>'100','color'=>$fluor2colors['Texas Red'],'label' => 'Texas Red Threshold')
														)
								)
						 );


Database::tdbConnect($db_Host,$db_User, $db_Pass,$db_Name,$db_Port);
Database::query('SET NAMES utf8');

?>
