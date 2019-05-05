
<?php

/*

Author: 	Mohammed Ahmed(M@@king)

Version:	1.0

Date:		10.Oct.2004

----------------------------

Last Update:    16.Nov.2004

----------------------------

E-mail:		m@maaking.com

MSN   :         m@maaking.com

WWW   : 	http://www.maaking.com





---Description -----------------------------------------------------

The Super Global Variable $_FILES is used in PHP 4.x.x.

$_FILES['upload']['size'] ==> Get the Size of the File in Bytes.

$_FILES['upload']['tmp_name'] ==> Returns the Temporary Name of the File.

$_FILES['upload']['name'] ==> Returns the Actual Name of the File.

$_FILES['upload']['type'] ==> Returns the Type of the File.



So if I uploaded the file 'test.doc', the $_FILES['upload']['name']

would be 'phptut.doc' and $_FILES['upload']['type'] would be 'application/msword'.

---------------------------------------------------------------------*/



//**********************************************************************//

//  $_FILES['filetoupload']  is the value of                            //

// file field from the form. <input type="file" name="filetoupload">    //

//**********************************************************************//



// this is the upload dir where files will go.

//Don't remove the /

//Chmod it (777)

$upload_dir = "uploaded/";   //change to whatever you want.



           //1500000 bytes = 100.0mbs

$size_bytes = 8000000; //File Size in bytes (change this value to fit your need)



$extlimit = "no"; //Do you want to limit the extensions of files uploaded (yes/no)

$limitedext = array(".gif",".jpg",".png",".jpeg",".mp3",".bmp",".zip"); //Extensions you want files uploaded limited to. also you can use:  //array(".gif",".jpg",".jpeg",".png",".txt",".JPG",".nfo",".doc",".rtf",".htm",".JPG",".dmg",".zip",".rar",".gz",".exe");



          //check if the directory exists or not.

          if (!is_dir("$upload_dir")) {

	     die ("The directory <b>($upload_dir)</b> doesn't exist");

          }

          //check if the directory is writable.

          if (!is_writeable("$upload_dir")){

             die ("The directory <b>($upload_dir)</b> is NOT writable, Please CHMOD (777)");

          }

          

  if($uploadform) // if you clicked the (Upload File) button. "If you submitted the form" then upload the file.

  {//begin of if($uploadform).





              //check if no file selected.

              if (!is_uploaded_file($_FILES['filetoupload']['tmp_name']))

              {

                     echo "Error: Please select a file to upload!. <br>--<a href=\"$_SERVER[PHP_SELF]\">back</a>";

                     exit(); //exit the script and don't do anything else.

              }



              //Get the Size of the File

              $size = $_FILES['filetoupload']['size'];

              //Make sure that file size is correct

              if ($size > $size_bytes)

              {

                    $kb = $size_bytes / 1024;

                    echo "File Too Large. File must be <b>$kb</b> KB. <br>--<a href=\"$_SERVER[PHP_SELF]\">back</a>";

                    exit();

              }



              //check file extension

              $ext = strrchr($_FILES['filetoupload'][name],'.');

              if (($extlimit == "yes") && (!in_array($ext,$limitedext))) {

                    echo("Wrong file extension. ");

                    exit();

              }



              // $filename will hold the value of the file name submetted from the form.

              $filename =  $_FILES['filetoupload']['name'];

              // Check if file is Already EXISTS.

              if(file_exists($upload_dir.$filename)){

                    echo "Oops! The file named <b>$filename </b>already exists. <br>--<a href=\"$_SERVER[PHP_SELF]\">back</a>";

                    exit();

              }



              //Move the File to the Directory of your choice

              //move_uploaded_file('filename','destination') Moves afile to a new location.

              if (move_uploaded_file($_FILES['filetoupload']['tmp_name'],$upload_dir.$filename)) {


                  chmod( $upload_dir.$filename, 0644 );
                  //tell the user that the file has been uploaded and make him alink.

                  echo "File [- <a href=$upload_dir$filename>$filename</a> -] Uploaded! <br>Click Here To Upload Another Image - <a href=\"$_SERVER[PHP_SELF]\">back</a>";

                  exit();



              }

                  // print error if there was a problem moving file.

                  else

              {

                  //Print error msg.

                  echo "There was a problem moving your file. <br>»<a href=\"$_SERVER[PHP_SELF]\">back</a>";

                  exit();

              }







  }//end of if($uploadform).



#---------------------------------------------------------------------------------#

// If the form has not been submitted, display it!

else

  {//begin of else

  

      ?><head><style type="text/css">

<!--

body {

	background-color: #313131;

}

body,td,th {

	color: #ffffff;

}

a:link {

	color: #ffffff;

}

a:visited {

	color: #ffffff;

}

a:hover {

	color: #00ccff;

}

a:active {

	color: #000000;

}

-->

</style>

      <title>Fagan-1.Com | Uploader | Enjoy</title>

<base target="_blank">
</head>

<body link="#00CCFF" vlink="#000000" alink="#FFFFFF" style="color: #000000; background-color:#333333">

      <form method="post" enctype="multipart/form-data" action="<?php echo $PHP_SELF ?>">

        <div align="center"><font face="Tahoma"><font size="8pt"><font style="strong">
			<img border="0" src="http://i24.photobucket.com/albums/c44/Y-K/uploaderimage.jpg" width="400" height="150"><br>
			<br>

          	</font><font face="Tahoma">

          <input type="file" name="filetoupload"></font><font size="2"> </font>

          	<font face="Tahoma"><span style="font-size: 8pt">

          <input type="Submit" name="uploadform" value="Upload File"></span></font><font size="2">

          <br>

          	</font>

          <input type="hidden" name="MAX_FILE_SIZE" value="<?echo $size_bytes; ?>">

          	<font size="2">

          <br>

        	</font></font>

        </div>

      </form>



<div align="center">

  <font color="#FFFFFF" style="font-size: 8pt; font-weight: 700">

  <?

  

  }//end of else



