<?php

move_uploaded_file($_FILES['file']['tmp_name'], 'newphotos/' . $_GET['filename']);