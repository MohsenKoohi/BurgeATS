<div class="main">
	<div class="container">
		<h1>{customer_details_text} <?php if($customer_info) echo $comma_text." ".$customer_info['customer_name']; ?></h1>

		<div class="row general-buttons">
			<div class="two columns button sub-primary button-type1 half-col-margin" onclick="printAddress()">
				{print_address_text}
			</div>
			<div class="two columns button sub-primary button-type2" onclick="customerLogin()">
				{login_text}
			</div>
		</div>

		<style type="text/css">
			.row.even-odd-bg .button-primary
			{
				font-size: 1.1em;
				padding:0;
			}

			label.big-font
			{
				font-size: 2em;
				color:#0C7B77;
			}

			.row.even-odd-bg div label,.row.even-odd-bg div span
			{
				overflow:hidden;
				text-overflow: ellipsis;
				display: block;
			}

			.task_histories .even-odd-bg  div.columns
			{
				box-shadow: 1px 1px #888,-1px -1px #ccc;
				border-radius: 5px;
				padding:10px;
			}

			.task_histories .even-odd-bg:nth-child(2n + 1)  div.columns
			{
				box-shadow: -1px -1px #888,1px 1px #ccc;
			}
			.task_histories .even-odd-bg  .two.columns
			{
				margin:5px;
				width:calc(16.66% - 10px);
			}

			.task_histories .even-odd-bg  .three.columns
			{
				margin:5px;
				width:calc(25% - 10px);
			}
			.task_histories .even-odd-bg  .six.columns
			{
				margin:5px;
				width:calc(50% - 10px);
			}

			.task_histories .manager_note
			{
				margin-top:20px;
			}

			.task_histories > div
			{
				margin-top:10px;
			}

		</style>

		<div class="tab-container">
			<ul class="tabs">
				<li><a href="#tasks">{tasks_text}</a></li>
				<li><a href="#events">{events_text}</a></li>
				<li><a href="#props">{properties_text}</a></li>
				<li><a href="#logs">{customer_logs_text}</a></li>
			</ul>
			<script type="text/javascript">
				$(function(){
				   $('ul.tabs').each(function(){
						var $active, $content, $links = $(this).find('a');
						$active = $($links.filter('[href="'+location.hash+'"]')[0] || $links[0]);
						$active.addClass('active');

						$content = $($active[0].hash);

						$links.not($active).each(function () {
						   $(this.hash).hide();
						});

						$(this).on('click', 'a', function(e){
						   $active.removeClass('active');
						   $content.hide();

						   $active = $(this);
						   $content = $(this.hash);

						   $active.addClass('active');

						   $content.show();						   	

						   e.preventDefault();
						   
						   <?php if(0) { ?>
							   //since each tab has different height, 
							   //we should reequalize  height of sidebar and main div.
							   //may be a bad hack,
							   //which should be corrected in future versions.
							   //
							   //what should we  do ?
							   //we should allow developers to register a list of functions 
							   //to be called on document\.ready event,
							   //but each function has a priority, 
							   //so we can sort their execution by that priority.
							   //and this will solve the problem
							   //for example in this situation, in each load, we should first equalize height of
							   //all tabs, and then call setupMovingHeader 
							   //thus we don't need to call setupMovingHeader in each tab change event
							<?php } ?>
						   setupMovingHeader();
						});
					});
				});
			</script>

			<div class="tab" id="tasks" style="">
				<div class="container">
					<h2>{tasks_text}</h2>
					<div class="row">
						<div class="three columns">{task_text}</div>
						<div class="six columns">
							<select class="full-width" onchange="if($(this).val()) document.location=$(this).val()">
								<option>&nbsp;</option>
								<?php 
									foreach ($customer_tasks as $tid => $tname)
									{
										$sel="";
										if($tid==$task_id)
											$sel="selected";
										$link=get_admin_customer_details_link($customer_id,$tid);
										echo "<option $sel value='$link'>$tname</option>";
									}
								?>
							</select>
						</div>
					</div>
					<?php if(isset($task_info)) { ?>
						<div class="separated">
							<h3>{task_specs_text}</h3>
							<div class="row even-odd-bg dont-magnify" >
								<div class="three columns">
									{task_name_text}
								</div>
								<div class="nine columns">
									<?php echo $task_info['task_name'];?>
								</div>
							</div>
							<div class="row even-odd-bg dont-magnify" >
								<div class="three columns">
									{task_desc_text}
								</div>
								<div class="nine columns">
									<?php echo nl2br($task_info['task_desc']);?>
								</div>
							</div>
						</div>

						<?php /*if($task_exec_info) { ?>
							<div class="separated">
								<h3>{task_last_exec_results_text}</h3>
								<div class="row even-odd-bg dont-magnify" >
									<div class="three columns">
										{task_status_text}
									</div>
									<div class="eight columns">
										<?php 
											echo ${"task_status_".$task_exec_info['te_status']."_text"};
										?>
									</div>
								</div>
								<div class="row even-odd-bg dont-magnify" >
									<div class="three columns">
										{task_exec_count_text}
									</div>
									<div class="eight columns">
										<?php echo $task_exec_info['te_exec_count']; ?>
									</div>
								</div>
								<div class="row even-odd-bg dont-magnify" >
									<div class="three columns">
										{task_last_exec_time_text}
									</div>
									<div class="eight columns">
										<?php echo $task_exec_info['te_last_exec_timestamp']; ?>
									</div>
								</div>
								<div class="row even-odd-bg dont-magnify" >
									<div class="three columns">
										{task_last_exec_user_text}
									</div>
									<div class="eight columns">
										{user_name_text}: <?php echo $task_exec_info['user_name']; ?>
										- {user_code_text}: <?php echo $task_exec_info['user_code']; ?>
									</div>
								</div>
								<div class="row even-odd-bg dont-magnify" >
									<div class="three columns">
										{task_last_exec_result_text}
									</div>
									<div class="eight columns">
										<?php echo nl2br($task_exec_info['te_last_exec_result']); ?>
									</div>
								</div>
								<?php 
									$filename=$task_exec_info['te_last_exec_result_file_name']; 
									if($filename)
									{
										$link=get_admin_task_exec_file($customer_id,$filename);
								?>								
										<div class="row even-odd-bg dont-magnify" >
											<div class="three columns">
												{task_last_exec_result_file_text}
											</div>
											<div class="eight columns">
												<?php echo "<a target='_blank' href='$link'>$filename</a>";?>
											</div>
										</div>
								<?php 
									}
								?>
								<div class="row even-odd-bg dont-magnify" >
									<div class="three columns">
										{task_last_exec_requires_manager_note_text}
									</div>
									<div class="eight columns">
										<?php 
											if($task_exec_info['te_last_exec_requires_manager_note'])
												echo $yes_text;
											else
												echo $no_text;
										?>
									</div>
								</div>
								<div class="row even-odd-bg dont-magnify" >
									<div class="three columns">
										{task_next_exec_text}
									</div>
									<div class="eight columns">
										<?php 
											if($task_exec_info['te_next_exec']!="0000-00-00 00:00:00")
												echo $task_exec_info['te_next_exec']; 
										?>
									</div>
								</div>
								<div class="row even-odd-bg dont-magnify">
									<div class="three columns">
										{task_last_exec_manager_note_text}
									</div>
									<div class="eight columns">
										<?php echo nl2br($task_exec_info['te_last_exec_manager_note']); ?>
									</div>
								</div>
								
							</div>
						<?php }*/ ?>

						<?php if($task_history) { ?>
							<div class="separated task_histories">
								<h3>{task_last_exec_results_text}</h3>
								<?php 
									$i=0; 
									foreach($task_history as $th) 
									{ 
										$i++;
								?>
									<div class="row even-odd-bg dont-magnify" >
										<div class="three columns">
											<label class="big-font">#<?php echo $i;?></label>
										</div>
										<div class="three columns">
											<label>{task_status_text}</label>
											<span>
												<?php 
													echo ${"task_status_".$th->status."_text"};
												?>
											</span>
										</div>											
										<div class="three columns">
											<label>{task_exec_time_text}</label>
											<span>
												<?php echo $th->timestamp; ?>
											</span>
										</div>
										<div class="three columns">
											<label>{task_last_exec_user_text}</label>
											<span>
												{user_name_text}: <?php echo $th->active_user_name; ?>
												- {user_code_text}: <?php echo $th->active_user_code; ?>
											</span>
										</div>									
										<div class="six columns">
											<label>{task_exec_result_text}</label>
											<span><?php echo strip_tags($th->last_exec_result); ?></span>
										</div>
								
										<?php 
											$filename=$th->last_exec_result_file_name; 
											if($filename)
											{
												$link=get_admin_task_exec_file($customer_id,$filename);
										?>								
											<div class="three columns">
												<label>
													{task_exec_file_text}
												</label>
												<span>
													<?php echo "<a target='_blank' href='$link'>$filename</a>";?>
												</span>
											</div>
										<?php 
											}
										?>
										<div class="three columns">
											<label>
												{task_last_exec_requires_manager_note_text}
											</label>
											<span>
												<?php 
													if($th->last_exec_requires_manager_note)
														echo $yes_text;
													else
														echo $no_text;
												?>
											</span>
										</div>									
										<div class="three columns">
											<label>
												{task_next_exec_text}
											</label>
											<span>
												<?php 
													if($th->next_exec!="0000-00-00 00:00:00")
														echo "<span style='display:inline;dir:ltr'>".$th->next_exec."</span>"; 
												?>
											</span>
										</div>
										<?php 
											if(isset($th->manager_note))
												foreach($th->manager_note as $note) 
												{ 
													$cols="three";
													if(isset($note->next_exec))
														$cols="two";
										?>
											
											<div class="twelve columns manager_note">
												<label>{manager_note_text}</label>
												<div class="<?php echo $cols;?> columns">
													<label>{time_text}</label>
													<span>
														<?php echo $note->timestamp; ?>
													</span>
												</div>
												<div class="<?php echo $cols;?> columns">
													<label>{status_text}</label>
													<span>
														<?php echo ${"task_status_".$note->status."_text"};?>
													</span>
												</div>
												<?php if(isset($note->next_exec)) { ?>
													<div class="two columns">
														<label>{task_next_exec_text}</label>
														<span>
															<?php echo $note->next_exec;?>
														</span>
													</div>
												<?php } ?>
												<div class="six columns">
													<label>{note_text}</label>
													<span>
														<?php echo ($note->last_exec_manager_note); ?>
													</span>
												</div>
											</div>
										<?php 
													} 
										?>
									</div>								
								<?php
									} 
								?>
							</div>
						<?php } ?>

						<?php if($task_exec_info && $user_is_manager) { ?>
							<div class="separated">
								<h3>{manager_note_text}</h3>
								<?php echo form_open(get_admin_customer_details_link($customer_id,$task_id,"tasks"),array()); ?>
									<input type="hidden" name="post_type" value="manager_note" />	
									<span></span>
									<div class="row even-odd-bg dont-magnify" >
										<div class="three columns">
											<span>{task_status_text}</span>
										</div>
										<div class="six columns">
											<select name="manager_task_status" class="full-width" onchange="managerTaskStatusChanged();">
												<?php 
													foreach($task_exec_statuses as $status)
													{
														$sel="";
														if($task_exec_info['te_status'] === $status)
															$sel="selected";
														echo "<option $sel value='$status'>".${"task_status_".$status."_text"}."</option>";
													}
												?>
											</select>
										</div>					
									</div>
									<div class="row even-odd-bg dont-magnify" >
										<div class="three columns">
											<span>{manager_note_text}</span>
										</div>
										<div class="six columns">
											<textarea rows="3" name="manager_note" class="full-width"></textarea>
										</div>					
									</div>
									<div class="row even-odd-bg dont-magnify" id="manager_remind_days_row" >
										<div class="three columns">
											<span>{task_exec_remind_in_text}</span>
										</div>
										<div class="six columns">
											<input type="number" name="manager_remind_in" value=""/>
											{days_text}
										</div>					
									</div>
									<script type="text/javascript">
										$(managerTaskStatusChanged);
										function managerTaskStatusChanged()
										{
											val=$("select[name='manager_task_status']").val();
											if(val == "changing")
												$("#manager_remind_days_row").fadeIn();
											else
												$("#manager_remind_days_row").fadeOut();
										}
									</script>
								
									<br><br>
									<div class="row">
											<div class="four columns">&nbsp;</div>
											<input type="submit" class=" button-primary four columns" value="{save_text}"/>
									</div>
								</form>
							</div>
						<?php } ?>
						
						<?php if($user_can_exec) { ?>
							<div class="separated">
								<h3>{task_exec_text}</h3>
								<?php echo form_open_multipart(get_admin_customer_details_link($customer_id,$task_id,"tasks"),array()); ?>
									<input type="hidden" name="post_type" value="task_exec" />	
									<span></span>
									<div class="row even-odd-bg dont-magnify" >
										<div class="three columns">
											<span>{task_status_text}</span>
										</div>
										<div class="six columns">
											<select name="task_status" class="full-width" onchange="taskStatusChanged();">
												<?php 
													foreach($task_exec_statuses as $status)
													{
														$sel="";
														if($task_exec_info && ($task_exec_info['te_status'] === $status))
															$sel="selected";
														echo "<option $sel value='$status'>".${"task_status_".$status."_text"}."</option>";
													}
												?>
											</select>
										</div>					
									</div>
									<div class="row even-odd-bg dont-magnify" >
										<div class="three columns">
											<span>{task_exec_result_text}</span>
										</div>
										<div class="six columns">
											<textarea rows="3" name="task_exec_result" class="full-width"></textarea>
										</div>					
									</div>
									<div class="row even-odd-bg dont-magnify" >
										<div class="three columns">
											<span>{task_exec_result_file_text}</span>
										</div>
										<div class="six columns">
											<input type="file" name="task_exec_file" />
										</div>					
									</div>
									<div class="row even-odd-bg dont-magnify" >
										<div class="three columns">
											<span>{task_exec_request_manager_note_text}</span>
										</div>
										<div class="six columns">
											<input type="checkbox" name="task_exec_requires_manager_note" class="graphical"/>
										</div>					
									</div>
									<div class="row even-odd-bg dont-magnify" id="remind_days_row" >
										<div class="three columns">
											<span>{task_exec_remind_in_text}</span>
										</div>
										<div class="six columns">
											<input type="number" name="task_exec_remind_in" value="1"/>
											{days_text}
										</div>					
									</div>
									<br><br>
									<div class="row">
											<div class="four columns">&nbsp;</div>
											<input type="submit" class=" button-primary four columns" value="{save_text}"/>
									</div>				
								</form>
								<script type="text/javascript">
									$(taskStatusChanged);
									function taskStatusChanged()
									{
										val=$("select[name='task_status']").val();
										if(val == "changing")
											$("#remind_days_row").fadeIn();
										else
											$("#remind_days_row").fadeOut();
									}
								</script>
							</div>
						<?php } ?>	
					<?php } ?>
				</div>
			</div>
			
			<div class="tab" id="events" style="">
				<div class="container">
					<h2>{events_text}</h2>
					<?php echo form_open(get_admin_customer_details_link($customer_id,$task_id,"events"),array()); ?>
						<input type="hidden" name="post_type" value="set_events" />
						<?php foreach($customer_event_types as $et) { ?>
							<div class="row even-odd-bg dont-magnify" >
								<div class="four columns">
									<?php echo ${"customer_event_".$et."_text"}; ?>
								</div>
								<div class="four columns">
									<input type="checkbox" name="<?php echo $et;?>" class="graphical"
										<?php if(isset($customer_events[$et])) echo "checked" ?>
									/>
								</div>
								<div class="four columns">
									<?php if(isset($customer_events[$et])) echo $customer_events[$et]; ?>
								</div>
							</div>
						<?php } ?>
						<br><br>
						<div class="row">
							<div class="four columns">&nbsp;</div>
							<input type="submit" class="button-primary four columns" value="{save_text}"/>
						</div>				
					</form>
				</div>
			</div>

			<div class="tab" id="props" style="">
				<div class="container">
					<h2>{properties_text}</h2>	
						<?php if($customer_info) { ?>
							<?php echo form_open(get_admin_customer_details_link($customer_id,$task_id,"props"),array()); ?>
								<input type="hidden" name="post_type" value="customer_properties" />	
								<span></span>
								<div class="row even-odd-bg dont-magnify" >
									<div class="three columns">
										<label>{name_text}</label>
										<input value="<?php echo $customer_info['customer_name'];?>" 
											type="text" name="customer_name" class="full-width" />
									</div>
									<div class="three columns">
										<label>{type_text}</label>
										<select name="customer_type" class="full-width">
											<?php
												foreach ($customer_types as $type)
												{
													$sel="";
													if($type==$customer_info['customer_type'])
														$sel="selected";
													echo "<option value='$type' $sel>".${"type_".$type."_text"}."</option>";
												}
											?>
										</select>
									</div>
									<div class="three columns">
										<label>{email_text}</label>
										<input value="<?php echo $customer_info['customer_email'];?>" 
											type="text" name="customer_email" class="full-width" />
									</div>
									<div class="three columns">
										<label>{code_text}</label>
										<input value="<?php echo $customer_info['customer_code'];?>" 
											type="text" name="customer_code" class="full-width" />
									</div>
									<div class="three columns">
										<label>{province_text}</label>
										<select name="customer_province" class="full-width" onchange="setCities($(this).val());">
											<?php 
												foreach($provinces as $pv)
													echo "<option value='".$pv['province_id']."'>".$pv['province_name']."</option>";
											?>
										</select>
									</div>

									<div class="three columns">
										<label>{city_text}</label>
										<select name="customer_city" class="full-width">
										</select>
									</div>
									<div class="six columns">
										<label>{address_text}</label>
										<input value="<?php echo $customer_info['customer_address'];?>" 
											type="text" name="customer_address" class="full-width" />
									</div>
									<div class="three columns">
										<label>{phone_text}</label>
										<input value="<?php echo $customer_info['customer_phone'];?>" 
											type="text" name="customer_phone" class="full-width eng ltr" />
									</div>
									<div class="three columns">
										<label>{mobile_text}</label>
										<input value="<?php echo $customer_info['customer_mobile'];?>" 
											type="text" name="customer_mobile" class="full-width eng ltr" />
									</div>
								</div>
								<div class="row even-odd-bg dont-magnify" >
									<div class="six columns">
										<label>{desc_text}</label>
										<input type="text" name="desc" class="full-width" />
									</div>					
								</div>
								<br><br>
								<div class="row">
										<div class="four columns">&nbsp;</div>
										<input type="submit" class=" button-primary four columns" value="{save_text}"/>
								</div>				
							</form>
							<script type="text/javascript">
								var cities=JSON.parse('<?php echo json_encode($cities);?>');

								function setCities(province_id)
								{
									var html='';//<option value="">--- انتخاب نمایید ---</option>';
									var provinceCities=cities[province_id];
									for(var i in provinceCities)
										if(provinceCities.hasOwnProperty(i))
											html+='<option value="'+i+'">'+provinceCities[i]+'</option>';
									$("select[name=customer_city]").html(html);
								}

								$(function()
								{
									var province="<?php echo $customer_info['customer_province'];?>";
									var city="<?php echo $customer_info['customer_city'];?>";
									$("select[name=customer_province]").val(province);
									setCities(province);
									$("select[name=customer_city]").val(city);

								})
							</script>
						<?php } ?>				
						
				</div>
			</div>

			<div class="tab" id="logs">
				<div class="container">
					<h2>{customer_logs_text}</h2>
					<?php if($customer_info) { ?>
						<div class="container separated">
							<div class="row filter">
								<div class="three columns">
									<label>{log_type_text}</label>
									<select name="log_type" class="full-width en ltr">
										<option value=""></option>
										<?php
											foreach ($log_types as $text=>$type)
												echo "<option value='$text'>$text</option>";
										?>
									</select>
								</div>
								<div class="two columns results-search-again">
									<label></label>
									<input type="button" onclick="searchAgain()" value="{search_again_text}" class="full-width button-primary" />
								</div>
							</div>
							
							<div class="row results-count" >
								<div class="six columns">
									<label>
										{results_text} {logs_start} {to_text} {logs_end} - {total_results_text}: {logs_total}
									</label>
								</div>
								<div class="three columns results-page-select">
									<select class="full-width" onchange="pageChanged($(this).val());">
										<?php 
											for($i=1;$i<=$logs_total_pages;$i++)
											{
												$sel="";
												if($i == $logs_current_page)
													$sel="selected";

												echo "<option value='$i' $sel>$page_text $i</option>";
											}
										?>
									</select>
								</div>
							</div>

							<script type="text/javascript">
								var initialFilters=[];
								<?php
									foreach($filter as $key => $val)
										echo 'initialFilters["'.$key.'"]="'.$val.'";';
								?>
								var rawPageUrl="{raw_page_url}";

								$(function()
								{
									$(".filter input, .filter select").keypress(function(ev)
									{
										if(13 != ev.keyCode)
											return;

										searchAgain();
									});

									for(i in initialFilters)
										$(".filter [name='"+i+"']").val(initialFilters[i]);
								});

								function searchAgain()
								{
									document.location=getCustomerSearchUrl(getSearchConditions())+"#logs";
								}

								function getSearchConditions()
								{
									var conds=[];

									$(".filter input, .filter select").each(
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
										ret+="&"+i+"="+encodeURIComponent(filters[i].trim().replace(/\s+/g," "));
									return ret;
								}

								function pageChanged(pageNumber)
								{
									document.location=getCustomerSearchUrl(initialFilters)+"&page="+pageNumber+"#logs";
								}
							</script>
						</div>		
						<br>
						<?php $i=$logs_start;foreach($customer_logs as $log) { ?>
							<div class="row even-odd-bg" style="display:flex;flex-wrap:wrap">
								<div class="three columns" style="">
									<label class="big-font">#<?php echo $i++;?></label>
								</div>
								<?php 
									$c=1;
									if($log)
										foreach ($log as $key => $value) { 
								?>
									<div class="three columns eng ltr separated " style="">
										<label><?php echo $key;?></label>
										<span class="eng ltr"><?php echo $value;?></span>
									</div>
								<?php } ?>				
							</div>
						<?php } ?>
						<script type="text/javascript">
							$(function()
							{
								$(".row.even-odd-bg div label").each(
									function(index,el)
									{
										$(el).prop("title",$(el).text());
									}
								);
							});
						</script>
					<?php } ?>
				</div>
			</div>
		</div>
		<br>	
		<br>
		<?php 
			echo form_open(get_admin_customer_details_link($customer_id,$task_id),
						array(
							"style"=>'display:none'
							,"id"=>"hidden_form"
							,"target"=>"_blank"
						)
					);
		?>
			<input type="hidden" name="post_type"> 
		</form>
		<script type="text/javascript">
			var our_address="<?php echo $our_address;?>";

			function printAddress()
			{
				if(!our_address)
				{
					alert("{address_has_not_been_specified_text}");
					return;
				}

				html="<html><head><meta charset='UTF-8' /></head><body style='direction:rtl;font-family: b mitra;font-size:40px;font-weight:bold'>";
				html+="{reciever_text}:<br>";
				html+=$("#props [name='customer_province']").val();
				html+=" - "+$("#props [name='customer_city']").val();
				html+=" - "+$("#props [name='customer_address']").val();
				html+="<br>"+$("#props [name='customer_name']").val();
				html+="<br><br>";
				html+="{sender_text}:<br>";
				html+=our_address;
				html+="<div style='page-break-after:always'></div>";
				html+="</body></html>";

				var win=window.open("","customer_address");

				win.document.documentElement.innerHTML+=html;
				 
				win.stop();
			}

			function customerLogin()
			{
				if(!$("#props input[name='customer_email']").val())
				{
					alert("{customer_email_has_not_been_specified_text}");
					return;
				}

				$("#hidden_form input[name='post_type']").val("customer_login");
				$("#hidden_form").submit();
			}
			
		</script>
	</div>
</div>