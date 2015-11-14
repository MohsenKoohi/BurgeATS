<div class="main">
	<div class="container">
		<h1>{tasks_exec_text}</h1>

		<style type="text/css">
			.row.even-odd-bg span
			{
				font-size: .8em;
				//color:#555;
			}

			.row.even-odd-bg .button-primary
			{
				font-size: 1.1em;
				padding:0;
			}

			label.big-font
			{
				font-size: 2em;
				color:#0C7B77;
			}

			.row.even-odd-bg div label
			{
				overflow:hidden;
				text-overflow: ellipsis;
			}
		</style>

		<div class="tab-container">
			<ul class="tabs">
				<li><a href="#exec">{execution_text}</a></li>
				<li><a href="#logs">{logs_text}</a></li>
			</ul>
			<script type="text/javascript">
				$(function(){
				   $('ul.tabs').each(function(){
						var $active, $content, $links = $(this).find('a');
						$active = $($links.filter('[href="'+location.hash+'"]')[0] || $links[0]);
						$active.addClass('active');

						$content = $($active[0].hash);

						$links.not($active).each(function () {
						   $(this.hash).hide();
						});

						$(this).on('click', 'a', function(e){
						   $active.removeClass('active');
						   $content.hide();

						   $active = $(this);
						   $content = $(this.hash);

						   $active.addClass('active');

						   $content.show();						   	

						   e.preventDefault();
						   
						   <?php if(0) { ?>
							   //since each tab has different height, 
							   //we should reequalize  height of sidebar and main div.
							   //may be a bad hack,
							   //which should be corrected in future versions.
							   //
							   //what should we  do ?
							   //we should allow developers to register a list of functions 
							   //to be called on document\.ready event,
							   //but each function has a priority, 
							   //so we can sort their execution by that priority.
							   //and this will solve the problem
							   //for example in this situation, in each load, we should first equalize height of
							   //all tabs, and then call setupMovingHeader 
							   //thus we don't need to call setupMovingHeader in each tab change event
							<?php } ?>
						   setupMovingHeader();
						});
					});
				});
			</script>
			<div class="tab" id="exec" style="">
				<div class="container">
					<h2>{execution_text}</h2>	
					<?php foreach($tasks as $task) { ?>
						<div class="row even-odd-bg dont-magnify" >
							<a target="_blank" href="<?php echo get_admin_task_exec_details_link($task['task_id'],$task['customer_id']);?>">
								<div class="twelve columns">
									{task_text} :
									<?php echo $task['task_name'];?>
									, {customer_text} : 
									<?php echo $task['customer_name'];?>
								</div>
							</a>
						</div>				
					<?php } ?>						
				</div>
			</div>

			<div class="tab" id="logs">
				<div class="container">
					<h2>{customer_logs_text}</h2>
					<?php if($customer_info) { ?>
						<div class="container separated">
							<div class="row filter">
								<div class="three columns">
									<label>{log_type_text}</label>
									<select name="log_type" class="full-width en ltr">
										<option value=""></option>
										<?php
											foreach ($log_types as $text=>$type)
												echo "<option value='$text'>$text</option>";
										?>
									</select>
								</div>
								<div class="two columns results-search-again">
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

									for(i in initialFilters)
										$(".filter [name='"+i+"']").val(initialFilters[i]);
								});

								function searchAgain()
								{
									document.location=getCustomerSearchUrl(getSearchConditions())+"#logs";
								}

								function getSearchConditions()
								{
									var conds=[];

									$(".filter input, .filter select").each(
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
									document.location=getCustomerSearchUrl(initialFilters)+"&page="+pageNumber+"#logs";
								}
							</script>
						</div>		
						<br>
						<?php $i=$logs_start;foreach($customer_logs as $log) { ?>
							<div class="row even-odd-bg" >
								<div class="three columns">
									<label class="big-font">#<?php echo $i++;?></label>
								</div>
								<?php 
									if($log)
									foreach ($log as $key => $value) { 
								?>
									<div class="three columns eng ltr">
										<span><?php echo $key;?></span>
										<label class="eng ltr"><?php echo $value;?></label>
									</div>
								<?php } ?>				
							</div>
						<?php } ?>
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
					<?php } ?>
				</div>
			<div>
		</div>

		
		
		<br>
					
		<br>
		
		
	</div>
</div>