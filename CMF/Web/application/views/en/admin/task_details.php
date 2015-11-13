<div class="main">
	<div class="container">
		<h1>{task_details_text}</h1>

		<div class="container separated">
			<h2>{task_props_text}</h2>	
			<?php echo form_open(get_admin_task_details_link($task_info['task_id']),array()); ?>
				<input type="hidden" name="post_type" value="edit_task" />	
				<div class="row even-odd-bg dont-magnify" >
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
							<div class="five columns separated half-col-margin">
								<div class="six columns"><?php echo $user['user_email'];?></div>
								<div class="six columns">
									<input type="checkbox" 	class="graphical"
										name="task_user_<?php echo $user['user_id'];?>"								
										<?php 
											if(in_array($user['user_id'],$task_users_ids)) 
												echo "checked";
										?>
									/>
								</div>
							</div>
						<?php 
							}
						?>
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