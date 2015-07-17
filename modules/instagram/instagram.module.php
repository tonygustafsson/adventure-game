<?php
	class instagram_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;
			
			$this->index_image_delimiter = "\r\n";
			$this->index_value_delimiter = "###";
			$this->index_file = $this->opus->config->instagram->image_path . 'index.txt';

			$this->url_accessible = array('save_images', 'get_images');
		}

		public function get_images($count = FALSE, $offset = FALSE)
		{
			$output = array();
			$count = ($count !== FALSE) ? $count : $this->opus->config->instagram->no_images;
			$offset = ($offset !== FALSE) ? $offset : 0;

			$index = $this->get_index($count, $offset);

			foreach ($index as $image) {
				$current_data['created_time'] = $image['date'];
				$current_data['caption'] = $image['caption'];

				$large_image_path = $this->opus->config->instagram->image_path . $image['id'] . '-large.jpg';
				$medium_image_path = $this->opus->config->instagram->image_path . $image['id'] . '-medium.jpg';
				$small_image_path = $this->opus->config->instagram->image_path . $image['id'] . '-small.jpg';

				if ($this->opus->config->instagram->save_small_images)
					$current_data['small_image_url'] = $this->opus->path_to_url($small_image_path);

				if ($this->opus->config->instagram->save_medium_images)
					$current_data['medium_image_url'] = $this->opus->path_to_url($medium_image_path);

				if ($this->opus->config->instagram->save_large_images)
					$current_data['large_image_url'] = $this->opus->path_to_url($large_image_path);

				$output[] = $current_data;
			}

			return $output;
		}

		public function get_index($count = FALSE, $offset = FALSE)
		{
			//Read the index file and delivers an array
			if (! file_exists($this->index_file) || filesize($this->index_file) < 1)
				return array();

			$fp = fopen($this->index_file, "r");
			$index_blob = fread($fp, filesize($this->index_file));
			fclose($fp);

			$index = array();
			$image_items = explode($this->index_image_delimiter, $index_blob);

			if ($count !== FALSE && $offset !== FALSE)
				$image_items = array_slice($image_items, $offset, $count);

			foreach ($image_items as $image)
			{
				if (empty($image))
					continue;

				$image_items = explode($this->index_value_delimiter, $image);
				$image_item_id = $image_items[0];
				$image_item_date = $image_items[1];
				$image_item_caption = $image_items[2];

				$index[$image_item_id]['id'] = $image_item_id;
				$index[$image_item_id]['date'] = $image_item_date;
				$index[$image_item_id]['caption'] = $image_item_caption;
			}

			krsort($index);

			return $index;
		}

		public function save_index($index)
		{
			foreach ($index as $key => $val)
				$index[$key] = implode($this->index_value_delimiter, $val);

			$index = implode($this->index_image_delimiter, $index);

			$fp = fopen($this->index_file, 'w');
			fwrite($fp, $index . $this->index_image_delimiter);
			fclose($fp);
		}

		public function save_images()
		{
			$media_url = $this->opus->config->instagram->media_url;
			$existing_index = $this->get_index();
			$new_index = array();

			if (isset($_GET['min_id']))
				$media_url .= '&min_id=' . $_GET['min_id'];

			if (isset($_GET['max_id']))
				$media_url .= '&max_id=' . $_GET['max_id'];

			//Get the JSON from the API and save images that is not already saved
			$ch = curl_init($media_url);

			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

			$data = curl_exec($ch);
			$json = json_decode($data);

			curl_close($ch);

			foreach ($json->data as $image)
			{
				//Ignore videos
				if ($image->type != "image")
					continue;

				if (! array_key_exists($image->id, $existing_index))
				{
					//This image is not in the existing index!
					$created_time = $image->created_time;
					$caption = (isset($image->caption->text)) ? $image->caption->text : "";

					$new_index[$image->id]['id'] = $image->id;
					$new_index[$image->id]['date'] = $image->created_time;
					$new_index[$image->id]['caption'] = (isset($image->caption->text)) ? $image->caption->text : "";
				}

				$image_small_url = $image->images->thumbnail->url;
				$image_medium_url = $image->images->low_resolution->url;
				$image_large_url = $image->images->standard_resolution->url;

				$image_small_path = $this->opus->config->instagram->image_path . $image->id . '-small.jpg';
				$image_medium_path = $this->opus->config->instagram->image_path . $image->id . '-medium.jpg';
				$image_large_path = $this->opus->config->instagram->image_path . $image->id . '-large.jpg';
				
				if ($this->opus->config->instagram->save_small_images)
						$this->save_image($image_small_url, $image_small_path);

				if ($this->opus->config->instagram->save_medium_images)
						$this->save_image($image_medium_url, $image_medium_path);
				
				if ($this->opus->config->instagram->save_large_images)
						$this->save_image($image_large_url, $image_large_path);
			}

			if (count($new_index) > 0)
			{
				$existing_index = array_merge($existing_index, $new_index);
				$this->save_index($existing_index);
			}

            $this->opus->log->write('info', 'Downloading images from instagram with IP: ' . $_SERVER['REMOTE_ADDR']);
			echo count($new_index) . ' new photos were downloaded.';
			end($existing_index);
			echo ' <a href="' . $this->opus->url('instagram/save_images?max_id=' . key($existing_index)) . '">Get more?</a>';
		}

		public function save_image($url, $path)
		{
			if (! file_exists($path))
			{
				//Image is not saved yet
				$ch = curl_init($url);

				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

				$raw = curl_exec($ch);

				curl_close($ch);

				$fp = fopen($path, 'x');
				fwrite($fp, $raw);
				fclose($fp);
			}
		}
	}
?>