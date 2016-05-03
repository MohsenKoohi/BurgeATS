<div class="main">
	<div class="container">
		<h1>{add_new_message_text}</h1>

		<?php echo form_open(get_link("admin_message_new"),array("onsubmit"=>"return verifySubmit();")); ?>
			<input type="hidden" name="post_type" value="add_new_message"/>
			<div class="row even-odd-bg">
				<div class="two columns">
					{receiver_type_text}
				</div>
				<div class="two columns">
					<input type="radio" name="receiver_type" value="user" onchange="receiverTypeChanged()"/> {user_text}	
				</div>
				<?php if($op_access['customers']) {?>
					<div class="two columns">
						<input type="radio" name="receiver_type" value="customer" onchange="receiverTypeChanged()"/> {customer_text}	
					</div>
				<?php } ?>
			</div>
		</form>
	</div>		
</div>