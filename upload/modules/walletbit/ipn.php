<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/walletbit.php');

$str =
$_POST["merchant"].":".
$_POST["customer_email"].":".
$_POST["amount"].":".
$_POST["batchnumber"].":".
$_POST["txid"].":".
$_POST["address"].":".
Configuration::get('WALLETBIT_SECURITY_WORD');

$hash = strtoupper(hash('sha256', $str));

// proccessing payment only if hash is valid
if ($_POST["merchant"] == Configuration::get('WALLETBIT_MERCHANT') && $_POST["encrypted"] == $hash && $_POST["status"] == 1)
{
	print '1';

	$walletbit = new walletbit();
	$walletbit->updateDetails($_POST['id'], $_POST['batchnumber'], $_POST['txid'], $_POST['address']);
}
else
{
	print "Incorrect IPN";
}

?>