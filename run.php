<?php

require 'k_url_diff_notification.php';
require 'config.ini.php'; 

$s = new k_url_diff_notification($settings);

// 各 URLごとにメールする
//$s->sendmail_all();
// 各 URLごとに保存（[prefix_save_filenameの文字列]_キー名.html で保存）
// 前回保存データがあり、差分があるならメールで通知
$s->save_to_disk();
