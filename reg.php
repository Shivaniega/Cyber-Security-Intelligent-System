<?php
$fname=$_POST['fname'];
$lname=$_POST['lname'];
$email=$_POST['email'];
$uname=$_POST['username'];
$pwd=$_POST['pwd'];
$servername="sql300.infinityfree.com";
$username="if0_39854533";
$password="gajjalavarun";
$database="if0_39854533_login";
$con=new mysqli($servername,$username,$password,$database);
$sql="insert into details(fname,lname,email,username,pwd)values('$fname','$lname','$email','$uname','$pwd')";
$res=$con->query($sql);
if($res)
header("location:log.html");
else
echo("not reg")
?>















