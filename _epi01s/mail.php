<?php header("Content-Type:text/html;charset=utf-8"); ?>
<?php
require 'config.php';
?>
<?php //error_reporting(E_ALL | E_STRICT);
##-----------------------------------------------------------------------------------------------------------------##
#
#  PHPメールプログラム　フリー版 最終更新日2018/07/27
#　改造や改変は自己責任で行ってください。
#
#  今のところ特に問題点はありませんが、不具合等がありましたら下記までご連絡ください。
#  MailAddress: info@php-factory.net
#  name: K.Numata
#  HP: http://www.php-factory.net/
#
#  重要！！サイトでチェックボックスを使用する場合のみですが。。。
#  チェックボックスを使用する場合はinputタグに記述するname属性の値を必ず配列の形にしてください。
#  例　name="当サイトをしったきっかけ[]"  として下さい。
#  nameの値の最後に[と]を付ける。じゃないと複数の値を取得できません！
#
##-----------------------------------------------------------------------------------------------------------------##
if (version_compare(PHP_VERSION, '5.1.0', '>=')) { //PHP5.1.0以上の場合のみタイムゾーンを定義
	date_default_timezone_set('Asia/Tokyo'); //タイムゾーンの設定（日本以外の場合には適宜設定ください）
}
/*-------------------------------------------------------------------------------------------------------------------
* ★以下設定時の注意点　
* ・値（=の後）は数字以外の文字列（一部を除く）はダブルクオーテーション「"」、または「'」で囲んでいます。
* ・これをを外したり削除したりしないでください。後ろのセミコロン「;」も削除しないください。
* ・また先頭に「$」が付いた文字列は変更しないでください。数字の1または0で設定しているものは必ず半角数字で設定下さい。
* ・メールアドレスのname属性の値が「Email」ではない場合、以下必須設定箇所の「$Email」の値も変更下さい。
* ・name属性の値に半角スペースは使用できません。
*以上のことを間違えてしまうとプログラムが動作しなくなりますので注意下さい。
-------------------------------------------------------------------------------------------------------------------*/


//---------------------------　必須設定　必ず設定してください　-----------------------

