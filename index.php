<?php
$includeurl = false;
$startdir = './downloads';
$showthumbnails = True; 
$memorylimit = false; // Integer
$showdirs = true;
$forcedownloads = true;
$hide = array( 'dlf', 'index.php', 'Thumbs', '.htaccess', '.htpasswd','images' );
$showtypes = array( 'jpg', 'png', 'gif', 'zip', 'txt','pdf','exe','rar','gif','fla','swf');
$displayindex = false;
$allowuploads = false;
$uploadtypes = array( 'zip', 'gif', 'jpg', 'doc', 'png' );
$overwrite = False;
$indexfiles = array ('index.html',	'index.htm', 'default.htm',	'default.html');
$filetypes = array ( 'png' => 'jpg.gif','jpeg' => 'jpg.gif','bmp' => 'jpg.gif','jpg' => 'jpg.gif', 'gif' => 'gif.gif','zip' => 'archive.png',
			'rar' => 'archive.png','exe' => 'exe.gif','setup' => 'setup.gif', 'txt' => 'text.png','htm' => 'html.gif','html' => 'html.gif',
			'fla' => 'fla.gif','swf' => 'swf.gif','xls' => 'xls.gif', 'xlsx' => 'xlsx.gif','doc' => 'doc.gif','docx' => 'docx.gif',
			'sig' => 'sig.gif','fh10' => 'fh10.gif','pdf' => 'pdf.gif', 'psd' => 'psd.gif','rm' => 'real.gif','mpg' => 'video.gif',
			'mpeg' => 'video.gif','mov' => 'video2.gif','avi' => 'video.gif', 'eps' => 'eps.gif','gz' => 'archive.png', 'asc' => 'sig.gif',
			'csv' => 'csv.gif','odt' => 'ootext.gif','ods' => 'oocalc.gif', 'odp' => 'oopres.gif','odg' => 'oodraw.gif', 'odb' => 'oodbase.gif',
			);
			
/*
Only edit what is below this line if you are sure that you know what you are doing!
*/

if($includeurl)
{
	$includeurl = preg_replace("/^\//", "${1}", $includeurl);
	if(substr($includeurl, strrpos($includeurl, '/')) != '/') $includeurl.='/';
}

error_reporting(0);
if(!function_exists('imagecreatetruecolor')) $showthumbnails = false;
if($startdir) $startdir = preg_replace("/^\//", "${1}", $startdir);
$leadon = $startdir;
if($leadon=='.') $leadon = '';
if((substr($leadon, -1, 1)!='/') && $leadon!='') $leadon = $leadon . '/';
$startdir = $leadon;

if($_GET['dir']) {
	//check this is okay.
	
	if(substr($_GET['dir'], -1, 1)!='/') {
		$_GET['dir'] = strip_tags($_GET['dir']) . '/';
	}
	
	$dirok = true;
	$dirnames = split('/', strip_tags($_GET['dir']));
	for($di=0; $di<sizeof($dirnames); $di++) {
		
		if($di<(sizeof($dirnames)-2)) {
			$dotdotdir = $dotdotdir . $dirnames[$di] . '/';
		}
		
		if($dirnames[$di] == '..') {
			$dirok = false;
		}
	}
	
	if(substr($_GET['dir'], 0, 1)=='/') {
		$dirok = false;
	}
	
	if($dirok) {
		 $leadon = $leadon . strip_tags($_GET['dir']);
	}
}

if($_GET['download'] && $forcedownloads) {
	$file = str_replace('/', '', $_GET['download']);
	$file = str_replace('..', '', $file);

	if(file_exists($includeurl . $leadon . $file)) {
		header("Content-type: application/x-download");
		header("Content-Length: ".filesize($includeurl . $leadon . $file)); 
		header('Content-Disposition: attachment; filename="'.$file.'"');
		readfile($includeurl . $leadon . $file);
		die();
	}
	die();
}

