<div class="main">
	<div class="container">
		<h1>{contact_us_text}</h1>

		<div class="container">
			<?php if(!$customer_logged_in) { ?>
				<div class="row">
					<div class="tweleve columns" style="font-size:1.3em">
						{to_send_message_you_should_login_text}
						<br>
						<br>
						<div class="twelve columns half-col-margin">
							<a href="<?php echo get_link("customer_login")?>">{login_text}</a>
						</div>
						<br>
						<div class="twelve columns half-col-margin">
							<a href="<?php echo get_link("customer_signup")?>">{register_text}</a>
						</div>
					</div>
				</div>
			<?php } else {?>
				<?php echo form_open_multipart(get_link("customer_contact_us"),array("id"=>"contact-form","onsubmit"=>"return checkForm();")); ?>
					<div class="row">
						<div class="three columns">
							<label>{department_text}</label>
						</div>
						<div class="three columns">
							<select name="department" class="full-width">
								<option value="">{select_text}</option>
								<?php
									foreach ($departments as $index => $name)
										echo "<option value='$index'>${'department_'.$name.'_text'}</option>\n";
								?>
							</select>
						</div>
					</div>
					
					<div class="row">
						<div class="three columns">
							<label>{subject_text}</label>
						</div>
						<div class="nine columns">
							<input name="subject" class="full-width" value="{subject}"/>
						</div>
					</div>
					<div class="row">
						<div class="three columns">
							<label>{content_text}</label>
						</div>
						<div class="nine columns">
							<textarea name="content" class="full-width" rows="5">{content}</textarea>
						</div>
					</div>
					<div class="row">
						<div class="three columns">
							<span>{attachment_text}</span>
						</div>
						<div class="three columns">
							<input type="file" name="attachment" class="full-width" />
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

				<script type="text/javascript">
					function checkForm()
					{
						var form=$("#contact-form");
						var fields=["department","captcha","content","subject"];
						var result=true;
						$(fields).each(function(index,value)
						{
							var val=$("[name='"+value+"']",form).val();
							if(!val)
							{
								result=false;		
								return false;
							}							
						});

						if(!result)
							alert("{fill_all_fields_text}");
					
						return result;
					}

				</script>
			<?php } ?>
		</div>
	</div>
</div>