<div class="main">
	<div class="container">
		<style type="text/css">
			a
			{
				color:black;
			}

			.even-odd-bg div.message-content
			{
				text-overflow: ellipsis;
				overflow:hidden;
				max-height: 110px;
			}

			.view-img
			{
				max-width:60px;
				transition: max-width .5s;
				text-align: left;
			}

			.view-img:hover
			{
				max-width:70px;
				transition: max-width .5s;
			}
		</style>
		<h1>{messages_text}</h1>
		<div class="row general-buttons">
			<a href="<?php echo get_link('admin_message_new');?>" class="two columns">
				<div class="full-width button button-type1 half-col-margin">
					{add_new_message_text}
				</div>
			</a>
		</div>
		<div class="container separated">
			<div class="row filter half-col-margin-children">				
				<div class="three columns">
					<label>{start_date_text}</label>
					<input class="full-width ltr" name="start_date">
				</div>

				<div class="three columns ">
					<label>{end_date_text}</label>
					<input class="full-width ltr" name="end_date">
				</div>
			
				<div class="three columns ">
					<label>{status_text}</label>
					<select class="full-width" name="status">
						<option>&nbsp;</option>
						<option value="changing">{changing_text}</option>
						<option value="complete">{complete_text}</option>
					</select>
				</div>
				
				<div class="three columns">
					<label>{verification_status_of_last_message_text}</label>
					<select class="full-width" name="verified">
						<option>&nbsp;</option>
						<option value="yes">{verified_text}</option>
						<option value="no">{not_verified_text}</option>
					</select>
				</div>
				
				<div class="three columns">
					<label>{sender_text}</label>
					<select class="full-width" name="sender_type" onchange="setSender(this);">
						<option>&nbsp;</option>
						<option value="me">{me_text}</option>
						<?php 
							echo "<option value='user'>{user_text}</option>";
							echo "<option value='department'>{department_text}</option>";
							echo "<option value='customer'>{customer_text}</option>";							
						?>
					</select>

					<div class="no-display">
						
						<div class="three columns" id="sender-departments">
							<label>{sender_department_text}</label>
							<select name="sender_department" class="full-width">
								<option value="">&nbsp;</option>
								<?php
									foreach($departments as $id => $name)
										if($id)
											echo "<option value='$id'>".${"department_".$name."_text"}."</option>\n";
								?>
							</select>
						</div>
					
						<div class="three columns" id="sender-users">
							<label>{sender_user_name_or_id_text}</label>
							<input name="sender_user" type="text" class="full-width">
						</div>


						<div class="three columns" id="sender-customers">
							<label>{sender_customer_name_or_id_text}</label>
							<input name="sender_customer" type="text" class="full-width">
						</div>
					

					</div>
				</div>
				
				<div class="three columns">
					<label>{receiver_text}</label>
					<select class="full-width" name="receiver_type" onchange="setReceiver(this);">
						<option>&nbsp;</option>
						<option value="me">{me_text}</option>
						<?php 
							echo "<option value='user'>{user_text}</option>";
							echo "<option value='department'>{department_text}</option>";
							echo "<option value='customer'>{customer_text}</option>";							
						?>
					</select>

					<div class="no-display">
						<div class="three columns" id="receiver-departments">
							<label>{receiver_department_text}</label>
							<select name="receiver_department" class="full-width">
								<option value="">&nbsp;</option>
								<?php
									foreach($departments as $id => $name)
										if($id)
											echo "<option value='$id'>".${"department_".$name."_text"}."</option>\n";
								?>
							</select>
						</div>
					

						<div class="three columns " id="receiver-users">
							<label>{receiver_user_name_or_id_text}</label>
							<input name="receiver_user" type="text" class="full-width">
						</div>

						
						<div class="three columns " id="receiver-customers">
							<label>{receiver_customer_name_or_id_text}</label>
							<input name="receiver_customer" type="text" class="full-width">
						</div>
					
					</div>
				</div>

				<?php if($op_access['users']) {?>
					<div class="three columns">
						<label>{active_text}</label>
						<select class="full-width" name="active">
							<option>&nbsp;</option>
							<option value="yes">{active_text}</option>
							<option value="no">{inactive_text}</option>
						</select>
					</div>				
				<?php }?>
				<div class="two columns results-search-again ">
					<label></label>
					<input type="button" onclick="searchAgain()" value="{search_again_text}" class="full-width button-primary" />
				</div>				
				
			</div>

			<div class="row results-count" >
				<div class="six columns">
					<label>
						{results_text} {messages_start} {to_text} {messages_end} - {total_results_text}: {messages_total}
					</label>
				</div>
				<div class="three columns results-page-select">
					<select class="full-width" onchange="pageChanged($(this).val());">
						<?php 
							for($i=1;$i<=$messages_total_pages;$i++)
							{
								$sel="";
								if($i == $messages_current_page)
									$sel="selected";

								echo "<option value='$i' $sel>$page_text $i</option>";
							}
						?>
					</select>
				</div>
			</div>

			<script type="text/javascript">
				function setSender(el)
				{
					el=$(el);
					par=el.parent();
					newVal=el.val();
					$("#sender-departments, #sender-users, #sender-customers").each(function(index,elem){
						elem=$(elem);
						$("input,select",elem).addClass("inactive");
						$(".no-display",par).append(elem);
					});

					if(!newVal || newVal=="me")
						return;

					el.parent().after($("#sender-"+newVal+"s"));
					$("input,select",$("#sender-"+newVal+"s")).removeClass("inactive");
				}

				function setReceiver(el)
				{
					el=$(el);
					par=el.parent();
					newVal=el.val();
					$("#receiver-departments, #receiver-users, #receiver-customers").each(function(index,elem){
						elem=$(elem);
						$("input,select",elem).addClass("inactive");
						$(".no-display",par).append(elem);
					});

					if(!newVal || newVal=="me")
						return;

					el.parent().after($("#receiver-"+newVal+"s"));
					$("input,select",$("#receiver-"+newVal+"s")).removeClass("inactive");
				}


				var initialFilters=[];
				<?php
					foreach($filters as $key => $val)
						echo 'initialFilters["'.$key.'"]="'.$val.'";';
				?>
				
				var rawPageUrl="{raw_page_url}";

				$(function()
				{
					$(".filter div input, .filter div select").keypress(function(ev)
					{
						if(13 != ev.keyCode)
							return;

						searchAgain();
					});

					for(i in initialFilters)
						$(".filter [name='"+i+"']").val(initialFilters[i]);

					setSender($("select[name=sender_type]")[0]);
					setReceiver($("select[name=receiver_type]")[0]);
				});

				function searchAgain()
				{
					document.location=getCustomerSearchUrl(getSearchConditions());
				}

				function getSearchConditions()
				{
					var conds=[];

					$(".filter input:not(.inactive), .filter select:not(.inactive)").each(
						function(index,el)
						{
							var el=$(el);

							if(el.prop("type")=="button")
								return;

							if(el.val())
								conds[el.prop("name")]=el.val();

						}
					);
					
					return conds;
				}

				function getCustomerSearchUrl(filters)
				{
					var ret=rawPageUrl+"?";
					for(i in filters)
					{
						var val=filters[i].trim().replace(/\s+/g," ").replace(/[';"]/g,"");
						if(val)
							ret+="&"+i+"="+encodeURIComponent(val);
					}
					return ret;
				}

				function pageChanged(pageNumber)
				{
					document.location=getCustomerSearchUrl(initialFilters)+"&page="+pageNumber;
				}
			</script>
		</div>
		<br>
		<div class="container">			
			<?php 
				$i=$messages_start;
				$verification_status=array();
				if($messages_total)
					foreach($messages as $mess)
					{ 
						$mess_link=get_admin_message_details_link($mess['mi_message_id']);
			?>
						<div class="row even-odd-bg">
							<div class="one column counter">
								#<?php echo $i++;?>
							</div>

							<div class="three columns">
								{sender_from_text}:
								<?php 
									$type=$mess['mi_sender_type'];
									if($type === "department")
										$sender=$department_text." ".${"department_".$departments[$mess['mi_sender_id']]."_text"};
									if($type === "user")
										$sender=$user_text." ".$mess['suc']." - ".$mess['sun'];
									if($type === "customer")
									{
										$link=get_admin_customer_details_link($mess['mi_sender_id']);
										$sender="<a target='_blank' href='$link'>"
											.$customer_text." ".$mess['mi_sender_id']." - ".$mess['scn']
											."</a>";
									}
									echo "<span>".$sender."</span>";
								?>
								<br>
								{receiver_to_text}:
								<?php 
									$type=$mess['mi_receiver_type'];
									if($type === "department")
										$receiver=$department_text." ".${"department_".$departments[$mess['mi_receiver_id']]."_text"};
									if($type === "user")
										$receiver=$user_text." ".$mess['ruc']." - ".$mess['run'];
									if($type === "customer")
									{
										$link=get_admin_customer_details_link($mess['mi_receiver_id']);
										$receiver="<a target='_blank' href='$link'>"
											.$customer_text." ".$mess['mi_receiver_id']." - ".$mess['rcn']
											."</a>";
									}
									echo "<span>".$receiver."</span>";
								?>
								<div class='ltr'>
									<?php echo str_replace("-","/",$mess['mi_last_activity']); ?>
								</div>
							</div>
							
							<div class="two columns">
								<label>{subject_text}</label>
								<span>
									<?php echo $mess['mi_subject'];?>
								</span>
							</div>

							<div class="three columns message-content">
								<label>{content_of_last_message_text}</label>
								<span>
									<?php echo $mess['mt_content'];?>
								</span>
							</div>

							<div class="two columns">
								<label>{status_text}</label>
								<span>
									<?php
										if($mess['mi_complete'])
											echo $complete_text;
										else
											echo $changing_text;

										if($op_access['users'])
											if(!$mess['mi_active'])
												echo " - ".$inactive_text;

										if(($mess['mi_sender_type'] === "customer") && ($mess['mi_receiver_type'] === "customer") && ($mess['mt_sender_type'] === "customer"))
										{
											echo " - ";
											$verification_status[$mess['mt_thread_id']]=(int)$mess['mt_verifier_id'];
											if($mess['mt_verifier_id'])
											{
												$verify="checked";
												echo $verified_text;
											}
											else
											{
												$verify="";
												$not_verified_messages[]=$mess['mi_message_id'];
												echo $not_verified_text;
											}

											$id=$mess['mt_thread_id'];
											if($op_access['verifier'])
												echo "<br>".$verify_text.": <span>&nbsp;</span> <input type='checkbox' ".$verify." class='graphical' onchange='verifyMessage($id,$(this).prop(\"checked\"));'>";
										}
									?>
								</span>
							</div>

							<div class="one column">
								
								<a target="_blank" href="<?php echo $mess_link;?>">
									<img src="{images_url}/details.png" class="view-img anti-float" title="{view_details_text}";/>
								</a>
							
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
	</div>
	<script type="text/javascript">
		$(function()
		{
			$(".row.even-odd-bg div.message-content a").each(
				function(index,el)
				{
					$(el).prop("title",$(el).text());
				}
			);
		});
	</script>
</div>