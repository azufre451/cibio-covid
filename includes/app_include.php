<?php
$db_Host = "localhost";
$db_User = "covid_user";
$db_Pass = "Q2GtXNpnKj94IP4HEo0IyvCun";
$db_Name = 'covid';


function mysql_query($q){return Database::query($q);}
function mysql_fetch_array($q){return mysql_fetch_array($q);}
function mysql_fetch_assoc($q){return mysql_fetch_assoc($q);}
function mysql_error(){return mysql_error(Database::$link);}
function mysql_affected_rows(){return mysql_affected_rows(Database::$link);}

class Database
{
	public static $link;

	public static function tdbConnect($db_Host,$db_User, $db_Pass,$db_Name)
	{
		self::$link=mysql_connect($db_Host,$db_User, $db_Pass,$db_Name);
	}
	
	public static function tdbClose()
	{
		mysql_close(self::$link);
	}

	public static function query($query)
	{ 
		$QR = mysql_query(self::$link,$query);
		if(mysql_error(self::$link))
			echo mysql_error(self::$link);
		return $QR;
	}
}


Database::tdbConnect($db_Host,$db_User, $db_Pass,$db_Name);
Database::query('SET NAMES utf8');

?>