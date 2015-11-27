<div class="main">
	<div class="container">
		<h1>{tasks_text}</h1>

		<div class="container separated">
			<h2>{tasks_list_text}</h2>	
			<?php foreach($tasks_info as $task) {?>
				<div class="row even-odd-bg" >
					<div class="three columns">
						<label>{task_name_text}</label>
						<span><?php echo $task['task_name'];?></span>
					</div>
					
					<div class="three columns">
						<label>{task_active_text} </label>
						<span>
							<?php
								if($task['task_active'])
									echo $yes_text;
								else
									echo $no_text;
							?>
						</span>
					</div>
					<div class="three columns">
						<label>{task_page_text} </label>
						<a 
						href="<?php echo get_admin_task_details_link($task['task_id']); ?>"
						class="button button-primary sub-primary full-width"
						>
							{view_text}
						</a>
					</div>
				</div>
			<?php } ?>
		</div>

		<div class="container separated">
			<h2>{add_task_text}</h2>	
			<?php echo form_open(get_link("admin_task"),array()); ?>
				<input type="hidden" name="post_type" value="add_task" />	
				<div class="row even-odd-bg" >
					<div class="three columns half-col-margin">
						<label>{task_name_text}</label>
						<input type="text" name="name" class="full-width" />
					</div>
					<div class="six columns half-col-margin">
						<label>{task_desc_text}</label>
						<textarea name="desc" class="full-width" rows="4"></textarea>
					</div>
				</div>
				<div class="row">
						<div class="four columns">&nbsp;</div>
						<input type="submit" class=" button-primary four columns" value="{add_text}"/>
				</div>				
			</form>
		</div>

	</div>
</div>