if($allowuploads && $_FILES['file']) {
	$upload = true;
	if(!$overwrite) {
		if(file_exists($leadon.$_FILES['file']['name'])) {
			$upload = false;
		}
	}
	
	if($uploadtypes)
	{
		if(!in_array(substr($_FILES['file']['name'], strpos($_FILES['file']['name'], '.')+1, strlen($_FILES['file']['name'])), $uploadtypes))
		{
			$upload = false;
			$uploaderror = "<strong>ERROR: </strong> You may only upload files of type ";
			$i = 1;
			foreach($uploadtypes as $k => $v)
			{
				if($i == sizeof($uploadtypes) && sizeof($uploadtypes) != 1) $uploaderror.= ' and ';
				else if($i != 1) $uploaderror.= ', ';
				
				$uploaderror.= '.'.strtoupper($v);
				
				$i++;
			}
		}
	}
	
	if($upload) {
		move_uploaded_file($_FILES['file']['tmp_name'], $includeurl.$leadon . $_FILES['file']['name']);
	}
}

$opendir = $includeurl.$leadon;
if(!$leadon) $opendir = '.';
if(!file_exists($opendir)) {
	$opendir = '.';
	$leadon = $startdir;
}

clearstatcache();
if ($handle = opendir($opendir)) {
	while (false !== ($file = readdir($handle))) { 
		//first see if this file is required in the listing
		if ($file == "." || $file == "..")  continue;
		$discard = false;
		for($hi=0;$hi<sizeof($hide);$hi++) {
			if(strpos($file, $hide[$hi])!==false) {
				$discard = true;
			}
		}
		
		if($discard) continue;
		if (@filetype($includeurl.$leadon.$file) == "dir") {
			if(!$showdirs) continue;
		
			$n++;
			if($_GET['sort']=="date") {
				$key = @filemtime($includeurl.$leadon.$file) . ".$n";
			}
			else {
				$key = $n;
			}
			$dirs[$key] = $file . "/";
		}
		else {
			$n++;
			if($_GET['sort']=="date") {
				$key = @filemtime($includeurl.$leadon.$file) . ".$n";
			}
			elseif($_GET['sort']=="size") {
				$key = @filesize($includeurl.$leadon.$file) . ".$n";
			}
			else {
				$key = $n;
			}
			
			if($showtypes && !in_array(substr($file, strpos($file, '.')+1, strlen($file)), $showtypes)) unset($file);
			if($file) $files[$key] = $file;
			
			if($displayindex) {
				if(in_array(strtolower($file), $indexfiles)) {
					header("Location: $leadon$file");
					die();
				}
			}
		}
	}
	closedir($handle); 
}

//sort our files
if($_GET['sort']=="date") {
	@ksort($dirs, SORT_NUMERIC);
	@ksort($files, SORT_NUMERIC);
}
elseif($_GET['sort']=="size") {
	@natcasesort($dirs); 
	@ksort($files, SORT_NUMERIC);
}
else {
	@natcasesort($dirs); 
	@natcasesort($files);
}

//order correctly
if($_GET['order']=="desc" && $_GET['sort']!="size") {$dirs = @array_reverse($dirs);}
if($_GET['order']=="desc") {$files = @array_reverse($files);}
$dirs = @array_values($dirs); $files = @array_values($files);

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Untitled Page</title>
<meta name="generator" content="WYSIWYG Web Builder 9 - http://www.wysiwygwebbuilder.com">
<style type="text/css">
div#container
{
   width: 800px;
   position: relative;
   margin-top: 0px;
   margin-left: auto;
   margin-right: auto;
   text-align: left;
}
body
{
   text-align: center;
   margin: 0;
   background-color: #FFEBFA;
   background-image: url(images/index_bkgrnd.png);
   background-repeat: repeat-x;
   color: #000000;
}
</style>
<style type="text/css">
a
{
   color: #0000FF;
   text-decoration: underline;
}
a:visited
{
   color: #800080;
}
a:active
{
   color: #FF0000;
}
a:hover
{
   color: #0000FF;
   text-decoration: underline;
}
</style>
<link rel="stylesheet" type="text/css" href="<?php echo $includeurl; ?>dlf/phpdirectorylisting.css" />

