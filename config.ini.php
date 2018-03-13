<?php 
$settings = array(
	/**
	*** データ取得設定
	**/
	// タイムアウト設定（3秒）
	'timeout'	=> 3,
	// 'キー名' => 'URL' の形式で入力。下記は「調整さん」での例
	'chousei_urls' => array(
		'テスト'		=> 'https://chouseisan.com/s?h=◯◯',
//		'テスト２'		=> 'https://chouseisan.com/s?h=△△',
	),
	// 取り出し先頭パターン（調整さんでの例）
	'chousei_extract_start'	=>	'<table class="schedule" id="nittei">',
	// 取り出し終了パターン（調整さんでの例）
	'chousei_extract_end'	=>	'</table>',
	// タグを消去（'a', 'tr' だと <a>データ</a>と <tr>データ</tr> から、「データ」のみを抽出（複数行対応））
	//（調整さんでの例）
	'remove_tags'			=>	array(
		'a',
	),
	// タグ内コードの消去（'javascript:' だと、<a href="javascript: hogehoge();">データ</a>だと、<a href="">データ</a> となる（複数行対応）
	//（調整さんでの例）
	'remove_attr'			=>	array(
		'javascript:',
	),
	/**
	*** Email 設定
	**/
	// true = メール送信, false = メール送信しない（var_dump でデータを出力 / デバッグ用）
	// Gmailのサンプル

	'enable_email_send'	=> true,
	'host'			=> 'smtp.gmail.com',
	'port'			=> 587,
	'smtpauth'		=> true,
	'smtpsecure'	=> 'tls',
	'username'		=> '◯◯@gmail.com', 
	// ※１は、２段階認証有効なら「アプリ固有のパスワード」を生成してそれをいれる
	// 無効なら「安全性の低いアプリからのアクセスを許可」しておく
	'password'		=> '◯◯◯',
	'from'			=> '◯◯@gmail.com',
	// 表示名（表示名 <メールアドレス>）
	'fromname'		=> '◯◯',
	// 件名 = 件名 - キー名
	'subject'		=> '件名',
	'to'			=> '宛先',
	/**
	*** 表示メッセージ
	**/
	"dmesg"=> array(
		'send_ok'				=> '送信しました',
		'send_error'			=> '送信エラー',
		'get_url_data_error'	=> 'データの取得失敗！',
		'file_exist'			=> 'ファイルがあるよ！',
		'saved'					=> '保存したよ！',
		'diffed'				=> '前のデータから差分あるよ！',
		'prev_data'				=> '変更前のデータ',
		'current_data'			=> '現在のデータ',
	),
	/**
	*** データ保存
	**/
	// ファイルの接頭文字（save_[chousei_urlsのキー名].html）
	'prefix_save_filename'	=> 'save',
	/**
	*** データ比較
	**/
	'hash_algorithm'		=> 'sha256',

);