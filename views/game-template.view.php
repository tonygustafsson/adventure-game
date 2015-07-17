<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
		
		<title>
			<?php if (isset($page_title)): ?>
				<?=$page_title?> | <?=$this->opus->config->site_name?>
			<?php else: ?>
				<?= $this->opus->config->site_name?>
			<?php endif; ?>
		</title>

		<?php if (isset($page_description)): ?>
			<meta name="description" content="<?=$page_description?>">
		<?php endif; ?>
		<?php if (isset($page_keywords)): ?>
			<meta name="keywords" content="<?=$page_keywords?>">
		<?php endif; ?>

		<?php if (isset($css)): ?>
			<?php echo $css ?>
		<?php endif; ?>

		<meta charset="utf-8">
	</head>

	<body>
		<?php if (isset($partial)): ?>
			<?=$partial?>
		<?php endif; ?>
		
		<?php if (isset($js)): ?>
			<?php echo $js ?>
		<?php endif; ?>
	</body>

</html>