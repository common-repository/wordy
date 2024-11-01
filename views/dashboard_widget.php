<ul>
	
	<?php if (empty($status)): ?>

	<li>
		<h4>   
			<abbr title="Wordy Status">
				<?php _e('Nothing Sent to Wordy', 'wordy'); ?>
			</abbr>
		</h4>
	</li>
	
	<?php else: ?>

		<?php foreach ($status as $document): ?>

		<li>
			<h4>
				<a title="Edit “<?php echo $document['post']->post_title; ?>”" href="<?php echo bloginfo('url'); ?>/wp-admin/post.php?post=<?php echo $document['post']->ID; ?>&amp;action=edit"><?php echo $document['post']->post_title; ?></a><br/>
				<abbr title="Wordy Status"><?php echo $document['sent'] = 1 ? __('Sent to Wordy') : __('Edited to Wordy'); ?></abbr> 
			</h4>
		</li>

		<?php endforeach; ?>

	<?php endif; ?>

</ul>

<div class="wordy_dashboard_widget_footer">
	<a class="twitter_follow" target="_blank" href="http://twitter.com/wordyhq"><?php _e('Follow Wordy on Twitter','wordy'); ?></a>
</div>
	