{capture name=path}{l s='WalletBit payment' mod='walletbit'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<div class="entry clearfix1 post">
	<h1>{l s='Success!' mod='walletbit'}</h1>

	{assign var='current_step' value='payment'}
	{include file="$tpl_dir./order-steps.tpl"}

	<h3>{l s='Processing order now.' mod='walletbit'}</h3>

	<p>{l s='Please check your email for more information. In case you need help with your order, please send us this transaction id:' mod='walletbit'} {$batchnumber}</p>

	<br />

	<div style="text-align: center;" align="center">
		<img src="{$this_path_ssl}spinner.gif" alt="{l s='Waiting for WalletBit' mod='walletbit'}" class="status" />
		<br />
		<small>{l s='WAITING FOR WALLETBIT' mod='walletbit'}</small>
	</div>

	<br />

	<p>{l s='While you are waiting for WalletBit to process the transaction, please consider spreading the word!' mod='walletbit'}</p>

	<br />

	<a href="https://walletbit.com/" title="{l s='WalletBit - The Simple Way to Spend or Get Paid Online' mod='walletbit'}" target="_blank">{l s='WalletBit - The Simple Way to Spend or Get Paid Online' mod='walletbit'}</a>
</div>
{literal}
<script type="text/javascript">
jQuery(document).ready(function() {
	setInterval(function () {
		jQuery.ajax({
			url: '{/literal}{$this_path_ssl}{literal}check.php',
			type: "POST",
			data: JSON.stringify({ id: '{/literal}{$cart_id}{literal}', batchnumber: '{/literal}{$batchnumber}{literal}' }),
			dataType: "json",
			contentType: "application/json; charset=UTF-8",
			ifModified: true,
			beforeSend: function (xhr) {
				if (xhr) {
					xhr.setRequestHeader("Content-Type", "application/json");
					xhr.setRequestHeader("Accept", "application/json");

					if (xhr.overrideMimeType) {
						xhr.overrideMimeType("application/json; charset=UTF-8");
					}
				}
			},
			success: function (data) {
				if (data == '1') {
					jQuery('img.status').attr('src', '{/literal}{$this_path_ssl}{literal}check.png');

					setTimeout(function() {
						location.href = "{/literal}{$redirect}{literal}";
					}, 3000);
				}
			}
		});
	}, 1000);
});
</script>
{/literal}