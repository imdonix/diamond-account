<?php

$server_url = "localhost";
$server_username = "root";
$server_password = "";
$server_database = "diamond";

//Heroku databasess
if(getenv('JAWSDB_URL'))
{
	$url = getenv('JAWSDB_URL');
	$dbparts = parse_url($url);

	$server_url = $dbparts['host'];
	$server_username = $dbparts['user'];
	$server_password = $dbparts['pass'];
	$server_database = ltrim($dbparts['path'], '/');
}

$GLOBALS['database'] = mysqli_connect($server_url, $server_username, $server_password ,$server_database);
Check();

function Check()
{
  if(mysqli_connect_errno())
  die("E0");
}

function CreateStringFromArray($array)
{
	$colum = "";
	foreach($array as $r)
		$colum .= "," . $r;
	return substr($colum,1);
}

function GetRecordFromDB($table, $id, $value)
{
	Check();
	$sql = "SELECT * FROM $table WHERE $id = '$value'";
	$result = mysqli_query($GLOBALS['database'], $sql);
	
	$return = array();
	while ($row = mysqli_fetch_assoc($result))
		$return = $row;
	return $return;
}

function GetRecordFromDBwithSQL($sql)
{
	Check();
	$result = mysqli_query($GLOBALS['database'], $sql);
	
	$return = array();
	while ($row = mysqli_fetch_assoc($result))
		array_push($return, $row);
	return $return;
	
}

function DoSQL($sql)
{
	Check();
	mysqli_query($GLOBALS['database'], $sql);
}

function SetValueInDB($table, $id, $value, $wid, $wvalue)
{
	Check();	
	$sql = "UPDATE $table SET $id = '$value' WHERE $wid = '$wvalue'";
	$result = mysqli_query($GLOBALS['database'], $sql);
}

function InsertValue($table, $rows, $values)
{
	Check();
	$sql = "INSERT INTO $table (" . CreateStringFromArray($rows) . ") VALUES (" . CreateStringFromArray($values) . ");";
	mysqli_query($GLOBALS['database'], $sql);
}

function DeleteValue($table, $id, $value)
{
	Check();
	$sql = "DELETE FROM $table WHERE $id = '$value'";
	mysqli_query($GLOBALS['database'], $sql);
}

function IsValidLoginInDB($username, $password)
{
	Check();
	$sql = "SELECT id FROM users WHERE username = '$username' AND password = '$password'";
	$result = mysqli_query($GLOBALS['database'], $sql);
	if(mysqli_num_rows($result) == 1)
		return true;
	return false;
}

function IncreaseByOneInDB($table, $num, $id, $value)
{
	Check();	
	$sql = "UPDATE $table SET $num = $num + '1' WHERE $id = '$value'";
	$result = mysqli_query($GLOBALS['database'], $sql);
	if(mysqli_affected_rows($GLOBALS['database']) >= 0)
		return "Succes";
	return "";
}
?>