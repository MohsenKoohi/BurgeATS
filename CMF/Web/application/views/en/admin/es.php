<div class="main">
	<div class="container">
		<h1>{es_text}</h1>
		<div class="container separated">
			<div class="row filter">
				<div class="three columns">
					<label>{customer_text}</label>
					<input type="text" name="customer" class="full-width" />
				</div>
				<div class="three columns half-col-margin">
					<label>{start_date_text}</label>
					<input type="text" name="start_date" class="date full-width" />
				</div>
				<div class="three columns half-col-margin">
					<label>{end_date_text}</label>
					<input type="text" name="end_date" class="full-width" />
				</div>

				<div class="three columns">
					<label>{type_text}</label>
					<select name="type" class="full-width">
						<option value="">&nbsp;</option>
						<option value='email'>{email_text}</option>
						<option value='sms'>{sms_text}</option>
					</select>
				</div>
				<div class="three columns  half-col-margin">
					<label>{status_text}</label>
					<select name="status" class="full-width">
						<option value="">&nbsp;</option>
						<?php
							foreach ($statuses as $status)
								echo "<option value='$status'>".${"es_status_".$status."_text"}."</option>";
						?>
					</select>
				</div>
				
			</div>
			<div clas="row">
				<div class="two columns results-search-again">
					<input type="button" onclick="searchAgain()" value="{search_again_text}" class="full-width button-primary" />
				</div>
			</div>
			
			<div class="row results-count" >
				<div class="eight columns">
					<label>
						{results_text} {start} {to_text} {end} - {total_results_text}: {total}
					</label>
				</div>
				<div class="three columns results-page-select">
					<select class="full-width" onchange="pageChanged($(this).val());">
						<?php 
							for($i=1;$i<=$total_pages;$i++)
							{
								$sel="";
								if($i == $current_page)
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

					if(initialFilters['province'])
					{
						$(".filter [name='province']").val(initialFilters['province']);
						setCities($(".filter select[name=province]").val());
					}

					for(i in initialFilters)
						$(".filter [name='"+i+"']").val(initialFilters[i]);
				
				});

				function searchAgain()
				{
					document.location=getCustomerSearchUrl(getSearchConditions());
				}

				function getSearchConditions()
				{
					var conds=[];

					$(".filter input, .filter select").each(
						function(index,el)
						{
							var el=$(el);
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

			<?php foreach($es_info as $e) {?>
				<div class='row even-odd-bg'>
					<div class='two columns'>
						<span class='counter'><?php echo $e['es_id'];?></span>
					</div>

					<div class='two columns'>
						<label>{customer_text}</label>
						<span>
							<?php if($e['es_customer_id']>0){ ?>
								<a href="<?php echo get_admin_customer_details_link($e['es_customer_id']);?>"
									target='_blank'
								>
									<?php echo $e['customer_name'];?>
								</a>
							<?php } else { ?>
								<span class='date'><?php echo $e['es_customer_id'];?></span>
							<?php } ?>
						</span>
					</div>

					<div class='two columns'>
						<label>{type_text}</label>
						<span><?php echo ${$e['es_media']."_text"};?></span>
					</div>

					<div class='two columns'>
						<label>{status_text}</label>
						<span><?php echo ${'es_status_'.$e['es_status']."_text"};?></span>
					</div>

					<div class='two columns'>
						<label>{submit_time_text}</label>
						<span class='date'><?php echo $e['es_submit_time'];?></span>
					</div>

					<div class='two columns'>
						<label>{last_try_text}</label>
						<span class='date'> &nbsp;<?php echo $e['es_last_try_time'];?></span>
					</div>

					<div class='two columns'>
						<label>{try_count_text}</label>
						<span class='date'> <?php echo $e['es_try_count'];?></span>
					</div>

					<div class='two columns'>
						<label>{module_text}</label>
						<span class='date'><?php echo $e['module_name'];?></span>
					</div>

					<div class='two columns'>
						<label>{keyword_text}</label>
						<span class='date'><?php echo $e['es_sender_keyword'];?></span>
					</div>

				</div>
			<?php } ?>
		</div>		
	</div>
</div>