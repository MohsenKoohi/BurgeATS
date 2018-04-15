<div class="main">
	<div class="container">
		<h1>{news_letter_text}</h1>

		<div class="container">
			<?php echo form_open($raw_page_url,array("id"=>"nlform")); ?>
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
					<input type="button" class=" button-primary two columns" value="{subscribe_text}"
						onclick="subscribe();"
					/>

					<div class="one columns">&nbsp;</div>
					<input type="button" class=" button-primary button-type2 two columns" value="{unsubscribe_text}"
						onclick="unsubscribe();"
					/>
				</div>
			</form>

			<script type="text/javascript">
				function subscribe()
				{
					$("input[name=post_type]").val("add_email");
					$("#nlform").submit();
				}

				function unsubscribe()
				{
					$("input[name=post_type]").val("remove_email");
					$("#nlform").submit();
				}
			</script>
		</div>
	</div>
</div>