<?php
require_once("Mail.php");

function response($response_code, $data) {
	http_response_code($response_code);
	header('Content-Type: application/json');
	$body = json_encode($data);
	echo $body;
}

function sendmail($to, $subject, $message){
	// 言語と文字エンコーディングを正しくセット
	mb_language("Japanese");
	mb_internal_encoding("UTF-8");

	$params = array(
	  "host" => ini_get("SMTP"),   // SMTPサーバー名
	  "port" => 25,              // ポート番号
	  "auth" => false,            // SMTP認証を使用する
	);

	$headers = array(
	  "To" => $to,
	  "From" => "invitation@cross-party.com",
	  "Subject" => mb_encode_mimeheader($subject)
	);

	$message = mb_convert_encoding($message, "ISO-2022-JP", "auto");
	$mailObject = Mail::factory("smtp", $params);
	return $mailObject->send($to, $headers, $message);
}


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	response(405, array("result"=>"error", "message"=>"method not allowed"));
	exit(0);
}

$json_string = file_get_contents('php://input');
$data = json_decode($json_string, TRUE);

if ($data === NULL) {
	response(422, array("result"=>"error", "message"=>"invalid format"));
	exit(0);
}