//ドキュメントルート設定(ドキュメントルート直下の時は「''」、そうでないときは「'/hogehoge'」と記述)
define('_SITE_DOCUMENT_ROOT', '');
//ルート絶対パス
define('_SERVER_ROOT_DIR', $_SERVER['DOCUMENT_ROOT'] . '/'); // [ /var/www/html/example/ ]
//ルート絶対URL
define('_WEB_ROOT_DIR', _SITE_DOCUMENT_ROOT == '' ? '/' : _SITE_DOCUMENT_ROOT); // [ / ]
define('_WEB_ROOT_FULL_DIR', (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['SERVER_NAME'] . _SITE_DOCUMENT_ROOT);



//サイトのトップページのURL　※デフォルトでは送信完了後に「トップページへ戻る」ボタンが表示されますので
//$site_top = "http://rd-data.sakura.ne.jp/upload/181105_reala/";
$site_top = _WEB_ROOT_FULL_DIR;

//管理者のメールアドレス ※メールを受け取るメールアドレス(複数指定する場合は「,」で区切ってください 例 $to = "aa@aa.aa,bb@bb.bb";)
$to = "info@wi-clinic.com,wiclinic0707@gmail.com";


//自動返信メールの送信元メールアドレス
//必ず実在するメールアドレスでかつ出来る限り設置先サイトのドメインと同じドメインのメールアドレスとすることを強く推奨します
$from = "info@wi-clinic.com";

//アップロードファイルの一時保存ディレクトリ
$tmp_dir = '';

//アップロードファイルの本番保存ディレクトリ
$upload_dir = '';

//フォームのメールアドレス入力箇所のname属性の値（name="○○"　の○○部分）
$Email = "E-mail";

//フォームのファイルアップロード箇所のname属性の値（name="○○"　の○○部分）
$imgname = "顔写真";

//CSV保存モード(する=1, しない=0)
//送信されたお問い合わせ一覧をCSVに書き出す 保存先はアップロードファイルの本番ディレクトリ
$csvmode = 0;
$csvname = 'file.csv';

//---------------------------　必須設定　ここまで　------------------------------------


//---------------------------　セキュリティ、スパム防止のための設定　------------------------------------

//スパム防止のためのリファラチェック（フォーム側とこのファイルが同一ドメインであるかどうかのチェック）(する=1, しない=0)
//※有効にするにはこのファイルとフォームのページが同一ドメイン内にある必要があります
$Referer_check = 0;

//リファラチェックを「する」場合のドメイン ※設置するサイトのドメインを指定して下さい。
//もしこの設定が間違っている場合は送信テストですぐに気付けます。
$Referer_check_domain = "php-factory.net";

/*セッションによるワンタイムトークン（CSRF対策、及びスパム防止）(する=1, しない=0)
※ただし、この機能を使う場合は↓の送信確認画面の表示が必須です。（デフォルトではON（1）になっています）
※【重要】ガラケーは機種によってはクッキーが使えないためガラケーの利用も想定してる場合は「0」（OFF）にして下さい（PC、スマホは問題ないです）*/
$useToken = 1;
//---------------------------　セキュリティ、スパム防止のための設定　ここまで　------------------------------------


//---------------------- 任意設定　以下は必要に応じて設定してください ------------------------


// 管理者宛のメールで差出人を送信者のメールアドレスにする(する=1, しない=0)
// する場合は、メール入力欄のname属性の値を「$Email」で指定した値にしてください。
//メーラーなどで返信する場合に便利なので「する」がおすすめです。
$userMail = 1;

// Bccで送るメールアドレス(複数指定する場合は「,」で区切ってください 例 $BccMail = "aa@aa.aa,bb@bb.bb";)
$BccMail = "";

// 管理者宛に送信されるメールのタイトル（件名）
$subject = '【銀座院宛】脱毛LPよりお問い合わせがありました';

// 送信確認画面の表示(する=1, しない=0)
$confirmDsp = 1;

// 送信完了後に自動的に指定のページ(サンクスページなど)に移動する(する=1, しない=0)
// CV率を解析したい場合などはサンクスページを別途用意し、URLをこの下の項目で指定してください。
// 0にすると、デフォルトの送信完了画面が表示されます。
$jumpPage = 0;

// 送信完了後に表示するページURL（上記で1を設定した場合のみ）※httpから始まるURLで指定ください。（相対パスでも基本的には問題ないです）
$thanksPage = "thanks.php";

// 必須入力項目を設定する(する=1, しない=0)
$requireCheck = 1;

/* 必須入力項目(入力フォームで指定したname属性の値を指定してください。（上記で1を設定した場合のみ）
値はシングルクォーテーションで囲み、複数の場合はカンマで区切ってください。フォーム側と順番を合わせると良いです。
配列の形「name="○○[]"」の場合には必ず後ろの[]を取ったものを指定して下さい。*/
$require = array('第1希望日', '第1希望時間', '第2希望日', '第2希望時間', '希望コース', 'E-mail', '電話番号', 'お名前', 'よみがな', '年齢');

/* 確認画面非表示項目(入力フォームで指定したname属性の値を指定してください。*/
$notConfDisp = array('顔写真', 'pm1');

/* 自動送信メール非表示項目(入力フォームで指定したname属性の値を指定してください。*/
$notMailDisp = array('顔写真', 'pm1');



//----------------------------------------------------------------------
//  自動返信メール設定(START)
//----------------------------------------------------------------------

// 差出人に送信内容確認メール（自動返信メール）を送る(送る=1, 送らない=0)
// 送る場合は、フォーム側のメール入力欄のname属性の値が上記「$Email」で指定した値と同じである必要があります
$remail = 1;

//自動返信メールの送信者欄に表示される名前　※あなたの名前や会社名など（もし自動返信メールの送信者名が文字化けする場合ここは空にしてください）
$refrom_name = "【ウィクリニック銀座院】脱毛予約";

// 差出人に送信確認メールを送る場合のメールのタイトル（上記で1を設定した場合のみ）
$re_subject = "【ウィクリニック銀座院】お問い合わせありがとうございました";

//フォーム側の「名前」箇所のname属性の値　※自動返信メールの「○○様」の表示で使用します。
//指定しない、または存在しない場合は、○○様と表示されないだけです。あえて無効にしてもOK
$dsp_name = 'お名前';

//自動返信メールの冒頭の文言 ※日本語部分のみ変更可
$remail_text = <<< TEXT

ご予約、誠にありがとうございます。
このメールはご予約の受け付けをお知らせする自動返信メールです。
ご予約に対する返信ではございません。

ご予約いただきました内容につきましては、担当者より後日ご連絡いたします。

※現在コロナウイルスの影響により、ご連絡まで２～3日ほどお時間を頂く場合がございます。
※おまたせしてしまい申し訳ございませんが、何卒よろしくお願いいたします。

何かございましたら、お電話でも遠慮なくお申し付けください。
なお、本メールへの返信は受け付けておりませんのでご了承ください。

TEXT;


//自動返信メールに署名（フッター）を表示(する=1, しない=0)※管理者宛にも表示されます。
$mailFooterDsp = 1;

//上記で「1」を選択時に表示する署名（フッター）（FOOTER～FOOTER;の間に記述してください）
$mailSignature = <<< FOOTER

----------------------------------------------------------------------
ウィクリニック　銀座院
TEL0120-897-184
【住所】東京都中央区銀座8-9-16 長崎センタービル9階
【営業時間】 10:00～19:00 【定休日】不定休

----------------------------------------------------------------------

FOOTER;


//----------------------------------------------------------------------
//  自動返信メール設定(END)
//----------------------------------------------------------------------

//メールアドレスの形式チェックを行うかどうか。(する=1, しない=0)
//※デフォルトは「する」。特に理由がなければ変更しないで下さい。メール入力欄のname属性の値が上記「$Email」で指定した値である必要があります。
$mail_check = 1;

//全角英数字→半角変換を行うかどうか。(する=1, しない=0)
$hankaku = 0;

//全角英数字→半角変換を行う項目のname属性の値（name="○○"の「○○」部分）
//※複数の場合にはカンマで区切って下さい。（上記で「1」を指定した場合のみ有効）
//配列の形「name="○○[]"」の場合には必ず後ろの[]を取ったものを指定して下さい。
$hankaku_array = array('電話番号', '金額');

//-fオプションによるエンベロープFrom（Return-Path）の設定(する=1, しない=0)　
//※宛先不明（間違いなどで存在しないアドレス）の場合に 管理者宛に「Mail Delivery System」から「Undelivered Mail Returned to Sender」というメールが届きます。
//サーバーによっては稀にこの設定が必須の場合もあります。
//設置サーバーでPHPがセーフモードで動作している場合は使用できませんので送信時にエラーが出たりメールが届かない場合は「0」（OFF）として下さい。
$use_envelope = 0;

//機種依存文字の変換
/*たとえば㈱（かっこ株）や①（丸1）、その他特殊な記号や特殊な漢字などは変換できずに「？」と表示されます。それを回避するための機能です。
確認画面表示時に置換処理されます。「変換前の文字」が「変換後の文字」に変換され、送信メール内でも変換された状態で送信されます。（たとえば「㈱」の場合、「（株）」に変換されます）
必要に応じて自由に追加して下さい。ただし、変換前の文字と変換後の文字の順番と数は必ず合わせる必要がありますのでご注意下さい。*/

//変換前の文字
$replaceStr['before'] = array('①', '②', '③', '④', '⑤', '⑥', '⑦', '⑧', '⑨', '⑩', '№', '㈲', '㈱', '髙');
//変換後の文字
$replaceStr['after'] = array('(1)', '(2)', '(3)', '(4)', '(5)', '(6)', '(7)', '(8)', '(9)', '(10)', 'No.', '（有）', '（株）', '高');

//------------------------------- 任意設定ここまで ---------------------------------------------


// 以下の変更は知識のある方のみ自己責任でお願いします。

//----------------------------------------------------------------------
//  関数実行、変数初期化
//----------------------------------------------------------------------
//トークンチェック用のセッションスタート
if ($useToken == 1 && $confirmDsp == 1) {
	session_name('PHPMAILFORMSYSTEM');
	session_start();
}
$encode = "UTF-8"; //このファイルの文字コード定義（変更不可）
if (isset($_GET)) $_GET = sanitize($_GET); //NULLバイト除去//
if (isset($_POST)) $_POST = sanitize($_POST); //NULLバイト除去//
if (isset($_COOKIE)) $_COOKIE = sanitize($_COOKIE); //NULLバイト除去//
if ($encode == 'SJIS') $_POST = sjisReplace($_POST, $encode); //Shift-JISの場合に誤変換文字の置換実行
$funcRefererCheck = refererCheck($Referer_check, $Referer_check_domain); //リファラチェック実行

//変数初期化
$sendmail = 0;
$empty_flag = 0;
$post_mail = '';
$errm = '';
$header = '';

//必須チェック処理（抜けあればエラーフラグをONにする)
if ($requireCheck == 1) {
	$requireResArray = requireCheck($require); //必須チェック実行し返り値を受け取る
	$errm = $requireResArray['errm'];
	$empty_flag = $requireResArray['empty_flag'];
}
// 一時ファイルの可視化処理
if (!empty($_FILES[$imgname]['tmp_name'])) {
	$fp = fopen($_FILES[$imgname]['tmp_name'], "rb");
	$img = fread($fp, filesize($_FILES[$imgname]['tmp_name']));
	fclose($fp);
	$enc_img = base64_encode($img);
	$imginfo = getimagesize('data:application/octet-stream;base64,' . $enc_img);
}
// 添付ファイルがあった場合は一時ファイルをtmpディレクトリへ保存
if (!empty($_FILES[$imgname]['tmp_name'])) {

	//添付ファイルの拡張子確認
	$upload_filetype = substr($_FILES[$imgname]['name'], strrpos($_FILES[$imgname]['name'], '.') + 1);

	if ($upload_filetype == 'jpg' or $upload_filetype == 'png' or $upload_filetype == 'jpeg') {

		$imgdata = imgTmpUpload($_FILES[$imgname]['tmp_name'], $_POST['お名前'], $upload_filetype); //jpgかpngだったらTMPディレクトリに一時アップ

	} else {

		$errm .= "<p class=\"error_messe\">アップロードされた写真のファイル形式が正しくありません。</p>\n";
		$empty_flag = 1;
	}
}

//メールアドレスチェック
if (empty($errm)) {
	foreach ($_POST as $key => $val) {
		if ($val == "confirm_submit") $sendmail = 1;
		if ($key == $Email) $post_mail = h($val);
		if ($key == $Email && $mail_check == 1 && !empty($val)) {
			if (!checkMail($val)) {
				$errm .= "<p class=\"error_messe\">【" . $key . "】はメールアドレスの形式が正しくありません。</p>\n";
				$empty_flag = 1;
			}
		}
	}
}
if (($confirmDsp == 0 || $sendmail == 1) && $empty_flag != 1) {
	//トークンチェック（CSRF対策）※確認画面がONの場合のみ実施
	if ($useToken == 1 && $confirmDsp == 1) {
		if (empty($_SESSION['mailform_token']) || ($_SESSION['mailform_token'] !== $_POST['mailform_token'])) {
			exit('ページ遷移が不正です');
		}
		$tokenID = $_SESSION['mailform_token'];
		if (isset($_SESSION['mailform_token'])) unset($_SESSION['mailform_token']); //トークン破棄
		if (isset($_POST['mailform_token'])) unset($_POST['mailform_token']); //トークン破棄
	}
	//差出人に届くメールをセット
	if ($remail == 1) {
		$userBody = mailToUser($_POST, $dsp_name, $remail_text, $mailFooterDsp, $mailSignature, $encode);
		$reheader = userHeader($refrom_name, $from, $encode);
		$re_subject = "=?iso-2022-jp?B?" . base64_encode(mb_convert_encoding($re_subject, "JIS", $encode)) . "?=";
	}
	//管理者宛に届くメールをセット
	$adminBody = mailToAdmin($_POST, $subject, $mailFooterDsp, $mailSignature, $encode, $confirmDsp);
	$header = adminHeader($userMail, $post_mail, $BccMail, $to);
	$subject = "=?iso-2022-jp?B?" . base64_encode(mb_convert_encoding($subject, "JIS", $encode)) . "?=";

	//-fオプションによるエンベロープFrom（Return-Path）の設定(safe_modeがOFFの場合かつ上記設定がONの場合のみ実施)
	if ($use_envelope == 0) {
		mail($to, $subject, $adminBody, $header);
		if ($remail == 1 && !empty($post_mail)) mail($post_mail, $re_subject, $userBody, $reheader);
	} else {
		mail($to, $subject, $adminBody, $header, '-f' . $from);
		if ($remail == 1 && !empty($post_mail)) mail($post_mail, $re_subject, $userBody, $reheader, '-f' . $from);
	}
}



/*　▼▼▼送信確認画面のレイアウト※編集可　オリジナルのデザインも適用可能▼▼▼　*/ else if ($confirmDsp == 1) {
?>
	<!DOCTYPE HTML>
	<html lang="ja">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=640">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="format-detection" content="telephone=no">
		<meta name="description" content="">
		<meta name="keywords" content="">
		<title></title>

		<link rel="stylesheet" href="css/reset.css">
		<link rel="stylesheet" href="css/animate.wow.css">
		<link rel="stylesheet" href="css/common.css">
		<link rel="stylesheet" media="screen" type="text/css" href="css/validationEngine.jquery.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="css/jquery-ui.css" />
		<link rel="stylesheet" href="css/general.css">
		<link rel="stylesheet" href="css/index.css">


		<script src="js/jquery.js"></script>
		<script src="js/jquery.validationEngine.js"></script>
		<script src="js/jquery.validationEngine-ja.js"></script>
		<script src="js/jquery-ui.min.js"></script>
		<script src="js/jquery.ui.datepicker-ja.min.js"></script>


		<!-- Facebook Pixel Code -->
		<script>
		!function(f,b,e,v,n,t,s)
		{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};
		if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
		n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];
		s.parentNode.insertBefore(t,s)}(window, document,'script',
		'https://connect.facebook.net/en_US/fbevents.js');
		fbq('init', '903072450488562');
		fbq('track', 'PageView');
		</script>
		<noscript><img height="1" width="1" style="display:none"
		src="https://www.facebook.com/tr?id=903072450488562&ev=PageView&noscript=1"
		/></noscript>
		<!-- End Facebook Pixel Code -->

		<!-- Google Tag Manager -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-54WKRJT" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-NMV3QTM" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<script>
			(function(w, d, s, l, i) {
				w[l] = w[l] || [];
				w[l].push({
					"gtm.start": new Date().getTime(),
					event: "gtm.js"
				});
				var f = d.getElementsByTagName(s)[0],
					j = d.createElement(s);
				j.async = true;
				j.src = "https://www.googletagmanager.com/gtm.js?id=" + i + "&l=" + l;
				f.parentNode.insertBefore(j, f);
			})(window, document, "script", "dtLyr", "GTM-NMV3QTM");
		</script>
		<script>
			(function(w, d, s, l, i) {
				w[l] = w[l] || [];
				w[l].push({
					'gtm.start': new Date().getTime(),
					event: 'gtm.js'
				});
				var f = d.getElementsByTagName(s)[0],
					j = d.createElement(s),
					dl = l != 'dataLayer' ? '&l=' + l : '';
				j.async = true;
				j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
				f.parentNode.insertBefore(j, f);
			})(window, document, 'script', 'dataLayer', 'GTM-54WKRJT');
		</script>
		<script>
			window.dataLayer = window.dataLayer || [];

			function gtag() {
				dataLayer.push(arguments);
			}
			gtag('js', new Date());
			gtag('config', 'AW-758975358');
		</script>
		<script async src="https://www.googletagmanager.com/gtag/js?id=AW-758975358"></script>
		<!-- End Google Tag Manager -->

		<!-- Google Tag Manager -->
		<script>
			(function(w, d, s, l, i) {
				w[l] = w[l] || [];
				w[l].push({
					'gtm.start': new Date().getTime(),
					event: 'gtm.js'
				});
				var f = d.getElementsByTagName(s)[0],
					j = d.createElement(s),
					dl = l != 'dataLayer' ? '&l=' + l : '';
				j.async = true;
				j.src =
					'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
				f.parentNode.insertBefore(j, f);
			})(window, document, 'script', 'dataLayer', 'GTM-M5SLMSM');
		</script>
		<!-- End Google Tag Manager -->

	</head>

	<body>

		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M5SLMSM" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->

		<!-- ASP Tag START -->
		<script language='javascript' src='https://p-salm.jp/ad/js/lpjs.js'></script>
		<script>
			var _CIDN = "cid";
			var _PMTN = "p";
			var _LPTU = "./";
			var _param = location.search.substring(1).split("&");
			var _ulp = "",
				_ulcid = "";
			for (var i = 0; _param[i]; i++) {
				var kv = _param[i].split("=");
				if (kv[0] == _PMTN && kv[1].length > 1) {
					_ulp = kv[1];
				}
				if (kv[0] == _CIDN && kv[1].length > 1) {
					_ulcid = kv[1];
				}
			}
			if (_ulp && _ulcid) {
				_xhr = new XMLHttpRequest();
				_xhr.open("GET", _LPTU + "lptag.php?p=" + _ulp + "&cid=" + _ulcid);
				_xhr.send();
				localStorage.setItem("CL_" + _ulp, _ulcid);
			}
		</script>
		<!-- ASP Tag END -->

		<!-- Facebook Pixel Code -->
		<script>
			! function(f, b, e, v, n, t, s) {
				if (f.fbq) return;
				n = f.fbq = function() {
					n.callMethod ?
						n.callMethod.apply(n, arguments) : n.queue.push(arguments)
				};
				if (!f._fbq) f._fbq = n;
				n.push = n;
				n.loaded = !0;
				n.version = '2.0';
				n.queue = [];
				t = b.createElement(e);
				t.async = !0;
				t.src = v;
				s = b.getElementsByTagName(e)[0];
				s.parentNode.insertBefore(t, s)
			}(window, document, 'script',
				'https://connect.facebook.net/en_US/fbevents.js');
			fbq('init', '1047686985403416');
			fbq('track', 'PageView');
		</script>
		<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=1047686985403416&ev=PageView&noscript=1" /></noscript>
		<!-- End Facebook Pixel Code -->

		<div id="container">

			<!-- #BeginLibraryItem "/Library/header.lbi" -->
			<header id="gHeader">
				<h1><img src="img/index/header.png" alt="銀座駅に2019 年6月ウィクリニックがグランドオープン！全身医療脱毛で都内最安級を目指します！"></h1>
			</header>

			<!-- #EndLibraryItem -->
			<section id="contact">
				<div class="contact confirm">
					<h2>WEB予約内容確認</h2>
					<ol class="formBread">
						<li><span>入力</span></li>
						<li class="formBreadNow"><span>予約内容確認</span></li>
						<li><span>予約完了</span></li>
					</ol>
					<!-- ▲ Headerやその他コンテンツなど　※自由に編集可 ▲-->


					<!-- ▼************ 送信内容表示部　※編集は自己責任で ************ ▼-->
					<?php if ($empty_flag == 1) { ?>

						<!--エラー時処理-->
						<ul class="counselingNote">
							<li>入力にエラーがあります。<br>ご確認の上「戻る」ボタンにて修正をお願い致します。<br><br></li>
						</ul>
						<?php echo $errm; ?><br /><br />
						<ul class="submit clearfix">
							<li>
								<input type="submit" value="戻る" onclick="history.back()">
							</li>
						</ul>

					<?php } else { ?>

						<form action="<?php echo h($_SERVER['SCRIPT_NAME']); ?>" method="POST" class="mailForm">

							<dl class="form-list">
								<?php echo confirmOutput($_POST); //入力内容を表示
								?>
							</dl>
							<input type="hidden" name="httpReferer" value="<?php echo h($_SERVER['HTTP_REFERER']); ?>">
							<input type="hidden" name="imgdata" value="<?php if (!empty($_FILES[$imgname]['tmp_name'])) {
																														echo $imgdata;
																													} ?>">

							<ul class="submit clearfix">
								<input type="hidden" name="mail_set" value="confirm_submit">
								<li>
									<input type="button" value="前画面に戻る" class="firstSubmit" onclick="history.back()" />
								</li>
								<li>
									<input type="submit" value="送信する" class="firstSubmit" />
								</li>
							</ul>

						</form>

					<?php } ?>
					<!-- ▲ *********** 送信内容確認部　※編集は自己責任で ************ ▲-->

				</div>
			</section>

		</div>
	</body>

	</html>
