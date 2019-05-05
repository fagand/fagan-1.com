<?php

/* FS_EXTENSION

 _______________________________________
| C_Backup 				|
| ---------------------			|
| Author: Stuart Konen			|
| stuart@all-interviews.com		|
|					|
| Class for file handling		|
| Class to backup and restore files	|
|_______________________________________|

*/

require_once("file_db.php");

if (!class_exists('c_dbfile'))
	require("file_db.php");

if (!function_exists("file_get_contents")){

	function file_get_contents($filename){
	
		if (!file_exists($filename))
			return FALSE;
	
		if (filesize($filename) == 0)
			return "";
	
		if (!($fp = fopen($filename, "rb")))
			return FALSE;
		
		$contents = fread ($fp, filesize ($filename));
		fclose($fp);
	
		return $contents;
	}
}

if (!function_exists("file_put_contents")){

	function file_put_contents($filename, $data){
		
		if (!($fp = fopen($filename, "wb")))
			return FALSE;
	
		if (!fwrite($fp, $data, strlen($data))){
			fclose($fp);
			return FALSE;
		}
	
		fclose($fp);
	
		return TRUE;
	}
}

/*
 C_File : Class Methods and Members:
 __________________________

 -Backup(str filename)
 Automatically backup the file to filename.ext.bak

 -Read(str filename, int bytes, int offset, callback function(s) ...)
 Returns a certain part of the files contents in a string.
 Optional callback to function(s)

 -ReadAll(str filename, callback function(s) ...)
 Read file contents and put them in a string.
 Optional callback to function(s)

 -Restore(str filename)
 Restore filename.ext.bak if it exists.

 -Write(str filename, str string, mode, int length)
 Writes data to filename

 -$lasterror
 Last occurring error. (string)

*/

define('CFILE_BUFFERSIZE', 1024);
define('CFILE_MAX_SPLITS', 128);
define('CFILE_MIN_SPLIT', 8);

define('CFILE_OVERWRITE', 0);
define('CFILE_APPEND', 1);
define('CFILE_PREPEND', 1);

class C_File {
	
	var $lasterror = false; 

	function C_File(){
	}

	function backup($filename){
		
		if (((!isset($this)) && !C_File::__FILEMUSTEXIST($filename)) || ((isset($this)) && !$this->__FILEMUSTEXIST($filename)))
			return FALSE;

		$backup = new C_Backup($filename . ".bak");
		$backup->push_files($filename);
		$backup->backup_db->check_modified();
		$backup->backup_db->flags |= DB_NO_AUTOMODIFY;

		return TRUE;
	}

	function perform($filename, $callback){
		
		if (((!isset($this)) && !C_File::__FILEMUSTEXIST($filename)) || ((isset($this)) && !$this->__FILEMUSTEXIST($filename)))
			return FALSE;

		if (!isset($this)){
			$cfile_handle = new C_File();
		} else {
			$cfile_handle = &$this;
		}

		if (($contents = $cfile_handle->ReadAll($filename)) === FALSE)
			return FALSE;

		for ($i=1; $i<func_num_args(); $i++){	
			$contents = call_user_func(func_get_arg($i), $contents);
		}

		C_File::Write($filename, $contents);

		return TRUE;			
	}

	function read($filename, $bytes, $offset=0){

		if (((!isset($this)) && !C_File::__FILEMUSTEXIST($filename)) || ((isset($this)) && !$this->__FILEMUSTEXIST($filename)))
			return FALSE;

		if (!($fp = fopen($filename, "rb"))){
			$this->last_error =  C_File::push_error(NULL, "Unable to open " . $filename . " for reading.");
		}

		fseek($fp, $offset);
		$contents_part = fread($fp, $bytes);
		fclose($fp);

		for ($i=3; $i<$num_args; $i++){			
			$contents_part = call_user_func(func_get_arg($i), $contents_part);
		}
	}

	function readall($filename){

		if (((!isset($this)) && !C_File::__FILEMUSTEXIST($filename)) || ((isset($this)) && !$this->__FILEMUSTEXIST($filename)))
			return FALSE;

		if (($contents = file_get_contents($filename)) === FALSE || ($num_args = func_num_args()) == 1){
			return $contents;
		}
		for ($i=1; $i<$num_args; $i++){			
			$contents = call_user_func(func_get_arg($i), $contents);
		}
		
		return $contents;		
	}

	function restore($filename){

		if (((!isset($this)) && !C_File::__FILEMUSTEXIST($filename . ".bak")) || ((isset($this)) && !$this->__FILEMUSTEXIST($filename . ".bak")))
			return FALSE;
		
		$backup = new C_Backup($filename . ".bak");
		$backup->restore();
	}

	function split_by_num($filename, $num, $destination = ""){
		return split_by_size($filename, ceil(filesize($filename)/$num), $destination);
	}

