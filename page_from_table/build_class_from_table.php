#!/usr/bin/php
<?php
/* Copyright (C) 2015 delcroip <pmpdelcroix@gmail.com>
 * Copyright (C) 2008-2014 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       dev/skeletons/build_class_from_table.php
 *  \ingroup    core
 *  \brief      Create a complete class file from a table in database
 */

$sapi_type = php_sapi_name();
$script_file = basename(__FILE__);
$path=dirname(__FILE__).'/';

// Test if batch mode
if (substr($sapi_type, 0, 3) == 'cgi') {
    echo "Error: You are using PHP for CGI. To execute ".$script_file." from command line, you must use PHP for CLI mode.\n";
    exit;
}

// Include Dolibarr environment
require_once("/var/www/html/dolibarr/htdocs/master.inc.php");
// After this $db is a defined handler to database.

// Main
$version='3.2';
@set_time_limit(0);
$error=0;

$langs->load("main");


print "***** $script_file ($version) *****\n";


// -------------------- START OF BUILD_CLASS_FROM_TABLE SCRIPT --------------------

// Check parameters
if (! isset($argv[1]) || (isset($argv[2]) && ! isset($argv[6])))
{
    print "Usage: $script_file tablename [server port databasename user pass]\n";
    exit;
}

if (isset($argv[2]) && isset($argv[3]) && isset($argv[4]) && isset($argv[5]) && isset($argv[6]))
{
	print 'Use specific database ids'."\n";
	$db=getDoliDBInstance('mysqli',$argv[2],$argv[5],$argv[6],$argv[4],$argv[3]);
}

if ($db->type != 'mysql' && $db->type != 'mysqli')
{
	print "Error: This script works with mysql or mysqli driver only\n";
	exit;
}

// Show parameters
print 'Tablename='.$argv[1]."\n";
print "Current dir is ".getcwd()."\n";


// Define array with list of properties
$property=array();
$table=$argv[1];
$foundprimary=0;
$resql=$db->DDLDescTable($table);
if ($resql)
{
	$i=0;
	while($obj=$db->fetch_object($resql))
	{
		$i++;
		$property[$i]['field']=$obj->Field;
                
                // sve the default value from the dB
                $property[$i]['default']=$obj->Default;
                
                // object variable name
                if(strpos($obj->Field,"fk_")===0){
                    $property[$i]['var']=substr($obj->Field,3);
                }else{
                    $property[$i]['var']=$obj->Field;
                }
                $property[$i]['display']=preg_replace('/_/','',ucfirst($property[$i]['var']));
		if ($obj->Key == 'PRI')
		{
			$property[$i]['primary']=1;
			$foundprimary=1;
		}
		else
		{
			$property[$i]['primary']=0;
		}
		$property[$i]['type'] =$obj->Type;
		$property[$i]['null'] =$obj->Null;
		$property[$i]['extra']=$obj->Extra;
		if ($property[$i]['type'] == 'date'
			|| $property[$i]['type'] == 'datetime'
			|| $property[$i]['type'] == 'timestamp')
		{
			$property[$i]['istime']=true;
		}
		else
		{
			$property[$i]['istime']=false;
		}
		if (preg_match('/varchar/i',$property[$i]['type'])
			|| preg_match('/text/i',$property[$i]['type'])
                        || preg_match('/note/i',$property[$i]['field']))
		{
			$property[$i]['ischar']=true;
		}
		else
		{
			$property[$i]['ischar']=false;
		}
                
                $addfield=1;
                if($property[$i]['var']=='id') $addfield=0;
                if($property[$i]['var']=='entity') $addfield=0;
                if($property[$i]['var']=='rowid') $addfield=0;
                if($property[$i]['var']=='user_modification' ) $addfield=0;
                if($property[$i]['var']=='user_modif') $addfield=0;
                if($property[$i]['var']=='date_modification') $addfield=0;
                if($property[$i]['var']=='date_modif') $addfield=0;
                if($property[$i]['var']=='user_creation') $addfield=0;
                if($property[$i]['var']=='user_author') $addfield=0;
                if($property[$i]['var']=='user_creat') $addfield=0;
                if($property[$i]['var']=='date_creation') $addfield=0;
                if($property[$i]['var']=='date_creat') $addfield=0;
                if($property[$i]['var']=='datec') $addfield=0;
                if ($property[$i]['field'] == 'tms') $addfield=0;	// This is a field of type timestamp edited automatically
                if ($property[$i]['extra'] == 'auto_increment') $addfield=0;
                $property[$i]['showfield']=($addfield==1)?true:false;
                //insert/create  exclusion
                $addfield=1;
                if($property[$i]['var']=='id') $addfield=0;
                if($property[$i]['var']=='entity') $addfield=0;
                if($property[$i]['var']=='rowid') $addfield=0;
                if($property[$i]['var']=='user_modification') $addfield=0;
                if($property[$i]['var']=='user_modif') $addfield=0;
                if($property[$i]['var']=='date_modification' ) $addfield=0;
                if($property[$i]['var']=='date_modif' ) $addfield=0;
                if ($property[$i]['field'] == 'tms') $addfield=0;	// This is a field of type timestamp edited automatically
                if ($property[$i]['extra'] == 'auto_increment') $addfield=0;
                $property[$i]['insertfield']=($addfield==1)?true:false;
                //update exclusiton
                $addfield=1;
                if($property[$i]['var']=='id') $addfield=0;
                if($property[$i]['var']=='entity') $addfield=0;
                if($property[$i]['var']=='rowid') $addfield=0;
                if($property[$i]['var']=='user_creation') $addfield=0;
                if($property[$i]['var']=='user_creat') $addfield=0;
                if($property[$i]['var']=='user_author') $addfield=0;
                if($property[$i]['var']=='date_creation') $addfield=0;
                if($property[$i]['var']=='date_creat') $addfield=0;
                 if($property[$i]['var']=='datec') $addfield=0;
                if ($property[$i]['field'] == 'tms') $addfield=0;	// This is a field of type timestamp edited automatically
                if ($property[$i]['extra'] == 'auto_increment') $addfield=0;
                $property[$i]['updatefield']=($addfield==1)?true:false;
                
	}
}
else
{
	print "Error: Failed to get description for table '".$table."'.\n";
	return false;
}

