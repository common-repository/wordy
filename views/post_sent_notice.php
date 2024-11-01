

<div id="wordy-post-sent-notice" class="updated">
	<p>
		
		<span class="wordy-message"><?php echo $message; ?></span>

		<?php if ($show_cancel): ?>

			<a href="post.php?action=edit&post=<?php echo intVal($post->ID); ?>&cancel_wordy=true"><?php _e('Cancel order', 'wordy'); ?></a>&nbsp;<?php _e('or', 'wordy'); ?> <a href="#" onclick="location.href=location.href;return false;"><?php _e('Update Wordy status', 'wordy'); ?></a>

		<?php endif; ?>

	</p>
</div>