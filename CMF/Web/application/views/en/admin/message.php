<div class="main">
	<div class="container">
		<h1>{messages_text}</h1>
		<div class="container separated">
			<div class="row filter">
				<style type="text/css">
					.no-display
					{
						display: none;
					}
				</style>
				<div class="row">
					<div class="three columns">
						<label>{sender_text}</label>
						<select class="full-width" name="sender_type" onchange="setSenders($(this).val());">
							<option>&nbsp;</option>
							<option value="me">{me_text}</option>
							<?php 
								if($op_access['users'])	
									echo "<option value='user'>{user_text}</option>";
								if($op_access['departments'])	
									echo "<option value='department'>{department_text}</option>";
								if($op_access['customers'])	
									echo "<option value='customer'>{customer_text}</option>";							
							?>
						</select>
					</div>
					<? if($op_access['departments']) { ?>
						<div class="three columns half-col-margin" id="sender-departments">
							<label>{sender_department_text}</label>
							<select name="sender_department" class="full-width">
								<option value="">&nbsp;</option>
								<?php
									foreach($op_access['departments'] as $name => $id)
										if($id)
											echo "<option value='$id'>".${"department_".$name."_text"}."</option>\n";
								?>
							</select>
						</div>
					<?php } ?>
					<? if($op_access['users'])	{?>
						<div class="three columns half-col-margin" id="sender-users">
							<label>{sender_user_name_or_id_text}</label>
							<input name="sender_user" type="text" class="full-width">
						</div>
					<?php } ?>
					<? if($op_access['customers'])	{?>
						<div class="three columns half-col-margin" id="sender-customers">
							<label>{sender_customer_name_or_id_text}</label>
							<input name="sender_customer" type="text" class="full-width">
						</div>
					<?php } ?>
				</div>

				
				<div class="two columns results-search-again half-col-margin">
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
				function setSenders(newVal)
				{
					$("#sender-departments, #sender-users, #sender-customers").each(function(index,el){
						$(el).addClass("no-display");
					});

					if(!newVal || newVal=="me")
						return;

					$("#sender-"+newVal+"s").toggleClass("no-display");
				}

				var initialFilters=[];
				<?php
					if(0)
					foreach($filter as $key => $val)
						echo 'initialFilters["'.$key.'"]="'.$val.'";';
				?>
				
				var rawPageUrl="{raw_page_url}";

				$(function()
				{
					$(".filter div:not(.no-display) input, .filter div:not(.no-display) select").keypress(function(ev)
					{
						if(13 != ev.keyCode)
							return;

						searchAgain();
					});

					for(i in initialFilters)
						$(".filter [name='"+i+"']").val(initialFilters[i]);

					setSenders($("select[name=sender_type]").val());
				});

				function searchAgain()
				{
					document.location=getCustomerSearchUrl(getSearchConditions());
				}

				function getSearchConditions()
				{
					var conds=[];

					$(".filter div:not(.no-display) input, .filter div:not(.no-display) select").each(
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
					document.location=getCustomerSearchUrl(initialFilters)+"&page="+pageNumber;
				}
			</script>
		</div>
		<br>
		<div class="container">			
			<?php 
				if($logs['total'])
					for($i=$logs['start'];$i<$logs['end'];$i++)
					{ 
						$log=$logs[$i];
			?>
						<div class="row even-odd-bg" style="display:flex;flex-wrap:wrap">
							<div class="three columns">
								<label>#<?php echo 1+$i;?></label>
							</div>
							<?php foreach ($log as $key => $value) { 
							?>
								<div class="three columns lang-en">
									<span><?php echo $key;?></span>
									<label class="lang-en"><?php echo $value;?></label>
								</div>
							<?php } ?>				
						</div>
			<?php 
					}
			?>
		</div>
	</div>
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
</div>