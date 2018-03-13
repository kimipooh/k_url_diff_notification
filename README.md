# k_url_diff_notification
このツールは、前回の保存データと比較してサイト（URL）に差分があれば、メール通知することを目的にしています。サイト（URL）データの一部を保存したり、HTMLメールで送信することも可能です。

なお、デフォルトでは、調整さん（ https://chouseisan.com/ ）を例に Gmailに通知を送るように調整しています。

## 動作環境

- mbstring モジュールの入った PHP 7.0以降（7.2でも動作確認済）
- PHPMailer 6.0 以降

で動作します。

## インストール方法

macOS 10.13.3 での事例を紹介します。
仮にデスクトップに「k_url_diff_notification」というフォルダを作成して、そこの中にいれると仮定します。


1. k_url_diff_notification.php と config.inc.php をダウンロードします。
2. https://github.com/Synchro/PHPMailer より、PHPMailer をダウンロードします。
3. 下記のフォルダ構成になるように、ダウンロードしたファイルやフォルダを移動します。

	k_url_diff_notification
	┣ k_url_diff_notification.php
	┣ config.ini.php
	┣ run.php
	┗ PHPMailer（フォルダ）

となっているはずです。

## config.ini.php を編集

「調整さん」で「Gmail」でよいなら、変数箇所は、

	URL：chousei_urls

送信メール設定

	username = Gmailアドレス
	passowrd = パスワード（※１）
	from = 差出人アドレス
	fromname = 差出人表示名
	subject = 件名
	to = 宛先

	※１
	２段階認証有効なら「アプリ固有のパスワード」を生成してそれをいれる
	無効なら「安全性の低いアプリからのアクセスを許可」しておく

の部分のみになります。まぁこの手のツールを使うなら、アプリ固有のパスワードを一時利用するのをオススメ。パスワードも捨てパスワードを使えるため。


## run.php の編集

	$s->save_to_disk();
	$s->sendmail_all();

のいずれかをコメントしてください（下記参照）。

	// 各 URLごとにメールする
	//$s->sendmail_all();
	// 各 URLごとに保存（[prefix_save_filenameの文字列]_キー名.html で保存）
	// 前回保存データがあり、差分があるならメールで通知
	$s->save_to_disk();

config.ini.php の

	'chousei_urls' => array(
		'テスト'			=> 'https://chouseisan.com/s?◯◯'
	),
	'prefix_save_filename'	=> 'save',

の２つの設定にしているなら、
$HOME/Desktop/k_url_diff_notification の「save_テスト.html」に、調整さんのデータで必要な部分のみ（table タグ かつ javascriptコード除外）が保存されます。


## 利用方法

ターミナルより

	cd $HOME/Desktop
	cd k_url_diff_notification
	php run.php

で動作します。Warning （utf-8絡み）が出ますが、無視でOKです。

調整さんデータ

<img src="https://user-images.githubusercontent.com/4168939/37337827-cbad3fb6-26f8-11e8-890f-7ee6c6eca7ef.png" width="50%">

調整さん差分データのメール通知

<img src="https://user-images.githubusercontent.com/4168939/37337829-cdd03f0a-26f8-11e8-97aa-6aa087f34444.png" width="50%">

## 応用編

php run.php を cron 等で定期的に実行させておけば、もしデータに変更があれば変更前と変更後のデータを１つのメールで通知してくれますよ！

