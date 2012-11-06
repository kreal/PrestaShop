Copyright (C) 2012 by Kris

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

About
	Bitcoin payment via walletbit.com for PrestaShop.

Version 0.3
	Currency convert between all currencies automatically.
	Set Minimum Exchange Rate for one Bitcoin, automatically updated if prices go above.
	Fetches current exchange rate from Mt.Gox.
	
System Requirements:
	WalletBit.com account
	PrestaShop
	PHP 5+
	Curl PHP Extension
  
Configuration Instructions:
	1. Upload files to your PrestaShop installation.
	2. Go to your PrestaShop administration. Modules -> Payments & Gateways -> "WalletBit" click [Install]
	3. Go to your PrestaShop administration. Modules -> Payments & Gateways -> "WalletBit" click [Configure]
	4. In WalletBit.com IPN Handler URL (https://walletbit.com/businesstools#manageIPNhandler) Enter the provided link from configure section of WalletBit PrestaShop Payment Module. (http://YOUR_PRESTASHOP_URL/modules/walletbit/ipn.php)
	5. Enter a strong Security Word in WalletBit Manage IPN Handler.
	6. In module settings "E-Mail" <- set your WalletBit.com email.
	7. In module settings "Token" <- copy from WalletBit.com (https://walletbit.com/businesstools#manageIPNhandler) "Token"
	8. In module settings "Security Word" <- Enter your Security Word.
	9. In module settings "Exchange Rate" -> Type the minimum amount you want for one Bitcoin.