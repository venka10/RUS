<?php
class Project extends AppModel {

	var $name = 'Project';
	var $validate = array(
		'id' => VALID_NOT_EMPTY,
		'states_id' => VALID_NOT_EMPTY,
		'tandberg_regions_id' => VALID_NOT_EMPTY,
		'name' => VALID_NOT_EMPTY,
		'leveraging_points' => VALID_NUMBER,
		'ez_ec_points' => VALID_NUMBER
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
			'State' =>
				array('className' => 'State',
						'foreignKey' => 'states_id',
						'conditions' => '',
						'fields' => '',
						'order' => '',
						'counterCache' => ''
				),

			'TandbergRegion' =>
				array('className' => 'TandbergRegion',
						'foreignKey' => 'tandberg_regions_id',
						'conditions' => '',
						'fields' => '',
						'order' => '',
						'counterCache' => ''
				),

	);

	var $hasMany = array(
			'Site' =>
				array('className' => 'Site',
						'foreignKey' => 'projects_id',
						'conditions' => '',
						'fields' => '',
//						'order' => 'site_types.order_no, order_no',
						'limit' => '',
						'offset' => '',
						'dependent' => '',
						'exclusive' => '',
						'finderQuery' => 'SELECT Site.id, Site.projects_id, Site.town_or_place_name, Site.site_types_id, Site.site_name, Site.site_address, Site.county, Site.school_district, Site.congressional_district, Site.rwf_census_population, Site.nslpf_total_students, Site.nslpf_perc_eligible, Site.nslpf_source_data, Site.nslpf_source_data2, Site.nslp_url, Site.lon_lat, Site.abbreviation, Site.order_no FROM sites AS Site, site_types as SiteType WHERE Site.site_types_id=SiteType.id and Site.projects_id IN ({$__cakeID__$}) ORDER BY SiteType.order_no ASC, order_no ASC',
						'counterQuery' => ''
				),

	);

	var $hasAndBelongsToMany = array(
			'AppUser' =>
				array('className' => 'AppUser',
						'joinTable' => 'app_users_projects',
						'foreignKey' => 'project_id',
						'associationForeignKey' => 'app_user_id',
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
	
	function beforeSave(){
		if(isset($this->data['Project']['leveraging_points']) && $this->data['Project']['leveraging_points'] == ''){
			$this->data['Project']['leveraging_points'] = 0;	
		}
		if(isset($this->data['Project']['ez_ec_points']) && $this->data['Project']['ez_ec_points'] == ''){
			$this->data['Project']['ez_ec_points'] = 0;	
		}
		if(isset($this->data['Project']['score']) && $this->data['Project']['score'] == ''){
			$this->data['Project']['score'] = 0;	
		}
		//pr($this->data);
		return true;
	}
}
?>