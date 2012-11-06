<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/walletbit.php');

$handle = fopen('php://input','r');
$jsonInput = fgets($handle);
$decoded = json_decode($jsonInput, true);
fclose($handle);

$cart_id = intval($decoded['id']);
$batchnumber = intval($decoded['batchnumber']);

$db = Db::getInstance();
$result = $db->ExecuteS('SELECT txid FROM `' . _DB_PREFIX_ . 'order_bitcoin` WHERE `cart_id` = ' . $cart_id . ' AND `batchnumber` = ' . $batchnumber . ' LIMIT 1;');

if (strlen($result[0]['txid']) > 0)
{
	print '1';
}
else
{
	print '0';
}
?>