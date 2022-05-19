<?php 
$ckDir = PATH_WEB."vendors/ckeditor-4.7.1/";
$ckFinderDir = PATH_WEB."vendors/ckfinder-3.4.2/";
$_SESSION["ckfinderAuthentication"] = true;
if(CDN)
{
	$_SESSION["ckfinderBaseURL"] = CDN_PATH_WEB."fajlok/";
	$_SESSION["ckfinderRoot"] = CDN_PATH."fajlok/";
}
?>
<script src="<?php echo $ckDir; ?>ckeditor.js"></script>
<script>
$(document).ready(function(){
	//CKFinder integration
	CKEDITOR.config.filebrowserBrowseUrl = "<?php echo $ckFinderDir; ?>ckfinder.html";
	CKEDITOR.config.filebrowserImageBrowseUrl = "<?php echo $ckFinderDir; ?>ckfinder.html?type=Images";
	CKEDITOR.config.filebrowserFlashBrowseUrl = "<?php echo $ckFinderDir; ?>ckfinder.html?type=Flash";
	CKEDITOR.config.filebrowserUploadUrl = "<?php echo $ckFinderDir; ?>core/connector/php/connector.php?command=QuickUpload&type=Files";
	CKEDITOR.config.filebrowserImageUploadUrl = "<?php echo $ckFinderDir; ?>core/connector/php/connector.php?command=QuickUpload&type=Images";
	CKEDITOR.config.filebrowserFlashUploadUrl = "<?php echo $ckFinderDir; ?>core/connector/php/connector.php?command=QuickUpload&type=Flash";
});
</script>