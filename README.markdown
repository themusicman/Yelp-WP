# Yelp WP #

Super simple plugin that aids in making Yelp API requests in the Wordpress templates.

## Usage

* Render Prebuilt HTML List  

		<?php echo get_yelp_businesses_list('Richmond, VA'); ?>

* Or you can have access to the PHP objects returned from the API request to customize HTML to your liking  

		<ul>
			<?php foreach(get_yelp_businesses('Ricmond, VA') as $business): ?>
				<li>
					<?php if ($business->photo_url): ?>
						<img src="<?php echo $business->photo_url ?>" alt="alt" />
					<?php endif; ?>
					<p>
						<a href="<?php echo $business->url ?>"><?php echo $business->name ?></a>
						<span>
							<?php echo $business->address1 ?>
						</span>
					</p>
				</li>
			<?php endforeach; ?>
		</ul>
		
