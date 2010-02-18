<?php
class AppUsersController extends AppController {

	var $name = 'AppUsers';
	var $helpers = array('Html', 'Form' );
	var $pageTitle = 'Maintain Users';
	
	
	
	
	function login()
    {
    	
    	$this->pageTitle = "RUS Toolkit";
        //Don't show the error message if no data has been submitted.
        $this->set('error', false); 
		
        
		
        // If a user has submitted form data:
        if (!empty($this->data))
        {
            // First, let's see if there are any users in the database
            // with the username supplied by the user using the form:
			$this->AppUser->recursive = 1;
            $someone = $this->AppUser->findByUser_name($this->data['AppUser']['user_name']);
			//pr($someone);
            			
            
        	
            // At this point, $someone is full of user data, or its empty.
            // Let's compare the form-submitted password with the one in 
            // the database.

            if(!empty($someone['AppUser']['pwd1']) && $someone['AppUser']['pwd1'] == sha1($this->data['AppUser']['pwd1']))
            {
                // This means they were the same. We can now build some basic
                // session information to remember this user as 'logged-in'.

                $this->Session->write('AppUser', $someone['AppUser']['id']);
                $this->Session->write('AppRole_name', $someone['AppRole']['name']);
                
                //based on the user's role, they will only have access to certain webpages, so we 
                //load up the allowed urls into a string and stash in the session
                $role=$this->AppUser->AppRole->find("id=".$someone['AppRole']['id'],"*","",1);
                
                $this->Session->write('AllowedURLs', $role['SiteMap']);
                //pr($role['SiteMap']);
                
                // Now that we have them stored in a session, forward them on
                // to a landing page for the application. 

                $this->redirect('/Projects');
            }
            // Else, they supplied incorrect data:
            else
            {
                // Remember the $error var in the view? Let's set that to true:
                $this->set('error', true);
            }
        }
        
                
        
    }

    function logout()
    {
        // Redirect users to this action if they click on a Logout button.
        // All we need to do here is trash the session information:

        $this->Session->delete('AppUser');
        $this->Session->delete('AppRole_name');
		$this->Session->delete('AllowedURLs');
        // And we should probably forward them somewhere, too...
     
        $this->redirect('/AppUsers/login');
    }
	
	function index() {
		//pr("jjj");
		if($this->Session->read('AppRole_name') != 'Regional Mgr'){
			$this->AppUser->recursive = 0;
			$this->set('appUsers', $this->AppUser->findAll());
		}else{
			$this->AppUser->recursive = 1;
			$this->set('appUsers', $this->AppUser->findAll("AppRole.name='Writer'"));
		}
		
	}

	function view($id = null) {
		if (!$id || !$this->isAllowedAccess($id)) {
			$this->Session->setFlash('Invalid id for User.');
			$this->redirect('/app_users/index');
		}
		$this->set('appUser', $this->AppUser->read(null, $id));
	}

	function add() {
		$this->set('pwd_error','');
		if (empty($this->data)) {
			//$this->set('projects', $this->AppUser->Project->generateList());
			$this->set('selectedProjects', null);
			$this->set('tandbergRegions', $this->AppUser->TandbergRegion->generateList());
			if($this->Session->read('AppRole_name') != 'SuperAdmin'){
				$this->set('appRoles', $this->AppUser->AppRole->generateList("AppRole.name not in ('SuperAdmin','Regional Mgr')",
					'id ASC',null,'{n}.AppRole.id', '{n}.AppRole.name'));
			}else{
				$this->set('appRoles', $this->AppUser->AppRole->generateList());
			}
			$this->render();
		} else {
			$this->cleanUpFields();
			if ($this->AppUser->save($this->data)) {
				$this->Session->setFlash('The User has been saved');
				$this->redirect('/app_users/index');
			} else {
				//pr($this);
				$db =& ConnectionManager::getDataSource($this->AppUser->useDbConfig);  
            	$this->Session->setFlash('The following System Error: <i>' . $db->error . '</i> occurred. <br>Please verify that this email address has not already been registered in the system or correct any errors above.');
            	
				//$this->set('projects', $this->AppUser->Project->generateList());
				//if (empty($this->data['Project']['Project'])) { $this->data['Project']['Project'] = null; }
				//$this->set('selectedProjects', $this->data['Project']['Project']);
				$this->set('tandbergRegions', $this->AppUser->TandbergRegion->generateList());
				if($this->Session->read('AppRole_name') != 'SuperAdmin'){
					$this->set('appRoles', $this->AppUser->AppRole->generateList("AppRole.name not in ('SuperAdmin','Regional Mgr')",
						'id ASC',null,'{n}.AppRole.id', '{n}.AppRole.name'));
				}else{
					$this->set('appRoles', $this->AppUser->AppRole->generateList());
				}
			}
		}
	}

