<div class="main">
	<div class="container">
		<h1>{news_letter_text}</h1>

		<div class="container">
			<?php echo form_open($raw_page_url,array()); ?>
				<input type='hidden' name='post_type' value='add_email'/>
				<div class="row">
					<div class="three columns">
						<label>{email_text}</label>
					</div>
					<div class="nine columns">
						<input name="email" type="text" class="lang-en ltr full-width"/>
					</div>
				</div>
				<div class="row">
					<div class="three columns">
						{captcha}
					</div>
					<div class="nine columns">
						<input name="captcha" class="lang-en"/>
					</div>
				</div>
				<div class="row">
					<div class="six columns">&nbsp;</div>
					<input type="submit" class=" button-primary three columns" value="{submit_text}"/>
				</div>
			</form>
		</div>
	</div>
</div>