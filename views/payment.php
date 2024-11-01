
<script type="text/javascript">

	function wordy_iframe_loaded() 
	{
		jQuery('#TB_window').addClass('wordy')
		
		jQuery('body', jQuery('#TB_iframeContent').contents())
			.append('<form method="post" action="<?php echo $wordy['signin_url']; ?>/signin" id="wordyLoginForm" name="wordyLoginForm">'
								+ '<input type="hidden" name="email" value="<?php echo $options['email']; ?>" />'
								+ '<input type="hidden" name="password" value="<?php echo $options['password']; ?>" />'
								+ '<input type="hidden" name="language_code" value="<?php echo $options['language_code']; ?>" />'
								+ '<input type="hidden" name="returnTo" value="<?php echo $wordy['signin_url']; ?>/order/new/pay/order_id/<?php echo intVal($_SESSION['wordy_order_id']); ?>" />'
							+ '</form>');
		
		wordy_tbiframe_resize();
	}

	jQuery(window).ready(function() 
	{
		wordy_init_payment('<?php echo $wordy_plugin_url; ?>');
	});
	
</script>

