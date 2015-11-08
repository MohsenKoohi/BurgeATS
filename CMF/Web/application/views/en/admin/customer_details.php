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
		</style>
		<!--
		<div class="container separated">
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
						<span>{name_text}</span>
						<label><?php echo $cs['customer_name'];?></label>
					</div>
					<div class="three columns">
						<span>{type_text}</span>
						<label><?php echo ${"type_".$cs['customer_type']."_text"};?></label>
					</div>
					<div class="two columns">
						<span>{customer_page_text} </span>
						<a 
						href="<?php echo get_admin_customer_details_link($cs['customer_id']); ?>"
						class="button button-primary full-width"
						>
							{view_text}
						</a>
					</div>
				</div>
			<?php } ?>
		</div>
		<br>
		-->
		<?php bprint_r($customer_info);?>
		<div class="container separated">
			<h2>{properties_text}</h2>	
				<?php if($customer_info) { ?>
					<?php echo form_open(get_link("admin_customer"),array()); ?>
					<input type="hidden" name="post_type" value="customer_properties" />	
						<div class="row even-odd-bg" >
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
								<input value="<?php echo $customer_info['customer_province'];?>" 
									type="text" name="customer_email" class="full-width" />
							</div>
							<div class="three columns">
								<label>{city_text}</label>
								<input value="<?php echo $customer_info['customer_city'];?>" 
									type="text" name="customer_email" class="full-width" />
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

							<div class="three columns">
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
				<?php } ?>				
				
		</div>
		
	</div>
</div>