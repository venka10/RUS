<?php
class Site extends AppModel {

	var $name = 'Site';
	var $recursive=1;
	
	var $validate = array(
		'sites_id' => VALID_NOT_EMPTY,
		//'site_name' => VALID_NOT_EMPTY,
		//'abbreviation' => VALID_NOT_EMPTY,
		//'site_address' => VALID_NOT_EMPTY,
		//'site_types_id' => VALID_NOT_EMPTY,
		//'county' => VALID_NOT_EMPTY,
		//'school_district' => VALID_NOT_EMPTY,
		//'congressional_district' => VALID_NOT_EMPTY,
		'projects_id' => VALID_NOT_EMPTY,
		//'census_designations_id' => VALID_NOT_EMPTY,
		//'rwf_census_population' => VALID_NUMBER,
		'rwf_rurality_points' => VALID_NUMBER,
		//'nslpf_total_students' => VALID_NUMBER,
		//'nslpf_perc_eligible' => VALID_NOT_EMPTY,
		//'nslpf_source_data' => VALID_NOT_EMPTY,
		//'nslpf_source_data2' => VALID_NOT_EMPTY
		//'rwn_population_in_service' => VALID_NUMBER,
		//'rwn_rurality_points' => VALID_NUMBER,
		//'rwn_product' => VALID_NUMBER,
		//'nslpn_total_students' => VALID_NUMBER
	);

	function beforeValidate(){
		//
		$valid_data = true;
		
		return $valid_data;
		
	}
	
	function beforeSave(){
		if(isset($this->data['Site']['rwf_census_population']) && $this->data['Site']['rwf_census_population'] == ''){
			$this->data['Site']['rwf_census_population'] = NULL;
		}
		
		if(isset($this->data['Site']['nslpf_total_students']) && $this->data['Site']['nslpf_total_students'] == ''){
			$this->data['Site']['nslpf_total_students'] = NULL;
		}
		
//		if(isset($this->data['Site']['rwf_census_population']) && $this->data['Site']['rwf_census_population'] == ''){
//			//need to create a list of saveable fields without this one
//			$i = 0;
//			foreach($this->data['Site'] as $n=>$v){
//				if($n != 'rwf_census_population'){
//					$this->whitelist[$i] = $n;
//					$i++;
//				}
//			}
//		}
		
//		if(isset($this->data['Site']['nslpf_total_students']) && $this->data['Site']['nslpf_total_students'] == ''){
//			//need to create a list of saveable fields without this one
//			$i = 0;
//			foreach($this->data['Site'] as $n=>$v){
//				if($n != 'nslpf_total_students'){
//					$this->whitelist[$i] = $n;
//					$i++;
//				}
//			}
//		}
		
//		if((isset($this->data['Site']['rwf_census_population']) && $this->data['Site']['rwf_census_population'] == '') &&
//			(isset($this->data['Site']['nslpf_total_students']) && $this->data['Site']['nslpf_total_students'] == '')){
//				//BOTH fields have blanks so don't save them
//			$i = 0;
//			foreach($this->data['Site'] as $n=>$v){
//				if($n != 'rwf_census_population' && $n != 'nslpf_total_students'){
//					$this->whitelist[$i] = $n;
//					$i++;
//				}
//			}	
//		}else{
//			//one or the other or none have blank fields
//			if(isset($this->data['Site']['rwf_census_population']) && $this->data['Site']['rwf_census_population'] == ''){
//				//need to create a list of saveable fields without this one
//				$i = 0;
//				foreach($this->data['Site'] as $n=>$v){
//					if($n != 'rwf_census_population'){
//						$this->whitelist[$i] = $n;
//						$i++;
//					}
//				}
//			}
//			
//			if(isset($this->data['Site']['nslpf_total_students']) && $this->data['Site']['nslpf_total_students'] == ''){
//				//need to create a list of saveable fields without this one
//				$i = 0;
//				foreach($this->data['Site'] as $n=>$v){
//					if($n != 'nslpf_total_students'){
//						$this->whitelist[$i] = $n;
//						$i++;
//					}
//				}
//				unset($this->data['Site']['nslpf_total_students']);
//			}
//			
//		}
		//pr($this->whitelist);
		$this->data['Site']['rurality_points'] = 
			$this->calculateRuralityPoints($this->data['Site']);
		
		return true;
	}
	
	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
			'Project' =>
				array('className' => 'Project',
						'foreignKey' => 'projects_id',
						'conditions' => '',
						'fields' => '',
						'order' => '',
						'counterCache' => ''
				),
		
			'SiteType' =>
				array('className' => 'SiteType',
						'foreignKey' => 'site_types_id',
						'conditions' => '',
						'fields' => '',
						'order' => '',
						'counterCache' => ''
				),

	);

	function calculateRuralityPoints($site_parm){
		
		if(isset($site_parm['rwf_census_population'])){

                 if ($site_parm['rwf_census_population'] > 0 && $site_parm['rwf_census_population'] <=5000){
                    return 45;
                 }else if($site_parm['rwf_census_population'] > 5000 && $site_parm['rwf_census_population'] <=10000){
                    return 30;
                 }else if($site_parm['rwf_census_population'] > 10000 && $site_parm['rwf_census_population'] <=20000){
                    return 15;
                 }else{
                    return 0;
                 } 
		}else{
			return 0;
		}
		
	}
	
	function afterFind($results){
		//pr($results);
		if(countdim($results[0]['Site']) == 1){
			//a find() was executed
			if(isset($results[0]['Site']['id'])){
				$site = $results[0]['Site'];
				$results[0]['Site']['rurality_points']=$this->calculateRuralityPoints($site);
			}
		}elseif(countdim($results[0]['Site']) == 2){
			//a findall
			for($i=0; $i<count($results[0]['Site']); $i++){
				$results[0]['Site'][$i]['rurality_points']=$this->calculateRuralityPoints($results[0]['Site'][$i]);
			}
		}
		
		//pr($results[0]['Site']);
		return $results;
	}
		
}
?>
