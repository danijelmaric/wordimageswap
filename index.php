<?php
/*
@author Danijel Marić 
*/


if (isset($_GET['action']) && $_GET['action'] == 'unzip') {

	move_uploaded_file($_FILES['file']['tmp_name'], './docx.zip');

	$zip = new ZipArchive;
	exec('rm -r ./unzip');
	if ($zip->open('docx.zip') === TRUE) {
	    $zip->extractTo('./unzip');
	    $zip->close();
	    header("Location: index.php");
	} else {
	    echo 'failed';
	}

}

if (isset($_GET['action']) && $_GET['action'] == 'putback') {

	exec("rm -r ./newzip");
	recurse_copy('./unzip', './newzip');

	recurse_copy('./newphotos', './newzip/word/media');

	exec("rm ./newzip/word/media/.DS_Store");

	exec('cd ./newzip && zip -r prirucnik.docx *');

	header("Location: newzip/prirucnik.docx");
}

function recurse_copy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
} 

function fprint_r($arr) {
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}


$files1 = scandir('unzip/word/media/');

$filesForSort = array();
foreach ($files1 as $i => $f) {
	preg_match("|\d+|", $f, $m);
	if (isset($m[0]) && is_numeric($m[0])) {
		$filesForSort[$m[0]] = $f;
	}
}
ksort($filesForSort);
//fprint_r($filesForSort);


?>

<!DOCTYPE html>
<html lang="">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Word swap slika</title>

		<!-- Bootstrap CSS -->
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">

		<style type="text/css">

			.new_img {
				position: relative;
				border: 2px dashed #000;
			}

			.new_img .dropzone {
				position: absolute;
				width: 100%;
				height: 100%
			}

			.dz-success-mark {
				display: none;
			}

		</style>
	</head>
	<body>
		<h1 class="text-center">Word swap slika<br/>
			<a href="?action=fromscratch" type="button" class="btn btn-default ">Počni izpočetka</a>
			<a href="?action=putback" type="button" class="btn btn-default ">Vrati nazad u docx</a>
		</h1>
		
		<hr>

<?php if (isset($_GET['action']) && $_GET['action'] == 'fromscratch') { ?>

	<div class="container">
		<form action="index.php?action=unzip" method="POST" enctype="multipart/form-data" class="form-inline" role="form">
		
			<div class="form-group">
				<label class="sr-only" for="">label</label>
				<input type="file" name="file" class="form-control" id="" placeholder="">
			</div>
		
			
		
			<button type="submit" class="btn btn-primary">Submit</button>
		</form>
	</div>


<?php } 

else {

?>
<table class="table table-bordered table-hover">
	<thead>
		<tr>
			<th>Orginal</th>
			<th>Nova</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($filesForSort as $key => $file) { 
			if(@!is_array(getimagesize("unzip/word/media/" . $file))) continue;

			?>
		<tr>
			<td style="width:600px;"> 
				<?php echo $file;?><br/>
				<?php 
				$imgsize = getimagesize("unzip/word/media/" . $file);
				echo $imgsize[3];
				 ?>
				<br/>
				<img style="max-width: 600px" src="unzip/word/media/<?php echo $file;?>"/> 

			</td>
			<td class="new_img">
				<?php 
				$up = '';
				if(@is_array(getimagesize("newphotos/" . $file))) { 
					$up = "hidden"; ?>
				<img style="max-width: 600px" src="newphotos/<?php echo $file;?>" />
				<?php } ?>
		        <form action="index2.php?filename=<?php echo $file;?>" class="dropzone <?php echo $up; ?>">
		            <div class="fallback" class="droping">
		                <input name="file" type="file" multiple />
		            </div>
		        </form>
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<?php } ?>

		<!-- jQuery -->
		<script src="//code.jquery.com/jquery.js"></script>
		<script src="dropzone.js"></script>
		<!-- Bootstrap JavaScript -->
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

		<script type="text/javascript">



		</script>
	</body>
</html>