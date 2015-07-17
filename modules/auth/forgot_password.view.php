<h2>Forgot password</h2>

<form method="post" action="<?=$this->opus->url('auth/forgot_password_post')?>">
	<?=$form_elements?>

	<input type="submit" value="E-mail my password">
</form>