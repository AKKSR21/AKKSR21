<?php
/*
【Lunalys かんたん設置／更新】
・Lunalys を設置するサーバーに setup.php を置いてください
・ブラウザで setup.php にアクセスしてください
・インストール／アップデートを実行ボタンを押してください
・完了後、自動的に Lunalys の管理画面に遷移します
・管理画面のバージョン情報からアップデート可能です
・もし設置できないサーバーがありましたら教えてください
・当方で試して改善可能なら改善します

【設置例】
https://example.com/setup.php ←これを実行すると
https://example.com/lunalys/  ←こうなります
https://example.com/lunalys/analyzer/ ←管理画面はこちら

【公式サイト】
https://yuh-nagomi.jp/lunalys/
*/
////////////////////////////////////////////////////////////

// エラー出力設定を定義
ini_set('error_reporting', E_ALL);
ini_set('display_errors' , 1);

// メイン処理実行
Setup::execute();

////////////////////////////////////////////////////////////

// クラスを定義
class Setup {
	
	//---------------------------------------------------------
	//  メイン処理
	//---------------------------------------------------------
	
	public static function execute() {
		
		// 【認証可否分岐】
		// ・未設置     → なし
		// ・PW設定なし → なし
		// ・PW設定あり＆Cookieあり → なし
		// ・PW設定あり＆Cookieなし → あり
		// （ログイン画面へリダイレクト）
		
		////////////////////////////////////////////////////////////
		
		// Cookieが存在するか確認
		$admin = (isset($_COOKIE['lunalysLogin']));
		
		// 設置済か確認
		$exists = file_exists('lunalys');
		
		////////////////////////////////////////////////////////////
		
		// 設置済＆Cookieなしの時
		if ($exists && !$admin) {
			
			// iniファイルのパスを設定
			$confFile = 'lunalys/analyzer/config.ini';
			
			// iniファイルが存在する時
			if (file_exists($confFile)) {
				
				// 設定iniファイルを解析
				$config = parse_ini_file($confFile, true);
				
				// ID&PWが設定済の時はログイン画面へ遷移
				if ($config['login']['username'] && $config['login']['username']) {
					self::jump('lunalys/analyzer/');
				}
				
			}
			
		}
		
		////////////////////////////////////////////////////////////
		
		// 最新バージョンファイル
		$verFile0 = 'https://yuh-nagomi.jp/lunalys/api/latest_ver.txt';
		
		// 最新バージョンを取得
		$latest = self::getFile($verFile0);
		
		////////////////////////////////////////////////////////////
		
		// POSTメソッドの時
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			
			// setup.php 削除フラグを取得
			$del = (int) ($_POST['del'] ?? 0);
			
			// 設置先ディレクトリを取得
			$dir = $_POST['dir'] ?: 'lunalys';
			
			// 設置／更新を実行
			self::update($latest, $dir, $exists);
			
			// setup.phpを削除
			if ($del) {
				unlink('setup.php');
			}
			
			// 管理画面に遷移
			self::jump("$dir/analyzer/");
			
		}
		
		// GETメソッドの時
		else {
			
			// アクションを取得
			$act = (int) ($_GET['act'] ?? 0);
			
			// リンクからURL遷移の時
			if ($act === 1) {
				self::jump('https://yuh-nagomi.jp/lunalys/');
			}
			
		}
		
		////////////////////////////////////////////////////////////
		
		// 設置済の時
		if ($exists) {
			
			// ボタンのラベルを設定
			$button = 'アップデートを実行';
			
			// iniファイルのパスを設定
			$verFile1 = 'lunalys/analyzer/templates/ini/version.ini';// 旧
			$verFile2 = 'lunalys/analyzer/views/ini/version.ini';    // 新
			
			// 使うiniファイルを設定
			$verFile = file_exists($verFile2) ? $verFile2 : $verFile1;
			
			// スクリプトバージョンファイルを解析
			$version = parse_ini_file($verFile);
			
			// 現行バージョン
			$current = $version['version'];
			
			// ボタンの無効化を設定
			$disabled = version_compare($current, $latest, '<') ? '' : 'disabled';
			
		}
		
		// 未設置の時
		else {
			
			// ボタンのラベルを設定
			$button = 'インストールを実行';
			
			// 現行バージョン
			$current = '未設置';
			
			// ボタンの無効化を設定
			$disabled = '';
			
		}
		
		////////////////////////////////////////////////////////////
		
		// URLパスディレクトリを設定
		$pathDir = 'https://yuh-nagomi.jp/lunalys/api';
		
		// 現在の年数を取得
		$year = gmdate('Y', $_SERVER['REQUEST_TIME']);
		
