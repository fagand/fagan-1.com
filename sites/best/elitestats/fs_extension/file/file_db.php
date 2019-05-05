<?php

/* FS_EXTENSION

 _______________________________________
| C_DBFile class			|
| ---------------------			|
| Author: Stuart Konen			|
| stuart@all-interviews.com		|
|					|
| Easy to use alternative to MySQL	|
|_______________________________________|

*/

define('DB_CREATE', 2);
define('DB_OPEN', 4);
define('DB_E_NO_HALT', 8);
define('DB_NO_AUTOMODIFY', 16);
define('DB_ARRAY_IN_ARRAY', 32);
define('DB_VIRTUAL', 64);

$db_in_use = Array();

class C_DBFile {

	var $db_path;
	var $db_orig_vals = Array();
	var $db_flags;
	var $last_error = false;	
	var $a_data = Array();

	function C_DBFile($path, $db_flags = 4){ 


		$this->db_flags = $db_flags; 
		$this->db_path = $path;
		$path = &$this->db_path;

		if ($path == "" && !($db_flags & DB_VIRTUAL)){			
			$this->push_error(1);
			return;
		}

		if (substr_count($path, ".") == 0)
			$path .= ".dat";		

		if (!($db_flags & DB_CREATE) && !($db_flags & DB_VIRTUAL)){
			if (!file_exists($path)){
				//File does not exist
				$this->push_error(0);
				return;	
			}
		}
		if ($db_flags & DB_OPEN && file_exists($path)){
			$this->DB_Read();
		}

		$this->db_orig_vals = $this->a_data;

		foreach($this->db_orig_vals as $key=>$val){
			if (substr_count($key, "_loop") > 0){
				unset ($this->db_orig_vals[str_replace("_loop", "", $key)]);
				$this->db_orig_vals[str_replace("_loop", "", $key)] = $this->db_orig_vals[$key];
			}
		} 

		DB_RegSFunc($this);
	}

	function DB_Read($contents = NULL, $virtual = false){

		static $array_keys;

		$a_data_buf = Array();
		$b_store_buffer = false;

		if ($contents == NULL){
			if (($contents = C_DBFile::DB_File_Read($this->db_path)) == -1)
				$this->push_error(4);
		}
		else if (!$virtual){		
			$a_data_buf = $this->a_data;
			$b_store_buffer = true;
		}

		//Empty current stack
		$this->a_data = Array();

		$curloc = 0; $i=0;

		while (strpos($contents, "<#!", $curloc) !== FALSE && strpos($contents, "!#>", $curloc) !== FALSE){
			$loc_s = strpos($contents, "<#!", $curloc)+3;
			$loc_e = strpos($contents, "!#>", $curloc);			
			$vname = substr($contents, $loc_s, $loc_e-$loc_s);			
		
			if (substr_count($contents, "<#!END" . $vname . "!#>") == 0){
				$curloc++; continue;
			}

			$loc_fe = strpos($contents, "<#!END" . $vname . "!#>", $curloc);
			$loc_se = (($loc_s-3)+strlen("<#!" . $vname . "!#>"));
			$str_value = substr($contents, $loc_se, $loc_fe-$loc_se);

			if (substr_count($str_value, "<#!" . $vname . "!#>") != substr_count($str_value, "<#!END" . $vname . "!#>")){
				$loc_fe = strpos($contents, "<#!END" . $vname . "!#>", $loc_fe+1);
				if ($loc_fe === FALSE){
					$curloc++; continue;
				}
				$str_value = substr($contents, $loc_se, $loc_fe-$loc_se);
			}

			$curloc = $loc_fe+strlen("<#!END" . $vname . "!#>");
			$vname = strtolower($vname);			

			if (strlen($vname) == 0){
				continue;
			}

			if (strlen($str_value) > 0){
				$orig_array_keys = $array_keys;
				$array_keys .= "[" . $vname . "]";
				$ary_value = $this->DB_Read($str_value);
				$array_keys = $orig_array_keys;
			}
			
			if (count($ary_value) > 0){
				$str_value = $ary_value;
			} else if ($str_value == "-null-array-") {
				$str_value = Array();
			} else {
				$str_value = str_replace("<^!", "<#!", $str_value);
				$str_value = str_replace("!^>", "!#>", $str_value);
			}

			//Check for repeatitive value arrays
			if (array_key_exists($vname . "_loop", $this->a_data)) {
				//Multiple values
				$this->a_data[$vname . "_loop"][count($this->a_data[$vname . "_loop"])] = $str_value;	
			} else {
				if (array_key_exists($vname, $this->a_data)) {
					$vname_buf = $this->a_data[$vname];
					$this->a_data[$vname . "_loop"] = Array();
					$this->a_data[$vname . "_loop"][0] = $vname_buf;
					$this->a_data[$vname . "_loop"][1] = $str_value;
					$this->a_data[$vname] = &$this->a_data[$vname . "_loop"];
				} else if (is_array($str_value) && $this->db_flags & 'DB_ARRAY_IN_ARRAY'){
					//$this->a_data[$vname . "_loop"] = Array();
					//$this->a_data[$vname . "_loop"][0] = $str_value;
					//$this->a_data[$vname] = &$this->a_data[$vname . "_loop"];					
				} else {
					$this->a_data[$vname] = $str_value;
				}

			}       
		}		

		if ($b_store_buffer){
			$switchbuf = $this->a_data;
			$this->a_data = $a_data_buf;
			return $switchbuf;
		}
	}

