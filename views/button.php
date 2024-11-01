<div id="wordy-publish-wordy">
	
	<input 
		name="wordy[publish_via_wordy]" 
		id="publish_via_wordy" 
		<?php if (empty($this->options['email'])): ?>
			onclick="alert('<?php _e('Please create a new account or enter your account information to publish via Wordy', 'wordy'); ?>');return false;"
		<?php endif; ?> 
		type="submit" 
		class="button-primary" 
		tabindex="5" 
		accesskey="w" 
		value="<?php 
			if ($post->post_status != 'publish'):
				_e('Publish via Wordy', 'wordy'); 
			else: 
				_e('Send to Wordy', 'wordy'); 
			endif;
		?>" 
	/>
		
</div>

<script type="text/javascript">

	jQuery(document).ready(function()
	{
		var delete_link = jQuery('#delete-action').css({
			'float': 'left'
			,'clear': 'both'
		});
		var clear = jQuery('#major-publishing-actions .clear');
		
		jQuery('#delete-action, #major-publishing-actions .clear').remove();
		
		jQuery('#major-publishing-actions').append(delete_link, clear);	
	});

</script>