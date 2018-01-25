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
		
		<div class="twelve columns" style="margin-top:50px">
			<style type="text/css">
				.openid-login
				{
					width:calc(25% - 10px);
					margin:0px 5px;
					direction:ltr;
					cursor: pointer;
					border-radius: 5px;
					box-shadow: 5px 5px 5px #333;
					transition: box-shadow .3s;
				}

				.openid-login:hover
				{
					box-shadow: 2px 4px 5px #aaa, -2px -4px 5px #aaa;
					transition: box-shadow .3s;
				}
			</style>
			<labeL>{social_media_login_text}</label>
			<br>
			<div style="font-size:0">
			<img class="openid-login" src="{images_url}/login-ym.jpg" title="Yahoo!" onclick="yahooLogin();" />
			<img class="openid-login" src="{images_url}/login-gm.jpg" title="Google" onclick="googleLogin();" />
			<img class="openid-login" src="{images_url}/login-fb.jpg" title="Facebook" onclick="facebookLogin();" />
			<img class="openid-login" src="{images_url}/login-ms.jpg" title="Microsoft Live Connect" onclick="microsoftLogin();" />
		</div>
			<script type="text/javascript">
				function microsoftLogin()
				{
					window.open("{microsoft_login_page}","_blank","width=600, height=400");
				}

				function yahooLogin()
				{
					window.open("{yahoo_login_page}","_blank","width=600, height=400");
				}

				function googleLogin()
				{
					window.open("{google_login_page}","_blank","width=600, height=400");
				}

				function facebookLogin()
				{
					window.open("{facebook_login_page}","_blank","width=600, height=400");
				}
			</script>
		</div>
	</div>
</div>