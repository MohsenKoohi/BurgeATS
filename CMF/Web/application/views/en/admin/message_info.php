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

							<?php 
								echo " - ";
								if($access['supervisor'])
								{
									if($message_info['mi_active']) 
										echo $active_text;
									else
										echo $inactive_text;
								}
							?>
						</div>
					</div>
				</div>			
				<div></div>
				<?php 
					$i=1;
					$verification_status=array();	
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
								{
									$sender=$department_text." ".${"department_".$departments[$thread['mt_sender_id']]."_text"};
									$sender.=" ( ".$user_text." ".$thread['vuc']." - ".$thread['vun']." ) ";
								}
								if($type === "user")
									$sender=$user_text." ".$thread['suc']." - ".$thread['sun'];
								if($type === "customer")
								{
									$link=get_admin_customer_details_link($thread['mt_sender_id']);
									$sender="<a target='_blank' href='$link'>"
										.$customer_text." ".$thread['mt_sender_id']." - ".$thread['scn']
										."</a>";
								}
								echo $sender;
							?>
						</div>

						<div class="three columns">
							<span style="direction:ltr;display:inline-block">
								<?php echo str_replace("-","/",$thread['mt_timestamp']); ?>
							</span>
						</div>

						<?php									
							if(($message_info['mi_sender_type'] === "customer") 
								&& ($message_info['mi_receiver_type'] === "customer")
								&& ($thread['mt_sender_type'] === "customer")
								)
							{
								echo '<div class="five columns">';
								
								$verification_status[$thread['mt_thread_id']]=(int)$thread['mt_verifier_id'];
								if($thread['mt_verifier_id'])
								{
									$verify="checked";
									echo $verified_text." ( ".$user_text." ".$thread['vuc']." - ".$thread['vun']." )";
								}
								else
								{
									$verify="";
									echo $not_verified_text;
								}
								$id=$thread['mt_thread_id'];
								if($access['verifier'])
									echo " - ".$verify_text.": <span>&nbsp;</span> <input type='checkbox' ".$verify." class='graphical' onchange='verifyMessage($id,$(this).prop(\"checked\"));'>";
								
								echo '</div>';
							}
						?>
						
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

			<?php 
				if($access['verifier'] && $verification_status) {
					echo form_open(get_link("admin_message"),array("onsubmit"=>"return verifySubmit();")); 
			?>
					<br><br>
					<input type="hidden" name="post_type" value="verify_c2c_messages"/>
					<input type="hidden" name="verified_messages" value=""/>
					<input type="hidden" name="redirect_link" value="<?php echo get_admin_message_info_link($message_id);?>"/>
					<input type="hidden" name="not_verified_messages" value=""/>
					<div class="row">
							<div class="nine columns">&nbsp;</div>
							<input type="submit" class=" button-primary three columns" value="{verify_text}"/>
					</div>
				</form>

				<script type="text/javascript">
					var verificationStatus=JSON.parse('<?php echo json_encode($verification_status);?>');
					function verifyMessage(tid, checked)
					{
						verificationStatus[tid]=checked;
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
				<h2>{reply_or_comment_text}</h2>
				<?php echo form_open(get_admin_message_info_link($message_id),array()); ?>
				<input type="hidden" name="post_type" value="add_reply_comment" />			
					<div class="row response-type">
						<div class="three columns">
							<label>{type_text}</label>
							<div style="font-size: 1.2em;">
								<div style="display:inline-block">
									<input title="{add_comment_text}" type="radio" name="response_type" checked value="comment"/> {comment_text}
								</div>
								<div style="width:20px;display:inline-block;"></div>
								<div style="display:inline-block">
									<input title="{send_reply_text}" type="radio" name="response_type" value="reply"/> {response_text}
								</div>
							</div>
						</div>
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

						<div class="three columns half-col-margin">
							<label>{status_text}</label>
							<select  name="complete" class="full-width">
								<option value="0" <?php if(!$message_info['mi_complete']) echo "selected"; ?>>{changing_text}</option>
								<option value="1" <?php if($message_info['mi_complete']) echo "selected"; ?>>{complete_text}</option>
							</select>
						</div>

						<?php if($access['supervisor']) { ?>
							<div class="two columns half-col-margin">
								<label>{active_text}</label>
								<input type="checkbox" name="active" class="graphical" <?php if($message_info['mi_active']) echo "checked"; ?> />
							</div>
						<?php } ?>
					</div>	
					<br><br>
					<div class="row">
						<div class="twelve columns">
							<textarea id="content-ta" name="content" class="full-width" rows="7"></textarea>
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
			<div class="separated">
				<h2>{participants_text}</h2>
				<?php echo form_open(get_admin_message_info_link($message_id),array("onsubmit"=>"return participantsFormSubmitted();")); ?>
				<input type="hidden" name="post_type" value="set_participants" />			
					
					<div class="row even-odd-bg dont-magnify">			
						<div class="three columns">
							<span>{departments_text}</span>
						</div>
						<div class="three columns">
							<input type="text" class="deps-autocomplete full-width"/>
							<input type="hidden" name="departments" id="deps-main"/>
						</div>
						<div class="tweleve column aclist" id="deps-list">
							<?php 
								foreach ($access['added_departments'] as $id => $name) 
								{
									$dep_name=${"department_".$name."_text"};
									echo "
										<div class='three columns' data-id='$id'>
											$dep_name
											<span class='anti-float' onclick='$(this).parent().remove();'></span>
										</div>";
								}
							?>
						</div>

						<script type="text/javascript">
							$(document).ready(function()
						   {
						      var el=$("input.deps-autocomplete");
					      	var searchUrl="{departments_search_url}";
						      	
						      	
					      	el.autocomplete({
						         source: function(request, response)
						         {
						            var term=request["term"];
						            $.get(searchUrl+"/"+encodeURIComponent(term),
						              function(res)
						              {
						                var rets=[];
						                for(var i=0;i<res.length;i++)
						                  rets[rets.length]=
						                    {
						                      label:res[i].name
						                      ,name:res[i].name
						                      ,id:res[i].id						                      
						                      ,value:term
						                    };

						                response(rets); 

						                return;       
						              },"json"
						            ); 
						          },
						          delay:700,
						          minLength:1,
						          select: function(event,ui)
						          {
						            var item=ui.item;
						            var id=item.id;
						            var name=item.name;

						            if(!$("div[data-id="+id+"]",$("#deps-list")).length)
						            	$("#deps-list").append($("<div class='three columns' data-id='"+id+"'>"+name+"<span class='anti-float' onclick='$(this).parent().remove();'></span></div>"));
						            
						            el.val("");
						            return false;
						          }
						      });

						    });

							function setDeps()
							{
								var depIds=[];
								$("#deps-list div").each(function(index,el)
								{
									depIds[depIds.length]=$(el).data("id");
								});
								
								$("#deps-main").val(depIds.join(","));
							}

						</script>
					</div>

					<div class="row even-odd-bg dont-magnify">			
						<div class="three columns">
							<span>{users_text}</span>
						</div>
						<div class="three columns">
							<input type="text" class="users-autocomplete full-width"/>
							<input type="hidden" name="users" id="users-main"/>
						</div>
						<div class="tweleve column aclist" id="users-list">
							<?php 
								foreach ($access['added_users'] as $id => $name) 
								{
									$user_name=$name;
									echo "
										<div class='three columns' data-id='$id'>
											$user_name
											<span class='anti-float' onclick='$(this).parent().remove();'></span>
										</div>";
								}
							?>
						</div>

						<script type="text/javascript">
							$(document).ready(function()
						   {
						      var el=$("input.users-autocomplete");
					      	var searchUrl="{users_search_url}";
						      	
					      	el.autocomplete({
						         source: function(request, response)
						         {
						            var term=request["term"];
						            $.get(searchUrl+"/"+encodeURIComponent(term),
						              function(res)
						              {
						                var rets=[];
						                for(var i=0;i<res.length;i++)
						                  rets[rets.length]=
						                    {
						                      label:res[i].name
						                      ,name:res[i].name
						                      ,id:res[i].id						                      
						                      ,value:term
						                    };

						                response(rets); 

						                return;       
						              },"json"
						            ); 
						          },
						          delay:700,
						          minLength:1,
						          select: function(event,ui)
						          {
						            var item=ui.item;
						            var id=item.id;
						            var name=item.name;

						            if(!$("div[data-id="+id+"]",$("#users-list")).length)
						            	$("#users-list").append($("<div class='three columns' data-id='"+id+"'>"+name+"<span class='anti-float' onclick='$(this).parent().remove();'></span></div>"));
						            
						            el.val("");
						            return false;
						          }
						      });

						    });

							function setUsers()
							{
								var userIds=[];
								$("#users-list div").each(function(index,el)
								{
									userIds[userIds.length]=$(el).data("id");
								});
								
								$("#users-main").val(userIds.join(","));
							}

						</script>
					</div>

					<div class="row">
						<div class="four columns">&nbsp;</div>
						<input type="submit" class=" button-primary four columns" value="{submit_text}"/>
					</div>

					<script type="text/javascript">
						function participantsFormSubmitted()
						{
							if(!confirm("{are_you_sure_to_submit_text}"))
								return false;

							setDeps();
							setUsers();

							return true;
						}
					</script>

					
				</form>
			</div>
			<br><br><br>
		<?php 
			}
		?>
	</div>
</div>