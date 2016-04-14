<div class="main">
	<div class="container">
		<h1>{message_text} {message_id}
			<?php 
			if($messages) 
				echo $comma_text." ".$messages[0]['message_subject']
			?>
		</h1>		
		<?php 
			if(!$messages) {
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
				
			<?php 
				$i=1;
				$verification_status=array();	
				foreach($messages as $mess)
				{ 
			?>
				<div class="row even-odd-bg">
					<div class="one column counter">
						#<?php echo $i++;?>
					</div>

					<div class="three columns">
						{sender_from_text}:
						<?php 
							$type=$mess['message_sender_type'];
							if($type === "department")
								$sender=$department_text." ".${"department_".$departments[$mess['message_sender_id']]."_text"};
							if($type === "user")
								$sender=$user_text." ".$mess['suc']." - ".$mess['sun'];
							if($type === "customer")
							{
								$link=get_admin_customer_details_link($mess['message_sender_id']);
								$sender="<a href='$link'>"
									.$customer_text." ".$mess['message_sender_id']." - ".$mess['scn']
									."</a>";
							}
							echo "<span>".$sender."</span>";
						?>
						<br>
						{receiver_to_text}:
						<?php 
							$type=$mess['message_receiver_type'];
							if($type === "department")
								$receiver=$department_text." ".${"department_".$departments[$mess['message_receiver_id']]."_text"};
							if($type === "user")
								$receiver=$user_text." ".$mess['ruc']." - ".$mess['run'];
							if($type === "customer")
							{
								$link=get_admin_customer_details_link($mess['message_receiver_id']);
								$receiver="<a href='$link'>"
									.$customer_text." ".$mess['message_receiver_id']." - ".$mess['rcn']
									."</a>";
							}
							echo "<span>".$receiver."</span>";
						?>
						<div class='ltr'>
							<?php echo str_replace("-","/",$mess['message_timestamp']); ?>
						</div>
					</div>
					
					<div class="two columns">
						<label>{subject_text}</label>
						<span>
							<?php echo $mess['message_subject'];?>
						</span>
					</div>

					<div class="four columns message-content">
						<label>{content_text}</label>
						<span>
							<?php echo $mess['message_content'];?>
						</span>
					</div>
					<div class="two columns">
						<label>{status_text}</label>
						<span>
							<?php
								if($mess['message_reply_id'])
									echo $responded_text;
								else
									echo $not_responded_text;
								if(($mess['message_sender_type'] === "customer") && ($mess['message_receiver_type'] === "customer"))
								{
									echo " - ";
									$verification_status[$mess['message_id']]=(int)$mess['message_verifier_id'];
									if($mess['message_verifier_id'])
									{
										$verify="checked";
										echo $verified_text;
									}
									else
									{
										$verify="";
										$not_verified_messages[]=$mess['message_id'];
										echo $not_verified_text;
									}
									$id=$mess['message_id'];
									if($op_access['verifier'])
										echo "<br>".$verify_text.": <span>&nbsp;</span> <input type='checkbox' ".$verify." class='graphical' onchange='verifyMessage($id,$(this).prop(\"checked\"));'>";
								}
							?>
						</span>
					</div>
				</div>
			<?php 
					}
			?>

			<?php 
				if($op_access['verifier'] && $verification_status) {
					echo form_open(get_link("admin_message"),array("onsubmit"=>"return verifySubmit();")); 
			?>
					<br><br>
					<input type="hidden" name="post_type" value="verify_c2c_messages"/>
					<input type="hidden" name="verified_messages" value=""/>
					<input type="hidden" name="redirect_link" value=""/>
					<input type="hidden" name="not_verified_messages" value=""/>
					<div class="row">
							<div class="nine columns">&nbsp;</div>
							<input type="submit" class=" button-primary three columns" value="{verify_text}"/>
					</div>
				</form>

				<script type="text/javascript">
					var verificationStatus=JSON.parse('<?php echo json_encode($verification_status);?>');
					function verifyMessage(mid, checked)
					{
						verificationStatus[mid]=checked;
					}

					function verifySubmit()
					{
						if(!confirm("{are_you_sure_to_submit_text}"))
							return false;

						var v=[];
						var nv=[];
						for(i in verificationStatus)
							if(verificationStatus[i])
								v.push(i);
							else
								nv.push(i);

						$("input[name='verified_messages']").val(v.join(","));
						$("input[name='not_verified_messages']").val(nv.join(","));
						$("input[name='redirect_link']").val(getCustomerSearchUrl(initialFilters));

						return true;
					}
				</script>
			<?php
				}
			?>
		</div>

				<?php 
					if($info['cu_response_time']) {
				?>
					<div class="separated">
						<h2>{the_last_response_text}</h2>
						<div class="row even-odd-bg">
							<div class="three columns">
								<span>{response_time_text}</span>
							</div>
							<div class="nine columns">
								<span style="display:inline-block" class="ltr">
									<?php echo $info['cu_response_time'];?>
								</span>
							</div>
						</div>
						<div class="row even-odd-bg">
							<div class="three columns">
								<span>{user_text}</span>
							</div>
							<div class="nine columns">
								{code_text} <?php echo $info['user_code']." - ".$info['user_name'];?>
							</div>
						</div>
						<div class="row even-odd-bg">
							<div class="three columns">
								<span>{response_content_text}</span>
							</div>
							<?php
								if(preg_match("/ابپتثجچحخدذرز/",$info['cu_response']))
									$lang="fa";
								else
									$lang="en";
							?>
							<div class="nine columns lang-<?php echo $lang;?>">
								<?php echo nl2br($info['cu_response']);?>
							</div>
						</div>
					</div>
				<?php 
					}
				?>
				<div class="separated">
					<?php echo form_open(get_admin_contact_us_message_details_link($message_id),array()); ?>
					<input type="hidden" name="post_type" value="send_response" />			
						<h2>{response_text}</h2>						
						<div class="row even-odd-bg">
							<div class="three columns">
								<span>{language_text}</span>
							</div>
							<div class="three columns">
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
								</select>
								<script type="text/javascript">
									var langSelectVal;

									function langChanged(el)
									{
										if(langSelectVal)
										{
											$("#subject-in").toggleClass(langSelectVal);
											$("#content-ta").toggleClass(langSelectVal);
										}

										langSelectVal="lang-"+""+$(el).val();
										
										$("#subject-in").toggleClass(langSelectVal);
										$("#content-ta").toggleClass(langSelectVal);
									}

									$(function()
									{
										$("select[name='language']").trigger("change");
									});
								</script>
							</div>
						</div>

						<div class="row even-odd-bg">
							<div class="three columns">
								<span>{subject_text}</span>
							</div>
							<div class="nine columns">
								<input id="subject-in" name="subject"  class="full-width" 
									value="<?php echo $info['cu_message_subject'];?>"
								/>
							</div>
						</div>

						<div class="row even-odd-bg">
							<div class="three columns">
								<span>{response_content_text}</span>
							</div>
							<div class="nine columns">
								<textarea id="content-ta" name="content" class="full-width" rows="5"></textarea>
							</div>
						</div>
						<br><br>
						<div class="row">
							<div class="four columns">&nbsp;</div>
							<input type="submit" class=" button-primary four columns" value="{send_text}"/>
						</div>
					</form>
				</div>


			</div>
		<?php 
			}
		?>
	</div>
</div>