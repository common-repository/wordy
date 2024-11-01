<div id="wordy-post-edited-notice" class="updated" >

	<p>
		<span class="wordy-message"><?php _e('This post has been edited by Wordy.', 'wordy'); ?></span>

<?php if (isset($document->status_code) && $document->status_code == 'document_completed'): ?> 

	<a href="#" onclick="document.getElementById('wordyReclaim').style.display='block'; return false;">
		<?php _e('Claim re-edit', 'wordy'); ?>
	</a>

	<?php if ($post->post_status != 'publish'): ?>
		
		<?php _e('or', 'wordy'); ?>
		
		<a href="#" onclick="document.getElementById('publish').click(); return false;">
			<?php _e('publish post immediately', 'wordy'); ?>
		</a>
	
	<?php endif; ?>
	
	</p>
	
	<div style="margin-top: 30px; margin-left: 210px; width: 305px; display: none;" id="wordyReclaim">
	
		<form method="post" action="">
	
			<input type="hidden" name="post" value="'<?php echo $postID; ?>" />
	
			<span class="wordy-message"><?php _e('Claim re-edit', 'wordy'); ?>.</span>
			
			<br />
	
			<span><?php _e('State the reasons for claiming re-edit.', 'wordy'); ?></span>
	
			<textarea style="width: 300px; height: 120px;" name="message"></textarea>
			
			<br />
	
			<input type="submit" name="submit_reclaim" value="Send to editor" class="button" /> 
	
			<a href="#" onclick="document.getElementById('wordyReclaim').style.display='none';return false;">
				<?php _e('Cancel', 'wordy'); ?>
			</a>
	
			<br />
			<br />
	
		</form>

	</div>

<?php endif; ?>

</div>