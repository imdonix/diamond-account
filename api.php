<?php
include 'lib/database.php';
include 'lib/regexs.php';

const OUTPUT_DELIMITER = '&';
const SUB_OUTPUT_DELIMITER = '@';

$GLOBALS['API_version'] = "0.5";
$GLOBALS['commands'] = array();
$GLOBALS['expgain'] = array(10, 35, 50, 100);
$GLOBALS['masterkey'] = "e268443e43d93dab7ebef303bbe9642f";

//Core
AddCommand("login", "un,ps", 				"FunctionLogin");
AddCommand("register", "un,ig,ps,email", 	"FunctionRegister"); //optional invited
AddCommand("api", 	"", 					"FunctionAPI");
AddCommand("info", 	"loginkey",				"FunctionOwnAccountInfo");
AddCommand("findbyid", 	"ids",				"FunctionFindByIDSAccountsInfo");
AddCommand("findbyname", "name", 			"FunctionFindByNameAccountInfo");

//Games
AddCommand("game", 	"id", 					"FunctionGameInfo");
AddCommand("games", "", 					"FunctionAllGame");

//Friend system
AddCommand("Fsend", "id,loginkey",			"FunctionSendFriendRequest");
AddCommand("Faccept", "id,loginkey",		"FunctionAcceptFriendRequest");
AddCommand("Fdelete", "id,loginkey",		"FunctionDeleteFriendRequest");
AddCommand("Fpendings", "loginkey",			"FunctionGetPendingRequests");
AddCommand("friends", "loginkey",			"FunctionGetFriends");


CheckRequestStatements();
RunCommand();

//Setup
function Error($id)
{
	die("E$id");
}

function CheckRequestStatements()
{
	if($_SERVER["REQUEST_METHOD"] != "GET" || !isSet($_GET["type"]) || !IsAviableCommand($_GET["type"]))
		Error("1");
	
	if(!HasAllReq(GetAPartFromCommand(GetTheCommand($_GET["type"]),1)))
		Error("2");
	
	if(!isSet($_GET["apikey"]) || !IsValidKey("games","api"))
		Error("3");
	
	UserAuth();
}

function AddCommand($name, $arguments, $f)
{
	array_push($GLOBALS['commands'], "$name|$arguments|$f");
}

function GetAPartFromCommand($command, $id)
{
	$splitted = explode('|', $command);
	return $splitted[$id];
}

function UserAuth()
{
	if(isSet($_GET["loginkey"]))
	{
		if(!IsValidKey("users", "login"))
			Error("4");
		
	$apik = $_GET["apikey"];
	SetValueInDB("users", "lastgame", "(SELECT id FROM games WHERE apikey = '$apik')", "loginkey", $_GET["loginkey"]);
	return GetRecordFromDB("users", "loginkey", $_GET["loginkey"]);
	}
}

function IsMasterKey($key)
{
	if($key == $GLOBALS['masterkey'])
		return true;
	return false;
}

function IsValidKey($table, $type)
{
	$key = $type . "key";
	if($type == "api")
		if(IsMasterKey($_GET[$key] && IsAuthType()))
			return true;

	$ret = GetRecordFromDB($table, $key, $_GET[$key]);
	if(empty($ret))
		return false;
	
	IncreaseByOneInDB($table, "reqcounter", $key, $_GET[$key]);
	return true;	
}

function IsAviableCommand($name)
{
	foreach($GLOBALS['commands'] as $command)
	{
		$splitted = explode('|', $command);
		if($splitted[0] == $name)
			return true;
	}
	return false;
}

function GetTheCommand($name)
{
	foreach($GLOBALS['commands'] as $command)
	{
		if(GetAPartFromCommand($command, 0) == $name)
		return $command;
	}
	return null;
}

function HasReq($req)
{
	if(isSet($_GET[$req]))
		return true;
	return false;
}

function HasAllReq($input)
{
	$splitted = explode(',', $input);
	if(empty($input))
		return true;
	
	foreach($splitted as $req)
		if(!HasReq($req))
			return false;
	return true;		
}

