<?php
//###############################################################################################//

/**
 * phpサイト設定ファイル
 *
 * @copyright  Copyright (c) 2019 Kwin Inc.
 * @since      2011
 * @auther     miyazawa
 */

//###############################################################################################//
/*
** 内部エンコーディングを設定
*/

//内部エンコーディングはutf-8に設定
mb_internal_encoding("UTF-8");

//###############################################################################################//
/*
** 各種ファイルパスを設定
*/

//ドキュメントルート設定(ドキュメントルート直下の時は「''」、そうでないときは「'/hogehoge'」と記述)
define('_SITE_DOCUMENT_ROOT', '');

 //ルート絶対パス
define('_SERVER_ROOT_DIR', $_SERVER['DOCUMENT_ROOT'].'/'); // [ /var/www/html/example/ ]

//ルート絶対URL
define('_WEB_ROOT_DIR', _SITE_DOCUMENT_ROOT == '' ? '/' : _SITE_DOCUMENT_ROOT); // [ / ]
define('_WEB_ROOT_FULL_DIR', (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['SERVER_NAME'] . _SITE_DOCUMENT_ROOT); // [ http://www.example.com ]

//現階層の絶対URL（CSSなどスクリプトファイル読み込み用）
$now_file = basename($_SERVER["SCRIPT_FILENAME"]); // [index.php]
$web_now_file = str_replace(_SITE_DOCUMENT_ROOT,'',$_SERVER["PHP_SELF"]); // [ /about/menu/index.php ]
$web_now_dir  = preg_replace("/$now_file/", '', $web_now_file); // [ /about/menu/ ]
$web_now_full_file = _WEB_ROOT_FULL_DIR . $web_now_file;  // [http://www.example.com/about/menu/index.php]
$web_now_full_dir  = _WEB_ROOT_FULL_DIR . $web_now_dir; //[http://www.example.com/about/menu/]

//インクルードファイルのパス
define('_INCLUDE_FILE_DIR', _SERVER_ROOT_DIR . 'include/');


//###############################################################################################//

/*アップロードディレクトリ設定*/
define('TMP_DIR', '');
define('UPLOAD_DIR', '');

//###############################################################################################//

/** お問い合わせが届く管理者のメールアドレス（完了メールの送信先[複数可]）*/
define('ADMIN_ADDRESS', 'info@wi-clinic.com');
/** お問い合わせメールの件名*/
define('MAIL_SUBJECT', '脱毛LPよりお問い合わせがありました');

/** 自動返信メールの送信元アドレス */
define('REPLY_FROM_ADDRESS', 'info@wi-clinic.com');
/** 自動返信メールの件名 */
define('REPLY_DISP_SUBJECT', 'お問い合わせありがとうございました');

/** メール送信でエラーがあった際に返信されてくるアドレス */
define('REPLY_ADDRESS', '/');


//###############################################################################################//
/*
** 変数確認用
*/

/*
echo('<pre>');
var_dump(_SITE_DOCUMENT_ROOT);
echo('</pre>');

echo('<pre>');
var_dump(_SERVER_ROOT_DIR);
echo('</pre>');

echo('<pre>');
var_dump(_WEB_ROOT_DIR);
echo('</pre>');

echo('<pre>');
var_dump(_WEB_ROOT_FULL_DIR);
echo('</pre>');

echo('<pre>');
var_dump(TMP_DIR);
echo('</pre>');

echo('<pre>');
var_dump(UPLOAD_DIR);
echo('</pre>');


echo('<pre>');
var_dump($now_file);
echo('</pre>');

echo('<pre>');
var_dump($web_now_file);
echo('</pre>');

echo('<pre>');
var_dump($web_now_dir);
echo('</pre>');

echo('<pre>');
var_dump($web_now_full_file);
echo('</pre>');

echo('<pre>');
var_dump($web_now_full_dir);
echo('</pre>');
*/


//###############################################################################################//
?>
