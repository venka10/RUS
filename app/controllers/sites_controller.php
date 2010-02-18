<?php
class SitesController extends AppController {

	var $name = 'Sites';
	var $helpers = array('Html', 'Form' );

//	function index($id=null) {
//		$this->Site->recursive = 0;
//		if (!$id) {
//			$this->set('sites', $this->Site->findAll());
//		}else{
//			$this->set('sites', $this->Site->findAllByProjectId());
//		}
//	}

//	function view($id = null) {
//		if (!$id) {
//			$this->Session->setFlash('Invalid id for Site.');
//			$this->redirect('/sites/index');
//		}
//		$this->set('site', $this->Site->read(null, $id));
//	}

	function add($id = null) {
		//in this case, the incoming ID is the ProjectID which will be this Site's parent
		if (empty($this->data)) {
			//$this->set('projects', $this->Site->Project->generateList());
			$this->data['Site']['projects_id']=$id;
			$this->set('project',$this->Site->Project->read(null,$this->data['Site']['projects_id']));
			$this->set('censusDesignations', $this->Site->CensusDesignation->generateList(null, 'id ASC', null, '{n}.CensusDesignation.id', '{n}.CensusDesignation.cd'));
			$this->set('siteTypes', $this->Site->SiteType->generateList(null,'id ASC',null,'{n}.SiteType.id', '{n}.SiteType.cd'));
			$this->render();
		} else {
			$this->cleanUpFields();
			$order_no = $this->Site->query("select max(order_no) from sites where projects_id=" . $this->data['Site']['projects_id']);
			$this->data['Site']['order_no']=$order_no[0][0]['max(order_no)']+1;
			if ($this->RequestHandler->isPost() && $this->Site->save($this->data)) {
				$this->Session->setFlash('The Site has been saved');
				$this->redirect('/Projects/view/'.$this->data['Site']['projects_id']);
			} else {
				$this->Session->setFlash('Please correct errors above.');
				$this->set('project',$this->Site->Project->read(null,$this->data['Site']['projects_id']));
				//$this->set('projects', $this->Site->Project->generateList());
				$this->set('censusDesignations', $this->Site->CensusDesignation->generateList(null, 'id ASC', null, '{n}.CensusDesignation.id', '{n}.CensusDesignation.cd'));
				$this->set('siteTypes', $this->Site->SiteType->generateList(null,'id ASC',null,'{n}.SiteType.id', '{n}.SiteType.cd'));
			}
		}
	}

	function edit($id = null) {
		if (empty($this->data)) {
			if (!$id) {
				$this->Session->setFlash('Invalid id for Site');
				$this->redirect('/sites/index');
			}
			$this->data = $this->Site->read(null, $id);
			$this->set('project',$this->Site->Project->read(null,$this->data['Site']['projects_id']));
			
			//$this->set('projects', $this->Site->Project->generateList());
			$this->set('censusDesignations', $this->Site->CensusDesignation->generateList(null, 'id ASC', null, '{n}.CensusDesignation.id', '{n}.CensusDesignation.cd'));
			$this->set('siteTypes', $this->Site->SiteType->generateList(null,'id ASC',null,'{n}.SiteType.id', '{n}.SiteType.cd'));
		} else {
			$this->cleanUpFields();
			if ($this->RequestHandler->isPost() && $this->Site->save($this->data)) {
				$this->Session->setFlash('The Site has been saved');
				$this->redirect('/Projects/view/'.$this->data['Site']['projects_id']);
			} else {
				$this->Session->setFlash('Please correct errors above.');
				$this->set('project',$this->Site->Project->read(null,$this->data['Site']['projects_id']));
			
				//$this->set('projects', $this->Site->Project->generateList());
				$this->set('censusDesignations', $this->Site->CensusDesignation->generateList(null, 'id ASC', null, '{n}.CensusDesignation.id', '{n}.CensusDesignation.cd'));
				$this->set('siteTypes', $this->Site->SiteType->generateList(null,'id ASC',null,'{n}.SiteType.id', '{n}.SiteType.cd'));
			}
		}
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash('Invalid id for Site');
			$this->redirect('/sites/index');
			exit();
		}else{
			$this->data=$this->Site->read(null,$id);
		}
		if ( /*$this->RequestHandler->isPost() &&*/ $this->Site->del($id)) {
			$this->Session->setFlash('The Site deleted: id '.$id.'');
			$this->redirect('/Projects/view/'.$this->data['Site']['projects_id']);
			exit();
		}else{
			$this->Session->setFlash('The Site could not be deleted: id '.$id.' (method=GET)');
			$this->redirect('/Projects/view/'.$this->data['Site']['projects_id']);
		}
	}

}
?>