//find the last element for showfield / insertfields / update fields
$i=0;
foreach($property as $key => $prop)
{
    $i++;
    if ($prop['showfield'])
    {
        $lastshowfield=$i;
    }
    if ($prop['insertfield'])
    {
        $lastinsertfield=$i;
    }
    if ($prop['updatefield'])
    {
        $lastupdatefield=$i;
    }
}





//--------------------------------
// Build skeleton_class.class.php
//--------------------------------

// Define working variables
$table=strtolower($table);
$tablenoprefix=preg_replace('/'.preg_quote(MAIN_DB_PREFIX).'/i','',$table);
$classname=preg_replace('/_/','',ucfirst($tablenoprefix));
$classmin=preg_replace('/_/','',strtolower($classname));


// Read skeleton_class.class.php file
$skeletonfile=$path.'skeleton_class.class.php';
$sourcecontent=file_get_contents($skeletonfile);
if (! $sourcecontent)
{
	print "\n";
	print "Error: Failed to read skeleton sample '".$skeletonfile."'\n";
	print "Try to run script from skeletons directory.\n";
	exit;
}

// Define output variables
$outfile='out.'.$classmin.'.class.php';
$targetcontent=$sourcecontent;

// Substitute class name
$targetcontent=preg_replace('/skeleton_class\.class\.php/', $classmin.'.class.php', $targetcontent);
$targetcontent=preg_replace('/skeleton/', $classmin, $targetcontent);
//$targetcontent=preg_replace('/\$table_element=\'skeleton\'/', '\$table_element=\''.$classmin.'\'', $targetcontent);
$targetcontent=preg_replace('/Skeleton_Class/', $classname, $targetcontent);
$targetcontent=preg_replace('/Skeleton/', $classname, $targetcontent);
// Substitute comments
$targetcontent=preg_replace('/This file is an example to create a new class file/', 'Put here description of this class', $targetcontent);
$targetcontent=preg_replace('/\s*\/\/\.\.\./', '', $targetcontent);
$targetcontent=preg_replace('/Put here some comments/','Initialy built by build_class_from_table on '.strftime('%Y-%m-%d %H:%M',mktime()), $targetcontent);

// Substitute table name
//$targetcontent=preg_replace('/MAIN_DB_PREFIX."mytable/', 'MAIN_DB_PREFIX."'.$tablenoprefix, $targetcontent);
$targetcontent=preg_replace('/mytable/',$tablenoprefix, $targetcontent);
// Substitute declaration parameters
$varprop="\n";
$cleanparam='';
foreach($property as $key => $prop)
{
	if ($prop['field'] != 'rowid' && $prop['field'] != 'id')
	{
		$varprop.="\tpublic \$".$prop['var'];
		if ($prop['istime']) $varprop.="=''";
		$varprop.=";";
		if ($prop['comment']) $varprop.="\t// ".$prop['extra'];
		$varprop.="\n";
	}
}
$targetcontent=preg_replace('/public \$prop1;/', $varprop, $targetcontent);
$targetcontent=preg_replace('/public \$prop2;/', '', $targetcontent);

// Substitute clean parameters
$varprop="\n";
$cleanparam='';
foreach($property as $key => $prop)
{
	if ($prop['insertfield'] ||$prop['updatefield'])
	{
		$varprop.="\tif (!empty(\$this->".$prop['var'].")) \$this->".$prop['var']."=trim(\$this->".$prop['var'].");";
		$varprop.="\n";
	}
}
$targetcontent=preg_replace('/if \(isset\(\$this->prop1\)\) \$this->prop1=trim\(\$this->prop1\);/', $varprop, $targetcontent);
$targetcontent=preg_replace('/if \(isset\(\$this->prop2\)\) \$this->prop2=trim\(\$this->prop2\);/', '', $targetcontent);

