<?php
$name=$_POST['name'];
$email=$_POST['email'];
$spamtype=$_POST['spamtype'];
$complaint=$_POST['complaint'];
$servername="sql300.infinityfree.com";
$username="if0_39854533";
$password="gajjalavarun";
$database="if0_39854533_login";
$con=new mysqli($servername,$username,$password,$database);
$sql="insert into spam(name,email,spamtype,complaint)values('$name','$email','$spamtype','$complaint')";
$res=$con->query($sql);
if($res)
header("location:spam.html");
else
echo("not reg")
?>