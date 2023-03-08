<?php
require_once("object.php");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Progress Bars</title>
</head>

<body>
    <div id="page-wrap" style='width: 50%; margin: auto;'>

        <h1>ProgressObj Test</h1>

        <?php

        $remote_url = "http://localhost/images.zip";

        function download_zip_file($url)
        {
            $dest = sys_get_temp_dir() . '/' . time() . '-' . basename($url);
            if (file_put_contents($dest, file_get_contents($url))) {
                return array(
                    'status' => 'succeess',
                    'filePath' => $dest
                );
            } else {
                return array(
                    'status' => 'error'
                );
            }
        }

        function extract_file_and_list_contents($zip_file)
        {
            $extracted_path = sys_get_temp_dir() . '/' . basename($zip_file) . '-extracted';
            $zip = new ZipArchive;
            fopen($zip_file, 'r');
            if ($zip->open($zip_file) === TRUE) {
                $zip->extractTo($extracted_path);
                $zip->close();
                $files_to_process = array();
                $iterator = new DirectoryIterator($extracted_path);
                foreach ($iterator as $file) {
                    if ($file->isDot()) continue;
                    array_push($files_to_process, array(
                        'fileName' => $file->getFilename(),
                        'filePath' => $file->getPath()
                    ));
                }
                return $files_to_process;
            } else {
                return 'extration failed';
            }
        }

        $dl = download_zip_file($remote_url);

        echo "downloaded file to " . $dl['filePath'] ."<br>";

        $files_to_process = extract_file_and_list_contents($dl['filePath']);



        //create new progress bar object
        $po = new ProgressObj();

        //grab some data from a database to loop through
        $count = count($files_to_process);

        //in records present, set loading text and display bar
        $po->text = "Processing " . $count . " files.";
        $po->DisplayMeter();

        //give the number of units in your loop to the object
        $po->Calculate($count);

        //loop through the returned data 
        for ($i = 0; $i < $count; $i++) {

            //do something, like print a number
            $file = $files_to_process[$i]['filePath'] . "/" . $files_to_process[$i]['fileName'];
            if(str_contains(mime_content_type($file), 'image')){
                print(" <script> $('.status').append('Processing " . $file . " <br />'); </script>");
            } else {
                print(" <script> $('.status').append('Skipping non-image " . $file . " <br />'); </script>");
            }

            // This is for the buffer achieve the minimum size in order to flush data
            echo str_repeat(' ', 2480);

            //simulate long, complex function function
            usleep(500000);

            //at the end of each loop, run Animate() to move the bar
            $po->Animate();

            //push the content out to the browser
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }

        //hide the progress bar after everything is done
        $po->Hide();
        ?>

    </div>

</body>

</html>