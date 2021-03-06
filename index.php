<?php
/*
@author Danijel Marić 
*/


if (isset($_GET['action']) && $_GET['action'] == 'unzip') {

	move_uploaded_file($_FILES['file']['tmp_name'], './docx.zip');

	if (isset($_POST['delete_previous']) && $_POST['delete_previous'] == true) {
		exec("rm -r newphotos/*");
	}

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

	exec('cd ./newzip && zip -r generated.docx *');

	header("Location: newzip/generated.docx");
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

if (file_exists('unzip/word/media/')) {
	$files1 = scandir('unzip/word/media/');

	$filesForSort = array();
	foreach ($files1 as $i => $f) {
		preg_match("|\d+|", $f, $m);
		if (isset($m[0]) && is_numeric($m[0])) {
			$filesForSort[$m[0]] = $f;
		}
	}
	ksort($filesForSort);
}
else {
	$filesForSort = array();
}


?>

<!DOCTYPE html>
<html lang="">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>DOCX bulk images swap</title>

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

			.dz-success-mark, .dz-error-mark {
				display: none;
			}

			body { padding-top: 70px; }

			.onebyone {

			}

		</style>
	</head>
	<body>


		<nav class="navbar navbar-default navbar-fixed-top">
		  <div class="container">
			<div class="navbar-header">
			  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			  </button>
			  <a class="navbar-brand" href="#">DOCX bulk images swap</a>
			</div>
			<div class="collapse navbar-collapse">

				<div class="navbar-form navbar-left">
				  	<a href="?action=fromscratch" type="button" class="btn btn-default ">Start from scratch</a>
				</div>

				<div class="navbar-form navbar-right">
					<?php if (!empty($filesForSort)) { ?>
						<a href="?action=putback" type="button" class="btn btn-primary ">Download new .docx</a>
					<?php } ?>
				</div>

				
			</div>
		  </div>

		</nav>
		
		<hr>

<?php if (isset($_GET['action']) && $_GET['action'] == 'fromscratch') { ?>

	<div class="container">
		<form action="index.php?action=unzip" method="POST" enctype="multipart/form-data" class="form-inline" role="form">
		
			<div class="form-group">
				<label class="" for="">Choose a .docx file for upload:</label>
				<input type="file" name="file" class="form-control" id="" placeholder="">
			</div>

			<div class="checkbox">
				<label>
					<input type="checkbox" name="delete_previous" value="1">
					Delete previous uploaded bulk photos
				</label>
			</div>
		
			
		
			<button type="submit" class="btn btn-primary">Submit</button>
		</form>
	</div>


<?php } 

else {

?>

<div class="container onebyone">


	<div class="row text-center">
		<a href="javascript:;" class="btn btn-default previmg">Prevous image</a>
		<a href="javascript:;" class="btn btn-default nextimg">Next image</a>
		<hr>
		<div class="progress">
		  <div class="progress-bar progress-bar-success" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
		    <span class=""><p class="page">1/150</p></span>
		  </div>
		</div>
	</div>
	<div class="row">
		<table class="table table-bordered table-hover">
			<thead>
				<tr>
					<th>Orginal image</th>
					<th>New image</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($filesForSort as $key => $file) { 
					if(@!is_array(getimagesize("unzip/word/media/" . $file))) continue;

					?>
				<tr>
					<td style="width:50%;"> 
						<p class="badge"><?php echo $file;?></p>
						<p class="badge">
							<?php 
							$imgsize = getimagesize("unzip/word/media/" . $file);
							echo $imgsize[3];
							 ?>
						</p>
						<br/>
						<img style="max-width: 500px" src="unzip/word/media/<?php echo $file;?>"/> 

					</td>
					<td style="width:50%;" class="new_img">
						<?php 
						$up = '';
						if(@is_array(getimagesize("newphotos/" . $file))) { 
							$up = "hidden"; ?>
							<p class="badge"><?php echo $file;?></p>
							<p class="badge">
								<?php 
								$imgsize = getimagesize("newphotos/" . $file);
								echo $imgsize[3];
								 ?>
							</p>
							<br/>
						<img style="max-width: 500px;" src="newphotos/<?php echo $file;?>" />
						<?php } ?>
				        <form action="upload.php?filename=<?php echo $file;?>" class="dropzone <?php echo $up; ?>">
				            <div class="fallback" class="droping">
				                <input name="file" type="file" multiple />
				            </div>
				        </form>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>

</div>

<?php } ?>

		<!-- jQuery -->
		<script src="//code.jquery.com/jquery.js"></script>
		<script src="dropzone.js"></script>
		<!-- Bootstrap JavaScript -->
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

		<script type="text/javascript">



			$( document ).ready(function() {
			  // Handler for .ready() called.

				showSlide = function() {
					hash = window.location.hash;
					if (hash.indexOf("#page=") > -1) {
						page = hash.replace("#page=", "");
						console.log("hash change", page);
						$(".onebyone tbody tr").hide();
						$(".onebyone tbody tr:nth("+page+")").show();
						$(".page").html($(".onebyone tbody tr:visible").index() + "/" + ($(".onebyone tbody tr").length-1));
						var progress = $(".onebyone tbody tr:visible").index() / ($(".onebyone tbody tr").length-1) * 100;
						$(".progress-bar").css("width", progress + "%");
					}
				}

				$(".onebyone tbody tr").hide();
				$(".onebyone tbody tr:nth(0)").show();

				$(".nextimg").click(function() {
					if ($(".onebyone tbody tr").length-1 == $(".onebyone tbody tr:visible").index())  {
						window.location.hash = "page=0"
					}
					else {
						window.location.hash = "page=" + ($(".onebyone tbody tr:visible").index() + 1);
					}
				});

				$(window).on('hashchange', function() {
					return showSlide();
				});

				$(".previmg").click(function() {
					if ($(".onebyone tbody tr").length-1 == $(".onebyone tbody tr:visible").index())  {
						window.location.hash = "page="+$(".onebyone tbody tr:visible").index()
					}
					else {
						window.location.hash = "page=" + ($(".onebyone tbody tr:visible").index() - 1)
					}
				});

				showSlide();

			});

		</script>
	</body>
</html>