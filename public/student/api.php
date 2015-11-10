<?php
require_once("Mail.php");
include_once(dirname(__FILE__) . "/../../config.php");

function response($response_code, $data) {
	http_response_code($response_code);
	header('Content-Type: application/json');
	$body = json_encode($data);
	echo $body;
}

function sendmail($to, $coupon){
	// 言語と文字エンコーディングを正しくセット
	mb_language("Japanese");
	mb_internal_encoding("UTF-8");

	$params = array(
	  "host" => ini_get("SMTP"),   // SMTPサーバー名
	  "port" => 25,              // ポート番号
	  "auth" => false,            // SMTP認証を使用する
	);

// メール
	$message = "
こんにちは、

CROSS 実行委員会　です。
ユーザー情報をご登録頂きありがとうございます。

学割クーポンを発行いたしましたので、
チケット購入手続きの際に、ご入力ください。

クーポンコード       : $coupon
チケット販売サイトURL : https://peatix.com/sales/event/125846/tickets


その他、ご要望、ご質問などは、
CROSS公式Facebookページ、Twitterアカウントまでお問い合わせください。

facebook: https://www.facebook.com/engineersupportCROSS
Twitter : https://twitter.com/e_s_cross"
;

	$subject = '学割クーポン発行のお知らせ';

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

try{
	$pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);

	// sql実行時のエラーをexceptionでとるようにする
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// 重複チェック
	$stmt = $pdo->prepare("SELECT COUNT(id) AS count FROM users WHERE email = :mail");
	$stmt->bindValue(':mail', $data['mail'], PDO::PARAM_STR);
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($result["count"] > 0) {
		response(422, array("result"=>"error", "message"=>"duplicated"));
		exit(0);
	}

	// 登録
	$stmt = $pdo->prepare("INSERT INTO users (name, sex, email, university, department) VALUES (:name, :sex, :mail, :university, :department)");

	$stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
	$stmt->bindValue(':sex', $data['sex'], PDO::PARAM_INT);
	$stmt->bindValue(':mail', $data['mail'], PDO::PARAM_STR);
	$stmt->bindValue(':university', $data['university'], PDO::PARAM_STR);
	$stmt->bindValue(':department', $data['department'], PDO::PARAM_STR);

	$stmt->execute();

	// 登録したユーザのIDを取得
	$stmt = $pdo->prepare("SELECT id FROM users WHERE email=:mail limit 1");

	$stmt->bindValue(':mail', $data['mail'], PDO::PARAM_STR);
	$stmt->execute();
	$user_id = $stmt->fetchColumn();

	// メールとクーポンコードを紐付け
	$stmt = $pdo->prepare("UPDATE cupons SET user_id=:user_id where user_id is NULL limit 1");
	$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	$stmt->execute();

	// ひも付けたクーポンを取得
	$stmt = $pdo->prepare("SELECT code FROM cupons WHERE user_id=:user_id limit 1");
	$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	$stmt->execute();
	$code = $stmt->fetchColumn();

	// メール送信
	if (!sendmail($data['mail'], $code)){

	};

	// 成功
	response(200, array("result"=>"success"));

}catch (PDOException $e){
	response(500, array("result"=>"error", "message"=>"database exception"));
}
