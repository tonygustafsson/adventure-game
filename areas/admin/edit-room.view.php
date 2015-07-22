<form method="post" action="<?=$this->opus->url('admin/save_room')?>">
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
	
	<div class="items" id="items">
		<?php if (isset($room_data->items) && count($room_data->items) > 0): ?>
			<?php $counter = 0; ?>
		
			<?php foreach ($room_data->items as $data_key => $data_item): ?>
				<fieldset id="item_id_<?=$counter?>" data-item-counter="<?=$counter?>">
					<legend><?=$data_item->title?></legend>
			
					<input type="button" class="remove-item-button" data-item-remove="<?=$counter?>" value="Remove item">
			
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
		<?php endif; ?>
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