	function split_by_size($filename, $bytes, $destination = ""){

		if ($destination != ""){

			$last_char = $destination{strlen($destination)-1};
			
			if ($last_char != "\\" && $last_char != "/"){
				$destination .= "/";
			}
			if (!is_dir($destination)){
				$this->last_error =  C_File::push_error(NULL, "The following directory does not exist: \"" . $destination . "\"");
				return FALSE;
			}			
		}

		if (((!isset($this)) && !C_File::__FILEMUSTEXIST($filename)) || ((isset($this)) && !$this->__FILEMUSTEXIST($filename)))
			return FALSE;

		if (!is_int($bytes)){
			$this->last_error = C_File::push_error(NULL, "'$bytes' is not a valid file size.");
			return FALSE;
		}

		if ($bytes > filesize($filename))
			return TRUE;

		if ((filesize($filename)/$bytes) > CFILE_MAX_SPLITS){
			$this->last_error = C_File::push_error(NULL, "CFILE_MAX_SPLITS is set to '" .  CFILE_MAX_SPLITS . "', splitting this file into chunks $bytes bytes in size will result in " . (filesize($filename)/$bytes) . " files.");
			return FALSE;
		}
		if ($bytes < CFILE_MIN_SPLIT){
			$this->last_error = C_File::push_error(NULL, "CFILE_MIN_SPLIT is set to '" .  CFILE_MIN_SPLIT . "' bytes, you must either change the CFILE_MIN_SPLIT value or split the file into chunks larger than the value.");
			return FALSE;
		}

		if (!$fp = fopen($filename, "rb")){
			$this->last_error =  C_File::push_error(NULL, "Unable to open " . $filename . " for reading.");
			return FALSE;
		}

		$fileparts = pathinfo($filename);
		$dest_fp = NULL;
		$split_num = 1;
		$part_written = 0;

		//It's likely that massive files will be used
		//So read recursively

		while (strlen($str_slice = fread($fp, CFILE_BUFFERSIZE)) > 0){
			$bytes_read = strlen($str_slice);
			$total_written = 0;
			$bytes_written = 0;
			
			while ($total_written != $bytes_read){
		
				$dest_filename = $destination . basename($filename, "." . $fileparts["extension"]) . "_" . $split_num . "." . $fileparts["extension"];
		
				if (!$dest_fp){					
					if (!$dest_fp = fopen($dest_filename, "wb")){
						$this->last_error =  C_File::push_error(NULL, "Unable to open " . $dest_filename . " for writing.");
						return FALSE;
					}
				}
				$bytestowrite = $bytes-$part_written;

				if ($bytes_read < $bytestowrite) {
					$bytestowrite = strlen($str_slice);					
				}

				$bytes_written += $bytestowrite;
				$total_written += $bytestowrite;
				$part_written += $bytestowrite;
						
				fwrite($dest_fp, $str_slice, $bytestowrite);
				$str_slice = substr($str_slice, $bytestowrite);	
	
				if ($part_written >= $bytes){					
					$split_num++;
					$bytes_written = $part_written = 0;
					fclose($dest_fp); $dest_fp = NULL;
				}		
			}
		}

		fclose($fp);

		if ($dest_fp){
			fclose($dest_fp); $dest_fp = NULL;
		}

		$mending_batch = "@ECHO OFF\r\n";
		$mending_batch .= "ECHO FSEC Auto Restoring File\r\n";
		$mending_batch .= "ECHO Copyright (c) 2004, FireStorm\r\n";
		$mending_batch .= "ECHO FSEC Author: Stuart Konen\r\n";
		//$mending_batch .= "ECHO Contact: support@all-interviews.com\r\n";
		$mending_batch .= "ECHO Mending Split File...\r\nCOPY /B ";

		for ($i=1; $i<=$split_num; $i++){
			$splitname = basename($filename, "." . $fileparts["extension"]) . "_" . $i . "." . $fileparts["extension"];
		
			if ($i != 1)
				$mending_batch .= "+ ";
			$mending_batch .= "\"" . $splitname . "\" ";
			
		}

		$mending_batch .= "\"" . $fileparts["basename"] . "\"";

		if (!$fp = fopen($destination . basename($filename, "." . $fileparts["extension"]) . ".bat", "w"))
			return FALSE;

		fwrite($fp, $mending_batch);		
		fclose($fp);

		return TRUE;
	}

	function write($filename, $string, $mode = CFILE_OVERWRITE, $length = 0){

		if (strlen($string) == 0)
			return TRUE;

		if ($mode == 'CFILE_PREPEND' && file_exists($filename)){
			$string .= C_File::ReadAll($filename);
		}

		if ($mode == 'CFILE_APPEND'){ $w_mode = "a"; }
		else { $w_mode = "w"; }
	
		if (!$fp = fopen($filename, $w_mode)){
			$this->last_error = C_File::push_error(NULL, "Unable to access " . $filename . " for writing.");
			return FALSE;
		}

		if ($length > 0){ $written = fwrite($fp, $string, $length); }
		else { $written = fwrite($fp, $string);	}

		if (!$written){
			$this->last_error = C_File::push_error(NULL, "Unable to write to " . $filename);
			fclose($fp);
			return FALSE;
		}

		fclose($fp);

		return TRUE;
	}

