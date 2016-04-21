<div class="main">
	<div class="container">
		<style type="text/css">
			.even-odd-bg .even-odd-bg
			{
				margin-bottom:-8px;
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
								{
									$sender=$department_text." ".${"department_".$departments[$message_info['mi_sender_id']]."_text"};
									$sender.=" ( ".$user_text." ".$message_info['vuc']." - ".$message_info['vun']." ) ";
								}
								if($type === "user")
									$sender=$user_text." ".$message_info['suc']." - ".$message_info['sun'];
								if($type === "customer")
								{
									$link=get_admin_customer_details_link($message_info['mi_sender_id']);
									$sender="<a href='$link'>"
										.$customer_text." ".$message_info['mi_sender_id']." - ".$message_info['scn']
										."</a>";
								}
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
								if($type === "user")
									$receiver=$user_text." ".$message_info['ruc']." - ".$message_info['run'];
								if($type === "customer")
								{
									$link=get_admin_customer_details_link($message_info['mi_receiver_id']);
									$receiver="<a href='$link'>"
										.$customer_text." ".$message_info['mi_receiver_id']." - ".$message_info['rcn']
										."</a>";
								}
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

					<div class="row">
						<div class="two columns">
							{time_of_last_activity_text}:
						</div>
						<div class="ten columns">
							<span style="direction:ltr;display:inline-block">
								<?php echo str_replace("-","/",$message_info['mi_last_activity']); ?>
							</span>
						</div>
					</div>
							
					<div class="row">
						<div class="two columns">
							{status_text}:
						</div>
						<div class="ten columns">
							<?php									
								if($message_info['mi_complete'])
									echo $complete_text;
								else
									echo $changing_text;
							?>
						</div>
					</div>
				</div>			
					
				<?php 
					$i=1;
					$verification_status=array();	
					foreach($threads as $thread)
					{ 
				?>
					<div class="row even-odd-bg dont-magnify">
						<div class="one columns counter">
							#<?php echo $i++;?>
						</div>
						<div class="eleven columns">
							<div class="row even-odd-bg dont-magnify">
								<div class="two columns">
									{number_text}:
								</div>
								<div class="ten columns">
									<?php echo $mess['mt_thread_id']; ?>
								</div>
							</div>

							<div class="row even-odd-bg dont-magnify">
								<div class="two columns">
									{sender_from_text}:
								</div>
								<div class="ten columns">
									<?php 
										$type=$mess['mi_sender_type'];;
										if($type === "department")
										{
											$sender=$department_text." ".${"department_".$departments[$mess['mi_sender_id']]."_text"};
											$sender.=" ( ".$user_text." ".$mess['vuc']." - ".$mess['vun']." ) ";
										}
										if($type === "user")
											$sender=$user_text." ".$mess['suc']." - ".$mess['sun'];
										if($type === "customer")
										{
											$link=get_admin_customer_details_link($mess['mi_sender_id']);
											$sender="<a href='$link'>"
												.$customer_text." ".$mess['mi_sender_id']." - ".$mess['scn']
												."</a>";
										}
										echo $sender;
									?>
								</div>
							</div>

							<div class="row even-odd-bg dont-magnify">
								<div class="two columns">
									{receiver_to_text}:
								</div>
								<div class="ten columns">
									<?php 
										$type=$mess['mi_receiver_type'];
										if($type === "department")
											$receiver=$department_text." ".${"department_".$departments[$mess['mi_receiver_id']]."_text"};
										if($type === "user")
											$receiver=$user_text." ".$mess['ruc']." - ".$mess['run'];
										if($type === "customer")
										{
											$link=get_admin_customer_details_link($mess['mi_receiver_id']);
											$receiver="<a href='$link'>"
												.$customer_text." ".$mess['mi_receiver_id']." - ".$mess['rcn']
												."</a>";
										}
										echo $receiver;
									?>
								</div>
							</div>
							<div class="row even-odd-bg dont-magnify">
								<div class="two columns">
									{date_text}:
								</div>
								<div class="ten columns">
									<span style="direction:ltr;display:inline-block">
										<?php echo str_replace("-","/",$mess['mt_timestamp']); ?>
									</span>
								</div>
							</div>
							<div class="row even-odd-bg dont-magnify">
								<div class="two columns">
									{subject_text}:
								</div>
								<div class="ten columns">
									<?php echo $mess['mi_subject'];?>
								</div>
							</div>
							<div class="row even-odd-bg dont-magnify">
								<div class="two columns">
									{content_text}:
								</div>
								<?php
									if(preg_match("/[ابپتثجچحخدذرز]/",$mess['mt_content']))
										$lang="fa";
									else
										$lang="en";
								?>
								<div class="ten columns lang-<?php echo $lang;?>">
									<span>
										<?php echo nl2br($mess['mt_content']);?>
									</span>
								</div>
							</div>
							<div class="row even-odd-bg dont-magnify">
								<div class="two columns">
									{status_text}:
								</div>
								<div class="ten columns">
									<?php									
										if(($mess['mi_sender_type'] === "customer") && ($mess['mi_receiver_type'] === "customer"))
										{
											$verification_status[$mess['mt_thread_id']]=(int)$mess['mt_verifier_id'];
											if($mess['mt_verifier_id'])
											{
												$verify="checked";
												echo $verified_text." ( ".$user_text." ".$mess['vuc']." - ".$mess['vun']." )";
											}
											else
											{
												$verify="";
												echo $not_verified_text;
											}
											$id=$mess['mi_message_id'];
											if($access['verifier'])
												echo " - ".$verify_text.": <span>&nbsp;</span> <input type='checkbox' ".$verify." class='graphical' onchange='verifyMessage($id,$(this).prop(\"checked\"));'>";
										}
									?>
								</div>
							</div>			
						</div>
					</div>
				<?php 
						}
				?>

			<?php 
				if($access['verifier'] && $verification_status) {
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