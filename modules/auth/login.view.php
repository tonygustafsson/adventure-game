<h2>Log in</h2>

<form method="post" action="<?=$this->opus->url('auth/login_post')?>">
	<?=$form_elements?>

	<p><a href="<?=$this->opus->url('auth/forgot_password')?>">I forgot my password</a></p>

	<input type="submit" value="Log in">
</form>