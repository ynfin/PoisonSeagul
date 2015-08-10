<?php

#search directory and list contents
#accepts arguments for base dir, requested folder path, filter
function searchDir($base_dir="./",$p="",$f="",$allowed_depth=-1){
	$contents=array();

	# trim all input arguments for whitespace
	$base_dir=trim($base_dir);
	$p=trim($p);
	$f=trim($f);

  # if base dir is not given, use the this
	if($base_dir=="")$base_dir="./";

	# if last character of basedir lacks a "/" then add one
	if(substr($base_dir,-1)!="/")$base_dir.="/";

	# remove the "../" and "./" directories, and trim all "./"
	$p=str_replace(array("../","./"),"",trim($p,"./"));

	# add the requested path to the base directory string
	$p=$base_dir.$p;

	#if p is not a directory, meaning it is a file, get the path to it
	if(!is_dir($p))$p=dirname($p);

	#if the last character of p is not a "/" then add one
	if(substr($p,-1)!="/")$p.="/";

	# if caps are set on allowed depth (allowed depth>-1) count the dirs
	if($allowed_depth>-1){
		$allowed_depth=count(explode("/",$base_dir))+ $allowed_depth-1;
		$p=implode("/",array_slice(explode("/",$p),0,$allowed_depth));
		if(substr($p,-1)!="/")$p.="/";
	}

	# if f is empty, create an empty array, if not explode and store elements
	$filter=($f=="")?array():explode(",",strtolower($f));

	# store the files in the files array, and shut up while scanning
	$files=@scandir($p);

	# if there are no files after scan, return an empty array and the path
	if(!$files)return array("contents"=>array(),"currentPath"=>$p);

	# iterate through all files found
	for ($i=0;$i<count($files);$i++){

		# gather name and path of each file
		$fName=$files[$i];
		$fPath=$p.$fName;

		# check if the file is a directory and tag it as directory
		# each file is tagged as a folder by default
		$isDir=is_dir($fPath);
		$add=false;
		$fType="folder";

		# if the file is a file
		if(!$isDir){

			# extract extension from filename
			$ft=strtolower(substr($files[$i],strrpos($files[$i],".")+1));
			$fType=$ft;

			# if there is anything in the filter that matches, add it
			if($f!=""){
				if(in_array($ft,$filter))$add=true;
			}else{
				$add=true;
			}
		# if the file is a folder
		}else{
			if($fName==".")continue;
			$add=true;

			# filter it, and discard if bad
			if($f!=""){
				if(!in_array($fType,$filter))$add=false;
			}

			# if the file is the ".." folder
			if($fName==".."){
				# if we are in the base dir, no not add upper directory option
				if($p==$base_dir){
					$add=false;
				}else $add=true;

				# explode the path by path steps
				$tempar=explode("/",$fPath);
				# remove last two elements, so the file references parent dir
				array_splice($tempar,-2);
				# implode the array, to recreate the path string
				$fPath=implode("/",$tempar);
				# if for some reason, this reference is shorter than basedir, deny it!
				if(strlen($fPath)<= strlen($base_dir))$fPath="";
			}
		}

		# if the created fPath is non-zero, append to basedir
		if($fPath!="")$fPath=substr($fPath,strlen($base_dir));
		# if approved to add, add path, name and type to contents[] array
		if($add)$contents[]=array("fPath"=>$fPath,"fName"=>$fName,"fType"=>$fType);
	}

	# if p is shorter than base_dir, deny it! Else, cut away base_dir and use it
	$p=(strlen($p)<= strlen($base_dir))?$p="":substr($p,strlen($base_dir));
	# return the final contents and its respective path
	return array("contents"=>$contents,"currentPath"=>$p);
}

# if variables are posted, process them and encode to JSON
$p=isset($_POST["path"])?$_POST["path"]:"";
$f=isset($_POST["filter"])?$_POST["filter"]:"";
echo json_encode(searchDir("/var/www/data/disk/box",$p,$f,-1));
?>
