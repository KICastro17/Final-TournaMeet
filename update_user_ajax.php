<?php
include "config.php";

$id = $_POST['id'];
$username = $_POST['username'];
$email = $_POST['email'];
$role = $_POST['role'];
$status = $_POST['status'];

$stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=?, status=? WHERE id=?");
$stmt->bind_param("ssssi",$username,$email,$role,$status,$id);
$stmt->execute();

echo "success";
?>