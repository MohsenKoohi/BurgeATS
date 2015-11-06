<div class="main">
	<div class="container">
		<h1>{customers_text}</h1>

		<div class="container separated">
			<h2>{customers_list_text}</h2>		
				
			<?php foreach($customers_info as $cs) {?>
				<div class="row even-odd-bg" >
					<div class="three columns">
						<label>{name_text}</label>
						<span><?php echo $cs['customer_name'];?></span>
					</div>
					<div class="three columns">
						<label>{type_text}</label>
						<span><?php echo ${"type_".$cs['customer_type']."_text"};?></span>
					</div>
					<div class="three columns">
						<label>{page_text} </label>
						<input value="{view_text}" type="submit" class="button-primary full-width"/>
					</div>
				</div>
			<?php } ?>
			<!--
			<br><br>
			<div class="row">
					<div class="four columns">&nbsp;</div>
					<input type="submit" class=" button-primary four columns" value="{submit_text}"/>
			</div>
			-->
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