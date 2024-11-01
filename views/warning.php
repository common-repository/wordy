
<?php
	
	$link = !isset($_GET['page']) || (isset($_GET['page']) && $_GET['page'] != 'wordy.php');

?>

<div id="wordy-warning" class="updated fade">
	<p>
		
		<strong>
			<?php _e('Wordy is almost ready!', 'wordy'); ?> 
		</strong>
			
		<?php if ($link): ?>
			<a href="options-general.php?page=wordy.php">
		<?php endif; ?>
		
		 <?php _e('Please create a new account or enter your account information.', 'wordy'); ?>
	
		<?php if ($link): ?>
			</a>
		<?php endif; ?>
	
	</p>
</div>
