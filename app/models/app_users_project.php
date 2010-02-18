<?php
class AppUsersProject extends AppModel {

	var $name = 'AppUsersProject';
	var $primaryKey = 'app_user_id,project_id';

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
			'AppUser' =>
				array('className' => 'AppUser',
						'foreignKey' => 'app_user_id',
						'conditions' => '',
						'fields' => '',
						'order' => '',
						'counterCache' => ''
				),

			'Project' =>
				array('className' => 'Project',
						'foreignKey' => 'project_id',
						'conditions' => '',
						'fields' => '',
						'order' => '',
						'counterCache' => ''
				),

	);

}
?>