<?php
	/* ▲▲▲送信確認画面のレイアウト　※オリジナルのデザインも適用可能▲▲▲　*/ }



/* ▼▼▼送信完了画面のレイアウト　編集可 ※送信完了後に指定のページに移動しない場合のみ表示▼▼▼　*/
if (($jumpPage == 0 && $sendmail == 1) || ($jumpPage == 0 && ($confirmDsp == 0 && $sendmail == 0))) {
?>
	<!DOCTYPE HTML>
	<html lang="ja">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=640">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="format-detection" content="telephone=no">
		<meta name="description" content="">
		<meta name="keywords" content="">
		<title>ウィクリニック</title>
		<link rel="stylesheet" href="css/reset.css">
		<link rel="stylesheet" href="css/animate.wow.css">
		<link rel="stylesheet" href="css/common.css">
		<link rel="stylesheet" media="screen" type="text/css" href="css/validationEngine.jquery.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="css/jquery-ui.css" />
		<link rel="stylesheet" href="css/general.css">
		<link rel="stylesheet" href="css/index.css">

		<script src="js/jquery.js"></script>
		<script src="js/jquery.validationEngine.js"></script>
		<script src="js/jquery.validationEngine-ja.js"></script>
		<script src="js/jquery-ui.min.js"></script>
		<script src="js/jquery.ui.datepicker-ja.min.js"></script>

		<!-- Google Tag Manager -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-54WKRJT" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-NMV3QTM" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<script>
			(function(w, d, s, l, i) {
				w[l] = w[l] || [];
				w[l].push({
					"gtm.start": new Date().getTime(),
					event: "gtm.js"
				});
				var f = d.getElementsByTagName(s)[0],
					j = d.createElement(s);
				j.async = true;
				j.src = "https://www.googletagmanager.com/gtm.js?id=" + i + "&l=" + l;
				f.parentNode.insertBefore(j, f);
			})(window, document, "script", "dtLyr", "GTM-NMV3QTM");
		</script>
		<script>
			(function(w, d, s, l, i) {
				w[l] = w[l] || [];
				w[l].push({
					'gtm.start': new Date().getTime(),
					event: 'gtm.js'
				});
				var f = d.getElementsByTagName(s)[0],
					j = d.createElement(s),
					dl = l != 'dataLayer' ? '&l=' + l : '';
				j.async = true;
				j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
				f.parentNode.insertBefore(j, f);
			})(window, document, 'script', 'dataLayer', 'GTM-54WKRJT');
		</script>
		<script>
			window.dataLayer = window.dataLayer || [];

			function gtag() {
				dataLayer.push(arguments);
			}
			gtag('js', new Date());
			gtag('config', 'AW-758975358');
		</script>
		<script async src="https://www.googletagmanager.com/gtag/js?id=AW-758975358"></script>
		<!-- End Google Tag Manager -->

		<!-- Google Tag Manager -->
		<script>
			(function(w, d, s, l, i) {
				w[l] = w[l] || [];
				w[l].push({
					'gtm.start': new Date().getTime(),
					event: 'gtm.js'
				});
				var f = d.getElementsByTagName(s)[0],
					j = d.createElement(s),
					dl = l != 'dataLayer' ? '&l=' + l : '';
				j.async = true;
				j.src =
					'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
				f.parentNode.insertBefore(j, f);
			})(window, document, 'script', 'dataLayer', 'GTM-M5SLMSM');
		</script>
		<!-- End Google Tag Manager -->

	</head>


	<body onLoad="document.formName.submit();">

		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M5SLMSM" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->

		<!-- Facebook Pixel Code -->
		<script>
			! function(f, b, e, v, n, t, s) {
				if (f.fbq) return;
				n = f.fbq = function() {
					n.callMethod ?
						n.callMethod.apply(n, arguments) : n.queue.push(arguments)
				};
				if (!f._fbq) f._fbq = n;
				n.push = n;
				n.loaded = !0;
				n.version = '2.0';
				n.queue = [];
				t = b.createElement(e);
				t.async = !0;
				t.src = v;
				s = b.getElementsByTagName(e)[0];
				s.parentNode.insertBefore(t, s)
			}(window, document, 'script',
				'https://connect.facebook.net/en_US/fbevents.js');
			fbq('init', '1047686985403416');
			fbq('track', 'PageView');
		</script>
		<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=1047686985403416&ev=PageView&noscript=1" /></noscript>
		<!-- End Facebook Pixel Code -->

		<form action="<?= $thanksPage; ?>" method="post" name="formName">
			<input type="hidden" name="tokenID" value="<?php echo date("YmdHis"); ?>">
			<input type="hidden" name="pm1" value="<?php echo $_POST['pm1']; ?>">
			<input type="hidden" name="E-mail" value="<?php echo $_POST['E-mail']; ?>">
			<input type="hidden" name="電話番号" value="<?php echo $_POST['電話番号']; ?>">
			<input type="hidden" name="年齢" value="<?php echo $_POST['年齢']; ?>">
		</form>
	</body>

	</html>


	<?php
	/* ▲▲▲送信完了画面のレイアウト 編集可 ※送信完了後に指定のページに移動しない場合のみ表示▲▲▲　*/



}
/*確認画面無しの場合の表示、指定のページに移動する設定の場合、エラーチェックで問題が無ければ指定ページヘリダイレクト*/ else if (($jumpPage == 1 && $sendmail == 1) || $confirmDsp == 0) {
	if ($empty_flag == 1) { ?>

		<ul class="counselingNote">
			<li>入力にエラーがあります。下記をご確認の上「戻る」ボタンにて修正をお願い致します。</li>
		</ul>
		<div class="btnArea">
			<input type="button" value="前画面に戻る" class="firstSubmit" onclick="history.back()" />
		</div>

<?php
	} else {
		//tmpディレクトリから本番UPLOADへ保存
		if (!empty($_POST['imgdata'])) {
			imgProdUpload($_POST);
		}
		//CSV書き出し
		if ($csvmode == 1) {
			writeCSV($_POST, $upload_dir);
		}
		//指定ページへリダイレクト
		header("Location: " . $thanksPage);
	}
}

