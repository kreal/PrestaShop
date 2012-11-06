{capture name=path}{l s='WalletBit payment' mod='walletbit'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<div class="entry clearfix1 post">
	<h1>{l s='Pay by clicking WalletBit' mod='walletbit'}</h1>

	{assign var='current_step' value='payment'}
	{include file="$tpl_dir./order-steps.tpl"}

	<h4>{l s='Please click on WalletBit to pay the amount of' mod='walletbit'} <span class="price">{$price} {$sign}</span> (tax incl.)</h4>

{literal}
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<script type="text/javascript">
	  (function() {
		var wb = document.createElement('script'); wb.type = 'text/javascript'; wb.async = true;
		wb.src = 'https://walletbit.com/pay/{/literal}{$token}{literal}?url=' + escape(parent.location.href);
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(wb, s);
	  })();
	</script>
{/literal}
	<p class="cart_navigation">
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td align="left" valign="top">
				<a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Other payment methods' mod='walletbit'}</a>
			</td>
			<td align="right" valign="top">
				<a rel="{$bitcoin}" target="{$additional}" test="{$sandbox}" href="{$this_path_ssl}validation.php" class="WalletBitButton">{$item_name}</a>
			</td>
		</tr>
		</table>
	</p>
</div>