		// HTMLを出力
		echo <<<_HTML_
<!DOCTYPE html>
<html lang="ja">
	<head>
		<meta charset="UTF-8">
		<meta name="robots" content="noindex, nofollow, noarchive">
		<meta name="format-detection" content="telephone=no, address=no, email=no">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" media="(min-width: 960px)" href="$pathDir/css/style_pc.css?update=2026_04_15">
		<link rel="stylesheet" media="(max-width: 959px)" href="$pathDir/css/style_sp.css?update=2026_04_15">
		<link rel="stylesheet" href="$pathDir/css/setup.css?update=2026_04_15">
		<title>Setup</title>
	</head>
	<body>
		<main>
			<form action="setup.php" method="post" name="post">
				<h2>【Lunalys かんたん設置／更新】</h2>
				<p class="text_p">現行バージョン：$current</p>
				<p class="text_p">最新バージョン：$latest</p>
				<p class="text_p">ディレクトリ　：<input type="text" class="text" name="dir" value="lunalys" required></p>
				<p class="check_p"><label><input type="checkbox" class="check" name="del" value="1" checked>実行後に setup.php を削除</label></p>
				<p class="submit_p">
					<button class="submit" name="button" value="1" $disabled>$button</button>
				</p>
			</form>
		</main>
	</body>
</html>
_HTML_;
		
	}
	
	//---------------------------------------------------------
	//  URL遷移
	//---------------------------------------------------------
	
	private static function jump(string $url): void {
		
		// ジャンプ用ヘッダーを出力（リファラを残さない）
		header("Refresh: 0; URL=$url");
		
		// 終了
		exit();
		
	}
	
	//---------------------------------------------------------
	//  設置／更新を実行
	//---------------------------------------------------------
	
	private static function update(
			string $latest, 
			string $dir, 
			bool $exists
		): bool {
		
		// 読み込むファイル名（URL）
		$loadFile = 'https://yuh-nagomi.jp/lunalys/res/dl/lunalys.' . $latest;
		
		// 保存するファイル名
		$saveFile = 'lunalys.zip';
		
		// ファイルを読み込み
		$fileData = self::getFile($loadFile);
		
		// 読み込んだデータをファイルに保存
		file_put_contents($saveFile, $fileData);
		
		// 保存したファイルの絶対パスを取得
		$filePath = getcwd() . '/' . $saveFile;
		
		// 保存に失敗した時はfalseを返す
		if (!is_file($filePath)) {
			return false;
		}
		
		// zipファイルを解凍
		$output = shell_exec("unzip -o $filePath");
		
		// zipファイルを削除
		unlink($saveFile);
		
		// 設置先が「lunalys」でない時
		if ($dir !== 'lunalys') {
			
			// ディレクトリをマージ lunalys → 設置先
			shell_exec("cp -r lunalys/. $dir/");
			
			// lunalys ディレクトリを削除
			if (!$exists) {
				shell_exec("rm -r lunalys");
			}
			
		}
		
		// trueを返す
		return true;
		
	}
	
	//---------------------------------------------------------
	//  WebページのHTMLを取得（curl）
	//---------------------------------------------------------
	
	private static function getFile(string $url): mixed {
		
		// IPアドレスを取得
		$ipAddress = $_SERVER['REMOTE_ADDR'];
		
		// プロトコルを取得
		$protocol = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') ? 'http' : 'https';
		
		// 絶対URLに変換
		$requestUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		
		// HTTPヘッダー配列を設定
		$headers = [
			'From: ' . $ipAddress,
			'Referer: ' . $requestUrl,
			'Accept-language: ja',
			'User-Agent: Lunalys',
		];
		
		// オプション配列を設定
		$options = [
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_SSL_VERIFYPEER => false,// 証明書をチェックしない
			CURLOPT_SSL_VERIFYHOST => false,// 証明書をチェックしない
			CURLOPT_RETURNTRANSFER => true, // レスポンスを変数で受け取る
			CURLOPT_FAILONERROR    => true, // リダイレクトを許容する
			CURLOPT_TIMEOUT        => 10,   // タイムアウトを設定
		];
		
		// cURLハンドルを作成
		$ch = curl_init();
		
		// オプションを設定
		curl_setopt_array($ch, $options);
		
		// リクエストを実行してHTMLを取得
		$result = curl_exec($ch);
		
		// ハンドルを閉じる
		curl_close($ch);
		
		// HTMLを返す
		return $result ?: self::getFileClassic($url);
		
	}
	
	//---------------------------------------------------------
	//  WebページのHTMLを取得（file_get_contents）
	//---------------------------------------------------------
	
	private static function getFileClassic(string $url): mixed {
		
		// 基本情報を設定
		$stream['http'] = [
			'method' => 'GET', 
			'header' => "User-Agent: Lunalys\r\n", 
		];
		
		// HTTPS用追加設定
		$stream['ssl'] = [
			'verify_peer' => false, 
			'verify_peer_name' => false, 
		];
		
		// ストリームコンテキストを生成
		$context = stream_context_create($stream);
		
		// HTMLを読み込み
		return @file_get_contents($url, false, $context);
		
	}
	
}

