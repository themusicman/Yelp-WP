<ul>
	<?php foreach($businesses as $business): ?>
		<li style="list-style: none; float: left; display: inline; width: 200px; height: 300px; margin-right: 5px;">
			<?php if ($business->photo_url): ?>
				<img src="<?php echo $business->photo_url ?>" alt="alt" />
			<?php endif; ?>
			<p>
				<a href="<?php echo $business->url ?>"><?php echo $business->name ?></a>
				<span style="display: block; font-size: 12px; font-style: italic:">
					<?php echo $business->address1 ?>
				</span>
			</p>
		</li>
	<?php endforeach; ?>
</ul>
