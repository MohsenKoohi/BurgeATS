<div class="main">
	<div class="container">
		<h1>{customer_details_text}</h1>

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
				<li><a href="#props">{properties_text}</a></li>
				<li><a href="#logs">{customer_logs_text}</a></li>
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
			<div class="tab" id="props" style="">
				<div class="container">
					<h2>{properties_text}</h2>	
						<?php if($customer_info) { ?>
							<?php echo form_open(get_admin_customer_details_link($customer_id,$task_id,"props"),array()); ?>
							<input type="hidden" name="post_type" value="customer_properties" />	
							<input type="hidden" name="customer_id" value="<?php echo $customer_id ?>" />	
							<input type="hidden" name="task_id" value="<?php echo $task_id ?>" />	
							
								<div class="row even-odd-bg dont-magnify" >
									<div class="three columns">
										<label>{name_text}</label>
										<input value="<?php echo $customer_info['customer_name'];?>" 
											type="text" name="customer_name" class="full-width" />
									</div>
									<div class="three columns">
										<label>{type_text}</label>
										<select name="customer_type" class="full-width">
											<?php
												foreach ($customer_types as $type)
												{
													$sel="";
													if($type==$customer_info['customer_type'])
														$sel="selected";
													echo "<option value='$type' $sel>".${"type_".$type."_text"}."</option>";
												}
											?>
										</select>
									</div>
									<div class="three columns">
										<label>{email_text}</label>
										<input value="<?php echo $customer_info['customer_email'];?>" 
											type="text" name="customer_email" class="full-width" />
									</div>
									<div class="three columns">
										<label>{code_text}</label>
										<input value="<?php echo $customer_info['customer_code'];?>" 
											type="text" name="customer_code" class="full-width" />
									</div>
									<div class="three columns">
										<label>{province_text}</label>
										<select name="customer_province" class="full-width" onchange="setCities($(this).val());">
											<?php 
												foreach($provinces as $pv)
													echo "<option value='".$pv['province_name']."'>".$pv['province_name']."</option>";
											?>
										</select>
									</div>

									<div class="three columns">
										<label>{city_text}</label>
										<select name="customer_city" class="full-width">
										</select>
									</div>
									<div class="six columns">
										<label>{address_text}</label>
										<input value="<?php echo $customer_info['customer_address'];?>" 
											type="text" name="customer_address" class="full-width" />
									</div>
									<div class="three columns">
										<label>{phone_text}</label>
										<input value="<?php echo $customer_info['customer_phone'];?>" 
											type="text" name="customer_phone" class="full-width eng ltr" />
									</div>
									<div class="three columns">
										<label>{mobile_text}</label>
										<input value="<?php echo $customer_info['customer_mobile'];?>" 
											type="text" name="customer_mobile" class="full-width eng ltr" />
									</div>
								</div>
								<div class="row even-odd-bg dont-magnify" >
									<div class="six columns">
										<label>{desc_text}</label>
										<input type="text" name="desc" class="full-width" />
									</div>					
								</div>
								<br><br>
								<div class="row">
										<div class="four columns">&nbsp;</div>
										<input type="submit" class=" button-primary four columns" value="{save_text}"/>
								</div>				
							</form>
							<script type="text/javascript">
								var cities=JSON.parse('<?php echo json_encode($cities);?>');

								function setCities(province)
								{
									var html='';//<option value="">--- انتخاب نمایید ---</option>';
									var provinceCities=cities[province];
									for(var i in provinceCities)
										html+='<option value="'+provinceCities[i]+'">'+provinceCities[i]+'</option>';
									$("select[name=customer_city]").html(html);
								}

								$(function()
								{
									var province="<?php echo $customer_info['customer_province'];?>";
									var city="<?php echo $customer_info['customer_city'];?>";
									$("select[name=customer_province]").val(province);
									setCities(province);
									$("select[name=customer_city]").val(city);

								})
							</script>
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