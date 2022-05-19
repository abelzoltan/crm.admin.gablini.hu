<?php if($VIEW["vars"]["newButton"] !== false) { ?>
	<a href="<?php if(isset($VIEW["vars"]["newButton"]["href"]) AND !empty($VIEW["vars"]["newButton"]["href"])) { echo $VIEW["vars"]["newButton"]["href"]; } else { echo $GLOBALS["URL"]->currentURL."/new"; } ?>" class="btn btn-sm btn-primary btn-title-right"><i class="fa fa-plus"></i>&nbsp;&nbsp;&nbsp;<?php if(isset($VIEW["vars"]["newButton"]["label"]) AND !empty($VIEW["vars"]["newButton"]["label"])) { echo $VIEW["vars"]["newButton"]["label"]; } else { ?>Új elem felvitele<?php } ?></a>
	<div class="clear"></div>
<?php } ?>