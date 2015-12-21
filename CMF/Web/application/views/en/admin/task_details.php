<div class="main">
	<div class="container">
		<h1>{task_details_text}</h1>

		<div class="container separated">
			<h2>{task_props_text}</h2>	
			<?php echo form_open(get_admin_task_details_link($task_info['task_id']),array()); ?>
				<input type="hidden" name="post_type" value="edit_task" />	
				<div class="row even-odd-bg dont-magnify" >
					<div class="seven columns">
						<label>{task_number_text}</label>
						<input type="text" name="task_id" class="full-width" 
							value="<?php echo $task_info['task_id'];?>"
						/>
					</div>
					<div class="seven columns">
						<label>{task_name_text}</label>
						<input type="text" name="task_name" class="full-width" 
							value="<?php echo $task_info['task_name'];?>"
						/>
					</div>
					<div class="seven columns">
						<label>{task_class_name_text}</label>
						<input type="text" name="task_class_name" class="full-width eng ltr" 
							value="<?php echo $task_info['task_class_name'];?>"
						/>
					</div>
					<div class="seven columns">
						<label>{task_active_text}</label>
						<input type="checkbox" name="task_active" class="full-width graphical" 
							<?php if($task_info['task_active'])echo "checked";?>
						/>
					</div>
					<div class="seven columns">
						<label>{task_priority_text}</label>
						<input type="text" name="task_priority" class="full-width ltr" 
							value="<?php echo $task_info['task_priority'];?>"
						/>
					</div>
					<div class="seven columns">
						<label>{task_period_text}</label>
						<input type="text" name="task_period" class="full-width ltr" 
							value="<?php echo $task_info['task_period'];?>"
						/>
					</div>
					<div class="seven columns">
						<label>{task_desc_text}</label>
						<textarea name="task_desc" class="full-width" 
						rows="4"><?php echo $task_info['task_desc'];?></textarea>
					</div>
					<div class="twelve columns">
						<label>{task_users_text}</label>
						<?php
							foreach ($potential_users as $user) {
						?>
							<div class="eleven columns separated half-col-margin">
								<div class="five columns"><?php echo $user['user_name']." ( $user_code_text ".$user['user_code']." )";?></div>
								<div class="three columns user_exec">
									{task_exec_text} :‌ 
									<input type="checkbox" 	class="graphical" 
										onchange="userChanged(this);"
										name="task_user_<?php echo $user['user_id'];?>"								
										<?php 

											if(in_array($user['user_id'],$task_users_ids)) 
												echo "checked";
										?>
									/>
								</div>
								<div class="one column">&nbsp;
								</div>
								<div class="three columns" id="mid_<?php echo $user['user_id'];?>">
									{task_manager_text} : 
									<input type="checkbox" 	class="graphical"
										name="task_user_is_manager_<?php echo $user['user_id'];?>"								
										<?php 
											if(in_array($user['user_id'],$task_managers_ids)) 
												echo "checked";
										?>
									/>
								</div>
							</div>
						<?php 
							}
						?>
						<script type="text/javascript">
							$(function()
							{
								$(".user_exec input").trigger("change");
							});
							function userChanged(el)
							{	
								v=$(el).prop("name").split("task_user_");
								uid=v[1];
								if($(el).prop("checked"))
									$("#mid_"+uid).fadeIn();
								else
									$("#mid_"+uid).fadeOut();
							}
						</script>
					</div>
				</div>
				<div class="row">
						<div class="four columns">&nbsp;</div>
						<input type="submit" class=" button-primary four columns" value="{save_text}"/>
				</div>				
			</form>
		</div>

	</div>
</div>