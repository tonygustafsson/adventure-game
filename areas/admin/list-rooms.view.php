<a href="<?=$this->opus->url('admin/create')?>">Create room</a>

<h2>Rooms</h2>

<ul>
	<?php foreach ($room_files as $room): ?>
		<li>
			<a href="admin/edit/<?=basename($room, ".json")?>"><?=basename($room, ".json")?></a>
		</li>
	<?php endforeach; ?>
</ul>