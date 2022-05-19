<?php 
foreach($VIEW["meta"] AS $key => $val)
{
	if(!empty($val))
	{
		if(substr($key, 0, 3) == "og:") { $keyAttr = "property"; }
		else { $keyAttr = "name"; }
		?><meta <?php echo $keyAttr; ?>="<?php echo $key; ?>" content="<?php echo $val; ?>"><?php
	}
}
?>