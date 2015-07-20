<h2>Rooms</h2>

<ul>
	<?php foreach ($room_files as $room): ?>
		<li>
			<a href="admin/edit/<?=basename($room)?>"><?=basename($room)?></a>
		</li>
	<?php endforeach; ?>
</ul>