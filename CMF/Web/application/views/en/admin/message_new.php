<div class="main">
	<link rel="stylesheet" type="text/css" href="{styles_url}/jquery-ui.min.css" />
	<script src="{scripts_url}/jquery-ui.min.js"></script>
	<div class="container">
		<h1>{add_new_message_text}</h1>

		<?php echo form_open_multipart(get_link("admin_message_new"),array("onsubmit"=>"return verifySubmit();")); ?>
			<input type="hidden" name="post_type" value="add_new_message"/>
			<div class="row even-odd-bg dont-magnify">
				<div class="two columns">
					<span>{receiver_text}</span>
				</div>
				<div class="three columns">
					<span>
						<input type="radio" name="receiver_type" value="user" onchange="receiverTypeChanged(true)"/> {user_text}	
					</span>
				</div>
				<?php if($op_access['customers']) {?>
					<div class="three columns">
						<span>
							<input type="radio" name="receiver_type" value="customer" onchange="receiverTypeChanged(true)"
								<?php if($receiver_type==="customer") echo 'checked';?>
							/> {customer_text}	
						</span>
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
								<div class='four columns' data-id='$id'>
									$name
									<span class='anti-float' onclick='$(this).parent().remove();'></span>
								</div>";
						}
					?>
				</div>				
			</div>

			<div class="row even-odd-bg dont-magnify">
				<div class="two columns">
					<span>
						{sender_text}
					</span>
				</div>
				<input type="hidden" name="sender_type" id="sender_type" value="" />
				
				<div class="three columns" id="sender_user" style="display:none">
					<span>
						{user_text} {sender_user_name}
					</span>
				</div>
				
				<?php if($sender_departments) { ?>
					<div class="three columns" id="sender_departments" style="display:none">
						<select name='sender_department' class="full-width">
							<option value="">{select_text}</option>
							<?php foreach ($sender_departments as $id => $name) 
								echo "<option value='$id'>".$department_text." ".${"department_".$name."_text"}."</option>";
							?>
						</select>
					</div>
				<?php } ?>
			</div>
			<div class="row even-odd-bg dont-magnify">
				<div class="two columns">
					<span>{language_text}</span>
				</div>
				<div class="three columns">
					<select name="language" class="full-width" onchange="langChanged(this);">
						<?php
							foreach($all_langs as $key => $val)
							{
								$sel="";
								if($key===$selected_lang)
									$sel="selected";

								echo "<option $sel value='$key'>$val</option>";
							}
						?>
						<script type="text/javascript">
							var langSelectVal;

							function langChanged(el)
							{
								if(langSelectVal)
									$("#content,#subject").toggleClass(langSelectVal);
									
								langSelectVal="lang-"+""+$(el).val();
								
								$("#content,#subject").toggleClass(langSelectVal);
							}

							$(function()
							{
								$("select[name='language']").trigger("change");
							});
						</script>
					</select>
				</div>
			</div>
			<div class="row even-odd-bg dont-magnify">
				<div class="two columns">
					<span>
						{subject_text}
					</span>
				</div>
				<div class="ten columns">
					<input type="text" name="subject" id="subject" value="{subject}" class="full-width"/>
				</div>
			</div>
			<div class="row even-odd-bg dont-magnify">
				<div class="two columns">
					<span>
						{content_text}
					</span>
				</div>
				<div class="ten columns">
					<textarea rows="10" name="content" id="content" class="full-width">{content}</textarea>
				</div>
			</div>
			<div class="row even-odd-bg">
				<div class="three columns">
					<span>{attachment_text}</span>
				</div>
				<div class="three columns">
					<input type="file" name="attachment" class="full-width" />
				</div>
			</div>

			<br><br>
			<div class="row">
				<div class="four columns">&nbsp;</div>
				<input type="submit" class=" button-primary four columns" value="{send_text}"/>
			</div>

			<script type="text/javascript">
				function receiverTypeChanged(reset)
				{
					if(reset)
						$("#receivers-list *").remove();

					var rt=$("input[name='receiver_type']:checked").val();
					if(!rt)
						return;

					if(rt === "user")
					{
						$("#sender_user").css("display","block");
						$("#sender_departments").css("display","none");
						$("#sender_type").val("user");
					}
					else
					{
						$("#sender_user").css("display","none");
						$("#sender_departments").css("display","block");
						$("#sender_type").val("department");
					}
				}

				$(document).ready(function()
			   {
			   	receiverTypeChanged(false);

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
			            	$("#receivers-list").append($("<div class='four columns' data-id='"+id+"'>"+name+"<span class='anti-float' onclick='$(this).parent().remove();'></span></div>"));
			            
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
					var result=true;

					if(!$("input[name='receiver_type']:checked").val())
					{
						alert("{please_specify_the_receiver_text}");
						result=false;
					}

					setReceivers();

					if(result)
						if(!$("#receivers-ids").val())
						{
							alert("{please_specify_the_receiver_text}");
							result=false;
						}

					if(result)
						if($("#sender_type").val()==="department") 
							if(!$("select[name='sender_department']").val())
							{
								alert("{please_specify_the_sender_department_text}");
								result=false;
							}

					if(result)
						if(!$("#subject").val() || !$("#content").val()) 
						{
							alert("{please_fill_subject_and_content_text}");
							result=false;
						}

					if(!result)
						return false;

					if(!confirm("{are_you_sure_to_send_text}"))
						return false;

					return true;
				}
			</script>
		</form>
	</div>		
</div>