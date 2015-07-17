<h1><?=$title?></h1>

<p class="center">
	<a href="<?=$prev_link?>">[ Previous ]</a>
	<a href="<?=$current_link?>">[ Now ]</a>
	<a href="<?=$next_link?>">[ Next ]</a>
</p>

<?php if (count($log) > 0): ?>
	<table>
		<tr>
			<th>Time</th>
			<th>Source</th>
			<th>Level</th>
			<th>Message</th>
		</tr>

		<?php foreach ($log as $entry): ?>
			<tr>
				<td><?=$entry['time']?></td>
				<td><?=$entry['source']?></td>
				<td><?=$entry['level']?></td>
				<td><?=$entry['message']?></td>
			</tr>
		<?php endforeach; ?>
	</table>

	<p class="center">
		<a href="<?=$prev_link?>">[ Previous ]</a>
		<a href="<?=$current_link?>">[ Now ]</a>
		<a href="<?=$next_link?>">[ Next ]</a>
	</p>
<?php else: ?>
	<p><em>No log data for this date.</em></p>
<?php endif; ?>