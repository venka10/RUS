<?php
class ProjectsController extends AppController {

	var $name = 'Projects';
	var $uses = array('Project','Site', 'SiteType', 'CensusDesignation','AppUser','AppUsersProject');
	var $components = array('Excel','Pagination');
	var $helpers = array('Pagination');
	
	function uploadSites($id = null){
		
		$colnames = array(
			"SITE NAME"=>"site_name",
			"RURALITY NAME (CENSUS UNIT)"=>"abbreviation",
			"ADDRESS/LOCATION"=>"site_address",
			"LOCATION 2"=>"lon_lat",
			"DESIGNATION"=>"site_types_id",
			"COUNTY"=>"county",
			"SCHOOL DISTRICT"=>"school_district",
			"CONGRESSIONAL DISTRICT"=>"congressional_district",
			"CENSUS DESIGNATION"=>"census_designations_id",
			"CENSUS POPULATION"=>"rwf_census_population",
			"TOTAL STUDENTS"=>"nslpf_total_students",
			"% NSLP ELIGIBLE"=>"nslpf_perc_eligible"
		);
		$tmpfilename = $this->params['form']['datafileXLS']['tmp_name'];
		$this->Excel->file = $tmpfilename;
		$this->Excel->read();
		
		$sitesdata = $this->Excel->SER->sheets['0']['cells'];
		
		/*
		 * Info & Assumptions:
		 * 1) The $sitesdata array first index begins with 1, not 0.
		 * 2) The first row in the Excel file should be the column headers, so, the $sitesdata[1] row are the column headers
		 */
		$no_saved_rows = 0;
		$output = "<ul>";
		$errors = false;
		$loadDataToDB=true;
		
		$this->Site->execute('SET autocommit=0');
		$this->Site->execute('START TRANSACTION');
		$this->Site->execute("DELETE FROM sites where projects_id=" . $id);
		
		for($i=2; $i<=count($sitesdata); $i++){
			$site = array('Site'=>array());
			$site['Site']['projects_id']=$id;
			for($j=1; $j<=count($sitesdata[1]); $j++){
				if(strcasecmp($sitesdata[1][$j],'projects_id') != 0 && strcasecmp($sitesdata[1][$j],'id') != 0 && isset($sitesdata[$i][$j])){
					if(strcasecmp($sitesdata[1][$j],'Census Designation') == 0){
						$census_designation_cd = $sitesdata[$i][$j];
						$census_designation = $this->CensusDesignation->find("upper(cd)='" . $census_designation_cd ."'");
						if(isset($census_designation['CensusDesignation']['id'])){
							$site['Site']['census_designations_id']=$census_designation['CensusDesignation']['id'];
						}else{
							$errors = true;
							$output = $output . "<li>Error: row " . $i . " has an invalid Census Designation entry of: '" . $census_designation_cd . "'</li>";
						}
					}else if(strcasecmp($sitesdata[1][$j],'Designation') == 0){
						$site_type_cd = $sitesdata[$i][$j];
						$site_type = $this->SiteType->find("upper(cd)='" . $site_type_cd ."'");
						if(isset($site_type['SiteType']['id'])){
							$site['Site']['site_types_id']=$site_type['SiteType']['id'];
						}else{
							$errors = true;
							$output = $output . "<li>Error: row " . $i . " has an invalid Site Type entry of: '" . $site_type_cd . "'</li>";
						}
					}else if(strcasecmp($sitesdata[1][$j],'short_name_census_unit') == 0){	
						$site['Site']['abbreviation'] = $sitesdata[$i][$j];
					}else{	
						$spreadsheet_col_header = strtoupper($sitesdata[1][$j]);
						if(isset($colnames[$spreadsheet_col_header])){
							$site['Site'][$colnames[$spreadsheet_col_header]] = $sitesdata[$i][$j];
						}
					}
				}	
			}
			if(!isset($site['Site']['order_no'])){
				$site['Site']['order_no'] = $i;
			}
			
			
			if(!$errors  && $this->Site->save($site) ){
				$no_saved_rows++;
				$this->Site->id=false;
			}else{
				$loadDataToDB=false;  //any errors in the spreadsheet means we can't load the spreadsheet data
				$errors = false;
//				$output = $output . "<li>Error: row " . $i . " could not be saved. Please check the import data.</li>";
				$output = $output . "<li>Error: row " . $i . " could not be saved. Please correct the import data and re-upload.</li>";
			}
		}
		
		if($loadDataToDB){
			$this->Site->execute("commit");
		}else{
			$this->Site->execute("rollback");
		}
		
		
		//$output = $output . "<li>" . $no_saved_rows . " row(s) saved successfully.</li>";
		
		$output = $output . "</ul>";
		$this->Session->write('output',$output);
		$this->redirect('/projects/view/' . $id . '?cache=' . rand(1,9999999));
		
	}
	
