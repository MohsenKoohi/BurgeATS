<div class="main">
	<div class="container width-400">
		<h1>{signup_text}</h1>
		<a class="results-search-again" style="font-size:1.2em" 
			href="<?php echo get_link('customer_login');?>"
		>
			{login_text}
		</a>
		<br><br>
		<?php echo form_open(get_link("customer_signup"),array()); ?>
			<div class="twelve columns mrg-btn-20">
				<label>{email_text}</label>
				<input class="lang-en full-width" type="text" name="email" />
			</div>
			<div class="twelve columns mrg-btn-40">
				<labeL>{captcha}</label>
				<input class="lang-en full-width" type="text" name="captcha" />
			</div>		
			<div class="twelve columns">				
				<input class="full-width button-primary" type="submit"  value="{signup_text}" />
			</div>
		</form>	

		<div class="twelve columns" style="margin-top:50px">
			<style type="text/css">
				.openid-login
				{
					width:calc(20% - 10px);
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
			<!--<labeL>{social_media_signup_text}</label>-->
			<br>
			<div style="font-size:0">
			<img class="openid-login" src="{images_url}/login-ym.jpg" title="Yahoo!" onclick="yahooSingup();" />
			<img class="openid-login" src="{images_url}/login-gm.jpg" title="Google" onclick="googleSingup();" />
			<img class="openid-login" src="{images_url}/login-fb.jpg" title="Facebook" onclick="facebookSingup();" />
			<img class="openid-login" src="{images_url}/login-ms.jpg" title="Microsoft Live Connect" onclick="microsoftSingup();" />
			<img class="openid-login" src="{images_url}/login-in.jpg" title="Linkedin" onclick="linkedinSingup();" />
		</div>
			<script type="text/javascript">

				function linkedinSingup()
				{
					window.open("{linkedin_signup_page}","_blank","width=600, height=400");
				}

				function microsoftSingup()
				{
					window.open("{microsoft_signup_page}","_blank","width=600, height=400");
				}

				function yahooSingup()
				{
					window.open("{yahoo_signup_page}","_blank","width=600, height=400");
				}

				function googleSingup()
				{
					window.open("{google_signup_page}","_blank","width=600, height=400");
				}

				function facebookSingup()
				{
					window.open("{facebook_signup_page}","_blank","width=600, height=400");
				}
			</script>
		</div>	
	</div>
</div>