<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
	<meta charset="UTF-8" />
	<title>{header_title}</title>
	<link rel="shortcut icon" href="{images_url}/favicon.png"/>
  	<link rel="stylesheet" type="text/css" href="{styles_url}/customer.css" />
</head>
<body style="text-align:center;background:none">
	<br>
	<br>
 	<?php if($redirect_link) { ?>
 		<img style="vertical-align:middle;border-radius:10px" class="openid-login" src="{images_url}/{image_name}" title="{social_network_name}"/>
 		<br>
 		<h1 style="font-size:1.5em;margin:20px 50px">Please wait ... </h1>
 		<script type="text/javascript">
 			window.location="<?php echo urldecode($redirect_link);?>";
 		</script>
 	<?php } 	else { ?>
		<h1>خطا در اتصال</h1><br>
		{redirect_error_code}<br>
		{redirect_error_desc}<br>
 	<?php } ?>
 </body>
 </html>
