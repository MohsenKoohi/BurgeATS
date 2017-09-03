<div class="main">
	<div class="container">
		<h1>{es_text}</h1>
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
					var html='';//<option value="">--- انتخاب نمایید ---</option>';
					var provinceCities=cities[province_id];

					//sorting
					var allCities=[];
					var c=0;
					for(var i in provinceCities)
						allCities[c++]={id:i,name:provinceCities[i]};
					for(i=0;i<c;i++)
						for(j=i+1;j<c;j++)
							if(allCities[i].name > allCities[j].name)
							{
								var tempId=allCities[i].id;
								var tempName=allCities[i].name;

								allCities[i].id=allCities[j].id;
								allCities[i].name=allCities[j].name;

								allCities[j].id=tempId;
								allCities[j].name=tempName;
							}

					for(var i in allCities)
						if(allCities.hasOwnProperty(i))
							html+='<option value="'+allCities[i].id+'">'+allCities[i].name+'</option>';
					
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
	</div>
</div>