	function edit($id = null) {
		if (!$id || !$this->isAllowedAccess($id)) {
				$this->Session->setFlash('Invalid id for User');
				$this->redirect('/app_users/index');
				exit();
		}
		
		if (empty($this->data)) {
			
			$this->data = $this->AppUser->read(null, $id);
			//pr($this->data);
			//$this->set('projects', $this->AppUser->Project->generateList());
			if (empty($this->data['Project'])) { 
				$this->data['Project'] = null; 
			}
			$this->set('selectedProjects', $this->_selectedArray($this->data['Project']));
			$this->set('tandbergRegions', $this->AppUser->TandbergRegion->generateList());
			$this->set('appRoles', $this->AppUser->AppRole->generateList());
		} else {
			//print('test:' . isset($this->data['AppUser']['pwd1']));
			$this->cleanUpFields();
			
			//if the user has a role not equal to Regional Mgr, then, we have to assign them the default Tandberg Region
			if(isset($this->data['AppUser']['app_roles_id'])){
				$appRole = $this->AppUser->AppRole->read(null,$this->data['AppUser']['app_roles_id']);
			}
			if(!isset($this->data['AppUser']['app_roles_id']) || $appRole['AppRole']['name'] != 'Regional Mgr'){
				$this->data['AppUser']['tandberg_regions_id'] = 1;
			}
			
			if ($this->RequestHandler->isPost() && $this->AppUser->save($this->data)
//					(isset($this->data['AppUser']['pwd1']) && $this->AppUser->save($this->data ,true,array('id','pwd1','tandberg_regions_id','user_name','app_roles_id','last_name','first_name') ) ||
//					 !isset($this->data['AppUser']['pwd1']) && $this->AppUser->save($this->data))
				) 
				{
				$this->Session->setFlash('The user has been saved');
				$this->redirect('/app_users/index');
				exit();
			} else {
				$this->Session->setFlash('Please correct errors below.');
				//$this->set('projects', $this->AppUser->Project->generateList());
				if (empty($this->data['Project']['Project'])) { $this->data['Project']['Project'] = null; }
				$this->set('selectedProjects', $this->data['Project']['Project']);
				$this->set('tandbergRegions', $this->AppUser->TandbergRegion->generateList());
				$this->set('appRoles', $this->AppUser->AppRole->generateList());
			}
		}
	}

	function delete($id = null) {
		
		if (!$id || !$this->isAllowedAccess($id)) {
			$this->Session->setFlash('Invalid id for User');
			$this->redirect('/AppUsers/index');
		}
		
		if ($this->RequestHandler->isPost() && $id != 1 && $this->AppUser->del($id)) {
			$this->Session->setFlash('This User deleted: id '.$id.'');
			$this->redirect('/AppUsers/index');
		}else{
			$this->redirect('/AppUsers/index');
		}
	}

	function not_authorized(){
		
	}
	
	function isAllowedAccess($id){
		//this function checks to see if the logged in user has the rights to access this ID; 
		if($this->Session->read('AppRole_name') != 'SuperAdmin'){
			$this->AppUser->recursive=1;
			$tmp = $this->AppUser->findById($id);
			
			if(empty($tmp) || $tmp['AppRole']['name'] == 'SuperAdmin' || $tmp['AppRole']['name'] == 'Regional Mgr'){
				return false;
			}else{
				return true;
			}
		}else{
			return true;
		}
	}
	
	
}
?>