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
	</div>
</div>