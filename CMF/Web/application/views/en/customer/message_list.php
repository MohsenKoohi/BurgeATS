<div class="main">
	<div class="container">
		<style type="text/css">
			a
			{
				color:black;
			}

			.even-odd-bg div.message-content
			{
				text-overflow: ellipsis;
				overflow:hidden;
				max-height: 110px;
			}

			.view-img
			{
				max-width:60px;
				transition: max-width .5s;
				text-align: left;
			}

			.view-img:hover
			{
				max-width:70px;
				transition: max-width .5s;
			}
		</style>
		<h1>{messages_text}</h1>

		<div class="container">	
			<div class="row results-count" >
				<div class="six columns">
					<label>
						{results_text} {messages_start} {to_text} {messages_end} - {total_results_text}: {messages_total}
					</label>
				</div>
				<div class="three columns results-page-select">
					<select class="full-width" onchange="pageChanged($(this).val());">
						<?php 
							for($i=1;$i<=$messages_total_pages;$i++)
							{
								$sel="";
								if($i == $messages_current_page)
									$sel="selected";

								echo "<option value='$i' $sel>$page_text $i</option>";
							}
						?>
					</select>
				</div>
				<script type="text/javascript">
					function pageChanged(pageNumber)
					{
						document.location="{page_link}?page="+pageNumber;
					}
				</script>
			</div>	

			<?php 
				$i=$messages_start;
				$verification_status=array();
				if($messages_total)
					foreach($messages as $mess)
					{ 
						$mess_link=get_customer_message_details_link($mess['mi_message_id']);
			?>
						<div class="row even-odd-bg">
							<div class="one column counter">
								#<?php echo $i++;?>
							</div>

							<div class="four columns">
								{sender_from_text}:
								<?php 
									$type=$mess['mi_sender_type'];
									if($type === "department")
										$sender=$department_text." ".${"department_".$departments[$mess['mi_sender_id']]."_text"};
									if($type === "customer")						
										$sender=$customer_text." ".$mess['mi_sender_id']." - ".$mess['scn'];
									echo "<span>".$sender."</span>";
								?>
								<br>
								{receiver_to_text}:
								<?php 
									$type=$mess['mi_receiver_type'];
									if($type === "department")
										$receiver=$department_text." ".${"department_".$departments[$mess['mi_receiver_id']]."_text"};
									if($type === "customer")
										$receiver=$customer_text." ".$mess['mi_receiver_id']." - ".$mess['rcn'];
									echo "<span>".$receiver."</span>";
								?>
								<div class='ltr'>
									<?php echo str_replace("-","/",$mess['mi_last_activity']); ?>
								</div>
							</div>
							
							<div class="three columns">
								<label>{subject_text}</label>
								<span>
									<?php echo $mess['mi_subject'];?>
								</span>
							</div>

							<div class="three columns message-content">
								<label>{content_of_last_message_text}</label>
								<span>
									<?php echo $mess['mt_content'];?>
								</span>
							</div>

							<div class="one column">
								
								<a target="_blank" href="<?php echo $mess_link;?>">
									<img src="{images_url}/details.png" class="view-img anti-float" title="{view_details_text}";/>
								</a>
							
							</div>
						</div>
			<?php 
					}
			?>
		</div>
	</div>
</div>