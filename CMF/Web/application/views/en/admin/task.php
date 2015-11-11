<div class="main">
	<div class="container">
		<h1>{tasks_text}</h1>

		<div class="container separated">
			<h2>{users_list_text}</h2>		
			<?php echo form_open(get_link("admin_user"),array()); ?>
				<input type="hidden" name="post_type" value="users_list" />
				<?php foreach($users_info as $user) {?>
					<div class="row even-odd-bg" >
						<div class="three columns">
							<label>{email_text}</label>
							<?php echo $user['user_email'];?>
						</div>
						<div class="three columns">
							<label>{new_password_text}</label>
							<input name="pass_user_id_<?php echo $user['user_id']?>" type="password" class="ltr eng"/>
						</div>
						<div class="three columns">
							<label>{delete_text} </label>
							<input name="delete_user_id_<?php echo $user['user_id']?>" type="checkbox" class="graphical" />
						</div>
					</div>
				<?php } ?>
				<br><br>
				<div class="row">
						<div class="four columns">&nbsp;</div>
						<input type="submit" class=" button-primary four columns" value="{submit_text}"/>
				</div>				
			</form>
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