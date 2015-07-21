<form method="post" action="<?=$this->opus->url('admin/edit_submit')?>">
	<input type="hidden" name="room_name" id="room_name" value="<?=$room_name?>">

	<fieldset>
		<legend><?=$room_data->title?></legend>
		
		<?php foreach ($model->room as $key => $item): ?>
				<?php $value = isset($room_data->$item['input_name']) ? $room_data->$item['input_name'] : ""; ?>
				<label for="<?=$item['input_name']?>"><?=$item['label']?></label>
				<input type="<?=$item['input_type']?>" name="<?=$item['input_name']?>[room]" id="<?=$item['input_name']?>" value="<?=$value?>">
				<p class="description"><?=$item['description']?></p>
		<?php endforeach; ?>
	</fieldset>
	
	<h2>Items</h2>
	
	<?php $counter = 0; ?>
	
	<?php foreach ($room_data->items as $data_key => $data_item): ?>
		<fieldset>
			<legend><?=$data_item->title?></legend>
	
			<?php foreach ($model->item as $key => $item): ?>
				<?php if ($item['input_type'] == 'checkbox'): ?>
					<?php $value = isset($data_item->$item['input_name']) ? "true" : "false" ?>
					<label for="item<?=$counter?>_<?=$item['input_name']?>"><?=$item['label']?></label>
					<input type="<?=$item['input_type']?>" name="<?=$item['input_name']?>[<?=$counter?>]?>" id="item<?=$counter?>_<?=$item['input_name']?>" value="<?=$value?>"<?=($value == "true") ? ' checked' : ''?>>
					<p class="description"><?=$item['description']?></p>
				<?php else: ?>
					<?php $value = isset($data_item->$item['input_name']) ? $data_item->$item['input_name'] : ""; ?>
					<label for="item<?=$counter?>_<?=$item['input_name']?>"><?=$item['label']?></label>
					<input type="<?=$item['input_type']?>" name="<?=$item['input_name']?>[<?=$counter?>]" id="item<?=$counter?>_<?=$item['input_name']?>" value="<?=$value?>">
					<p class="description"><?=$item['description']?></p>
				<?php endif; ?>
			<?php endforeach; ?>
		</fieldset>
		
		<?php $counter++; ?>
	<?php endforeach; ?>
	
	<input type="submit" value="Save">
</form>