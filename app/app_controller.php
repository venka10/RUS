<?php
class AppController extends Controller {
	
	var $components = array('RequestHandler');
	var $helpers = array('Html','Ajax','Session','Javascript','Form');
	
	function checkSession()
    {
    	// If the session info hasn't been set...
    	//$this->log(printf('strpos('.$this->here.',"/AppUsers/login"): %d',strpos($this->here,"/AppUsers/login")));
        //$this->log(printf('strpos('.$this->here.',"/app_users/login"): %d',strpos($this->here,"/app_users/login")));
    	if (
        	   !(strpos(' '.$this->here,"/AppUsers/login") > 0 || strpos(' '.$this->here,"/app_users/login") > 0)
        	&& !$this->Session->check('AppUser')
        	){
            // Force the user to login
            $this->redirect('/AppUsers/login');
            exit();
            return false;
        }else{
        	return true;
        }
    }
    
    function checkRole(){
    	
    	
    	$rows = $this->Session->read('AllowedURLs');
    	$allowed = false;
    	
    	if($this->name == 'AppUsers' && 
    		($this->action == 'not_authorized' ||
    		 $this->action == 'login' ||
    		 $this->action == 'logout')){
    		$allowed = true;
    	}
    	
    	//$this->log("checkRole() - name:".$this->name." action:".$this->action,LOG_DEBUG);
    	
        for($i=0; $i<count($rows) && !$allowed; $i++){
        	if($this->name == $rows[$i]['controller'] && 
        		( $this->action == $rows[$i]['action'] || $rows[$i]['action']=='*' )
        		){
        		$allowed = true;
        	}
        	
        }
        
        if(!$allowed){
        	$this->flash('You are not authorized to access this page and/or execute this command. If the page does not redirect in 5 seconds, please click here.','/',5);
        	//$this->redirect('/AppUsers/not_authorized');
        	exit();
        }
        
    }
    
    
    function beforeFilter(){
    	$this->checkSession();
    	$this->checkRole();
    	return true;
    }
    

    
}
?>