	function upload_instructions(){
		
	}
	
	function index() {
		$this->Project->recursive = 1;
		$conditions = "Project.archived=0 ";
		if($this->Session->read('AppRole_name')=='Writer'){
			$conditions = $conditions . "and Project.id in (select aup.project_id from app_users_projects aup where aup.app_user_id = " . $this->Session->read('AppUser') . ")";
		}
		list($order,$limit,$page) = $this->Pagination->init($conditions); // Added 
		//pr($order);
		if(stristr($order,"AppUser")){
			$this->AppUsersProject->recursive = 2;
			$this->set('data', $this->AppUsersProject->findAll($conditions,null /*array("Project.name","TandbergRegion.name","State.name","AppUser.last_name")*/,$order,$limit,$page)); /*"TandbergRegion.name, State.name, Project.name",*/
			//pr($this->viewVars['data']);
		}else{
			$this->set('data', $this->Project->findAll($conditions,null /*array("Project.name","TandbergRegion.name","State.name","AppUser.last_name")*/,$order,$limit,$page)); /*"TandbergRegion.name, State.name, Project.name",*/ 
		}
		//pr($conditions);
		//
	}

	function order(){
		$this->Project->recursive = 1;
		$id=$this->params['form']['projectId'];
		$ids_array = $this->params['form']['sitesListTable'];
		
		$this->data = $this->Project->read(null,$id);
		//$this->Site->begin();
		//pr($order_array);
		for($i=0; $i<count($ids_array); $i++){
			$site = $this->Site->read(null,$ids_array[$i]);
			$site['Site']['order_no']=$i;
			//$this->Site->data = $site;
			if(!$this->Site->save($site,false,array('order_no'))){
				//$this->Site->rollback();
				pr('Error saving');
			}
			else{
				pr('Saved: ' . $site['Site']['id'] . ' ' .  $site['Site']['order_no']);
			}
		}
		//$this->Site->commit();

	}
	
	function view($id = null) {
		if (!$id) {
			$this->Session->setFlash('Invalid id for Project.');
			$this->redirect('/projects/index');
			exit();
		}
		$output = $this->Session->read('output');
		$this->set('output',$output);
		$this->Session->delete('output');
		
		$conditions = "Project.id=".$id;
		if($this->Session->read('AppRole_name')=='Writer'){
			$conditions = $conditions." and Project.id in (select aup.project_id from app_users_projects aup where aup.app_user_id = " . $this->Session->read('AppUser') . ")";
		}
		$this->Project->recursive=2;
		$this->set('project', $this->Project->find($conditions,'*',null,2));
		
		$this->set('rurality_points',$this->calculateProjectRuralityPoints($this->viewVars['project']['Site']));
		$this->set('nslp_avg',$this->calculateNSLPAverageIgnoreHubSites($this->viewVars['project']['Site']));
		$this->set('nslp_points_total',$this->calculateNSLPPoints($this->viewVars['project']['Site']));
		
		
		//lets calculate how many continuation sheets will be needed
		$site_continuation_sheets = 0;
		$rurality_nslp_continuation_sheets = 0;
		$tmp = $this->insertBlankRowAfterHubSite($this->viewVars['project']);
		$no_of_sites = count($tmp['Site']);
		//$no_of_sites += 30;  //uncomment for testing
		$no_of_sites -= 5;
		if($no_of_sites > 0){
			$site_continuation_sheets = ceil($no_of_sites/10);
		}
		$this->set('no_site_continuation_sheets',$site_continuation_sheets);
		
		$rurality_continuation_sheets = 0;
		if($no_of_sites > 0){
			$rurality_continuation_sheets = ceil($no_of_sites/15);
		}
		$this->set('no_rurality_continuation_sheets',$rurality_continuation_sheets);

		$nslp_continuation_sheets = 0;
		if($no_of_sites > 0){
			$nslp_continuation_sheets = ceil($no_of_sites/16);
		}
		$this->set('no_nslp_continuation_sheets',$nslp_continuation_sheets);
	}

