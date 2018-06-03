<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include 'lib/includeMain.lib.php';
 global $conf,$langs,$db;
 top_httphead();
//get the token,exit if 
$token=GETPOST('token','apha');

if(!isset($_SESSION['ajaxQuerry'][$token]))exit();

$sqlarray=$_SESSION['ajaxQuerry'][$token]['sql'];
$fields=$_SESSION['ajaxQuerry'][$token]['fields'];
$htmlarray=$_SESSION['ajaxQuerry'][$token]['html']; 
$addtionnalChoices=$_SESSION['ajaxQuerry'][$token]['option'];
$separator=isset($htmlarray['separator'])?$htmlarray['separator']:' ';


 $search=GETPOST($htmlName,'alpha');
//find if barckets
$posBs=strpos($htmlName,'[');
if($posBs>0){
    $subStrL1= substr($htmlName, 0, $posBs);
    $search=$_GET[$subStrL1];
    while(is_array($search)){// assumption there is only one value in the array
        $search=array_pop($search);
    }
}


    $sql='SELECT DISTINCT ';
    $sql.=$sqlarray['keyfield'];
    $sql.=' ,'.$sqlarray['fields'];
    $sql.= ' FROM '.MAIN_DB_PREFIX.$sqlarray['table'].' as t';
    if(isset($sqlarray['join']) && !empty($sqlarray['join']))
            $sql.=' '.$sqlarray['join'];
    if(isset($sqlarray['where']) && !empty($sqlarray['where']))
            $sql.=' WHERE '.$sqlarray['where'];
    if(isset($sqlarray['tail']) && !empty($sqlarray['tail']))
            $sql.=' '.$sqlarray['tail'];      
    dol_syslog('form::select_sellist ', LOG_DEBUG);
    
       
    dol_syslog('form::ajax_select_generic ', LOG_DEBUG);
    $return_arr = array();
    $resql=$db->query($sql);
   
    if ($resql)
    {
          // support AS in the fields ex $field1='CONTACT(u.firstname,' ',u.lastname) AS fullname'
        // with sqltail= 'JOIN llx_user as u ON t.fk_user=u.rowid'
        $listFields=explode(',',$sqlarray['fields']);
        $fields=array();
        foreach($listFields as $item){
            $start=MAX(strpos($item,' AS '),strpos($item,' as '));
            $label=($start)? substr($item, $start+4):$item;
            $fields[]=array('select' => $item, 'label'=>$label);
        }

        $i=0;
         //return $table."this->db".$field;
        $num = $db->num_rows($resql);
        while ($i < $num)
        {
            
            $obj = $db->fetch_object($resql);
            
            if ($obj)
            {
                                $label='';
                foreach($fields as $item){
                    if(!empty($label))$label.=$separator;
                    $label.=$obj->{$item->label};
                }    
                $row_array['label'] =  $label;
                $value=$obj->{$sqlarray['keyfield']};
		//$row_array['value'] = $value;
                $row_array['value'] =  $label;
	        $row_array['key'] =$value;
                array_push($return_arr,$row_array);
            } 
            $i++;
        }
        if($addtionnalChoices)foreach($addtionnalChoices as $value => $label){
                $row_array['label'] = $label;
		$row_array['value'] = $label;
	        $row_array['key'] =$value;
            array_push($return_arr,$row_array);
        }

        
    }

 
      echo json_encode($return_arr);
