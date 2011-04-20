<div class="wrap">
	
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>Yelp WP Options</h2>
	
	<?php if ($message): ?>
	<p><?php echo $message ?></p>
	<?php endif; ?>
	
	<form name="yelp_wp_options" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<input type="hidden" name="yelp_wp_save" value="Y">
		
		<p>
			<label for="yelp_wp_api_key">API Key</label>
			<input type="text" name="yelp_wp_api_key" value="<?php echo $yelp_api_key; ?>" id="yelp_wp_api_key">
		</p>

		<p class="submit">
			<input type="submit" name="Submit" value="Update" />
		</p>
	</form>
	
</div>