// Substitute insert into parameters for the create
$varprop="\n";
$cleanparam='';
$i=0;
foreach($property as $key => $prop)
{
	$i++;
	if ($prop['insertfield'])
	{
		$varprop.="\t\$sql.= '".$prop['field'];
                if($i<$lastinsertfield)
                    $varprop.=",";
                $varprop.="';\n";
	}
}
$targetcontent=preg_replace('/\$sql\.= " field1,";/', $varprop, $targetcontent);
$targetcontent=preg_replace('/\$sql\.= " field2";/', '', $targetcontent);

// Substitute insert values parameters
$varprop="\n";
$cleanparam='';

$i=0;
foreach($property as $key => $prop)
{
	$i++;
	if ($prop['insertfield'])
	{
		$varprop.="\t\$sql.=' ";
		
                if($prop['var']=='date_creation' ||$prop['var']=='date_creat' || $prop['var']=='datec'){
                        $varprop.='NOW() ';
                }else if($prop['var']=='user_creation' ||$prop['var']=='user_author' || $prop['var']=='user_creat'){
//                        $varprop.='\'".\$user->id."\'';
                        $varprop.="\''.\$user->id.'\'";
                       // $varprop.='{\$user->id}'; //FIXME ?
                }else if ($prop['istime'])
		{
			$varprop.="'.(empty(\$this->".$prop['var'].') || dol_strlen($this->'.$prop['var'].")==0?'NULL':\"'\".\$this->db->idate(";
			$varprop.="\$this->".$prop['var'].")";
			$varprop.=".\"'\").'";
		}
		else if ($prop['ischar'])
		{
			$varprop.="'.(empty(\$this->".$prop['var'].")?'NULL':\"'\".";
			$varprop.="\$this->db->escape(\$this->".$prop['var'].")";
			$varprop.=".\"'\").'";
		}
		else
		{
			$varprop.="'.(empty(\$this->".$prop['var'].")?'NULL':\"'\".";
			$varprop.="\$this->".$prop['var']."";
			$varprop.=".\"'\").'";
		}
                if($i<$lastinsertfield)
                    $varprop.=",";
                $varprop.="';\n";
	
	}
}
$targetcontent=preg_replace('/\$sql\.= " \'".\$this->prop1\."\',";/', $varprop, $targetcontent);
$targetcontent=preg_replace('/\$sql\.= " \'".\$this->prop2\."\'";/', '', $targetcontent);

// Substitute update values parameters
$varprop="\n";
$cleanparam='';
$i=0;
foreach($property as $key => $prop)
{
	$i++;
	if ($prop['updatefield'])
	{
//FIXME the " should be removed in oder to avoid string processing as much as possible when page load
                
                $varprop.="\t\$sql.=' ";
                $varprop.=$prop['field'].'=';
                if($prop['var']=='date_modification'||$prop['var']=='date_modif'){
                    $varprop.='NOW() ';
                }else if($prop['var']=='user_modification'||$prop['var']=='user_modif'){
                    $varprop.="\"'.\$user->id.'\"";
                     // $varprop.='".\$user."'; //FIXME ?
                }else if ($prop['istime'])
                {
                        // (dol_strlen($this->datep)!=0 ? "'".$this->db->idate($this->datep)."'" : 'null')
                        $varprop.="'.(dol_strlen(\$this->".$prop['var'].")!=0 ? \"'\".\$this->db->idate(";
                        $varprop.="\$this->".$prop['var'];
                        $varprop.=").\"'\":'null').";
                        $varprop.="'";
                }else
                {
                        $varprop.="'.";
                        // $sql.= " field1=".(isset($this->field1)?"'".$this->db->escape($this->field1)."'":"null").",";
                        if ($prop['ischar']){
                            $varprop.='(empty($this->'.$prop['var'].")!=0 ? 'null':\"'\".\$this->db->escape(\$this->".$prop['var'].')."\'")';
                            // $sql.= " field1=".(isset($this->field1)?$this->field1:"null").",";                           
                        }else{
                            $varprop.='(empty($this->'.$prop['var'].")!=0 ? 'null':\"'\".\$this->".$prop['var'].'."\'")';
                        }
                        $varprop.=".'";
                }

                if($i<$lastupdatefield)
                    $varprop.=",";
                $varprop.="';\n";

	}
}
$targetcontent=preg_replace('/\$sql.= " field1=".\(isset\(\$this->field1\)\?"\'".\$this->db->escape\(\$this->field1\)."\'":"null"\).",";/', $varprop, $targetcontent);
$targetcontent=preg_replace('/\$sql.= " field2=".\(isset\(\$this->field2\)\?"\'".\$this->db->escape\(\$this->field2\)."\'":"null"\)."";/', '', $targetcontent);

