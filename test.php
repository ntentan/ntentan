<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>
</head>
<body>
<? 
include_once "ntentan.inc.php";
$db = Database::connect("mysql",array("host"=>"127.0.0.1","username"=>"root","password"=>"root"));
$db->selectDatabase("korianda");

?>
</body>
</html>