/*______________________________________________________________________________*/

//   Here is the most interesting part.

//    it views the directory contents.....i'll disscuss next version. (ver 2.0)

echo "<br><br><hr><br><b>Current Uploaded Files:</b><br>";

?>









<br>











  </font>











  <table border="1" bordercolor="#00CCFF" cellspacing="1" bgcolor="#313131" style="border-collapse: collapse">

    <tr bgcolor="#000000"> 

      <td width="259" bordercolor="#000000" bgcolor="#313131"><div align="center">
		<strong>
		<font color="#FFFFFF" face="Tahoma" style="font-size: 8pt">File Name</font></strong></div></td>

      <td width="177" bordercolor="#000000" bgcolor="#313131"><div align="center">
		<strong>
		<font color="#FFFFFF" face="Tahoma" style="font-size: 8pt">File Size</font></strong></div></td>

      <?

		$all = strlen($_GET['all']);

			if ($all > 1){



			echo "<td width=\"320\"><div align=\"center\"><strong>Preview</strong></div></td>";

			}

		?>

    </tr>

    <?

$all = strlen($_GET['all']);

$rep=opendir($upload_dir);

while ($file = readdir($rep)) {

	if($file != '..' && $file !='.' && $file !=''){

		if (!is_dir($file)){







				if ($all > 1){

					

					// print the file name and then make a link.

                       			echo "<tr><td> <a href=\"$upload_dir$file\" target=_blank>$file</a> </td>";                        

                       			#------------begin of file size.

    	           			//print the file size.

    	           			$file_size = filesize($upload_dir."".$file);



		     			if ($file_size >= 1048576)

		    			{

					$show_filesize = number_format(($file_size / 1048576),2) . " MB";

		   			}

		     			elseif ($file_size >= 1024)

		    			{

					$show_filesize = number_format(($file_size / 1024),2) . " KB";

		    			}

		       			 elseif ($file_size >= 0)

		       			{

					$show_filesize = $file_size . " bytes";

		      			}

		      			else

		      			{

					$show_filesize = "0 bytes";

		      			}

		        		echo "<td> $show_filesize </td>";

					echo "<td><img src=\"$upload_dir$file\" width=\"200\" height=\"200\"></td></tr>";

                        		#------------end of file size.

					



					} elseif ($all < 1) {



					// print the file name and then make a link.

                       			echo "<tr><td> <a href=\"$upload_dir$file\" target=_blank>$file</a> </td>";                        

                       			#------------begin of file size.

    	           			//print the file size.

    	           			$file_size = filesize($upload_dir."".$file);



		     			if ($file_size >= 1048576)

		    			{

					$show_filesize = number_format(($file_size / 1048576),2) . " MB";

		   			}

		     			elseif ($file_size >= 1024)

		    			{

					$show_filesize = number_format(($file_size / 1024),2) . " KB";

		    			}

		       			 elseif ($file_size >= 0)

		       			{

					$show_filesize = $file_size . " bytes";

		      			}

		      			else

		      			{

					$show_filesize = "0 bytes";

		      			}

		        		echo "<td> $show_filesize </td>";

                        		#------------end of file size.

				}



			}

                }

	}



closedir($rep);

clearstatcache();

die

?>

    <?

$all = $_GET['all'];

$rep=opendir($upload_dir);

while ($file = readdir($rep)) {

	if($file != '..' && $file !='.' && $file !=''){

		if (!is_dir($file)){











				if ($all = ''){

					// print the file name and then make a link.

                       			echo "<tr><td> <a href=\"$upload_dir$file\" target=_blank>$file</a> </td>";                        

                       			#------------begin of file size.

    	           			//print the file size.

    	           			$file_size = filesize($upload_dir."".$file);



		     			if ($file_size >= 1048576)

		    			{

					$show_filesize = number_format(($file_size / 1048576),2) . " MB";

		   			}

		     			elseif ($file_size >= 1024)

		    			{

					$show_filesize = number_format(($file_size / 1024),2) . " KB";

		    			}

		       			 elseif ($file_size >= 0)

		       			{

					$show_filesize = $file_size . " bytes";

		      			}

		      			else

		      			{

					$show_filesize = "0 bytes";

		      			}

		        		echo "<td> $show_filesize </td>";

					echo "<td><img src=\"$upload_dir$file\" width=\"200\" height=\"200\"></td></tr>";

                        		#------------end of file size.

					}



			}

                }

	}



closedir($rep);

clearstatcache();

die;

?>

  </table>

      </div>