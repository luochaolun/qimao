<?php
define("SIGNKEY","d3dGiJc651gSQ8w1");

class QiMao {
	private $headers = [];

	public function __construct() {
		$this->headers = $this->getQmHeaders();
	}

	private function getQmHeaders(){
		$signStr = md5("AUTHORIZATION=app-version=70720application-id=com.****.readerchannel=unknownnet-env=1platform=androidqm-params=reg=0".SIGNKEY);
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

	private function QmHttpGet($url) {
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
		   CURLOPT_HTTPHEADER => $this->headers,
		));

		$html = curl_exec($curl);
		curl_close($curl);

		return $html;
	}

	// $tab girl,boy,publish
	function getQmAllCategory($tab='publish'){
		$sign = md5("cache_ver=0gender=2tab_type=${tab}type=category_all".SIGNKEY);
		$url = "https://api-bc.wtzw.com/api/v4/category-rank/index?type=category_all&gender=2&tab_type=${tab}&cache_ver=0&sign=${sign}";
		$html = $this->QmHttpGet($url);

		return $html;
	}

	function getQmCategoryList($gid=2, $cid=1, $page=1) {
		$page = @intval($page) < 1 ? 1 : @intval($page);
		$keywords = urlencode($keyword);
		$sign = md5("category_id=${cid}gender=${gid}need_category=1need_filters=0over=-99page=${page}sort=0words=-99".SIGNKEY);
		$url = "https://api-bc.wtzw.com/api/v4/category/get-list?need_filters=0&need_category=1&words=-99&over=-99&gender=${gid}&category_id=${cid}&sort=0&page=${page}&sign=${sign}";
		$html = $this->QmHttpGet($url);

		return $html;
	}

	function getQmSearchList($keyword, $page=1) {
		$page = @intval($page) < 1 ? 1 : @intval($page);
		$keywords = urlencode($keyword);
		$sign = md5("page=${page}wd=${keyword}".SIGNKEY);
		$url = "https://api-bc.wtzw.com/api/v5/search/words?page=${page}&wd=${keywords}&sign=${sign}";
		$html = $this->QmHttpGet($url);

		return $html;
	}

	function getQmSearchArr($keyword, $page=1) {
		$searchArr = json_decode($this->getQmSearchList($keyword, $page), true);
		$retArr = [
			'page' => $searchArr['data']['meta']['page'] + 1,
			'total_page' => $searchArr['data']['meta']['total_page']
			];
		$retArr['books'] = array_map(function($item) {
			$bookArr = ['id' => $item['id'], 'title' => $item['original_title']];
			unset($item);
			return $bookArr;
		}, $searchArr['data']['books']);

		return $retArr;
	}

	function getQmBookDetail($bid) {
		$sign = md5("id=${bid}imei_ip=3684466020teeny_mode=0".SIGNKEY);
		$url = "https://api-bc.wtzw.com/api/v4/book/detail?id=${bid}&imei_ip=3684466020&teeny_mode=0&sign=${sign}";
		$html = $this->QmHttpGet($url);

		return $html;
	}

	function getBookArr($bid) {
		$bookArr = json_decode($this->getQmBookDetail($bid), true)['data']['book'];
		$retArr = [
			'id' => $bookArr['id'],
			'title' => $bookArr['title'],
			'first_chapter_id' => $bookArr['first_chapter_id'],
			'first_chapter_title' => $bookArr['first_chapter_title'],
			'latest_chapter_id' => $bookArr['latest_chapter_id'],
			'latest_chapter_title' => $bookArr['latest_chapter_title']
			];

		return $retArr;
	}

	function getQmBookChapterList($bid) {
		$sign = md5("id=${bid}".SIGNKEY);
		$url = "https://api-ks.wtzw.com/api/v1/chapter/chapter-list?id=${bid}&sign=${sign}";
		$html = $this->QmHttpGet($url);

		return $html;
	}

	function getChapterArr($bid) {
		$chapterArr = json_decode($this->getQmBookChapterList($bid), true)['data'];

		$retArr['bookId'] = $chapterArr['id'];
		$retArr['bookName'] = $this->getBookArr($bid)['title'];
		$retArr['chapterLists'] = array_map(function($item) {
			unset($item['content_md5']);
			unset($item['index']);
			unset($item['words']);
			unset($item['chapter_sort']);
			return $item;
		}, $chapterArr['chapter_lists']);

		return $retArr;
	}

	function getQmBookDetailEncode($bid, $cid){
		$sign = md5("chapterId=${cid}id=${bid}".SIGNKEY);
		$url = "https://api-ks.wtzw.com/api/v1/chapter/content?id=${bid}&chapterId=${cid}&sign=${sign}";
		$html = $this->QmHttpGet($url);

		return $html;
	}

	function decrypt($data, $iv) {
		$iv = hex2bin($iv);
		$key = hex2bin('32343263636238323330643730396531');
		$ret = openssl_decrypt(hex2bin($data), 'aes-128-cbc-hmac-sha256', $key, OPENSSL_RAW_DATA, $iv);

		return str_replace("\n", "<br>", trim($ret));
	}

	function getQmBookDetailDecode($bid, $cid) {
		$html = $this->getQmBookDetailEncode($bid, $cid);
		$jsonArr = json_decode($html, true);
		$encodeStr = $jsonArr['data']['content'];

		$txt = base64_decode($encodeStr);
		$iv = bin2hex(substr($txt, 0, 16)); //将二进制数据转换为十六进制表示
		$data = bin2hex(substr($txt, 16));
		$decodeTxt = $this->decrypt($data, $iv);

		return $decodeTxt;
	}

	function getBookDetail($bid, $cid) {
		$chapterArr = $this->getChapterArr($bid);

		//print_r($chapterArr);
		$index = array_search($cid, array_column($chapterArr['chapterLists'], 'id'));

		$retArr = [
			'bookId' => $chapterArr['bookId'],
			'bookName' => $chapterArr['bookName'],
			'prev_item_id' => $index == 0 ? "" : $chapterArr['chapterLists'][$index-1]['id'],
			'prev_item_title' => $index == 0 ? "" : $chapterArr['chapterLists'][$index-1]['title'],
			'next_item_id' => $index+1 == count($chapterArr['chapterLists']) ? "" : $chapterArr['chapterLists'][$index+1]['id'],
			'next_item_title' => $index+1 == count($chapterArr['chapterLists']) ? "" : $chapterArr['chapterLists'][$index+1]['title'],
			'chapterId' => $cid,
			'chapterName' => $chapterArr['chapterLists'][$index]['title'],
			'content' => $this->getQmBookDetailDecode($bid, $cid)
			];

		return $retArr;
	}
}

//$qm = new QiMao();
//print_r(json_decode($qm->getQmAllCategory('boy'), true));exit();
//print_r(json_decode($qm->getQmCategoryList(2, 1, 1), true));exit();
//print_r($qm->getQmSearchArr("世子先别死", 1));exit();

//$bid = "1830570";

//$bookArr = $qm->getBookArr($bid);
//print_r($bookArr);exit();

//$chapterArr = $qm->getChapterArr($bid);
//print_r($chapterArr);exit();

//$cid = "17157528430001";
//$cid = "17157528430002";
//$cid = "17242355400192";
//$cid = "17242355400193";
//$detailArr = $qm->getBookDetail($bid, $cid);
//print_r($detailArr);
?>