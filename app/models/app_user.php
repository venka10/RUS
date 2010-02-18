<?php
class AppUser extends AppModel {

	var $name = 'AppUser';
	var $validate = array(
		'tandberg_regions_id' => VALID_NOT_EMPTY,
		'user_name' => VALID_NOT_EMPTY,
		'user_name' => VALID_EMAIL,
		'app_roles_id' => VALID_NOT_EMPTY,
		'last_name' => VALID_NOT_EMPTY
	);
//,'password' => VALID_NOT_EMPTY

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
			'TandbergRegion' =>
				array('className' => 'TandbergRegion',
						'foreignKey' => 'tandberg_regions_id',
						'conditions' => '',
						'fields' => '',
						'order' => '',
						'counterCache' => ''
				),

			'AppRole' =>
				array('className' => 'AppRole',
						'foreignKey' => 'app_roles_id',
						'conditions' => '',
						'fields' => '',
						'order' => '',
						'counterCache' => ''
				),

	);

	var $hasAndBelongsToMany = array(
			'Project' =>
				array('className' => 'Project',
						'joinTable' => 'app_users_projects',
						'foreignKey' => 'app_user_id',
						'associationForeignKey' => 'project_id',
						'conditions' => '',
						'fields' => '',
						'order' => '',
						'limit' => '',
						'offset' => '',
						'unique' => '',
						'finderQuery' => '',
						'deleteQuery' => '',
						'insertQuery' => ''
				),

	);

	function beforeValidate(){
		if(strlen($this->data['AppUser']['password']) != 0 && strlen($this->data['AppUser']['password']) < 8 ){
			$this->invalidate('password');
		}
		
		if(strlen($this->data['AppUser']['password2']) != 0 && strlen($this->data['AppUser']['password2']) < 8 ){
			$this->invalidate('password2');
		}
		
		if(
			($this->data['AppUser']['password'] && 
				strcasecmp($this->data['AppUser']['password'],$this->data['AppUser']['password2']) == 0)  ||
			(strlen($this->data['AppUser']['password']) == 0 && strlen($this->data['AppUser']['password2']) == 0)){
			return true;
		}else{
			$this->invalidate('pwd');
			return false;
		}
	}
	
	function beforeSave(){
		
		if($this->data['AppUser']['password'] && strlen($this->data['AppUser']['password']) > 0 ){
			$this->data['AppUser']['pwd1'] = sha1($this->data['AppUser']['password']);
			$id = $this->data['AppUser']['id'];
			unset($this->data['AppUser']['id']);
			$this->data['AppUser']['id'] = $id;
			//$this->data['AppUser']['password'] = null;
			//$this->data['AppUser']['password2'] = null;
		}

		if(!isset($this->data['AppUser']['tandberg_regions_id'])){
			$this->data['AppUser']['tandberg_regions_id'] = 1;
		}
		
		return true;
	}
	
	function afterSave(){
		
		$this->data['AppUser']['password'] = null;
		$this->data['AppUser']['password2'] = null;
		return true;
	}
	
	
}
?>