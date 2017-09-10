<div class="main">
	<div class="container">
		<h1>{news_letter_details_text} {nl_id}
			<?php 
			if($news_letter_info && $news_letter_info['nlt_subject']) 
				echo $comma_text." ".$news_letter_info['nlt_subject'];
			?>
		</h1>		
		<?php 
			if(!$news_letter_info) {
		?>
			<h4>{not_found_text}</h4>
		<?php 
			}else{ 
		?>
			<script src="{scripts_url}/tinymce/tinymce.min.js"></script>
			<div class="container">
				<?php if(!$news_letter_info['nlt_sent']){ ?>
					<div class="row general-buttons">
						<a class="two columns " onclick="send_news_letter()">
							<div class="full-width button sub-primary button-type1">
								{send_text}
							</div>
						</a>
					</div>
				<?php } ?>
				<div class="row general-buttons">
					<a  class="two columns"  onclick="delete_news_letter()">
						<div class="full-width button sub-primary button-type2">
							{delete_text}
						</div>
					</a>
				</div>
				<br>
				<?php echo form_open(get_admin_news_letter_template_link($nl_id)); ?>
					<input type="hidden" name="news_letter_type" value="edit_news_letter" />
						
					
					<div class="row even-odd-bg" >
						<div class="three columns">
							<span>{sent_text}</span>
						</div>
						<div class="six columns">
							<?php if($news_letter_info['nlt_sent']) echo $yes_text; else echo $no_text; ?>
						</div>
					</div>
					
					<div class="row even-odd-bg" >
						<div class="three columns">
							<span>{title_text}</span>
						</div>
						<div class="nine columns">
							<input type="text" class="full-width" 
								name="subject" 
								value="<?php echo $news_letter_info['nlt_subject']; ?>"
							/>
						</div>
					</div>

					<div class="row even-odd-bg dont-magnify" >
						<div class="three columns">
							<span>{content_text}</span>
						</div>
						<div class="twelve columns ">
							<textarea class="full-width" rows="15" name="content"
							><?php echo $news_letter_info['nlt_content']; ?></textarea>
						</div>
					</div>
					
					<br><br>
					<div class="row">
						<div class="four columns">&nbsp;</div>
						<input type="submit" class="button-primary four columns" value="{submit_text}"/>
					</div>				
				</form>

				<div style="display:none">
					<?php echo form_open(get_admin_news_letter_template_link($news_letter_id),array("id"=>"delete")); ?>
						<input type="hidden" name="news_letter_type" value="delete_news_letter"/>
						<input type="hidden" name="news_letter_id" value="{news_letter_id}"/>
					</form>

					<script type="text/javascript">

					var tineMCEFontFamilies=
						"Mitra= b mitra, mitra;Yagut= b yagut, yagut; Titr= b titr, titr; Zar= b zar, zar; Koodak= b koodak, koodak;"+
						+"Andale Mono=andale mono,times;"
						+"Arial=arial,helvetica,sans-serif;"
						+"Arial Black=arial black,avant garde;"
						+"Book Antiqua=book antiqua,palatino;"
						+"Comic Sans MS=comic sans ms,sans-serif;"
						+"Courier New=courier new,courier;"
						+"Georgia=georgia,palatino;"
						+"Helvetica=helvetica;"
						+"Impact=impact,chicago;"
						+"Symbol=symbol;"
						+"Tahoma=tahoma,arial,helvetica,sans-serif;"
						+"Terminal=terminal,monaco;"
						+"Times New Roman=times new roman,times;"
						+"Trebuchet MS=trebuchet ms,geneva;"
						+"Verdana=verdana,geneva;"
						+"Webdings=webdings;"
						+"Wingdings=wingdings,zapf dingbats";
					var tinyMCEPlugins="directionality textcolor link image hr emoticons2 lineheight colorpicker media table code";
					var tinyMCEToolbar=[
					   "link image media hr bold italic underline strikethrough alignleft aligncenter alignright alignjustify styleselect formatselect fontselect fontsizeselect  emoticons2",
					   "cut copy paste bullist numlist outdent indent forecolor backcolor removeformat  ltr rtl lineheightselect table code"
					];

					function RoxyFileBrowser(field_name, url, type, win)
					{
						var roxyFileman ="<?php echo get_link('admin_file_inline');?>";

						if (roxyFileman.indexOf("?") < 0) {     
						 roxyFileman += "?type=" + type;   
						}
						else {
						 roxyFileman += "&type=" + type;
						}
						roxyFileman += '&input=' + field_name + '&value=' + win.document.getElementById(field_name).value;
						if(tinyMCE.activeEditor.settings.language){
						 roxyFileman += '&langCode=' + tinyMCE.activeEditor.settings.language;
						}
						tinyMCE.activeEditor.windowManager.open({
						  file: roxyFileman,
						  title: 'Roxy Fileman',
						  width: 850, 
						  height: 650,
						  resizable: "yes",
						  plugins: "media",
						  inline: "yes",
						  close_previous: "no"  
						}, {     window: win,     input: field_name    });
					
						return false; 
					}

					$(function()
					{
						tinymce.init({
							selector: "textarea"
							,plugins: tinyMCEPlugins
							,file_browser_callback: RoxyFileBrowser
							//,width:"600"
							,height:"600"
							,convert_urls:false
							,toolbar: tinyMCEToolbar
							,font_formats:tineMCEFontFamilies
							,media_live_embeds: true
               	});
					})

              	function delete_news_letter()
					{
						if(!confirm("{are_you_sure_to_delete_this_news_letter_text}"))
							return;

						$("form#delete").submit();
					}
					</script>
				</div>
			</div>
		<?php 
			}
		?>

	</div>
</div>