// 以下の変更は知識のある方のみ自己責任でお願いします。

//----------------------------------------------------------------------
//  関数定義(START)
//----------------------------------------------------------------------
function checkMail($str)
{
	$mailaddress_array = explode('@', $str);
	if (preg_match("/^[\.!#%&\-_0-9a-zA-Z\?\/\+]+\@[!#%&\-_0-9a-zA-Z]+(\.[!#%&\-_0-9a-zA-Z]+)+$/", "$str") && count($mailaddress_array) == 2) {
		return true;
	} else {
		return false;
	}
}
function h($string)
{
	global $encode;
	return htmlspecialchars($string, ENT_QUOTES, $encode);
}
function sanitize($arr)
{
	if (is_array($arr)) {
		return array_map('sanitize', $arr);
	}
	return str_replace("\0", "", $arr);
}
//Shift-JISの場合に誤変換文字の置換関数
function sjisReplace($arr, $encode)
{
	foreach ($arr as $key => $val) {
		$key = str_replace('＼', 'ー', $key);
		$resArray[$key] = $val;
	}
	return $resArray;
}


//送信メールにPOSTデータをセットする関数
function postToMail($arr)
{
	global $hankaku, $hankaku_array, $notMailDisp;
	$resArray = '';
	foreach ($arr as $key => $val) {
		$out = '';
		if (is_array($val)) {
			foreach ($val as $key02 => $item) {
				//連結項目の処理
				if (is_array($item)) {
					$out .= connect2val($item);
				} else {
					$out .= $item . ', ';
				}
			}
			$out = rtrim($out, ', ');
		} else {
			$out = $val;
		} //チェックボックス（配列）追記ここまで
		if (get_magic_quotes_gpc()) {
			$out = stripslashes($out);
		}

		//全角→半角変換
		if ($hankaku == 1) {
			$out = zenkaku2hankaku($key, $out, $hankaku_array);
		}

		$notMailDispCount = 0;
		foreach ($notMailDisp as $notMailDispVal) {
			if ($key == $notMailDispVal) {
				$notMailDispCount++;
			}
		}

		if ($notMailDispCount == 0) {
			if ($out != "confirm_submit" && $key != "httpReferer" && $key != "imgdata") {
				if ($out == '') {
					$resArray .= "【 " . h($key) . " 】未記入\n";
				} else {
					$resArray .= "【 " . h($key) . " 】 " . h($out) . "\n";
				}
			}
		}
	}
	return $resArray;
}
//確認画面の入力内容出力用関数
function confirmOutput($arr)
{
	global $hankaku, $hankaku_array, $useToken, $confirmDsp, $replaceStr, $notConfDisp;
	$html = '';
	foreach ($arr as $key => $val) {

		$out = '';
		if (is_array($val)) {
			foreach ($val as $key02 => $item) {
				//連結項目の処理
				if (is_array($item)) {
					$out .= connect2val($item);
				} else {
					$out .= $item . ', ';
				}
			}
			$out = rtrim($out, ', ');
		} else {
			$out = $val;
		} //チェックボックス（配列）追記ここまで
		if (get_magic_quotes_gpc()) {
			$out = stripslashes($out);
		}
		$out = nl2br(h($out)); //※追記 改行コードを<br>タグに変換
		$key = h($key);
		$out = str_replace($replaceStr['before'], $replaceStr['after'], $out); //機種依存文字の置換処理

		//全角→半角変換
		if ($hankaku == 1) {
			$out = zenkaku2hankaku($key, $out, $hankaku_array);
		}

		$notConfDispCount = 0;
		foreach ($notConfDisp as $notConfDispVal) {
			if ($key == $notConfDispVal) {
				$notConfDispCount++;
			}
		}

		if ($notConfDispCount > 0) {
			$html .= '<input type="hidden" name="' . $key . '" value="' . str_replace(array("<br />", "<br>"), "", $out) . '" />';
		} else {
			if ($out == '') {
				$html .= "<dt><p>" . $key . "</p></dt><dd><span>未記入";
				$html .= '<input type="hidden" name="' . $key . '" value="' . str_replace(array("<br />", "<br>"), "", $out) . '" />';
				$html .= "</span></dd></tr>\n";
			} else {
				$html .= "<dt><p>" . $key . "</p></dt><dd><span>" . $out;
				$html .= '<input type="hidden" name="' . $key . '" value="' . str_replace(array("<br />", "<br>"), "", $out) . '" />';
				$html .= "</span></dd></tr>\n";
			}
		}
	}
	//トークンをセット
	if ($useToken == 1 && $confirmDsp == 1) {
		$token = sha1(uniqid(mt_rand(), true));
		$_SESSION['mailform_token'] = $token;
		$html .= '<input type="hidden" name="mailform_token" value="' . $token . '" />';
	}

	return $html;
}

