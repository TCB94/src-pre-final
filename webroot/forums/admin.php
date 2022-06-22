<?php
session_start();
$loggedin=isset($_SESSION['User']);
$calledbefore=isset($_SESSION['User']['couldbeadmin']);
$authorized=false;

if($loggedin){

//The guy is already an admin. He's cool. Authorize him,
//and make sure we know he was at one point an admin.
if($_SESSION['User']['role']=='admin'){
    $_SESSION['User']['couldbeadmin']=true;
    $authorized=true;}

//Hm.. this person is just a regular user. Was he at one
//point an admin?
if($_SESSION['User']['role']=='user'){
    if($calledbefore){
        if($_SESSION['User']['couldbeadmin']){
            $authorized=true;}}}
}
//user isn't even logged into scratchr! get out of here!
else{
    header('Location: http://scratch.mit.edu');
}

if($authorized){

    //switch views
    if($_SESSION['User']['role']=='user'){
        $_SESSION['User']['role']='admin';
    }
    else{
        $_SESSION['User']['role']='user';}
    
    //redirect back
    if($_SERVER['REFERER']!=''){
        header('Location: '.$_SERVER['REFERER']);
    }
    else{
        header('Location: http://scratch.mit.edu');
    }
}
//user is not authorized to be here
else{
    header('Location: http://scratch.mit.edu');
}
?>