// Define substitute fetch/select parameters
$varpropselect="\n";
$cleanparam='';
$i=0;
foreach($property as $key => $prop)
{
    $i++;
    if ($prop['field'] != 'rowid')
    {
        $varpropselect.="\t\$sql.=' ";
        $varpropselect.="t.".$prop['field'];
        if ($i < count($property)) $varpropselect.=",";
        $varpropselect.="';";
        $varpropselect.="\n";
    }
}
// Substitute fetch/select parameters
$targetcontent=preg_replace('/\$sql\.= " t\.field1,";/', $varpropselect, $targetcontent);
$targetcontent=preg_replace('/\$sql\.= " t\.field2";/', '', $targetcontent);

// Substitute select set parameters
$varprop="\n";
$cleanparam='';
$i=0;
foreach($property as $key => $prop)
{
	$i++;
	if ($prop['field'] != 'rowid' && $prop['field'] != 'id')
	{
		$varprop.="\t\t\$this->".$prop['var']." = ";
		if ($prop['istime']) $varprop.='$this->db->jdate(';
		$varprop.='$obj->'.$prop['field'];
		if ($prop['istime']) $varprop.=')';
		$varprop.=";";
		$varprop.="\n";
	}
}
$targetcontent=preg_replace('/\$this->prop1 = \$obj->field1;/', $varprop, $targetcontent);
$targetcontent=preg_replace('/\$this->prop2 = \$obj->field2;/', '', $targetcontent);


// Substitute initasspecimen parameters
$varprop="\n";
$cleanparam='';
foreach($property as $key => $prop)
{
	if ($prop['field'] != 'rowid' && $prop['field'] != 'id')
	{
		$varprop.="\t\$this->".$prop['var']."='';";
		$varprop.="\n";
	}
}
$targetcontent=preg_replace('/\$this->prop1=\'prop1\';/', $varprop, $targetcontent);
$targetcontent=preg_replace('/\$this->prop2=\'prop2\';/', '', $targetcontent);
// Substitute serialize parameters
$varprop="\n";
$cleanparam='';
foreach($property as $key => $prop)
{
	if ($prop['insertfield'] ||$prop['updatefield'])
	{
		$varprop.="\t\$array['".$prop['var']."']=\$this->".$prop['var'].";";
		$varprop.="\n";
	}
}
$targetcontent=preg_replace('/\$array\[\'field1\'\]= \$this->field1;/', $varprop, $targetcontent);
$targetcontent=preg_replace('/\$array\[\'field2\'\]= \$this->field2;/', '', $targetcontent);


// Build file
$fp=fopen($outfile,"w");
if ($fp)
{
	fputs($fp, $targetcontent);
	fclose($fp);
	print "\n";
	print "File '".$outfile."' has been built in current directory.\n";
}
else $error++;
/*

//--------------------------------
// Build skeleton_script.php
//--------------------------------

// Read skeleton_script.php file
$skeletonfile=$path.'skeleton_script.php';
$sourcecontent=file_get_contents($skeletonfile);
if (! $sourcecontent)
{
	print "\n";
	print "Error: Failed to read skeleton sample '".$skeletonfile."'\n";
	print "Try to run script from skeletons directory.\n";
	exit;
}

// Define output variables
$outfile='out.'.$classmin.'_script.php';
$targetcontent=$sourcecontent;

// Substitute class name
$targetcontent=preg_replace('/skeleton_class\.class\.php/', $classmin.'.class.php', $targetcontent);
$targetcontent=preg_replace('/skeleton_script\.php/', $classmin.'_script.php', $targetcontent);
$targetcontent=preg_replace('/\$element=\'skeleton\'/', '\$element=\''.$classmin.'\'', $targetcontent);
$targetcontent=preg_replace('/\$table_element=\'skeleton\'/', '\$table_element=\''.$classmin.'\'', $targetcontent);
$targetcontent=preg_replace('/Skeleton_Class/', $classname, $targetcontent);

// Substitute comments
$targetcontent=preg_replace('/This file is an example to create a new class file/', 'Put here description of this class', $targetcontent);
$targetcontent=preg_replace('/\s*\/\/\.\.\./', '', $targetcontent);
$targetcontent=preg_replace('/Put here some comments/','Initialy built by build_class_from_table on '.strftime('%Y-%m-%d %H:%M',mktime()), $targetcontent);

// Substitute table name
$targetcontent=preg_replace('/MAIN_DB_PREFIX."mytable/', 'MAIN_DB_PREFIX."'.$tablenoprefix, $targetcontent);

// Build file
$fp=fopen($outfile,"w");
if ($fp)
{
	fputs($fp, $targetcontent);
	fclose($fp);
	print "File '".$outfile."' has been built in current directory.\n";
}
else $error++;


*/
//--------------------------------
// Build skeleton_card.php, Skeleton_list
//--------------------------------