//送信データをCSVに書き出す関数
function writeCSV($arr, $filePath)
{
	global $csvname;

	$csv_data_label = '';
	foreach ($_POST as $key => $value) {
		$csv_data_label[] = $key;
	}

	if (file_exists($filePath . $csvname)) {
		$data_title = '';
	} else {
		$data_title = $csv_data_label;
		mb_convert_variables('Shift_JIS', 'UTF-8', $data_title); //文字コードをUTF-8からShiftJISに変更
		$title_csv = fopen($filePath . $csvname, 'a');
		fputcsv($title_csv, $data_title); //変換した配列をcsvファイルに書き込み実行
		fclose($title_csv); //csvファイルを閉じる
	}

	$ShiftJIS = $arr; //文字コードを変えるので、専用の配列を作成してコピー
	mb_convert_variables('Shift_JIS', 'UTF-8', $ShiftJIS); //文字コードをUTF-8からShiftJISに変更
	$csv = fopen($filePath . $csvname, 'a'); //csvファイルと書き込みモードを指定
	fputcsv($csv, $ShiftJIS); //変換した配列をcsvファイルに書き込み実行
	fclose($csv); //csvファイルを閉じる
}

//添付画像データをTMPディレクトリに保存する関数
function imgTmpUpload($tmpfile, $postname, $filetype)
{
	global $tmp_dir;
	$imgdata = "";

	//アップロード時にファイルをリネーム、文字化け防止でエンコーディング
	$upload_filename = $postname . '.' . $filetype;
	$upload_filename_mb = mb_convert_encoding($upload_filename, "UTF-8", "AUTO");
	$upload_res = move_uploaded_file($tmpfile, $tmp_dir . date('Y-m-d_His_') . $upload_filename_mb);

	$imgdata = date('Y-m-d_His_') . $upload_filename_mb;

	return $imgdata;
}
//TMPの画像データを本番のディレクトリに保存する関数
function imgProdUpload($arr)
{
	global $tmp_dir, $upload_dir;

	$repost_filename = $arr['imgdata'];
	$repost_filename_mb = mb_convert_encoding($repost_filename, "UTF-8", "AUTO");
	$upload_res = rename($tmp_dir . $repost_filename_mb, $upload_dir . $repost_filename_mb);
	if ($upload_res !== true) {
		$error[] = 'ファイルのアップロードに失敗しました。';
	}
	return $upload_res;
}


