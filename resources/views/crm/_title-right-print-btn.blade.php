<?php
$url = $GLOBALS["URL"]->currentURL;
$get = $_GET;
$get["nyomtatas"] = 1;
$url .= "?".http_build_query($get);
?>
<a href="<?php echo $url; ?>" target="_blank" class="btn btn-sm btn-info btn-title-right"><i class="fa fa-print"></i>&nbsp;&nbsp;&nbsp;Nyomtatás</a>
<div class="clear"></div>