function RunCommand()
{
	$func = GetAPartFromCommand(GetTheCommand($_GET["type"]),2);
	$func();
}

function IsAuthType()
{
	$type = $_GET["type"];
	if($type == "login" || $type == "register")
		return true;
	return false;
}

function IsMD5Hash($string)
{
	if(strlen($string) == 32)
		return true;
	return false;
}

function CreateOutPutString($array)
{
	$outputstring = "";
	foreach($array as $str)
		if(!IsMD5Hash($str) && !isREGEXCorrectEmail($str))
			$outputstring .= OUTPUT_DELIMITER . $str;
	
	return substr($outputstring,1);
}

function WriteOutAndCheckResoult($string)
{
	if(empty($string))
		Error("9");
	echo $string ;
}

function CreateNewMD5HashLoginKey($arg)
{
	$date = date("Y:h:i:s");
	$name = $_GET["un"];
	$string = $date . "/" . $name . $arg;
	return md5($string);
}

function GetIDbyKey()
{
	$array = GetRecordFromDB("users", "loginkey", $_GET["loginkey"]);
	return $array['id'];
}

function IsValidUserID($id)
{
	if(count(GetRecordFromDB("users", "id", $id))>0)
		return true;
	return false;
}

function GetInviter()
{
	if(!isSet($_GET["ref"]))
		return "-1";
	return $_GET["ref"];
}

function IsEachElementUnique($arr)
{
	$uniq = array();
	foreach($arr as $element)
		if(!in_array($element, $uniq))
			array_push($uniq, $element);
	return count($uniq) == count($arr);
}

// Subs for Friend

function ClearConnection($from, $to)
{
	if($from > $to)
		return $to . "|" .$from;
	else
		return $from . "|" .$to;
}

function IsElementOfArray($array, $value)
{
	foreach($array as $element)
		if($element == $value)
			return true;
	return false;
}

function GetAllFriendConnection($id)
{
	$result = GetRecordFromDBwithSQL("SELECT * FROM friends WHERE pfrom = $id OR pto = $id");
	
	$return = array();
	foreach($result as $row)
		if(!IsElementOfArray($return, ClearConnection($row['pfrom'], $row['pto'])))
			array_push($return, ClearConnection($row['pfrom'], $row['pto']));
	return $return;
}

function GetAllFriendConnectionWithConditions($id, $condi)
{
	$result = GetRecordFromDBwithSQL("SELECT * FROM friends WHERE (pfrom = $id OR pto = $id) AND accepted = $condi");
	
	$return = array();
	foreach($result as $row)
		if(!IsElementOfArray($return, $row['id'] . "@" . $row['pfrom'] . "@" . $row['pto']))
			array_push($return, $row['id'] . "@" . $row['pfrom'] . "@" . $row['pto']);
	return $return;
}

//FUNCS
function FunctionLogin()
{	
	if(!IsValidLoginInDB($_GET["un"], $_GET["ps"]))
		Error("5");
	$newkey = CreateNewMD5HashLoginKey("");
	
	$c = 0;
	while(!empty(GetRecordFromDB("users", "loginkey", $newkey)))
		$newkey = CreateNewMD5HashLoginKey($c++);
	
	SetValueInDB("users", "loginkey", $newkey, "username", $_GET["un"]);
	WriteOutAndCheckResoult($newkey);	
}