//全角→半角変換
function zenkaku2hankaku($key, $out, $hankaku_array)
{
	global $encode;
	if (is_array($hankaku_array) && function_exists('mb_convert_kana')) {
		foreach ($hankaku_array as $hankaku_array_val) {
			if ($key == $hankaku_array_val) {
				$out = mb_convert_kana($out, 'a', $encode);
			}
		}
	}
	return $out;
}
//配列連結の処理
function connect2val($arr)
{
	$out = '';
	foreach ($arr as $key => $val) {
		if ($key === 0 || $val == '') { //配列が未記入（0）、または内容が空のの場合には連結文字を付加しない（型まで調べる必要あり）
			$key = '';
			$val = '未選択';
		} elseif (strpos($key, "円") !== false && $val != '' && preg_match("/^[0-9]+$/", $val)) {
			$val = number_format($val); //金額の場合には3桁ごとにカンマを追加
		}
		$out .= $val . $key;
	}
	return $out;
}

//管理者宛送信メールヘッダ
function adminHeader($userMail, $post_mail, $BccMail, $to)
{
	$header = '';
	if ($userMail == 1 && !empty($post_mail)) {
		$header = "From: $post_mail\n";
		if ($BccMail != '') {
			$header .= "Bcc: $BccMail\n";
		}
		$header .= "Reply-To: " . $post_mail . "\n";
	} else {
		if ($BccMail != '') {
			$header = "Bcc: $BccMail\n";
		}
		$header .= "Reply-To: " . $to . "\n";
	}
	$header .= "Content-Type:text/plain;charset=iso-2022-jp\nX-Mailer: PHP/" . phpversion();
	return $header;
}
//管理者宛送信メールボディ
function mailToAdmin($arr, $subject, $mailFooterDsp, $mailSignature, $encode, $confirmDsp)
{
	$adminBody = "「" . $subject . "」ご確認ください。\n\n";
	$adminBody .= "＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n\n";
	$adminBody .= postToMail($arr); //POSTデータを関数からセット
	$adminBody .= "\n＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n";
	$adminBody .= "送信された日時：" . date("Y/m/d (D) H:i:s", time()) . "\n";
	$adminBody .= "送信者のIPアドレス：" . @$_SERVER["REMOTE_ADDR"] . "\n";
	$adminBody .= "送信者のホスト名：" . getHostByAddr(getenv('REMOTE_ADDR')) . "\n";
	if ($confirmDsp != 1) {
		$adminBody .= "問い合わせのページURL：" . @$_SERVER['HTTP_REFERER'] . "\n";
	} else {
		$adminBody .= "問い合わせのページURL：" . @$arr['httpReferer'] . "\n";
	}
	if ($mailFooterDsp == 1) $adminBody .= $mailSignature;
	return mb_convert_encoding($adminBody, "JIS", $encode);
}

