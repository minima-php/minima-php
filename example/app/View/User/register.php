<h2>Register</h2>

<form class="aligned" action="" method="POST">
	<fieldset>
		<legend>Login Informations</legend>
		<?php echo $form->input('username') ?>
		 <span class="flash-notice">Start with a capital letter, allows a-z underscores and hypens, length 8 to 16</span><br/>
		<?php echo $form->input('password', null, 'password') ?>
		 <span class="flash-notice">Length 8 to 16</span><br/>
	</fieldset>

	<input type="submit"/>
</form>