<?php

/* FS_EXTENSION

 _______________________________________________________
| FSEC class						|
| ---------------------					|
| Author: Stuart Konen					|
| stuart@all-interviews.com				|
|							|
| Primary handler of FireStorm extension classes	|
|_______________________________________________________|

*/

define('C_FILE', 2);
define('C_DBFILE', 4);

$a_c_handles['c_file'] = Array ('cfile', 'c_file');

$GLOBALS['class_handles'] = Array (2=>$a_c_handles['c_file']);
$GLOBALS['STATIC_FSEC'] = NULL;
$GLOBALS['destructors'] = Array();


class FSEC {

	var $errors = Array();

	function FSEC($use_classes = NULL){
		if ($use_classes)
			FSEC_Enable($use_classes);
		$this->RegisterClass($this);
	}

	function _FSEC(){

	}

	function Enable($handlers){

		$this_Inst = &FSEC::__INSTANCE();

		if (!is_string($handlers)){
			$this_Inst->__FETCHCLASS($handlers);
			return;
		}
		$str_handle = strtolower($handlers);
		foreach($GLOBALS['class_handles'] as $handle=>$names){
			for($i=0; $i<sizeof($names); $i++){
				if ($names[$i] == $str_handle){
					$this_Inst->__FETCHCLASS($handle);
					return;
				}
			}
		}
		$this_Inst->push_error("Unable to find $str_handle");
	}

	function RegisterClass(&$class_object){
		$GLOBALS['destructors'][sizeof($GLOBALS['destructors'])] = &$class_object;		
	}

	function __FETCHCLASS($class_handle){
		$required = 0;
		if ($class_handle & C_FILE)
			$required += require_once(dirname(__FILE__) . "/file/file.php");
		if ($class_handle & C_DBFILE)
			$required += require_once(dirname(__FILE__) . "/file/file_db.php");

		if (!$required){
			$this_Inst = &FSEC::__INSTANCE();
			$this_Inst->push_error("Unable to load classes");
		}
	}

	function __INSTANCE(){
		if (!isset($this)){
			if (!isset($GLOBALS['STATIC_FSEC']))
				$GLOBALS['STATIC_FSEC'] = &new FSEC(NULL);

			return $GLOBALS['STATIC_FSEC'];
		} 
		return $this;
	}

	function lasterror(){
		$this_Inst = &FSEC::__INSTANCE();
		return $this_Inst->errors[sizeof($this_Inst->errors)];
	}

	function push_error($str_error, $error_type = E_USER_ERROR){

		$this_Inst = &FSEC::__INSTANCE();
		$this_error = &$this_Inst->errors[];

		$this_error['error'] = $str_error;
		$this_error['type'] = $error_type;

		if (error_reporting()){
			error_log ("FireStorm Extension: " . $str_error, 0);
			if (error_reporting() & E_USER_ERROR){
				if ($error_type == E_USER_WARNING)
					echo "<BR><fontface=arial size=2><u>FireStorm Extension <b>Warning:</b> " . $str_error . "<BR><BR>Send Questions to Stuart Konen: <a href='mailto:support@all-interviews.com'>support@all-interviews.com</a></font><BR>";
				else
					exit("<BR><fontface=arial size=2><u>FireStorm Extension has encountered an error:</u><BR><b>Fatal Error:</b> " . $str_error . "<BR><BR>Send Questions to Stuart Konen: <a href='mailto:support@all-interviews.com'>support@all-interviews.com</a></font><BR>");
			}
		}

	}
}

function __DECONSTRUCT(){

	global $destructors;

	for ($i=0; $i<count($destructors); $i++){
		$class_object = &$destructors[$i];
		$class = get_class($class_object);		
		while ($class){
			$function = "_$class";
			if (method_exists($class_object, $function)) {
				$class_object->$function(); break;
			} else {
				$class = get_parent_class($classname);
			}
		}
	}
}

register_shutdown_function ("__DECONSTRUCT");
?>