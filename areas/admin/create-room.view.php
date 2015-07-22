<h2>Create room</h2>

<form method="post" action="<?=$this->opus->url('admin/save_room')?>">
	<fieldset>
		<legend>New room</legend>
		
		<label for="room_name">Room name</label>
		<input type="text" name="room_name" id="room_name">
		
		<?php foreach ($model->room as $key => $item): ?>
			<label for="<?=$item['input_name']?>"><?=$item['label']?></label>
			<input type="<?=$item['input_type']?>" name="<?=$item['input_name']?>[room]" id="<?=$item['input_name']?>">
			<p class="description"><?=$item['description']?></p>
		<?php endforeach; ?>
	</fieldset>
	
	<h2>Items</h2>
	
	<div class="items" id="items">
	</div>
	
	<input type="button" id="new-item-button" value="Create new item">	
	<input type="submit" value="Save">
</form>

<fieldset id="new-item-template" class="structural" role="template">
	<legend>New item</legend>

	<input type="button" class="remove-item-button" data-item-remove="###" value="Remove item">

	<?php foreach ($model->item as $key => $item): ?>
		<?php if ($item['input_type'] == 'checkbox'): ?>
			<label for="item###_<?=$item['input_name']?>"><?=$item['label']?></label>
			<input type="<?=$item['input_type']?>" name="<?=$item['input_name']?>[###]?>" id="###_<?=$item['input_name']?>" value="">
			<p class="description"><?=$item['description']?></p>
		<?php else: ?>
			<?php $value = isset($data_item->$item['input_name']) ? $data_item->$item['input_name'] : ""; ?>
			<label for="item###_<?=$item['input_name']?>"><?=$item['label']?></label>
			<input type="<?=$item['input_type']?>" name="<?=$item['input_name']?>[###]" id="item###_<?=$item['input_name']?>" value="">
			<p class="description"><?=$item['description']?></p>
		<?php endif; ?>
	<?php endforeach; ?>
</fieldset>