function FunctionRegister()
{
	$un = $_GET['un'];
	$ig = $_GET['ig'];
	$email = $_GET['email'];
	if(!isREGEXCorrectName($un)) Error("701");
	if(!isREGEXCorrectName($ig)) Error("702");
	if(!isREGEXCorrectEmail($email)) Error("703");

	$untaken = GetRecordFromDBwithSQL("SELECT * FROM users WHERE username = '$un'");
	if(count($untaken) > 0)
		Error("711");

	$igtaken = GetRecordFromDBwithSQL("SELECT * FROM users WHERE name = '$ig'");
	if(count($igtaken) > 0)
		Error("712");
	
	$emailtaken = GetRecordFromDBwithSQL("SELECT * FROM users WHERE email = '$email'");
	if(count($emailtaken) > 0)
		Error("713");

	$MD5Pass = md5($_GET['ps']);
	
	$inviter=GetInviter();

	$rows = array("name", "username", "password", "email", "invited");
	$values = array("'$ig'", "'$un'", "'$MD5Pass'" , "'$email'", "'$inviter'");
	
	if(!InsertValue("users", $rows, $values))
		Error("0");
	
	$_GET["ps"] = $MD5Pass;
	FunctionLogin();
}

function FunctionAPI()
{
	WriteOutAndCheckResoult($GLOBALS['API_version']);
}

function FunctionGameInfo()
{	
	$output = CreateOutPutString(GetRecordFromDB("games", "id", $_GET["id"]));
	WriteOutAndCheckResoult($output);
}

function FunctionAllGame()
{
	$output = array();
	$input = GetRecordFromDBwithSQL("SELECT id FROM games");
	foreach($input as $row)
		array_push($output, $row['id']);
		
	WriteOutAndCheckResoult(CreateOutPutString($output));
}

function FunctionOwnAccountInfo()
{	
	$output = CreateOutPutString(GetRecordFromDB("users", "loginkey", $_GET["loginkey"]));
	WriteOutAndCheckResoult($output);
}

function FunctionFindByIDSAccountsInfo()
{
	$ids = explode("|", $_GET['ids']);
	if(!IsEachElementUnique($ids))
		Error("2");

	$sql = "SELECT * FROM users WHERE ";
	foreach($ids as $id)
		$sql .= "id = $id" . (end($ids) != $id  ? " OR " : "");

	$res = GetRecordFromDBwithSQL($sql);
	$output = "";
	foreach($res as $user)
		$output .= CreateOutPutString($user) . (end($res) != $user  ? SUB_OUTPUT_DELIMITER : "");

	WriteOutAndCheckResoult($output);
}

function FunctionFindByNameAccountInfo()
{	
	$output = CreateOutPutString(GetRecordFromDB("users", "name", $_GET["name"]));
	WriteOutAndCheckResoult($output);
}

function FunctionSendFriendRequest()
{
	if(GetIDbyKey() == $_GET["id"])
		Error("2");
	
	if(!IsValidUserID($_GET["id"]))
		Error("6");
	
	if(IsElementOfArray(GetAllFriendConnection(GetIDbyKey()), ClearConnection(GetIDbyKey(), $_GET["id"])))
		Error("6");
	
	$rows = array("pfrom", "pto");
	$values = array(GetIDbyKey(), $_GET["id"]);
	WriteOutAndCheckResoult(InsertValue("friends", $rows, $values));
}

function FunctionAcceptFriendRequest()
{
	if(!(GetRecordFromDB("friends", "id", $_GET["id"])['pto'] == GetIDbyKey()))
		Error("6");
	
	WriteOutAndCheckResoult(IncreaseByOneInDB("friends", "accepted", "id", $_GET["id"]));
}

function FunctionDeleteFriendRequest()
{
	$res = GetRecordFromDB("friends", "id", $_GET['id']);
	if(empty($res))
		Error("6");
	
	if(!($res['pfrom'] == GetIDbyKey() || $res['pto'] == GetIDbyKey()))
		Error("6");
	
	DeleteValue("friends", "id", $res['id']);
	WriteOutAndCheckResoult("Succes");
}

function FunctionGetPendingRequests()
{
	$array = GetAllFriendConnectionWithConditions(GetIDbyKey(), 0);
	WriteOutAndCheckResoult(CreateOutPutString($array));
}

function FunctionGetFriends()
{
	$array = GetAllFriendConnectionWithConditions(GetIDbyKey(), 1);
	WriteOutAndCheckResoult(CreateOutPutString($array));
}
?>