	function packvalues($array){

		static $array_keys;
		static $level = 0;		
		$str_write = ""; $level++;

		foreach ($array as $name=>$value){

			$tagname = strtoupper($name);			

			$this_write = "<#!" .$tagname . "!#>";

			if (substr_count($name, "_loop") > 0)
				continue;

			if (is_array($value)){
				if (count($value) == 0){				
				$this_write = /*"\r\n" . */$this_write . "-null-array-";
				} else {
					$orig_array_keys = $array_keys;
					$array_keys .= "[" . $name . "]";
					$this_write = /*"\r\n" . */$this_write . $this->packvalues($array[$name]);
					$array_keys = $orig_array_keys;
				}				
			} else {				
				$value = str_replace("!#>", "!^>", str_replace("<#!", "<^!", $value));				
				$this_write = /*"\r\n" . */$this_write . $value;
			}
			$str_write .= $this_write . "<#!END" . $tagname . "!#>\r\n";
		}

		$level--;
		return "\r\n" . $str_write;
	}

	function DB_Write(){		
		if (!is_dir(dirname($this->db_path))){
			$this->push_error(4);
			return;
		}

		$str_write = $this->packvalues($this->a_data);
			
		if (!C_DBFile::DB_File_Write($this->db_path, $str_write))
			$this->push_error(3);
	}

	function DB_File_Read($filename){
	
		clearstatcache();
		$f_complete = false;
		if (!is_writable($filename)){
			//Attempt to fix an invalid chmod
			@chmod($filename, 0777);
		}
	
		for ($i=0; $i<4 && !$f_complete; $i++){
			if (($temp = fopen ($filename, "r"))){
				if (filesize ($filename) <= 0){ $contents = ""; }
				else {$contents = fread ($temp, filesize ($filename)); }
				fclose($temp);
				$f_complete = true;
			}
		}
	
	        if (!$f_complete){ return -1; }
	
	        return $contents;
	}
	
	
	function DB_File_Write($filename, $contents){
	
		clearstatcache();
		$f_complete = false;

		if (!is_writable($filename)){
			//Attempt to fix an invalid chmod
			@chmod($filename, 0777);
		}
	
		for ($i=0; $i<4 && !$f_complete; $i++){
			if (($temp = fopen ($filename, "w"))){
				fputs ($temp, $contents);
				fclose($temp);
				$f_complete = true;
			}
		}
	
		return $f_complete;
	}

	function check_modified(){
		if (!($this->db_flags & DB_NO_AUTOMODIFY) && $this->a_data != $this->db_orig_vals){
			$this->DB_Write();
			$this->db_orig_vals = $this->a_data;
		}
	}

	function push_error($num){		
		$errors = Array();
		$errors[] = "Unable to locate file: " . $this->db_path . ".";
		$errors[] = "No input file defined.";
		$errors[] = "Unable to write to " . $this->db_path . ".";
		$errors[] = "Unable to read from " . $this->db_path . ".";
		$errors[] = "Unable to locate directory: " . dirname($this->db_path) . ".";

		if (!($this->db_flags & DB_E_NO_HALT)){
			error_log ("FireStorm DB: " . $errors[$num], 0);
			exit("<BR><fontface=arial size=2><u>FireStorm DB has encountered an error:</u><BR><b>Fatal Error:</b> " . $errors[$num] . "<BR><BR>Send Questions to Stuart Konen: <a href='mailto:support@all-interviews.com'>support@all-interviews.com</a></font><BR>");
		}
		$this->last_error = $errors[$num];
		FSEC::push_error($errors[$num]);
	}	
}

function DB_RegSFunc(&$class){
	global $db_in_use;
	if (get_class($class) != "c_dbfile")
		return;
	$db_in_use[sizeof($db_in_use)] = &$class;
	//array_push($db_in_use, &$class);
}

function DB_CModified(){
	global $db_in_use;
	for ($i=0; $i<count($db_in_use); $i++){
		$class = &$db_in_use[$i];
		if (get_class($class) != "c_dbfile")
			continue;
		$class->check_modified();	
	}
}

register_shutdown_function ('DB_CModified');

?>