<div class="main">
	<div class="container width-400">
		<h1>{login_text}</h1>
		<a class="results-search-again" style="font-size:1.2em" 
			href="<?php echo get_link('customer_signup');?>"
		>
			{signup_text}
		</a>
		<br><br>
		<?php echo form_open(get_link("customer_login"),array("id"=>"login-form")); ?>
			<div class="twelve columns mrg-btn-20">
				<label>{email_text}</label>
				<input class="lang-en full-width" type="text" name="email" />
			</div>
			<div class="twelve columns mrg-btn-20">
				<label>{password_text}</label>
				<input class="lang-en full-width" type="password" name="pass"/>
			</div>
			<div class="twelve columns mrg-btn-40">
				<labeL>{captcha}</label>
				<input class="lang-en full-width" type="text" name="captcha" />
			</div>
		
			<div class="twelve columns">				
				<input class="full-width button-primary" type="submit"  value="{sign-in_text}" />
			</div>
		</form>		
		<br>&nbsp;<br>
		<a style="font-size:1.2em" 
			onclick="forgottenPassword();"
		>
			{do_you_forget_your_password_text}
		</a>
		<script type="text/javascript">
			function forgottenPassword()
			{
				if(!$("#login-form input[name=email]").val() || !$("#login-form input[name=captcha]").val())
				{
					alert("{please_enter_your_email_and_captcha_text}");
					return;
				}

				$("#login-form").prop("action","<?php echo get_link('customer_forgotten_password');?>").submit();
			}
		</script>
	</div>
</div>