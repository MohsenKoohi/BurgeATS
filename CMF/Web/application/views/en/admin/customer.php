<div class="main">
	<div class="container">
		<h1>{customers_text}</h1>
		<div class="tab-container">
			<ul class="tabs">
				<li><a href="#list">{customers_list_text}</a></li>		
				<li><a href="#add">{add_customer_text}</a></li>				
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

			<div class="tab" id="list" style="">
				<h2>{customers_list_text}</h2>
				<div class="container separated">
					<div class="row filter">
						<div class="three columns">
							<label>{name_text}</label>
							<input type="text" name="name" class="full-width" />
						</div>
						<div class="three columns half-col-margin">
							<label>{type_text}</label>
							<select name="type" class="full-width">
								<option value=""></option>
								<?php
									foreach ($customer_types as $type)
										echo "<option value='$type'>".${"type_".$type."_text"}."</option>";
								?>
							</select>
						</div>
						<div class="three columns half-col-margin">
							<label>{email_text}</label>
							<input type="text" name="email" class="full-width" />
						</div>
						<div class="three columns">
							<label>{code_text}</label>
							<input type="text" name="code" class="full-width" />
						</div>
						<div class="three columns half-col-margin">
							<label>{province_text}</label>
							<select name="province" class="full-width" onchange="setCities($(this).val())">
								<option value=""></option>
								<?php 
									foreach($provinces as $pv)
										echo "<option value='".$pv['province_id']."'>".$pv['province_name']."</option>";
								?>
							</select>
						</div>
						<div class="three columns half-col-margin">
							<label>{city_text}</label>
							<select name="city" class="full-width">
							</select>
						</div>

						<div class="three columns ">
							<label>{address_text}</label>
							<input type="text" name="address" class="full-width" />
						</div>

						<div class="three columns half-col-margin">
							<label>{phone_text}/{mobile_text}</label>
							<input type="text" name="phone_mobile" class="full-width" />
						</div>	

					</div>
					<div clas="row">
						<div class="two columns results-search-again">
							<input type="button" onclick="searchAgain()" value="{search_again_text}" class="full-width button-primary" />
						</div>
					</div>
					
					<div class="row results-count" >
						<div class="three columns">
							<label>
								{results_text} {customers_start} {to_text} {customers_end} - {total_results_text}: {customers_total}
							</label>
						</div>
						<div class="three columns results-page-select">
							<select class="full-width" onchange="pageChanged($(this).val());">
								<?php 
									for($i=1;$i<=$customers_total_pages;$i++)
									{
										$sel="";
										if($i == $customers_current_page)
											$sel="selected";

										echo "<option value='$i' $sel>$page_text $i</option>";
									}
								?>
							</select>
						</div>
					</div>

					<script type="text/javascript">
						var cities=JSON.parse('<?php echo json_encode($cities);?>');

						function setCities(province_id)
						{
							var html='<option value=""></option>';
							var provinceCities=cities[province_id];
							for(var i in provinceCities)
								if(provinceCities.hasOwnProperty(i))
									html+='<option value="'+i+'">'+provinceCities[i]+'</option>';
							$(".filter select[name=city]").html(html);
						}

							
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
				</div>		
				<br><br>
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
						<div class="two columns">
							<label>{customer_page_text} </label>
							<a target="_blank" 
							href="<?php echo get_admin_customer_details_link($cs['customer_id']); ?>"
							class="button button-primary sub-primary full-width"
							>
								{view_text}
							</a>
						</div>
					</div>
				<?php } ?>
			</div>

			<div class="tab" id="add" style="">
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
							<label>{phone_text}</label>
							<input value="" type="text" name="customer_phone" class="full-width ltr" />
						</div>
						<div class="three columns">
							<label>{mobile_text}</label>
							<input value="" type="text" name="customer_mobile" class="full-width ltr" />
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
</div>