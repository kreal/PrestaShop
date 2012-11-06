<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/walletbit.php');

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');
   
$walletbit = new walletbit();
print $walletbit->process($cart);

include_once(dirname(__FILE__).'/../../footer.php');

?>