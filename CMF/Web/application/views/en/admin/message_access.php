<div class="main">
	<div class="container">
		<h1>{message_access_text}</h1>

		<div class="container">
			<div class="row">
				<div class="three columns">
					<label>{user_text}</label>
				</div>
				<div class="three columns">
					<select class="full-width" name="user_id" onchange="selectNewUser($(this).val());">
						<?php 
							if(!$user_id)
								echo "<option value=''>$select_text</option>";
						
							foreach ($users as $user)
							{
								$sel="";
								if((int)$user['user_id'] === $user_id)
									$sel="selected";
								echo "<option $sel value='".$user['user_id']."'>{code_text} ".$user['user_code']." - ".$user['user_name']."</option>\n";
							}
						?>
					</select>
				</div>
			</div>

			<script type="text/javascript">
				function selectNewUser(new_user_id)
				{
					if(new_user_id)
						document.location=
							"<?php echo get_admin_message_access_user_link('user_id')?>".replace("user_id",new_user_id);
				}
			</script>				

			<?php 
				if($user_id) { 
					echo form_open(get_admin_message_access_user_link($user_id),array()); 
			?>
					<input type="hidden" name="post_type" value="set_access" />
					<div class="row even-odd-bg">
						<div class="three columns">
							<label>{verifier_text}</label>
						</div>
						<div class="three columns">
							<input type="checkbox" name="verifier" class="graphical" 
								<?php if($message_access['verifier']) echo 'checked';?>
							/>
						</div>
					</div>

					<div class="row even-odd-bg">
						<div class="three columns">
							<label>{supervisor_text}</label>
						</div>
						<div class="three columns">
							<input type="checkbox" name="supervisor" class="graphical" 
								<?php if($message_access['supervisor']) echo 'checked';?>
							/>
						</div>
					</div>

					<?php foreach($message_access['departments'] as $dept_name => $dept_val) { ?>
						<div class="row even-odd-bg">
							<div class="three columns">
								<label><?php echo ${"department_".$dept_name."_text"};?></label>
							</div>
							<div class="three columns">
								<input type="checkbox" name="<?php echo $dept_name;?>" class="graphical" 
									<?php if($dept_val) echo 'checked';?>
								/>
							</div>
						</div>
					<?php } ?>

					<br><br>
					<div class="row">
							<div class="four columns">&nbsp;</div>
							<input type="submit" class=" button-primary four columns" value="{submit_text}"/>
					</div>
				</form>
			<?php } ?>
		</div>
	</div>
</div>