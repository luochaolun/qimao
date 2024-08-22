<?php
require_once('QiMao.php');

$qm = new QiMao();
//print_r(json_decode($qm->getQmAllCategory('boy'), true));exit();
//print_r(json_decode($qm->getQmCategoryList(2, 1, 1), true));exit();
//print_r($qm->getQmSearchArr("世子先别死", 1));exit();

$bid = "1830570";

//$bookArr = $qm->getBookArr($bid);
//print_r($bookArr);exit();

//$chapterArr = $qm->getChapterArr($bid);
//print_r($chapterArr);exit();

$cid = "17157528430001";
//$cid = "17157528430002";
//$cid = "17242355400192";
//$cid = "17242355400193";
$detailArr = $qm->getBookDetail($bid, $cid);
print_r($detailArr);
?>