<?php
if($showthumbnails) {
?>
<script language="javascript" type="text/javascript">
<!--
function o(n, i) {
	document.images['thumb'+n].src = '<?php echo $includeurl; ?>dlf/i.php?f='+i<?php if($memorylimit!==false) echo "+'&ml=".$memorylimit."'"; ?>;

}

function f(n) {
	document.images['thumb'+n].src = 'dlf/trans.gif';
}
//-->
</script>
<?php
}
?></head>
<body>
<div id="container">
<div id="directorylistingwrapper">
 <h6>Download <?php echo str_replace('\\', '', dirname(strip_tags($_SERVER['PHP_SELF']))).'/'.$leadon;?></h6>

  <div id="breadcrumbs">
	<a href="<?php echo strip_tags($_SERVER['PHP_SELF']);?>">Home</a> 
	<?php
 		$breadcrumbs = split('/', str_replace($startdir, '', $leadon));
		if(($bsize = sizeof($breadcrumbs))>0) {
  		$sofar = '';
  		for($bi=0;$bi<($bsize-1);$bi++) {
		$sofar = $sofar . $breadcrumbs[$bi] . '/';
		echo ' &gt; <a href="'.strip_tags($_SERVER['PHP_SELF']).'?dir='.urlencode($sofar).'">'.$breadcrumbs[$bi].'</a>';
		}
	  	}
		$baseurl = strip_tags($_SERVER['PHP_SELF']) . '?dir='.strip_tags($_GET['dir']) . '&amp;';
		$fileurl = 'sort=name&amp;order=asc';
		$sizeurl = 'sort=size&amp;order=asc';
		$dateurl = 'sort=date&amp;order=asc';
		
		switch ($_GET['sort']) {
		case 'name':
			if($_GET['order']=='asc') $fileurl = 'sort=name&amp;order=desc';
			break;
		case 'size':
			if($_GET['order']=='asc') $sizeurl = 'sort=size&amp;order=desc';
			break;
			
		case 'date':
			if($_GET['order']=='asc') $dateurl = 'sort=date&amp;order=desc';
			break;  
		default:
			$fileurl = 'sort=name&amp;order=desc';
			break;
		}
	?>
  </div> <!-- end div breadcrumbs -->

  <div id="listingcontainer">

    <div id="listingheader"> 
	<div id="headerfile"><a href="<?php echo $baseurl . $fileurl;?>">Sort by Filename</a></div>
	<div id="headersize"><a href="<?php echo $baseurl . $sizeurl;?>">by Size</a></div>
	<div id="headermodified"><a href="<?php echo $baseurl . $dateurl;?>">by Date</a></div>
    </div>  <!-- end div listingheader -->


    <div id="listing">
	<?php
		$class = 'b';	if($dirok) {	
	?>
	<div><a href="<?php echo strip_tags($_SERVER['PHP_SELF']).'?dir='.urlencode($dotdotdir);?>" class="<?php echo $class;?>"><img src="<?php echo $includeurl; ?>dlf/dirup.png" alt="Folder" /><strong>..</strong> <em>&nbsp;</em>&nbsp;</a></div>

	<?php
		if($class=='b') $class='w'; else $class = 'b';}
		$arsize = sizeof($dirs);for($i=0;$i<$arsize;$i++) {
	?>
	<div><a href="<?php echo strip_tags($_SERVER['PHP_SELF']).'?dir='.urlencode(str_replace($startdir,'',$leadon).$dirs[$i]);?>" class="<?php echo $class;?>"><img src="<?php echo $includeurl; ?>dlf/folder.png" alt="<?php echo $dirs[$i];?>" /><strong><?php echo $dirs[$i];?></strong> <em>- <?php echo date ("M d Y h:i:s A", filemtime($includeurl.$leadon.$dirs[$i]));?></em></a></div>

	<?php
		if($class=='b') $class='w';	else $class = 'b';}
		$arsize = sizeof($files);for($i=0;$i<$arsize;$i++) {
		$icon = 'unknown.png';
		$ext = strtolower(substr($files[$i], strrpos($files[$i], '.')+1));
		$supportedimages = array('gif', 'png', 'jpeg', 'jpg');
		$thumb = '';
			if($showthumbnails && in_array($ext, $supportedimages)) {
			$thumb = '<span><img src="dlf/trans.gif" alt="'.$files[$i].'" name="thumb'.$i.'" /></span>';
			$thumb2 = ' onmouseover="o('.$i.', \''.urlencode($leadon . $files[$i]).'\');" onmouseout="f('.$i.');"';			
		}
		
		if($filetypes[$ext]) {$icon = $filetypes[$ext];	} $filename = $files[$i]; if(strlen($filename)>256) {$filename = substr($files[$i], 0, 255) . '...';}		
		$fileurl = $includeurl . $leadon . $files[$i];	if($forcedownloads) {$fileurl = $_SESSION['PHP_SELF'] . '?dir=' . urlencode(str_replace($startdir,'',$leadon)) . '&download=' . urlencode($files[$i]);	}
	?>

	<div><a href="<?php echo $fileurl;?>" target="_self" class="<?php echo $class;?>"<?php echo $thumb2;?>><img src="<?php echo $includeurl; ?>dlf/<?php echo $icon;?>" alt="<?php echo $files[$i];?>" /><strong><?php echo $filename;?></strong> <em><?php echo round(filesize($includeurl.$leadon.$files[$i])/1024);?> Kb -    <?php echo date ("M d Y h:i:s A", filemtime($includeurl.$leadon.$files[$i]));?></em><?php echo $thumb;?></a></div>

	<?php
		if($class=='b') $class='w';	else $class = 'b';		}	
	?>

	</div>  <!-- end div listing -->
	
	<?php
	if($allowuploads) {
		$phpallowuploads = (bool) ini_get('file_uploads');		
		$phpmaxsize = ini_get('upload_max_filesize');
		$phpmaxsize = trim($phpmaxsize);
		$last = strtolower($phpmaxsize{strlen($phpmaxsize)-1});
		switch($last) {
			case 'g':
				$phpmaxsize *= 1024;
			case 'm':
				$phpmaxsize *= 1024;
		}
	
	?>

	<div id="upload">
		<div id="uploadtitle">
			<strong>File Upload</strong> (Max Filesize: <?php echo $phpmaxsize;?>Kb)

			<?php if($uploaderror) echo '<div class="upload-error">'.$uploaderror.'</div>'; ?>
		</div>  <!-- end div uploadtitle -->

		<div id="uploadcontent">
			<?php
			if($phpallowuploads) {
			?>
			<form method="post" action="<?php echo strip_tags($_SERVER['PHP_SELF']);?>?dir=<?php echo urlencode(str_replace($startdir,'',$leadon));?>" enctype="multipart/form-data">
			<input type="file" name="file" /> <input type="submit" value="Upload" />
			</form>
			<?php
			}
			else {
			?>
			File uploads are disabled in your php.ini file. Please enable them.
			<?php
			}
			?>
		</div>  <!-- end div uploadcontent -->
		
	</div>  <!-- end div upload -->
	<?php
	}
	?>
  </div>  <!-- end div listing container -->
 </div>  <!-- end div directorylistingwrapper -->
<div id="copy">Download server By Dwe Gruppe &copy;2008</div>

<img src="images/img0001.jpg" id="Banner1" alt="Dowenload Server" style="position:absolute;left:9px;top:8px;width:796px;height:50px;border-width:0;z-index:1;">
</div>
</body>
</html>