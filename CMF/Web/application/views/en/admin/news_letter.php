<div class="main">
	<div class="container">
		<h1>{news_letter_text}</h1>
		<div class="row general-buttons">
			<div class="three columns">
				<?php echo form_open(get_link("admin_news_letter"),array());?>
					<input type="hidden" name="post_type" value="add_news_letter"/>
					<input type="submit" class="button button-primary full-width" value="{add_news_letter_text}"/>
				</form>
			</div>
		</div>
		<br><br>
		<div class="container separated">
			<div class="row filter">
				<div class="three columns">
					<label>{title_text}</label>
					<input name="title" type="text" class="full-width" value=""/>
				</div>
				
				<div class="two columns results-search-again half-col-margin">
					<label></label>
					<input type="button" onclick="searchAgain()" value="{search_again_text}" class="full-width button-primary" />
				</div>
				
			</div>

			<div class="row results-count" >
				<div class="six columns">
					<label>
						{results_text} {results_start} {to_text} {results_end} - {total_results_text}: {total_results}
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

					for(i in initialFilters)
						$(".filter [name='"+i+"']").val(initialFilters[i]);
				});

				function searchAgain()
				{
					document.location=getSearchUrl(getSearchConditions());
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

				function getSearchUrl(filters)
				{
					var ret=rawPageUrl+"?";
					for(i in filters)
						ret+="&"+i+"="+encodeURIComponent(filters[i].trim().replace(/\s+/g," "));
					return ret;
				}

				function pageChanged(pageNumber)
				{
					document.location=getSearchUrl(initialFilters)+"&page="+pageNumber;
				}
			</script>
		</div>
		<br>
		<div class="container">
			<?php 
				$i=1;
				if(isset($news_letters))
					foreach($news_letters as $n)
					{ 
			?>
						<a target="_blank" href="<?php echo get_admin_news_letter_details_link($post['nlt_id']);?>">
							<div class="row even-odd-bg" >
								<div class="nine columns">
									<span>
										<?php echo $post['nlt_id'];?>)
										<?php 
											if($post['nlt_subject']) 
												echo $post['nlt_subject'];
											else
												echo $no_title_text;
										?>
									</span>
								</div>
							</div>
						</a>
			<?php
					}
			?>
		</div>

	</div>
</div>