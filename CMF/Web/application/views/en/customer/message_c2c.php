<div class="main">
	<div class="container">
		<h1>{send_message_text}</h1>

		<div class="container">
			<?php echo form_open($post_url,array("id"=>"send-message-form","onsubmit"=>"return checkForm();")); ?>
				<div class="row">
					<div class="three columns">
						<label>{receiver_text}</label>
					</div>
					<div class="three columns">
						{customer_name}
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
						{captcha}
					</div>
					<div class="nine columns">
						<input name="captcha" class="lang-en"/>
					</div>
				</div>
				<div class="row">
					<div class="six columns">&nbsp;</div>
					<input type="submit" class=" button-primary three columns" value="{send_text}"/>
				</div>
			</form>

			<script type="text/javascript">
				function checkForm()
				{
					var form=$("#send-message-form");
					var fields=["captcha","content","subject"];
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
		</div>
	</div>
</div>