<div class="main">
	<div class="container">
		<h1>{customers_text}</h1>

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
			<h2>{add_customer_text}</h2>	
			<?php echo form_open(get_link("admin_customer"),array()); ?>
				<input type="hidden" name="post_type" value="add_customer" />	
				<div class="row even-odd-bg" >
					<div class="three columns">
						<label>{name_text}</label>
						<input type="text" name="customer_name" class="full-width" />
					</div>
					<div class="three columns half-col-margin">
						<label>{type_text}</label>
						<select name="customer_type" class="full-width">
							<?php
								foreach ($customer_types as $type)
									echo "<option value='$type'>".${"type_".$type."_text"}."</option>";
							?>
						</select>
					</div>
					<div class="three columns half-col-margin">
						<label>{desc_text}</label>
						<input type="text" name="desc" class="full-width" />
					</div>					
				</div>
				<br><br>
				<div class="row">
						<div class="four columns">&nbsp;</div>
						<input type="submit" class=" button-primary four columns" value="{add_text}"/>
				</div>				
			</form>
		</div>

	</div>
</div>