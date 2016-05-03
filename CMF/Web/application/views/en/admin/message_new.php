<div class="main">
	<div class="container">
		<h1>{add_new_message_text}</h1>

		<?php echo form_open(get_link("admin_message_new"),array("onsubmit"=>"return verifySubmit();")); ?>
			<input type="hidden" name="post_type" value="add_new_message"/>
			<div class="row even-odd-bg">
				<div class="two columns">
					{receiver_text}
				</div>
				<div class="two columns">
					<input type="radio" name="receiver_type" value="user" onchange="receiverTypeChanged()"/> {user_text}	
				</div>
				<?php if($op_access['customers']) {?>
					<div class="two columns">
						<input type="radio" name="receiver_type" value="customer" onchange="receiverTypeChanged()"/> {customer_text}	
					</div>
				<?php } ?>
			
				<div class="three columns">
					<input type="text" class="autocomplete full-width"/>
					<input type="hidden" name="receivers_ids" id="receivers-ids"/>
				</div>
				<div class="tweleve column aclist" id="receivers-list">
					<?php 
						foreach ($receivers_ids as $id => $name) 
						{
							echo "
								<div class='three columns' data-id='$id'>
									$name
									<span class='anti-float' onclick='$(this).parent().remove();'></span>
								</div>";
						}
					?>
				</div>

				<script type="text/javascript">
					function receiverTypeChanged()
					{
						$("#receivers-list *").remove();
					}

					$(document).ready(function()
				   {
				      var el=$("input.autocomplete");
			      	var usersSearchUrl="{users_search_url}";
			      	var customersSearchUrl="{customers_search_url}";
				      	
			      	el.autocomplete({
				         source: function(request, response)
				         {
				         	var tp=$("input[name='receiver_type']:checked").val();	
				         	if(!tp)
				         		return;

				         	var searchUrl;

				         	if(tp == "customer")
				         		searchUrl=customersSearchUrl;
				         	if(tp == "user")
				         		searchUrl=usersSearchUrl;

				            var term=request["term"];
				            $.get(searchUrl+"/"+encodeURIComponent(term),
				              function(res)
				              {
				                var rets=[];
				                for(var i=0;i<res.length;i++)
				                  rets[rets.length]=
				                    {
				                      label:res[i].name
				                      ,name:res[i].name
				                      ,id:res[i].id						                      
				                      ,value:term
				                    };

				                response(rets); 

				                return;       
				              },"json"
				            ); 
				          },
				          delay:700,
				          minLength:1,
				          select: function(event,ui)
				          {
				            var item=ui.item;
				            var id=item.id;
				            var name=item.name;

				            if(!$("div[data-id="+id+"]",$("#receivers-list")).length)
				            	$("#receivers-list").append($("<div class='three columns' data-id='"+id+"'>"+name+"<span class='anti-float' onclick='$(this).parent().remove();'></span></div>"));
				            
				            el.val("");
				            return false;
				          }
				      });

				    });

					function setReceivers()
					{
						var ids=[];
						$("#receivers-list div").each(function(index,el)
						{
							ids[ids.length]=$(el).data("id");
						});
						
						$("#receivers-ids").val(ids.join(","));
					}

					function verifySubmit()
					{
						if(!confirm("{are_you_sure_to_submit_text}"))
							return false;

						setReceivers();

						return true;
					}

				</script>
			</div>
		</form>
	</div>		
</div>