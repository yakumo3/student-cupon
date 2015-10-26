<?php

$dsn = 'mysql:dbname=test;host=localhost';
$user = 'root';
$password = 'root';

try{
	$pdo = new PDO($dsn, $user, $password);

	// sql実行時のエラーをexceptionでとるようにする
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$stmt = $pdo -> prepare("INSERT INTO USERS (NAME, SEX, MAIL, UNIVERSITY, DEPARTMENT, CAREER) VALUES (:name, :sex, :mail, :university, :department, :career)");

	$stmt -> bindValue(':name', $_POST['name'], PDO::PARAM_STR);
	$stmt -> bindValue(':sex', $_POST['sex'], PDO::PARAM_INT);
	$stmt -> bindValue(':mail', $_POST['mail'], PDO::PARAM_STR);
	$stmt -> bindValue(':university', $_POST['university'], PDO::PARAM_STR);
	$stmt -> bindValue(':department', $_POST['department'], PDO::PARAM_STR);
	$stmt -> bindValue(':career', $_POST['career'], PDO::PARAM_INT);

	$stmt -> execute();

	// Content-TypeをJSONに指定する
	header('Content-Type: application/json');

	// {"data":"200 OK"}を返す
	$data = "200 OK";
	echo json_encode(compact('data'));
	die();

//重複するレコードがあればエラー
}catch (PDOException $e){
	$data = "error";
	echo json_encode(compact('data'));
	die();
}
?>