<?php
$url = $GLOBALS["URL"]->currentURL;
$get = $_GET;
$get["todo-csv-export"] = 1;
$url .= "?".http_build_query($get);
?>
<a href="<?php echo $url; ?>" target="_blank" class="btn btn-sm btn-primary btn-title-right"><i class="fa fa-download"></i>&nbsp;&nbsp;&nbsp;Export</a>
<div class="clear"></div>