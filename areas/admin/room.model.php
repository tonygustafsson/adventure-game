<?php
	class room_model
	{
		public function __construct()
		{
			$this->room = array(
				'width' 		=>	array(
					'input_name'	=>	'width',
					'input_type'	=>	'number',
					'label'			=>	'Width',
					'description'	=>	'The width of the room background in pixels.'	
				),
				'height' 		=>	array(
					'input_name'	=>	'height',
					'input_type'	=>	'number',
					'label'			=>	'Height',
					'description'	=>	'The height of the room background in pixels.'	
				),
				'x' 			=>	array(
					'input_name'	=>	'x',
					'input_type'	=>	'number',
					'label'			=>	'X',
					'description'	=>	'The vertical position in pixels. Usually 0.'	
				),
				'y' 			=>	array(
					'input_name'	=>	'y',
					'input_type'	=>	'number',
					'label'			=>	'Y',
					'description'	=>	'The horizontal position in pixels. Usually 0.'	
				),
				'image' 		=>	array(
					'input_name'	=>	'image',
					'input_type'	=>	'text',
					'label'			=>	'Image',
					'description'	=>	'The name of the image containing the background.'	
				),
				'title' 		=>	array(
					'input_name'	=>	'title',
					'input_type'	=>	'text',
					'label'			=>	'Title',
					'description'	=>	'The room name/title.'	
				),
				'description' 		=>	array(
					'input_name'	=>	'description',
					'input_type'	=>	'text',
					'label'			=>	'Description',
					'description'	=>	'A description of the room.'	
				)
			);
			
			$this->item = array(
				'id'			=>	array(
					'input_name'	=>	'id',
					'input_type'	=>	'text',
					'label'			=>	'ID',
					'description'	=>	'A unique identifier, like "flower" or "desk".'	
				),
				'width' 		=>	array(
					'input_name'	=>	'width',
					'input_type'	=>	'number',
					'label'			=>	'Width',
					'description'	=>	'The width of the room background in pixels.'	
				),
				'height' 		=>	array(
					'input_name'	=>	'height',
					'input_type'	=>	'number',
					'label'			=>	'Height',
					'description'	=>	'The height of the room background in pixels.'	
				),
				'x' 			=>	array(
					'input_name'	=>	'x',
					'input_type'	=>	'number',
					'label'			=>	'X',
					'description'	=>	'The vertical position in pixels. Usually 0.'	
				),
				'y' 			=>	array(
					'input_name'	=>	'y',
					'input_type'	=>	'number',
					'label'			=>	'Y',
					'description'	=>	'The horizontal position in pixels. Usually 0.'	
				),
				'image' 		=>	array(
					'input_name'	=>	'image',
					'input_type'	=>	'text',
					'label'			=>	'Image',
					'description'	=>	'The name of the image containing the background.'	
				),
				'title' 		=>	array(
					'input_name'	=>	'title',
					'input_type'	=>	'text',
					'label'			=>	'Title',
					'description'	=>	'The room name/title.'	
				),
				'description' 		=>	array(
					'input_name'	=>	'description',
					'input_type'	=>	'text',
					'label'			=>	'Description',
					'description'	=>	'A description of the room.'	
				),
				'takable'	 		=>	array(
					'input_name'	=>	'takable',
					'input_type'	=>	'checkbox',
					'label'			=>	'Takable',
					'description'	=>	'Can you take this item to inventory?'	
				),
				'smellable'	 		=>	array(
					'input_name'	=>	'smellable',
					'input_type'	=>	'checkbox',
					'label'			=>	'Smellable',
					'description'	=>	'Can you smell this item?'	
				),
				'smellMessage'	 	=>	array(
					'input_name'	=>	'smellMessage',
					'input_type'	=>	'text',
					'label'			=>	'Smell message',
					'description'	=>	'What does this item smell like?'	
				),
				'usable'	 	=>	array(
					'input_name'	=>	'usable',
					'input_type'	=>	'checkbox',
					'label'			=>	'Usable',
					'description'	=>	'Can you use this item?'	
				),
				'useMessage'	 	=>	array(
					'input_name'	=>	'useMessage',
					'input_type'	=>	'text',
					'label'			=>	'Use message',
					'description'	=>	'What will be say when the item is used?'	
				)
			);
		}
	}
?>