	function __FILEMUSTEXIST($filename){		

		if (!file_exists($filename)){
			$this->last_error = C_File::push_error(NULL, "Unable to locate " . $filename);
			return FALSE;
		}
		return TRUE;
	}

	function push_error($num, $str_error = ""){

		if (strlen($str_error) == 0){
			$errors = Array();
			$str_error = $errors[$num];
		}
		if (error_reporting()){
			error_log ("FireStorm Extension: " . $str_error, 0);
			exit("<BR><fontface=arial size=2><u>FireStorm Extension has encountered an error:</u><BR><b>Fatal Error:</b> " . $str_error . "<BR><BR>Send Questions to Stuart Konen: <a href='mailto:support@all-interviews.com'>support@all-interviews.com</a></font><BR>");
		}

		FSEC::push_error($str_error);
		return $str_error;		
	}
}

class C_Backup {

	var $last_error = false;
	var $backup_db;
	var $backup_files;
	var $backup_path;

	function C_Backup($path){

		if ($path == ""){
			$this->push_error(0);
			return;
		}

		$this->backup_path = $path;

		$this->backup_db = &new C_DBFile($path, DB_CREATE);
		$this->backup_db->a_data['b_file'] = Array();
		
		$this->backup_files = &$this->backup_db->a_data['b_file'];

		for ($i=1; $i<func_num_args(); $i++){
			push_files(func_get_arg($i));
		}
	}

	function push_files($filename){

		if (func_num_args() == 0)
			return;

		$files = func_get_args(); $backups = &$this->backup_files;

		foreach($files as $key=>$path){

			if (!file_exists($path)){
				$this->push_error(NULL, "Unable to locate file: " . $path);
				return;
			}

			//The file exists, retrieve the data
			unset($this_backup); $this_backup = NULL;

			for ($i=0; $i<count($backups); $i++){
				if ($backups[$i]['name'] == strtolower($path)){
					$this_backup = &$backups[$i];
					break;
				}
			}
			if ($this_backup == NULL){
				$this_backup = &$backups[count($backups)];
				$this_backup['name'] = strtolower($path);
			}

			$loaded = false;
			for ($i=0; $i<4 && !$loaded; $i++){
				if (($this_backup['data'] = file_get_contents($path)) !== FALSE)
					$loaded = true;				
			}

			if (!$loaded){
				$this->push_error(NULL, "Unable to read from " . $path);
			}
		}
	}

	function restore($restore_path=""){

		if (!file_exists($this->backup_path)){
			$this->push_error(1);
		}

		$restore_files = NULL;

		for ($i=1; $i<func_num_args(); $i++){
			if (!is_array($restore_files)){
				$restore_files = Array();
			}
			$restore_files[] = func_get_arg($i);
		}

		$this->backup_db->DB_Read();
		$this->backup_files = &$this->backup_db->a_data['b_file'];

		if (is_array($this->backup_files)){

			for ($i=0; $i<count($this->backup_files); $i++){

				$orig_name = $this->backup_files[$i]['name'];
				$lname = strtolower($orig_name);

				if ($restore_files != NULL && !array_key_exists($lname, $restore_files) && !array_key_exists(basename($lname), $restore_files)){
					continue;
				}
		
				if ($restore_path != ""){
					$last_char = $restore_path{strlen($restore_path)-1};
					if ($last_char != "/" && $last_char != "\\")
						$restore_path .= "/";
					$this->backup_files[$i]['name'] = $restore_path . basename($restore_path);
				}

				if (!is_dir(dirname($this->backup_files[$i]['name']))){
					$this->push_error("Directory does not exist: " . dirname($this->backup_files[$i]['name']));
					continue;
				}
				if (!($fp = fopen($this->backup_files[$i]['name'], "wb"))){
					$this->push_error("Unable to retain file access with " . $this->backup_files[$i]['name']);
					continue;
				}

				//Binary safe write
				if (!fputs($fp, $this->backup_files[$i]['data'], strlen($this->backup_files[$i]['data']))){
					fclose($fp);
					$this->push_error("Unable to write to " . $this->backup_files[$i]['name']);
					continue;
				}
				fclose($fp);

				$this->backup_files[$i]['name'] = $orig_name;
			}
		}
	}

	function push_error($num, $str_error = ""){

		if (strlen($str_error) == 0){
			$errors = Array();
			$errors[] = "No backup file specified.";
			$errors[] = "Unable to restore backup. The backup file does not exist.";
			$str_error = $errors[$num];
		}
		if (error_reporting()){
			error_log ("FireStorm Extension: " . $str_error, 0);
			exit("<BR><fontface=arial size=2><u>FireStorm Extension has encountered an error:</u><BR><b>Fatal Error:</b> " . $str_error . "<BR><BR>Send Questions to Stuart Konen: <a href='mailto:support@all-interviews.com'>support@all-interviews.com</a></font><BR>");
		}
		$this->last_error = $str_error;
		FSEC::push_error($str_error);
	}
}

?>