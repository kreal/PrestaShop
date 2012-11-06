<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/walletbit.php');

$total = $cart->getOrderTotal(true);

if (isset($_POST['batchnumber']))
{
	$walletbit = new walletbit();

	$method = _PS_OS_PREPARATION_;

	if ($cart->isVirtualCart())
	{
		$method = _PS_OS_PAYMENT_;
	}

	$walletbit->validateOrder($cart->id, $method, $total, $walletbit->displayName, NULL, NULL, $currency->id);

	$order = new Order($walletbit->currentOrder);
	$walletbit->writeDetails($order->id, $cart->id, $_POST['batchnumber']);

	Tools::redirectLink((Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$walletbit->name.'/process.php?id=' . $cart->id . '&batchnumber=' . $_POST['batchnumber'] . '&redirect=' . rawurlencode(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$walletbit->id.'&id_order='.$walletbit->currentOrder.'&key='.$order->secure_key));

	exit;
}
else
{
	Tools::redirectLink((Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$walletbit->name.'/payment.php');
}
?>