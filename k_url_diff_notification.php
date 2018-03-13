<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
	
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

class k_url_diff_notification{
	protected $mail;
	private $settings;
	
	function __construct($settings){
		$this->settings = $settings;
		
		mb_language("japanese"); 
		mb_internal_encoding("UTF-8");
		$this->mail = new PHPMailer();
		$this->mail->isSMTP();
		$this->mail->Encoding = "7bit";
		$this->mail->CharSet = '"UTF-8"';
		$this->mail->Host = $settings['host'];
		$this->mail->Port = $settings['port'];
		$this->mail->SMTPAuth = $settings['smtpauth'];
		$this->mail->SMTPSecure =  $settings['smtpsecure'];
		$this->mail->Username = $settings['username'];
		$this->mail->Password = $settings['password'];
		$this->mail->From     = $settings['from']; 
		$this->mail->FromName = mb_encode_mimeheader($settings['fromname'],"ISO-2022-JP","UTF-8");
		$this->mail->AddAddress($settings['to']);
	}
	
	// システムメッセージの表示
	protected function message($message){
		if(!empty($message))
			fwrite(STDERR, $message);
	}
	
	// 取得したデータの処理
	protected function data_processing($data){
		$settings = $this->settings;
		
		$start	= mb_strpos($data, $settings['chousei_extract_start']);
		$end	= mb_strpos($data, $settings['chousei_extract_end']);

		$get_data = mb_substr($data, $start, $end - $start + strlen($settings['chousei_extract_end']), UTF8);

		// タグを除去（囲みがあるのもだけ <a href="">データ</a> から、データのみを抜き出す（JavaScriptコード除去）
		// $setteings['remove_tags'] = array('a'); などで指定。
		foreach ($settings['remove_tags'] as $tag):
			$pattern = '/<'.$tag.'.*?>(.*?)<\/'.$tag.'>/si';
			$get_data = preg_replace($pattern, "$1", $get_data);
		endforeach;
		
		// タグ内の特定文字の消去（<a href="javascript:hogehoge">データ</a> から、データのみを抜き出す（JavaScriptコード除去）		
		// $setteings['remove_attr'] = array('javascript:'); などで指定。
		foreach ($settings['remove_attr'] as $attr):
			$pattern = '/(["\'])'.$attr.'.*?(["\'])/si';
			$get_data = preg_replace($pattern, "$1$2", $get_data);
		endforeach;
		return $get_data;
	}
	
	// データの取得と処理（$file で指定された URLかファイル）
	protected function get_data($file){
		$data = array();
		$settings = $this->settings;

		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $file);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_TIMEOUT, $settings['timeout']);
		$html = curl_exec($ch);
		if($html !== FALSE):
			$data = $this->data_processing($html);
		else:
			$data = '';
			$this->message( curl_error($ch) . "\n" );
		endif;
		curl_close($ch);
		
		return $data;
	}
	
	// 全データの取得（取得データは $settings で指定）と処理
	// 返り値  $data = array[$settings['chousei_urls']のキー名][データ] 
	protected function get_data_all(){
		$data = array();
		$settings = $this->settings;
		if (isset($settings['chousei_urls'])):
			foreach($settings['chousei_urls'] as $key=>$value):
				$data[$key] = $this->get_data($value);
			endforeach;
		endif;
		
		return $data;
	}

	// 差分チェック（２つのファイルの比較） sha256 ハッシュ値でチェック
	// false = 違う, true = 同一
	public function diff_files($prev_file, $current_file){
		$settings = $this->settings;

		if(!is_file($current_file) || !is_file($prev_file)) return '';

		if (hash_file($settings['hash_algorithm'], $prev_file) === hash_file($settings['hash_algorithm'], $current_file)):
			return false;
		else:
			return true;
		endif;
	}

	// 差分チェック（前に保存したデータがあれば、今回取得したデータと比較する）
	// 比較は hash (sha256) でチェックする
	// 違う = 前のデータ, 同一 or 片方のデータが値なし = 空データ
	protected function diff($key, $current_data){
		$settings = $this->settings;

		if(empty($key) || empty($current_data)) return '';

		// 保存済みのデータ
		$file_name = $settings['prefix_save_filename'] . '_' . $key . '.html'; 
		$prev_data = file_get_contents($file_name);
		
		if(empty($prev_data)) return '';

		if (hash($settings['hash_algorithm'], $prev_data) === hash($settings['hash_algorithm'], $current_data)):
			return '';
		else:
			return $prev_data;
		endif;
	}

	// データをディスクに保存
	public function save_to_disk(){
		$settings = $this->settings;
		$data = $this->get_data_all();

		if (!isset($settings['chousei_urls']))
			return;

		foreach($settings['chousei_urls'] as $key=>$value):
			if(!empty($data[$key])):
				$file_name = $settings['prefix_save_filename'] . '_' . $key . '.html'; 
				// ファイルが存在するときには、差分をチェックして通知をだすかどうかの処理を行う
				if(is_file($file_name)):
					$this->message($settings['dmesg']['file_exist'] . "\n");
					// 差分あり！
					if(!empty($prev_data = $this->diff($key,$data[$key]))):
						$this->message($settings['dmesg']['diffed'] . "\n");
						$data_c = "<h2>". $settings['dmesg']['prev_data'] . "</h2> \n";
						$data_c .= $prev_data;
						$data_c .= "<h2>". $settings['dmesg']['current_data'] . "</h2> \n";
						$data_c .= $data[$key];
					//	echo $data_c;
						$this->sendmail($key,$data_c);
					endif;
				endif;
				// 上書きする（とりあえずそのまま保存。いずれは JSONで保存するのをデフォルトにしたい）
				file_put_contents($file_name, $data[$key]);
				$this->message( $settings['dmesg']['saved'] . " : ". $file_name . "\n");
			endif;
		endforeach;
	}
	
	// １サイトごとのメール送信
	// $key = $settings['chousei_urls']のキー名（件名に利用）
	protected function sendmail($key, $data){
		$settings = $this->settings;

		if(empty($data) || empty($key)) return false;
		
		$this->mail->Subject  = mb_encode_mimeheader($settings['subject'] . ' - ' . $key,"ISO-2022-JP", "UTF-8");
		$this->mail->msgHTML(mb_convert_encoding($data,"UTF-8","auto"));
		
		if ($settings['enable_email_send'] === true):
			if (!$this->mail->send()):
				$this->message( $settings['subject'] . ' - ' . $key . " : " . $settings['dmesg']['send_error'] . $this->mail->ErrorInfo );
			else:
				$this->message( $settings['subject'] . ' - ' . $key . " : " . $settings['dmesg']['send_ok']  . "\n" );
			endif;
		else:
			var_dump( $data );
		endif;
		
		return true;
	}
	
	// 全データのメール送信
	public function sendmail_all(){
		$settings = $this->settings;
		$data = $this->get_data_all();

		if (!isset($settings['chousei_urls']))
			return;
		
		foreach($settings['chousei_urls'] as $key=>$value):
			if(!empty($data[$key])):
				$this->sendmail($key, $data[$key]);
			else:
				$this->message( $settings['subject'] . ' - ' . $key . " : " . $settings['dmesg']['get_url_data_error'] . "\n" );
			endif;
		endforeach;
	}
}