$files=array('_card', '_list', '_document','_list','.lib','_agenda','_note');
foreach ($files as $file){
    
// Read skeleton_page.php file
$skeletonfile=$path.'skeleton'.$file.'.php';
$sourcecontent=file_get_contents($skeletonfile);
if (! $sourcecontent)
{
    print "\n";
    print "Error: Failed to read skeleton sample '".$skeletonfile."'\n";
    print "Try to run script from skeletons directory.\n";
    exit;
}
// Define output variables
$outfile='out.'.$classmin.$file.'.php';
$targetcontent=$sourcecontent;

// Substitute class name
$targetcontent=preg_replace('/skeleton_class\.class\.php/', $classmin.'.class.php', $targetcontent);
$targetcontent=preg_replace('/skeleton_script\.php/', $classmin.'_script.php', $targetcontent);
$targetcontent=preg_replace('/\$element=\'skeleton\'/', '\$element=\''.$classmin.'\'', $targetcontent);
$targetcontent=preg_replace('/\$table_element=\'skeleton\'/', '\$table_element=\''.$classmin.'\'', $targetcontent);
$targetcontent=preg_replace('/Skeleton_Class/', $classname, $targetcontent);
$targetcontent=preg_replace('/Skeleton/', $classname, $targetcontent);
$targetcontent=preg_replace('/skeleton/', $classmin, $targetcontent);

// Substitute comments
$targetcontent=preg_replace('/This file is an example to create a new class file/', 'Put here description of this class', $targetcontent);
$targetcontent=preg_replace('/\s*\/\/\.\.\./', '', $targetcontent);
$targetcontent=preg_replace('/Put here some comments/','Initialy built by build_class_from_table on '.strftime('%Y-%m-%d %H:%M',mktime()), $targetcontent);

// Substitute table name
$targetcontent=preg_replace('/mytable/',$tablenoprefix, $targetcontent);


// Define substitute fetch/select parameters
$varpropselect="\n";
$cleanparam='';
$i=0;
foreach($property as $key => $prop)
{
    $i++;
    if ($prop['showfield'] ==true)
    {
        $varpropselect.="\t\$sql.=' ";
        $varpropselect.="t.".$prop['field'];
        if ($i < $lastshowfield) $varpropselect.=",";
        $varpropselect.="';";
        $varpropselect.="\n";
    }
}
// Substitute fetch/select parameters
$targetcontent=preg_replace('/\$sql\.= " t\.field1,";/', $varpropselect, $targetcontent);
$targetcontent=preg_replace('/\$sql\.= " t\.field2";/', '', $targetcontent);

/*
 * substitue GETPOST
 */
$varpropget="";
$cleanparam='';
foreach($property as $key => $prop)
{
    if ($prop['showfield']==true){
        if($prop['istime'])
        {
            $varpropget.="\t\$object->".$prop['var']."=dol_mktime(0, 0, 0,";
            $varpropget.='GETPOST(\''.$prop['display']."month'),";
            $varpropget.='GETPOST(\''.$prop['display']."day'),";
            $varpropget.='GETPOST(\''.$prop['display']."year'));\n";
        }else{
            $varpropget.="\t\$object->".$prop['var']."=GETPOST('";
            $varpropget.=$prop['display']."');\n";
            
        }
    }
}
  $targetcontent=preg_replace('/\$object->prop1=GETPOST\("field1"\);/',$varpropget, $targetcontent);
  $targetcontent=preg_replace('/\$object->prop2=GETPOST\("field2"\);/','', $targetcontent);

/*
 * substitue GETPOST list seqrch
 */

$varpropget="";
$cleanparam='';
foreach($property as $key => $prop)
{	
    if($prop['showfield']==true)  
    {  

        if($prop['istime']){
           $varpropget.="\t\$ls_".$prop['var']."_month= GETPOST('ls_".$prop['var']."_month','int');\n";
           $varpropget.="\t\$ls_".$prop['var']."_year= GETPOST('ls_".$prop['var']."_year','int');\n";

        }else if($prop['ischar']){
           $varpropget.="\t\$ls_".$prop['var']."= GETPOST('ls_".$prop['var']."','alpha');\n";
        }else if(strpos($prop['type'],'enum')===0){
            $varpropget.="\t\$ls_".$prop['var']."= GETPOST('ls_".$prop['var']."','alpha');\n";
        }else{
           $varpropget.="\t\$ls_".$prop['var']."= GETPOST('ls_".$prop['var']."','int');\n";               
           if(strpos($prop['field'],'fk_user') ===0)$varpropget.="\tif(\$ls_".$prop['var']."==-1)\$ls_".$prop['var']."='';\n";
        }

    }
}
        
  $targetcontent=preg_replace('/\$ls_fields1=GETPOST\(\'ls_fields1\'\,\'int\'\);/',$varpropget, $targetcontent);
  $targetcontent=preg_replace('/\$ls_fields2=GETPOST\(\'ls_fields2\'\,\'alpha\'\);/','', $targetcontent);

  /*
 * substitue list natural search
 */

$varpropget="";
$cleanparam='';
foreach($property as $key => $prop)
{	
    if($prop['showfield']==true)
    {  

        if($prop['istime']){
           $varpropget.="\tif(\$ls_".$prop['var']."_month)\$sqlwhere .= ' AND MONTH(t.".$prop['field'].")=\"'.\$ls_".$prop['var']."_month.\"'\";\n";
           $varpropget.="\tif(\$ls_".$prop['var']."_year)\$sqlwhere .= ' AND YEAR(t.".$prop['field'].")=\"'.\$ls_".$prop['var']."_year.\"'\";\n";
        }else if($prop['ischar']){
           $varpropget.="\tif(\$ls_".$prop['var'].") \$sqlwhere .= natural_search('t.".$prop['field']."', \$ls_".$prop['var'].");\n";
        }else{
           $varpropget.="\tif(\$ls_".$prop['var'].") \$sqlwhere .= natural_search(array('t.".$prop['field']."'), \$ls_".$prop['var'].");\n";
        }

    }
}
        
  $targetcontent=preg_replace('/if \(\$ls_fields1\) \$sqlwhere \.= natural_search\(array\(\'t\.fields1\'\)\, \$ls_fields1\);/',$varpropget, $targetcontent);
  $targetcontent=preg_replace('/if \(\$ls_fields2\) \$sqlwhere \.= natural_search\(\'t\.fields2\'\, \$ls_fields2\);/','', $targetcontent);

  
  /*
 * substitute table lines
 */

$varprop="\n";
$cleanparam='';
$nbproperty=count($property);
$i=0;
foreach($property as $key => $prop)
{
	if ($prop['showfield']==true)
	{
                
                $varprop.="\n// show the field ".$prop['var']."\n\n";
                $varprop.="\tprint \"<tr>\\n\";\n";
                if ($prop['null']=='YES') //value required
                    $varprop.="\tprint '<td>'.\$langs->trans('";
                else
                    $varprop.="\tprint '<td class=\"fieldrequired\">'.\$langs->trans('";
                $varprop.=$prop['display'];
                $varprop.="').' </td><td>';\n";
                //suport the edit mode
                if(strpos($prop['field'],'fk_') ===0){
                        $varprop.="\t\$sql_".$prop['var']."=array('table'=> '".$prop['var']."','keyfield'=> 'rowid','fields'=>'ref,label', 'join' => '', 'where'=>'','tail'=>'');\n";
                        $varprop.="\t\$html_".$prop['var']."=array('name'=>'".$prop['display']."','class'=>'','otherparam'=>'','ajaxNbChar'=>'','separator'=> '-');\n";
                        $varprop.="\t\$addChoices_".$prop['var']."=null;\n";
                }
                $varprop.="\tif(\$edit==1){\n";
                

                if ($prop['istime']){
                    $varprop.="\tif(\$new==1){\n";
                    $varprop.="\t\tprint \$form->select_date(-1,'";
                    $varprop.=$prop['display']."');\n";
                    $varprop.="\t}else{\n";                                               
                    $varprop.="\t\tprint \$form->select_date(\$object->";
                    $varprop.=$prop['var'].",'";
                    $varprop.=$prop['display']."');\n";  
                    $varprop.="\t}\n\t}else{\n";
                    $varprop.="\t\tprint dol_print_date(\$object->";
                    $varprop.=$prop['var'].",'day');\n";
                }else if(strpos($prop['field'],'user')===0 || strpos($prop['field'],'fk_user') ===0) 
                 {
                        $varprop.="\tprint \$form->select_dolusers(\$object->".$prop['var'].", '";
                        $varprop.=$prop['display']."', 1, '', 0 );\n";
                        $varprop.="\t}else{\n";
                        $varprop.="\t\$fuser->fetch(\$object->".$prop['var'].");\n";
                        $varprop.="\tprint \$fuser->getNomUrl(1);\n";
                 }else if(strpos($prop['field'],'fk_') ===0) 
                { 

                        $varprop.="\tprint select_sellist(\$sql_".$prop['var'].",\$html_".$prop['var'].", \$object->".$prop['var'].",\$addChoices_".$prop['var']." );\n";
                     
                       // $varprop.="\tprint select_generic('".$prop['var']."','rowid','";
                       //$varprop.= $prop['display']."','rowid','description',";
                       // $varprop.= "\$object->".$prop['var'].");\n";
                        $varprop.="\t}else{\n";
                        $varprop.="\tprint print_sellist(\$sql_".$prop['var'].",\$object->".$prop['var'].",'-');";
                        //$varprop.="\tprint print_generic('".$prop['var']."','rowid',";
                        //$varprop.="\$object->".$prop['var'].",'rowid','description');\n";
                }else if(strpos($prop['type'],'enum')===0){
                        $varprop.="\tprint select_enum('{$tablenoprefix}','{$prop['field']}','";
                        $varprop.= $prop['display']."',";
                        $varprop.= "\$object->".$prop['var'].");\n";
                        $varprop.="\t}else{\n";
                        $varprop.="\tprint \$langs->trans(\$object->".$prop['var'].");\n";
                }else                            
                {
                        if(!empty($prop['default'])){
                            $varprop.="\tif (\$new==1)\n";
                            $varprop.="\t\tprint '<input type=\"text\" value=\"";
                            $varprop.=$prop['default']."\" name=\"";
                            $varprop.=$prop['display'];
                            $varprop.="\">';\n\telse\n\t";
                        }
                        $varprop.="\t\tprint '<input type=\"text\" value=\"'.\$object->";
                        $varprop.=$prop['var'].".'\" name=\"";
                        $varprop.=$prop['display']."\">';\n";  

                        $varprop.="\t}else{\n";
                        $varprop.="\t\tprint \$object->";
                        $varprop.=$prop['var'].";\n";
                }

                $varprop.="\t}\n";
                $varprop.="\tprint \"</td>\";\n";
                
//                $varprop.=( $i%2==1)?"\tprint \"\\n</tr>\\n\";\n":'';
                $varprop.="\tprint \"\\n</tr>\\n\";\n";
                $i++;
	}
        
}
//if there is an unpair number of line
if($i%2==1)
{
    $varprop.="\tprint \"<td></td></tr>\\n\";\n";
                
}



$targetcontent=preg_replace('/print "<tr><td>prop1<\/td><td>".\$object->field1."<\/td><\/tr>";/', $varprop, $targetcontent);
$targetcontent=preg_replace('/print "<tr><td>prop2<\/td><td>".\$object->field2."<\/td><\/tr>";/', '', $targetcontent);

/*
 * substitute list header
 */
$varprop='';

foreach($property as $key => $prop)
{
    if($prop['showfield']==true)  
    {
    $varprop.="\tprint_liste_field_titre(\$langs->trans('";
    $varprop.=$prop['display']."'),\$PHP_SELF,'t.";
    $varprop.=$prop['field']."','',\$param,'',\$sortfield,\$sortorder);\n\tprint \"\\n\";\n";
    }
   
}
$targetcontent=preg_replace('/print_liste_field_titre\(\$langs->trans\(\'field1\'\),\$PHP_SELF,\'t\.field1\',\'\',\$param,\'\',\$sortfield,\$sortorder\);/', $varprop, $targetcontent);
$targetcontent=preg_replace('/print_liste_field_titre\(\$langs->trans\(\'field2\'\),\$PHP_SELF,\'t\.field2\',\'\',\$param,\'\',\$sortfield,\$sortorder\);/','', $targetcontent);

/*
 * substitute list serach  param fill 
 */
$varprop='';

foreach($property as $key => $prop)
{
    if($prop['showfield']) 
    {
        if($prop['istime']){
            $varprop.="\tif (!empty(\$ls_".$prop['var']."_month))	\$param.='&ls_".$prop['var']."_month='.urlencode(\$ls_".$prop['var']."_month);\n";
            $varprop.="\tif (!empty(\$ls_".$prop['var']."_year))	\$param.='&ls_".$prop['var']."_year='.urlencode(\$ls_".$prop['var']."_year);\n";            
        }else{
            $varprop.="\tif (!empty(\$ls_".$prop['var']."))	\$param.='&ls_".$prop['var']."='.urlencode(\$ls_".$prop['var'].");\n";
        }
    }
   
}
$targetcontent=preg_replace('/\$param\.=empty\(\$ls_fields1\)\?\'\':\'&ls_fields1=\'\.urlencode\(\$ls_fields1\);/', $varprop, $targetcontent);
$targetcontent=preg_replace('/\$param\.=empty\(\$ls_fields2\)\?\'\':\'&ls_fields2=\'\.urlencode\(\$ls_fields2\);/','', $targetcontent);

/*
 * substitute search fields
 */
$varprop='';

foreach($property as $key => $prop)
{
    if($prop['showfield']==true) 
    {
        $varprop.="//Search field for".$prop['var']."\n";
        $varprop.="\tprint '<td class=\"liste_titre\" colspan=\"1\" >';\n";

        if($prop['istime']){
            
            $varprop.="\tprint '<input class=\"flat\" type=\"text\" size=\"1\" maxlength=\"2\" name=\"";
            $varprop.=$prop['var']."_month\" value=\"'.\$ls_".$prop['var']."_month.'\">';\n";
            $varprop.="\t\$syear = \$ls_".$prop['var']."_year;\n";
            $varprop.="\t\$formother->select_year(\$syear?\$syear:-1,'ls_".$prop['var']."_year',1, 20, 5);\n";

        }else if(strpos($prop['field'],'fk_user') ===0||strpos($prop['field'],'user') ===0) {//FIXME select user instead
            $varprop.="\tprint \$form->select_dolusers('".$prop['var']."',";                
            $varprop.= "\$ls_".$prop['var'].");\n";
                                
        }else if(strpos($prop['field'],'fk_') ===0) {
            $varprop.="\t\$sql_".$prop['var']."=array('table'=> '".$prop['var']."','keyfield'=> 'rowid','fields'=>'ref,label', 'join' => '', 'where'=>'','tail'=>'');\n";
            $varprop.="\t\$html_".$prop['var']."=array('name'=>'ls_".$prop['var']."','class'=>'','otherparam'=>'','ajaxNbChar'=>'','separator'=> '-');\n";
            $varprop.="\t\$addChoices_".$prop['var']."=null;\n";
             $varprop.="\tprint select_sellist(\$sql_".$prop['var'].",\$html_".$prop['var'].", \$ls_".$prop['var'].",\$addChoices_".$prop['var']." );\n";
            
            //$varprop.="\tprint select_generic('".$prop['var']."','rowid','";          
            //$varprop.= "ls_".$prop['var']."','rowid','description',";          
            //$varprop.= "\$ls_".$prop['var'].");\n";
                                
        }else if(strpos($prop['type'],'enum')===0){
            $varprop.="\tprint select_enum('{$tablenoprefix}','{$prop['field']}','";
            $varprop.= "ls_".$prop['var']."',";
            $varprop.= "\$ls_".$prop['var'].");\n";            
        }else
        {                     
            $varprop.="\tprint '<input class=\"flat\" size=\"16\" type=\"text\" name=\"ls_";
            $varprop.=$prop['var']."\" value=\"'.\$ls_".$prop['var'].".'\">';\n";
        }
        $varprop.="\tprint '</td>';\n";

    }
   
}
$targetcontent=preg_replace('/print \'<td><input type="text" name="ls_fields1" value="\'.\$ls_fields1.\'"><\/td>\';/', $varprop, $targetcontent);
$targetcontent=preg_replace('/print \'<td><input type="text" name="ls_fields2" value="\'.\$ls_fields2.\'"><\/td>\';/','', $targetcontent);

/*
 * substitute list rows
 */
$varprop='';

$varprop.="\tprint \"<tr class=\\\"oddeven\\\"  onclick=\\\"location.href='\";\n";
$varprop.="\tprint \$basedurl.\$obj->rowid.\"'\\\" >\";\n";
foreach($property as $key => $prop)
{
if($prop['showfield']==true)  
 {
    
    if($prop['istime']){
        $varprop.="\tprint \"<td>\".dol_print_date(\$db->jdate(\$obj->";
        $varprop.=$prop['field']."),'day').\"</td>\";\n";      
    }else if(strpos($prop['field'],'fk_user') ===0) {
        //$varprop.="\tprint \"<td>\".print_generic('user','rowid',";
        //$varprop.="\$obj->".$prop['field'].",'lastname','firstname',' ').\"</td>\";\n";
        $varprop.="print '<td>';\n";
        $varprop.="if(\$obj->".$prop['field'].">0){";
        $varprop.="\t\$huser=new User(\$db);\n";
	$varprop.="\t\$huser->fetch(\$obj->".$prop['field'].")\n";
	$varprop.="\tprint \$huser->getNomUrl(1);}\n";
        $varprop.="print '</td>';\n";
        
    }else if(strpos($prop['field'],'fk_soc')===0 || strpos($prop['field'],'fk_third_party')===0 )
    {                     
        $varprop.="print '<td>';\n";
        $varprop.="if(\$obj->".$prop['field'].">0){";
        $varprop.="\$societe = new Societe(\$db);\n";
        $varprop.="\$societe->fetch(\$obj->".$prop['field'].");\n";
        $varprop.=" print \$societe->getNomUrl(1,'');}\n";
         $varprop.="print '</td>';\n";
        //$varprop.="\tprint \"<td>\".\$langs->trans(\$obj->".$prop['field'].").\"</td>\";\n";
    }else if(strpos($prop['field'],'fk_') ===0) {
        //$varprop.="\tprint \"<td>\".print_generic('".$prop['var']."','rowid',";
        //$varprop.="\$obj->".$prop['field'].",'rowid','description').\"</td>\";\n";
        $varprop.="\tprint select_sellist(\$sql_".$prop['var'].",\$html_".$prop['var'].", \$ls_".$prop['var'].",\$addChoices_".$prop['var']." );\n";

    }else if($prop['field']=='id' || $prop['field']=='rowid'){
        $varprop.="\tprint \"<td>\".\$object->getNomUrl(\$obj->rowid,\$obj->rowid,'',1).\"</td>\";\n";
    }else if($prop['field']=='ref'){
        $varprop.="\tprint \"<td>\".\$object->getNomUrl(\$obj->ref,'',\$obj->ref,0).\"</td>\";\n";
    }else{                     
        $varprop.="\tprint \"<td>\".\$obj->".$prop['field'].".\"</td>\";\n";
    }
 }

}
$varprop.="\tprint '<td><a href=\"{$classmin}_card.php?action=delete&id='.\$obj->rowid.'\">'.img_delete().'</a></td>';\n";
						
$varprop.="\tprint \"</tr>\";\n";
$targetcontent=preg_replace('/print "<tr><td>prop1<\/td><td>"\.\$obj->field1\."<\/td><\/tr>";/', $varprop, $targetcontent);
$targetcontent=preg_replace('/print "<tr><td>prop2<\/td><td>"\.\$obj->field2\."<\/td><\/tr>";/', '', $targetcontent);



// Build file
$fp=fopen($outfile,"w");
if ($fp)
{
    fputs($fp, $targetcontent);
    fclose($fp);
    print "File '".$outfile."' has been built in current directory.\n";
}
else $error++;

}
// -------------------- END OF BUILD_CLASS_FROM_TABLE SCRIPT --------------------

print "You can now rename generated files by removing the 'out.' prefix in their name and store them into directory /yourmodule/class.\n";
return $error;
