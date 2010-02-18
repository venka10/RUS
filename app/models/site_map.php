<?php
class SiteMap extends AppModel {

	var $name = 'SiteMap';

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $hasAndBelongsToMany = array(
			'AppRole' =>
				array('className' => 'AppRole',
						'joinTable' => 'app_roles_site_maps',
						'foreignKey' => 'site_map_id',
						'associationForeignKey' => 'app_role_id',
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