	function add() {
		if (empty($this->data)) {
			$this->set('appUsers', $this->Project->AppUser->generateList("AppUser.id not in (select a.id from app_users a, app_roles r where a.app_roles_id=r.id and r.name='SuperAdmin')", 'id ASC', null, '{n}.AppUser.id', '{n}.AppUser.last_name'));
			$this->set('selectedAppUsers', null);
			$this->set('states', $this->Project->State->generateList());
			$this->set('tandbergRegions', $this->Project->TandbergRegion->generateList());
			$this->render();
		} else {
			$this->cleanUpFields();
			$this->Project->begin();
			$this->data['AppUser']['AppUser'] = array($this->data['AppUser']['AppUser']);
			if ($this->RequestHandler->isPost() && $this->Project->save($this->data)) {
				$this->Project->commit();
				$this->Session->setFlash('The Project has been saved');
				//$this->redirect('/projects/index');
				$this->redirect('/Projects/view/'.$this->Project->getLastInsertID());
			} else {
				$this->Project->rollback();
				$this->Session->setFlash('Please correct errors below.');
				$this->set('appUsers', $this->Project->AppUser->generateList("AppUser.id not in (select a.id from app_users a, app_roles r where a.app_roles_id=r.id and r.name='SuperAdmin')", 'id ASC', null, '{n}.AppUser.id', '{n}.AppUser.last_name'));
				if (empty($this->data['AppUser']['AppUser'])) { $this->data['AppUser']['AppUser'] = null; }
				$this->set('selectedAppUsers', $this->data['AppUser']['AppUser']);
				$this->set('states', $this->Project->State->generateList());
				$this->set('tandbergRegions', $this->Project->TandbergRegion->generateList());
			}
		}
	}

	function archived() {
		$this->Project->recursive = 1;
		$conditions = "Project.archived=1 ";
		if($this->Session->read('AppRole_name')=='Writer'){
			$conditions = "and Project.id in (select aup.project_id from app_users_projects aup where aup.app_user_id = " . $this->Session->read('AppUser') . ")";
		}		
		$this->set('projects', $this->Project->findAll($conditions,array(),"TandbergRegion.name, State.name, Project.name",0,0,1)); 
	}
	
	function hide($id = null){
		if($this->Session->read('AppRole_name')=='SuperAdmin'){
			$this->data = $this->Project->findById($id);
			$this->data['Project']['archived']=1;
			if(!$this->Project->save($this->data)){
				$this->Session->setFlash('System error - Project could not be hidden.');
			}
		}
//		else{
//			$this->Session->setFlash('You are not authorized to Hide Projects');
//		}
		$this->redirect('/projects/index');
	}
	
	function reactivate($id = null){
		if($this->Session->read('AppRole_name')=='SuperAdmin'){
			$this->data = $this->Project->findById($id);
			$this->data['Project']['archived']=0;
			if(!$this->Project->save($this->data)){
				$this->Session->setFlash('System error - Project could not be reactivated.');
			}
		}
//		else{
//			$this->Session->setFlash('You are not authorized to Hide Projects');
//		}
		$conditions = "Project.archived=1 ";
		$projects = $this->Project->findAll($conditions);
		if(count($projects) == 0){
			$this->redirect('/projects/index');
		}else{
			$this->redirect('/projects/archived');
		}
	}
	
