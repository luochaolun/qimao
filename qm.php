<?php
define("SIGNKEY","d3dGiJc651gSQ8w1");

function getQmHeaders($signStr){
	$headers = [
		'AUTHORIZATION;',
		'app-version: 70720',
		'application-id: com.****.reader',
		'channel: unknown',
		'net-env: 1',
		'platform: android',
		'qm-params;',
		'reg: 0',
		'sign: ' . $signStr
	];

	return $headers;
}

function getQmSearchList($keyword, $page=1) {
	$page = @intval($page) < 1 ? 1 : @intval($page);
	$keywords = urlencode($keyword);
	$sign = md5("page=${page}wd=${keyword}".SIGNKEY);
	$signStr = md5("AUTHORIZATION=app-version=70720application-id=com.****.readerchannel=unknownnet-env=1platform=androidqm-params=reg=0".SIGNKEY);
	$url = "https://api-bc.wtzw.com/api/v5/search/words?page=${page}&wd=${keywords}&sign=${sign}";
	//echo $signStr;exit();
	$headers = getQmHeaders($signStr);

	$curl = curl_init();
	curl_setopt_array($curl, array(
	   CURLOPT_URL => $url,
	   CURLOPT_RETURNTRANSFER => true,
	   CURLOPT_ENCODING => '',
	   CURLOPT_MAXREDIRS => 10,
	   CURLOPT_TIMEOUT => 0,
	   CURLOPT_FOLLOWLOCATION => true,
	   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	   CURLOPT_SSL_VERIFYPEER => false,
	   CURLOPT_SSL_VERIFYHOST => false,
	   CURLOPT_CUSTOMREQUEST => 'GET',
	   CURLOPT_HTTPHEADER => $headers,
	));

	$html = curl_exec($curl);
	curl_close($curl);

	return $html;
}

function getQmBookDetail($bid) {
	$sign = md5("id=${bid}imei_ip=3684466020teeny_mode=0".SIGNKEY);
	$signStr = md5("AUTHORIZATION=app-version=70720application-id=com.****.readerchannel=unknownnet-env=1platform=androidqm-params=reg=0".SIGNKEY);
	$url = "https://api-bc.wtzw.com/api/v4/book/detail?id=${bid}&imei_ip=3684466020&teeny_mode=0&sign=${sign}";
	$headers = getQmHeaders($signStr);

	$curl = curl_init();
	curl_setopt_array($curl, array(
	   CURLOPT_URL => $url,
	   CURLOPT_RETURNTRANSFER => true,
	   CURLOPT_ENCODING => '',
	   CURLOPT_MAXREDIRS => 10,
	   CURLOPT_TIMEOUT => 0,
	   CURLOPT_FOLLOWLOCATION => true,
	   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	   CURLOPT_SSL_VERIFYPEER => false,
	   CURLOPT_SSL_VERIFYHOST => false,
	   CURLOPT_CUSTOMREQUEST => 'GET',
	   CURLOPT_HTTPHEADER => $headers,
	));

	$html = curl_exec($curl);
	curl_close($curl);

	return $html;
}

function getQmBookChapterList($bid) {
	$sign = md5("id=${bid}".SIGNKEY);
	$signStr = md5("AUTHORIZATION=app-version=70720application-id=com.****.readerchannel=unknownnet-env=1platform=androidqm-params=reg=0".SIGNKEY);
	$url = "https://api-ks.wtzw.com/api/v1/chapter/chapter-list?id=${bid}&sign=${sign}";
	$headers = getQmHeaders($signStr);

	$curl = curl_init();
	curl_setopt_array($curl, array(
	   CURLOPT_URL => $url,
	   CURLOPT_RETURNTRANSFER => true,
	   CURLOPT_ENCODING => '',
	   CURLOPT_MAXREDIRS => 10,
	   CURLOPT_TIMEOUT => 0,
	   CURLOPT_FOLLOWLOCATION => true,
	   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	   CURLOPT_SSL_VERIFYPEER => false,
	   CURLOPT_SSL_VERIFYHOST => false,
	   CURLOPT_CUSTOMREQUEST => 'GET',
	   CURLOPT_HTTPHEADER => $headers,
	));

	$html = curl_exec($curl);
	curl_close($curl);

	return $html;
}

function getQmBookContEncode($bid, $cid){
	$sign = md5("chapterId=${cid}id=${bid}".SIGNKEY);
	$signStr = md5("AUTHORIZATION=app-version=70720application-id=com.****.readerchannel=unknownnet-env=1platform=androidqm-params=reg=0".SIGNKEY);
	$url = "https://api-ks.wtzw.com/api/v1/chapter/content?id=${bid}&chapterId=${cid}&sign=${sign}";
	$headers = getQmHeaders($signStr);

	$curl = curl_init();
	curl_setopt_array($curl, array(
	   CURLOPT_URL => $url,
	   CURLOPT_RETURNTRANSFER => true,
	   CURLOPT_ENCODING => '',
	   CURLOPT_MAXREDIRS => 10,
	   CURLOPT_TIMEOUT => 0,
	   CURLOPT_FOLLOWLOCATION => true,
	   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	   CURLOPT_SSL_VERIFYPEER => false,
	   CURLOPT_SSL_VERIFYHOST => false,
	   CURLOPT_CUSTOMREQUEST => 'GET',
	   CURLOPT_HTTPHEADER => $headers,
	));

	$html = curl_exec($curl);
	curl_close($curl);

	return $html;
}

function decrypt($data, $iv) {
	$iv = hex2bin($iv);
	$key = hex2bin('32343263636238323330643730396531');
	$ret = openssl_decrypt(hex2bin($data), 'aes-128-cbc-hmac-sha256', $key, OPENSSL_RAW_DATA, $iv);

	return str_replace("\n", "<br>", trim($ret));
}

function getQmBookContDecode($bid, $cid) {
	$html = getQmBookContEncode($bid, $cid);
	$jsonArr = json_decode($html, true);
	$encodeStr = $jsonArr['data']['content'];

	$txt = base64_decode($encodeStr);
	$iv = bin2hex(substr($txt, 0, 16)); //将二进制数据转换为十六进制表示
	$data = bin2hex(substr($txt, 16));
	$decodeTxt = decrypt($data, $iv);

	return $decodeTxt;
}

print_r(json_decode(getQmSearchList("世子先别死", 2), true));exit();

$bid = "1830570";
//echo getQmBookDetail($bid);exit();
//echo getQmBookChapterList($bid);exit();
$cid = "17157528430001";
$decodeTxt = getQmBookContDecode($bid, $cid);
echo $decodeTxt;
?>