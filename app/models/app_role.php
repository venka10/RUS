<?php
class AppRole extends AppModel {

	var $name = 'AppRole';

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $hasAndBelongsToMany = array(
			'SiteMap' =>
				array('className' => 'SiteMap',
						'joinTable' => 'app_roles_site_maps',
						'foreignKey' => 'app_role_id',
						'associationForeignKey' => 'site_map_id',
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

}
?>