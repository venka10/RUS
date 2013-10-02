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
		//06012013 - VENKA
              //if( isset($this->data['Site']['census_designations_id']) && ($this->data['Site']['census_designations_id'] > 1 && (!isset($this->data['Site']['rwf_census_population']) || !preg_match(VALID_NUMBER, $this->data['Site']['rwf_census_population'])) )){
		//	$this->invalidate('rwf_census_population');
		//	$valid_data = $valid_data && false;
		//}
		
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
                     //06012013 - VENKA
			//'CensusDesignation' =>
			//	array('className' => 'CensusDesignation',
			//			'foreignKey' => 'census_designations_id',
			//			'conditions' => '',
			//			'fields' => '',
			//			'order' => '',
			//			'counterCache' => ''
			//	),

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
	
//06012013 - VENKA	
         return 0;		
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
	
//function save($data = null, $validate = true, $fieldList = array()) {
//		$db =& ConnectionManager::getDataSource($this->useDbConfig);
//		$_whitelist = $this->whitelist;
//
//		if (!empty($fieldList)) {
//			$this->whitelist = $fieldList;
//		} elseif ($fieldList === null) {
//			$this->whitelist = array();
//		}
//
//		if ($data) {
//			if (countdim($data) == 1) {
//				$this->set(array($this->name => $data));
//			} else {
//				$this->set($data);
//			}
//		}
//
//		if ($validate && !$this->validates()) {
//			$this->whitelist = $_whitelist;
//			return false;
//		}
//
//		if (!$this->beforeSave()) {
//			$this->whitelist = $_whitelist;
//			return false;
//		}
//		$fields = $values = array();
//
//		if (count($this->data) > 1) {
//			$weHaveMulti = true;
//			$joined = false;
//		} else {
//			$weHaveMulti = false;
//		}
//		$habtm = count($this->hasAndBelongsToMany);
//
//		foreach ($this->data as $n => $v) {
//			if (isset($weHaveMulti) && isset($v[$n]) && $habtm > 0) {
//				$joined[] = $v;
//			} else {
//				if ($n === $this->name) {
//					foreach (array('created', 'updated', 'modified') as $field) {
//						if (array_key_exists($field, $v) && (empty($v[$field]) || $v[$field] === null)) {
//							unset($v[$field]);
//						}
//					}
//
//					foreach ($v as $x => $y) {
//						if ($this->hasField($x)) {
//							$fields[] = $x;
//							$values[] = $y;
//						}
//					}
//				}
//			}
//		}
//		$exists = $this->exists();
//
//		if (!$exists && $this->hasField('created') && !in_array('created', $fields)) {
//			$fields[] = 'created';
//			$values[] = date('Y-m-d H:i:s');
//		}
//
//		if ($this->hasField('modified') && !in_array('modified', $fields)) {
//			$fields[] = 'modified';
//			$values[] = date('Y-m-d H:i:s');
//		}
//
//		if ($this->hasField('updated') && !in_array('updated', $fields)) {
//			$fields[] = 'updated';
//			$values[] = date('Y-m-d H:i:s');
//		}
//
//		if (!$exists) {
//			$this->id = false;
//		}
//		$this->whitelist = $_whitelist;
//$this->log('line 274 ' . $this->id);
//$this->log('save() $data:');
//$this->log($data);
//$this->log('save() $this->data:');
//$this->log($this->data);
//
//		if (count($fields)) {
//			if (!empty($this->id)) {
//				if ($db->update($this, $fields, $values)) {
//					if (!empty($joined)) {
//						$this->__saveMulti($joined, $this->id);
//					}
//
//					$this->afterSave();
//					$this->data = false;
//					$this->_clearCache();
//					return true;
//				} else {
//					return false;
//				}
//			} else {
//				if ($db->create($this, $fields, $values)) {
//					if (!empty($joined)) {
//						$this->__saveMulti($joined, $this->id);
//					}
//
//					$this->afterSave();
//					$this->data = false;
//					$this->_clearCache();
//					$this->validationErrors = array();
//					return true;
//				} else {
//					return false;
//				}
//			}
//		} else {
//			return false;
//		}
//	}
	
}
?>
