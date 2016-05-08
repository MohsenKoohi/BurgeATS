<div class="main">
	<div class="container">
		<style type="text/css">
			.even-odd-bg .even-odd-bg
			{
				margin-bottom:-8px;
			}

			.even-odd-bg.row div.content
			{
				padding:10px;
				border:1px solid #ddd;
				border-radius: 10px;
				max-height: 200px;
				overflow: auto;
				min-height: 50px;
			}
		</style>
		<h1>{message_text} {message_id}
			<?php 
				if($message_info) 
					echo $comma_text." ".$message_info['mi_subject'];
			?>
		</h1>		
		<?php 
			if(!$message_info) {
		?>
			<h4>{not_found_text}</h4>
		<?php 
			}else{ 
		?>
			<div class="container">
				<?php if(0){ ?>	
					<div class="row general-buttons">
						<div class="two columns button sub-primary button-type2" onclick="deleteMessage()">
							{delete_text}
						</div>
					</div>
					<br><br>
				<?php } ?>		
			
				<div style="font-size:1.3em">
					<div class="row">
						<div class="two columns">
							{sender_from_text}:
						</div>
						<div class="ten columns">
							<?php 
								$type=$message_info['mi_sender_type'];;
								if($type === "department")
									$sender=$department_text." ".${"department_".$departments[$message_info['mi_sender_id']]."_text"};
								if($type === "customer")
									$sender=$customer_text." ".$message_info['mi_sender_id']." - ".$message_info['scn'];
								echo $sender;
							?>
						</div>
					</div>

					<div class="row">
						<div class="two columns">
							{receiver_to_text}:
						</div>
						<div class="ten columns">
							<?php 
								$type=$message_info['mi_receiver_type'];
								if($type === "department")
									$receiver=$department_text." ".${"department_".$departments[$message_info['mi_receiver_id']]."_text"};
								if($type === "customer")
									$receiver=$customer_text." ".$message_info['mi_receiver_id']." - ".$message_info['rcn'];
								echo $receiver;
							?>
						</div>
					</div>

					<div class="row">
						<div class="two columns">
							{subject_text}:
						</div>
						<div class="ten columns">
							<?php echo $message_info['mi_subject'];?>
						</div>
					</div>
				</div>			
				<div></div>
				<?php 
					$i=1;
					foreach($threads as $thread)
					{ 
				?>
					<div class="row even-odd-bg dont-magnify">
						<div class="one columns counter" title="<?php echo $thread['mt_thread_id']; ?>">
							# <?php echo $i++;?>
						</div>								
						<div class="three columns">
							<?php 
								$type=$thread['mt_sender_type'];;
								if($type === "department")
									$sender=$department_text." ".${"department_".$departments[$thread['mt_sender_id']]."_text"};
								if($type === "customer")
									$sender=$customer_text." ".$thread['mt_sender_id']." - ".$thread['scn'];
								echo $sender;
							?>
						</div>

						<div class="three columns">
							<span style="direction:ltr;display:inline-block">
								<?php echo str_replace("-","/",$thread['mt_timestamp']); ?>
							</span>
						</div>

						<?php
							if(preg_match("/[ابپتثجچحخدذرز]/",$thread['mt_content']))
								$lang="fa";
							else
								$lang="en";
						?>
						<div class="content eleven columns lang-<?php echo $lang;?>">
							<span>
								<?php echo nl2br($thread['mt_content']);?>
							</span>
						</div>			
					</div>
				<?php 
						}
				?>
			</div>
			
			<div class="separated">
				<h2>{reply_text}</h2>
				<?php echo form_open(get_customer_message_details_link($message_id),array(
					"onsubmit"=>"return confirm('{are_you_sure_to_send_text}')")); ?>
				<input type="hidden" name="post_type" value="add_reply" />			
					<div class="row response-type">
						<div class="three columns">
							<label>{language_text}</label>
							<select name="language" class="full-width" onchange="langChanged(this);">
								<?php
									foreach($all_langs as $key => $val)
									{
										$sel="";
										if($key===$selected_lang)
											$sel="selected";

										echo "<option $sel value='$key'>$val</option>";
									}
								?>
								<script type="text/javascript">
									var langSelectVal;

									function langChanged(el)
									{
										if(langSelectVal)
											$("#content-ta").toggleClass(langSelectVal);

										langSelectVal="lang-"+""+$(el).val();
										
										$("#content-ta").toggleClass(langSelectVal);
									}

									$(function()
									{
										$("select[name='language']").trigger("change");
									});
								</script>
							</select>
						</div>
					</div>	
					<br><br>
					<div class="row">
						<div class="twelve columns">
							<textarea id="content-ta" name="content" class="full-width" rows="7"></textarea>
						</div>
					</div>
					<div class="row">
						<div class="two columns">
							{captcha}
						</div>
						<div class="three columns">
							<input name="captcha" class="full-width"/>
						</div>
					</div>
					<br><br>
					<div class="row">
						<div class="four columns">&nbsp;</div>
						<input type="submit" class=" button-primary four columns" value="{send_text}"/>
					</div>
				</form>
			</div>
			<br><br>
		<?php 
			}
		?>
	</div>
</div>