	function edit($id = null) {
		
		$conditions = "Project.id=".$id;
		if($this->Session->read('AppRole_name')=='Writer'){
			$conditions = $conditions." and Project.id in (select aup.project_id from app_users_projects aup where aup.app_user_id = " . $this->Session->read('AppUser') . ")";
		}
		$test = $this->Project->find($conditions,'*',null,0);
//		pr(is_null($test).' '.count($test));
//		pr($test);
//		pr($conditions);
		if(!is_null($test) && count($test) > 1){
			if (empty($this->data)) {
				if (!$id) {
					$this->Session->setFlash('Invalid id for Project');
					$this->redirect('/projects/index');
				}
				$this->data = $this->Project->findById($id);
				$this->set('appUsers', $this->Project->AppUser->generateList("AppUser.id not in (select a.id from app_users a, app_roles r where a.app_roles_id=r.id and r.name='SuperAdmin')", 'id ASC', null, '{n}.AppUser.id', '{n}.AppUser.last_name'));
				if (empty($this->data['AppUser'])) { $this->data['AppUser'] = null; }
				$this->set('selectedAppUsers', $this->_selectedArray($this->data['AppUser']));
				$this->set('states', $this->Project->State->generateList());
				$this->set('tandbergRegions', $this->Project->TandbergRegion->generateList());
			} else {
				$this->cleanUpFields();
				$this->Project->begin();
				//have to fix the data array since i'm using a single select dropdown
				$this->data['AppUser']['AppUser'] = array($this->data['AppUser']['AppUser']);
				
				if ($this->RequestHandler->isPost() && $this->Project->save($this->data)) {
					$this->Project->commit();
					$this->Session->setFlash('The Project has been saved');
					$this->redirect('/projects/view/'.$this->data['Project']['id']);
				} else {
					$this->Project->rollback();
					$this->Session->setFlash('Please correct errors below.');
					$this->set('appUsers', $this->Project->AppUser->generateList("AppUser.id not in (select a.id from app_users a, app_roles r where a.app_roles_id=r.id and r.name='SuperAdmin')", 'id ASC', null, '{n}.AppUser.id', '{n}.AppUser.last_name'));
					if (empty($this->data['AppUser']['AppUser'])) { $this->data['AppUser']['AppUser'] = null; }
					$this->set('selectedAppUsers', $this->data['AppUser']['AppUser']);
					$this->set('states', $this->Project->State->generateList());
					$this->set('tandbergRegions', $this->Project->TandbergRegion->generateList());
				}
			}
		}else{
			$this->Session->setFlash('You are not authorized to edit Project:'.$id);
			$this->redirect('/projects/index');
		}
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash('Invalid id for Project');
			$this->redirect('/projects/index');
		}
		if (/*$this->RequestHandler->isPost() && */$this->Project->del($id)) {
			$this->Session->setFlash('The Project deleted: id '.$id.'');
			$this->redirect('/projects/index');
		}
	}

	function update(){
		
		
		//pr($this->params['form']);
		if($this->RequestHandler->isAjax() && isset($this->params['form']['id'])){
			$this->data = $this->Project->read(null,$this->params['form']['id']);
			if((isset($this->params['form']['leveraging_points']) && $this->Project->saveField('leveraging_points',$this->params['form']['leveraging_points'],true)) ||
			   (isset($this->params['form']['ez_ec_points']) && $this->Project->saveField('ez_ec_points',$this->params['form']['ez_ec_points'],true))){
				$this->set('output','Saved successfully.');
			}else{
				$this->set('output','Error updating this field.');
			}
			//$this->data['leveraging_points'] = $this->params['form']['leveraging_points'];
		}else{
			$this->redirect('/Projects/index');
		}
		
	}
	
	function sitesWorksheet($id=null){
		$this->set('pagetitle',"Sites Worksheet");
		if (!$id) {
			$this->Session->setFlash('Invalid id for Site Worksheet');
			$this->redirect('/projects/index');
		}else{
			$this->layout = null;
			$this->set('project',$this->insertBlankRowAfterHubSite($this->Project->find("Project.id=".$id,"*",null,2)));	
			
			
			
		}
	}
	
	function sitesContinuation(){
		$this->set('pagetitle',"Sites Continuation");
		if(!(isset($this->params['url']['id']) && isset($this->params['url']['page']))){
			$this->Session->setFlash('Invalid parameters for Site Continuation');
			$this->redirect('/projects/index');
		}else{
			$project_id = $this->params['url']['id'];
			$this->set('page',$this->params['url']['page']);
			
			$this->layout = null;
			$this->set('project',$this->insertBlankRowAfterHubSite($this->Project->find("Project.id=".$project_id,"*",null,2)));	
			
			
			
		}	
	}
	
	function ruralityWorksheet($id=null){
		$this->set('pagetitle',"Rurality Worksheet");
		if (!$id) {
			$this->Session->setFlash('Invalid id for Rurality Worksheet');
			$this->redirect('/projects/index');
		}else{
			$this->layout = null;
			$this->set('project',$this->insertBlankRowAfterHubSite($this->Project->find("Project.id=".$id,"*",null,2)));	
			$this->set('rurality_points',$this->calculateProjectRuralityPoints($this->viewVars['project']['Site']));
		}
	}
	
	function ruralityContinuation($id=null){
		$this->set('pagetitle',"Rurality Continuation");
		if(!(isset($this->params['url']['id']) && isset($this->params['url']['page']))){
			$this->Session->setFlash('Invalid parameters for Rurality Continuation');
			$this->redirect('/projects/index');
		}else{
			$project_id = $this->params['url']['id'];
			$this->set('page',$this->params['url']['page']);
			
			$this->layout = null;
			$this->set('project',$this->insertBlankRowAfterHubSite($this->Project->find("Project.id=".$project_id,"*",null,2)));
			
		}
	}
	
	function nslpWorksheet($id=null){
		$this->set('pagetitle',"NSLP Worksheet");
		if (!$id) {
			$this->Session->setFlash('Invalid id for NSLP Worksheet');
			$this->redirect('/projects/index');
		}else{
			$this->layout = null;
			$this->set('project',$this->insertBlankRowAfterHubSite($this->Project->find("Project.id=".$id,"*",null,2)));	
			$this->set('nslp_average',$this->calculateNSLPAverageIgnoreHubSites($this->viewVars['project']['Site']));
			$this->set('nslp_est_score',$this->calculateNSLPPoints($this->viewVars['project']['Site']));
		}
	}
	
	function nslpContinuation($id=null){
		$this->set('pagetitle',"NSLP Continuation");
		if(!(isset($this->params['url']['id']) && isset($this->params['url']['page']))){
			$this->Session->setFlash('Invalid parameters for NSLP Continuation');
			$this->redirect('/projects/index');
		}else{
			$project_id = $this->params['url']['id'];
			$this->set('page',$this->params['url']['page']);
			
			$this->layout = null;
			$this->set('project',$this->insertBlankRowAfterHubSite($this->Project->find("Project.id=".$project_id,"*",null,2)));			
		}
	}
	
	function printingInstructions(){
		
	}
	
	function insertBlankRowAfterHubSite($data){
		for($i=0; $i<count($data['Site']); $i++){
			if($i > 0){
				if($data['Site'][$i]['SiteType']['cd'] != 'Hub' && $data['Site'][$i-1]['SiteType']['cd'] == 'Hub'){
					$data['SiteInsertRowIndex'] = $i;
				}
			}
		}
		$return = array();
		if(!isset($data['SiteInsertRowIndex'])){
			$data['SiteInsertRowIndex'] = count($data['Site'])+1;
			$no_rows = count($data['Site']);
		}else{
			$no_rows = count($data['Site'])+1;
		}
		//pr($data['SiteInsertRowIndex']);
		for($i=0; $i<$no_rows; $i++){
			if($i < $data['SiteInsertRowIndex'] && $i<$no_rows ){
				$return[] = $data['Site'][$i];
			}else if($i == $data['SiteInsertRowIndex']){
				$return[] = array();
			}else{
				if(isset($data['Site'][$i-1])){
					$return[] = $data['Site'][$i-1];
				}
			}
		}
		$data['Site'] = $return;
		return $data;
	}
	
	/*private*/ function calculateProjectRuralityPoints($data = array()){
		$rurality_points_total = 0;
		$sites_for_calculations=0;
		foreach($data as $site){
			//pr($site);
			if(isset($site['SiteType']['cd']) && $site['SiteType']['cd'] != 'Hub'){
				$rurality_points_total += $site['rurality_points'];
				$sites_for_calculations++;
			}
		}
		if($sites_for_calculations > 0){
			return number_format($rurality_points_total / $sites_for_calculations,2);
		}else{
			return 0;
		}
	}
	
	/*private*/ function calculateNSLPPoints($data = array()){
		$nslp_points = 0;
		/*foreach($data as $site){
			if($site['nslpf_perc_eligible']	>= 25.00 && $site['nslpf_perc_eligible'] < 50.00){
				$nslp_points += 15;
			}elseif($site['nslpf_perc_eligible'] >= 50.00 && $site['nslpf_perc_eligible'] < 75.00){
				$nslp_points += 25;
			}elseif($site['nslpf_perc_eligible'] >= 75.00){
				$nslp_points += 35;
			}
		}*/
		$nslp_avg = $this->calculateNSLPAverageIgnoreHubSites($data);
		if($nslp_avg	>= 25.00 && $nslp_avg < 50.00){
			$nslp_points = 15;
		}elseif($nslp_avg >= 50.00 && $nslp_avg < 75.00){
			$nslp_points = 25;
		}elseif($nslp_avg >= 75.00){
			$nslp_points = 35;
		}
		return $nslp_points;
	}
	
	/*private*/ function calculateNSLPAverage($data = array()){
		$nslp_total = 0; 
		foreach($data as $site){
			if(isset($site['nslpf_perc_eligible'])){
				$nslp_total += $site['nslpf_perc_eligible'];
			}
		}
		if(count($data) > 0){
			return round($nslp_total / count($data));			
		}else{
			return 0;
		}

	}
	
	/*private*/ function calculateNSLPAverageIgnoreHubSites($data = array()){
		//pr($data);
		$nslp_total = 0; 
		$no_sites = 0;
		foreach($data as $site){
			if(isset($site['id']) && ( isset($site['SiteType']['cd']) && $site['SiteType']['cd'] != 'Hub' )){
				$no_sites++;
			}
			if(isset($site['nslpf_perc_eligible']) && ( isset($site['SiteType']['cd']) && $site['SiteType']['cd'] != 'Hub' )){
				$nslp_total += $site['nslpf_perc_eligible'];
			}
		}
		
		if($no_sites > 0){
			return round($nslp_total / $no_sites);			
		}else{
			return 0;
		}

	}
	
}
?>