//ユーザ宛送信メールヘッダ
function userHeader($refrom_name, $to, $encode)
{
	$reheader = "From: ";
	if (!empty($refrom_name)) {
		$default_internal_encode = mb_internal_encoding();
		if ($default_internal_encode != $encode) {
			mb_internal_encoding($encode);
		}
		$reheader .= mb_encode_mimeheader($refrom_name) . " <" . $to . ">\nReply-To: " . $to;
	} else {
		$reheader .= "$to\nReply-To: " . $to;
	}
	$reheader .= "\nContent-Type: text/plain;charset=iso-2022-jp\nX-Mailer: PHP/" . phpversion();
	return $reheader;
}
//ユーザ宛送信メールボディ
function mailToUser($arr, $dsp_name, $remail_text, $mailFooterDsp, $mailSignature, $encode)
{
	$userBody = '';
	if (isset($arr[$dsp_name])) $userBody = h($arr[$dsp_name]) . " 様\n";
	$userBody .= $remail_text;
	$userBody .= "\n＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n\n";
	$userBody .= postToMail($arr); //POSTデータを関数からセット
	$userBody .= "\n＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n\n";
	$userBody .= "送信日時：" . date("Y/m/d (D) H:i:s", time()) . "\n";
	if ($mailFooterDsp == 1) $userBody .= $mailSignature;
	return mb_convert_encoding($userBody, "JIS", $encode);
}
//必須チェック関数
function requireCheck($require)
{
	global $imgname;
	$res['errm'] = '';
	$res['empty_flag'] = 0;


	if (!empty($_FILES[$imgname]['tmp_name'])) {
		$_POST[$imgname] = $_FILES[$imgname]['name'];
	}

	foreach ($require as $requireVal) {
		$existsFalg = '';
		foreach ($_POST as $key => $val) {
			if ($key == $requireVal) {

				//連結指定の項目（配列）のための必須チェック
				if (is_array($val)) { //valueが配列だったとき
					$connectEmpty = 0;
					$allArrayEmpty = 0;
					$allArrayCount = 0;

					foreach ($val as $kk => $vv) { //value配列の中身を調べる
						if ($vv == '') { //配列の中身が空だった場合
							$allArrayEmpty++; //空カウントを追加
							$allArrayCount++; //配列の総数カウントを追加
						} else { //空でなかった時
							$allArrayCount++; //配列の総数カウントを追加
						}
						if (is_array($vv)) {
							foreach ($vv as $kk02 => $vv02) {
								if ($vv02 == '') {
									$connectEmpty++;
								}
							}
						}
					}

					if ($allArrayCount <= $allArrayEmpty) {
						$connectEmpty++; //配列の中身がすべて空だった場合は空白とみなしエラー処理する
					}
					if ($connectEmpty > 0) {
						$res['errm'] .= "<p class=\"error_messe\">【" . h($key) . "】は必須項目です。</p>\n";
						$res['empty_flag'] = 1;
					}
				}
				//デフォルト必須チェック
				elseif ($val == '') {
					$res['errm'] .= "<p class=\"error_messe\">【" . h($key) . "】は必須項目です。</p>\n";
					$res['empty_flag'] = 1;
				}

				$existsFalg = 1;
				break;
			}
		}
		if ($existsFalg != 1) {
			$res['errm'] .= "<p class=\"error_messe\">【" . $requireVal . "】が未選択です。</p>\n";
			$res['empty_flag'] = 1;
		}
	}

	return $res;
}
//リファラチェック
function refererCheck($Referer_check, $Referer_check_domain)
{
	if ($Referer_check == 1 && !empty($Referer_check_domain)) {
		if (strpos($_SERVER['HTTP_REFERER'], $Referer_check_domain) === false) {
			return exit('<p align="center">リファラチェックエラー。フォームページのドメインとこのファイルのドメインが一致しません</p>');
		}
	}
}
function copyright()
{
	echo '<a style="display:block;text-align:center;margin:15px 0;font-size:11px;color:#aaa;text-decoration:none" href="http://www.php-factory.net/" target="_blank">- PHP工房 -</a>';
}
//----------------------------------------------------------------------
//  関数定義(END)
//----------------------------------------------------------------------
?>
