<h2>Reset password</h2>

<p>Please set a new password below.</p>

<?php if (isset($form_elements)): ?>
	<form method="post" action="<?=$this->opus->url('auth/reset_password_post')?>">
		<?=$form_elements?>
		<input type="submit" value="Reset password">
	</form>
<?php endif; ?>