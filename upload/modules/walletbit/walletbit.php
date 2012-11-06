<?php
	class walletbit extends PaymentModule
	{
		private $_html = '';
		private $_postErrors = array();

		function __construct()
		{
			$this->name = 'walletbit';
			$this->tab = 'payments_gateways';
			$this->version = '0.3';

			$this->currencies = true;
			$this->currencies_mode = 'checkbox';

			parent::__construct();

			$this->page = basename(__FILE__, '.php');
			$this->displayName = $this->l('WalletBit');
			$this->description = $this->l('Accepts payments by Bitcoin via WalletBit.');
			$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');

			if (Configuration::get('WALLETBIT_MERCHANT') == '')
			{
				$this->warning = $this->l('Please setup merchant e-mail. ');
			}

			if (Configuration::get('WALLETBIT_TOKEN') == '')
			{
				$this->warning .= $this->l('You are currently using the default token. ');
			}

			if (Configuration::get('WALLETBIT_SECURITY_WORD') == '')
			{
				$this->warning .= $this->l('Security Word is empty.');
			}
		}

		public function install()
		{
			if (!parent::install() || !$this->registerHook('invoice') || !$this->registerHook('payment') || !$this->registerHook('paymentReturn'))
			{
				return false;
			}

			$db = Db::getInstance();
			$query = "CREATE TABLE `"._DB_PREFIX_."order_bitcoin` (
			`id_payment` int(11) NOT NULL AUTO_INCREMENT,
			`id_order` int(11) NOT NULL,
			`cart_id` int(11) NOT NULL,
			`batchnumber` int(11) NOT NULL,
			`txid` varchar(255) NOT NULL,
			`address` varchar(255) NOT NULL,
			PRIMARY KEY (`id_payment`),
			UNIQUE KEY `batchnumber` (`batchnumber`),
			UNIQUE KEY `txid` (`txid`)
			) ENGINE="._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

			$db->Execute($query);

			Configuration::updateValue('WALLETBIT_SANDBOX', 0);
			Configuration::updateValue('WALLETBIT_MERCHANT', '');
			Configuration::updateValue('WALLETBIT_TOKEN', '');
			Configuration::updateValue('WALLETBIT_SECURITY_WORD', '');
			Configuration::updateValue('WALLETBIT_EXCHANGE', '15');

			return true;
		}

		public function uninstall()
		{
			Configuration::deleteByName('WALLETBIT_SANDBOX');
			Configuration::deleteByName('WALLETBIT_MERCHANT');
			Configuration::deleteByName('WALLETBIT_TOKEN');
			Configuration::deleteByName('WALLETBIT_SECURITY_WORD');
			Configuration::deleteByName('WALLETBIT_EXCHANGE');
			
			return parent::uninstall();
		}

		public function getContent()
		{
			$this->_html .= '<h2>'.$this->l('WalletBit').'</h2>';	
	
			$this->_postProcess();
			$this->_setWalletBitSubscription();
			$this->_setConfigurationForm();
			
			return $this->_html;
		}

		function hookPayment($params)
		{
			global $smarty;

			$smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_ssl' => Configuration::get('PS_FO_PROTOCOL').$_SERVER['HTTP_HOST'].__PS_BASE_URI__."modules/{$this->name}/"));

			return $this->display(__FILE__, 'payment.tpl');
		}

		private function _setWalletBitSubscription()
		{
			$this->_html .= '
			<div style="float: right; width: 440px; height: 150px; border: dashed 1px #666; padding: 8px; margin-left: 12px;">
				<h2>'.$this->l('Opening your WalletBit account').'</h2>
				<div style="clear: both;"></div>
				<p>'.$this->l('When opening your WalletBit account by clicking on the following image, you are helping us significantly to improve the WalletBit Solution:').'</p>
				<p style="text-align: center;"><a href="https://walletbit.com/ref/45ab2793b84a351e3354661e6532c1c7"><img src="../modules/walletbit/prestashop_walletbit.png" alt="PrestaShop & WalletBit" style="margin-top: 12px;" /></a></p>
				<div style="clear: right;"></div>
			</div>
			<img src="../modules/walletbit/bitcoin.png" style="float:left; margin-right:15px;" />
			<b>'.$this->l('This module allows you to accept payments by WalletBit.').'</b><br /><br />
			'.$this->l('If the client chooses this payment mode, your WalletBit account will be automatically credited.').'<br />
			'.$this->l('You need to configure your WalletBit account before using this module.').'
			<div style="clear:both;">&nbsp;</div>';
		}

		private function _setConfigurationForm()
		{
			$this->_html .= '
			<form method="post" action="'.htmlentities($_SERVER['REQUEST_URI']).'">	
				<script type="text/javascript">
					var pos_select = '.(($tab = (int)Tools::getValue('tabs')) ? $tab : '0').';
				</script>
				<script type="text/javascript" src="'._PS_BASE_URL_._PS_JS_DIR_.'tabpane.js"></script>
				<link type="text/css" rel="stylesheet" href="'._PS_BASE_URL_._PS_CSS_DIR_.'tabpane.css" />
				<input type="hidden" name="tabs" id="tabs" value="0" />
				<div class="tab-pane" id="tab-pane-1" style="width:100%;">
					<div class="tab-page" id="step1">
						<h4 class="tab">'.$this->l('Settings').'</h2>
						'.$this->_getSettingsTabHtml().'
					</div>
				</div>
				<div class="clear"></div>
				<script type="text/javascript">
					function loadTab(id){}
					setupAllTabs();
				</script>
			</form>';
		}

		private function _getSettingsTabHtml()
		{
			global $cookie;

			$sandboxMode = (int)(Tools::getValue('sandbox_mode', Configuration::get('WALLETBIT_SANDBOX')));

			$ticker = round(file_get_contents('https://walletbit.com/btcusd'), 2);

			$html = '
			<h2>'.$this->l('Settings').'</h2>
			<label>'.$this->l('Sandbox mode (tests)').':</label>
			<div class="margin-form" style="padding-top:2px;">
				<input type="radio" name="sandbox_mode" id="sandbox_mode_1" value="1" '.($sandboxMode ? 'checked="checked" ' : '').'/> <label for="sandbox_mode_1" class="t">'.$this->l('Active').'</label> 
				<input type="radio" name="sandbox_mode" id="sandbox_mode_0" value="0" style="margin-left:15px;" '.(!$sandboxMode ? 'checked="checked" ' : '').'/> <label for="sandbox_mode_0" class="t">'.$this->l('Inactive').'</label>
			</div>
			<label>'.$this->l('WalletBit account e-mail').':</label>
			<div class="margin-form">
				<input type="text" name="merchant_walletbit" value="'.htmlentities(Tools::getValue('merchant_walletbit', Configuration::get('WALLETBIT_MERCHANT')), ENT_COMPAT, 'UTF-8').'" size="30" />
			</div>
			<label>'.$this->l('Token').':</label>
			<div class="margin-form">
				<input type="text" name="token_walletbit" value="'.htmlentities(Tools::getValue('token_walletbit', Configuration::get('WALLETBIT_TOKEN')), ENT_COMPAT, 'UTF-8').'" size="40" />
			</div>
			<label>'.$this->l('Security Word').':</label>
			<div class="margin-form">
				<input type="password" name="security_word_walletbit" value="'.htmlentities(Tools::getValue('security_word_walletbit', Configuration::get('WALLETBIT_SECURITY_WORD')), ENT_COMPAT, 'UTF-8').'" size="30" />
			</div>
			<h3 style="clear:both;">'.$this->l('Minimum Exchange Rate for one Bitcoin, automatically updated if prices go above.').'</h3>
			<label>'.$this->l('Exchange Rate').':</label>
			<div class="margin-form">
				$<input type="text" name="exchange_rate_walletbit" value="'.htmlentities(Tools::getValue('exchange_rate', Configuration::get('WALLETBIT_EXCHANGE')), ENT_COMPAT, 'UTF-8').'" size="1" /> ' . $this->l('Current exchange rate: $') . $ticker . '
			</div>
			<br /><br />
			<h3>' . $this->l('Please copy this to Manage IPN Handler URL in WalletBit Business Tools') . '</h3>
			' . (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/ipn.php' . '

			<p class="center"><input class="button" type="submit" name="submitWalletBit" value="'.$this->l('Save settings').'" /></p>';
			return $html;
		}

		private function _postProcess()
		{
			global $currentIndex, $cookie;

			if (Tools::isSubmit('submitWalletBit'))
			{
				$template_available = array('A', 'B', 'C');

				$this->_errors = array();

				if (Tools::getValue('merchant_walletbit') == NULL)
				{
					$this->_errors[] = $this->l('Missing WalletBit account e-mail');
				}

				if (Tools::getValue('token_walletbit') == NULL)
				{
					$this->_errors[] = $this->l('Missing WalletBit Token');
				}

				if (Tools::getValue('security_word_walletbit') == NULL)
				{
					$this->_errors[] = $this->l('Missing Security Word');
				}

				if (Tools::getValue('exchange_rate_walletbit') == NULL)
				{
					$this->_errors[] = $this->l('Missing Exchange Rate');
				}
				
				if (count($this->_errors) > 0)
				{
					$error_msg = '';
					foreach ($this->_errors AS $error)
						$error_msg .= $error.'<br />';
					$this->_html = $this->displayError($error_msg);
				}
				else
				{
					Configuration::updateValue('WALLETBIT_SANDBOX', (int)(Tools::getValue('sandbox_mode')));
					Configuration::updateValue('WALLETBIT_MERCHANT', trim(Tools::getValue('merchant_walletbit')));
					Configuration::updateValue('WALLETBIT_TOKEN', trim(Tools::getValue('token_walletbit')));
					Configuration::updateValue('WALLETBIT_SECURITY_WORD', trim(Tools::getValue('security_word_walletbit')));
					Configuration::updateValue('WALLETBIT_EXCHANGE', trim(Tools::getValue('exchange_rate_walletbit')));

					$this->_html = $this->displayConfirmation($this->l('Settings updated'));
				}
			}
		}

		public function amountTObitcoin($cart)
		{
			$currency = new Currency((int)($cart->id_currency));
			$sign = $currency->sign;
			$currency = $currency->iso_code;

			$bitcoin = 0;

			if (strtolower($currency) == 'btc')
			{
				$bitcoin = $cart->getOrderTotal(true);
			}
			else
			{
				$amount = $cart->getOrderTotal(true);
	
				if ($currency != 'USD')
				{
					$ch = curl_init();
					$timeout = 0;
					curl_setopt ($ch, CURLOPT_URL, 'http://www.google.com/ig/calculator?hl=en&q=' . $amount . $currency . '=?USD');
					curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
					curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
					$rawdata = curl_exec($ch);
					curl_close($ch);
	
					$data = explode('"', $rawdata);
					$data = explode(' ', $data['3']);
					$var = $data['0'];

					$new_string = preg_replace("/[^0-9,.]/", "", $var);

					$amount = round($new_string, 3);
				}
	
				$exchange_rate_dollars = Configuration::get('WALLETBIT_EXCHANGE');

				$ticker = floatval(file_get_contents('https://walletbit.com/btcusd'));

				if ($ticker < $exchange_rate_dollars)
				{
					$ticker = $exchange_rate_dollars;
				}

				$bitcoin = round($amount / $ticker, 8);
			}

			return array('bitcoin' => $bitcoin, 'sign' => $sign);
		}

		public function execPayment($cart)
		{
			global $cookie, $smarty;

			if (!$this->active)
				return ;

			$arr = $this->amountTObitcoin($cart);

			$smarty->assign(array(
				'this_path' => $this->_path,
				'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/',
				'price' => $cart->getOrderTotal(true),
				'sign' => $arr['sign'],
				'token' => Configuration::get('WALLETBIT_TOKEN'),
				'bitcoin' => $arr['bitcoin'],
				'additional' => 'id=' . $cart->id . '|id_customer=' . $cart->id_customer,
				'sandbox' => intval(Configuration::get('WALLETBIT_SANDBOX')),
				'item_name' => 'Cart ' . $cart->id
			));

			return $this->display(__FILE__, 'payment_execution.tpl');
		}

		function writeDetails($id_order, $cart_id, $batchnumber)
		{
			$db = Db::getInstance();
			$result = $db->Execute('INSERT INTO `' . _DB_PREFIX_ . 'order_bitcoin` (`id_order`, `cart_id`, `batchnumber`) VALUES(' . intval($id_order)  .', ' . intval($cart_id) . ', ' . intval($batchnumber) . ')');
		}

		function process($cart)
		{
			global $cookie, $smarty;

			$smarty->assign(array(
				'this_path' => $this->_path,
				'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/',
				'token' => Configuration::get('WALLETBIT_TOKEN'),
				'cart_id' => intval($_GET['id']),
				'batchnumber' => intval($_GET['batchnumber']),
				'redirect' => rawurldecode($_GET['redirect'])
			));

			return $this->display(__FILE__, 'payment_processing.tpl');
		}

		function updateDetails($cart_id, $batchnumber, $txid, $address)
		{
			$txid = stripslashes(str_replace("'", '', $txid));
			$address = stripslashes(str_replace("'", '', $address));

			$db = Db::getInstance();
			$result = $db->Execute('UPDATE `' . _DB_PREFIX_ . 'order_bitcoin` SET txid = "' . $txid . '", address = "' . $address . '" WHERE cart_id = ' . intval($cart_id) . ' AND batchnumber = ' . intval($batchnumber) . ' LIMIT 1;');
		}

		function readBitcoinpaymentdetails($id_order)
		{
			$db = Db::getInstance();
			$result = $db->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'order_bitcoin` WHERE `id_order` = ' . intval($id_order) . ';');
			return $result[0];
		}

		function hookInvoice($params)
		{
			global $smarty;

			$id_order = $params['id_order'];

			$bitcoinpaymentdetails = $this->readBitcoinpaymentdetails($id_order);

			$smarty->assign(array(
				'batchnumber' => $bitcoinpaymentdetails['batchnumber'],
				'txid' => $bitcoinpaymentdetails['txid'],
				'address' => $bitcoinpaymentdetails['address'],
				'id_order' => $id_order,
				'this_page' => $_SERVER['REQUEST_URI'],
				'this_path' => $this->_path,
				'this_path_ssl' => Configuration::get('PS_FO_PROTOCOL').$_SERVER['HTTP_HOST'].__PS_BASE_URI__."modules/{$this->name}/"
			));
		
			return $this->display(__FILE__, 'invoice_block.tpl');
		}

		function hookpaymentReturn($params)
		{
			global $smarty;

			$smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_ssl' => Configuration::get('PS_FO_PROTOCOL').$_SERVER['HTTP_HOST'].__PS_BASE_URI__."modules/{$this->name}/"));

			return $this->display(__FILE__, 'complete.tpl');
		}
	}
?>