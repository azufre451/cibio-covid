<?php
error_reporting(E_ERROR);
$db_Host = "colab1.cibio.unitn.it";
$db_User = "covid_user";
$db_Pass = "***REMOVED***";
$db_Name = 'covid'; 
$db_Port = 33006; 

$appVersion = "1.0.4";

//$db_Host = "localhost";
//$db_User = "debian-sys-maint";
//$db_Pass = "uvMB19ny5KOciZlt";
//$db_Name = 'covid';  
//$db_Port = 3306; 

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


Database::tdbConnect($db_Host,$db_User, $db_Pass,$db_Name,$db_Port);
Database::query('SET NAMES utf8');

?>
