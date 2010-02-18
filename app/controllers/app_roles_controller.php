<?php
class AppRolesController extends AppController {

	var $name = 'AppRoles';
	var $helpers = array('Html', 'Form' );
	var $pageTitle = 'Maintain Roles';

	function index() {
		$this->AppRole->recursive = 0;
		$this->set('appRoles', $this->AppRole->findAll());
	}

	function view($id = null) {
		if (!$id) {
			$this->Session->setFlash('Invalid id for App Role.');
			$this->redirect('/app_roles/index');
		}
		$this->set('appRole', $this->AppRole->read(null, $id));
	}

	function add() {
		if (empty($this->data)) {
			$this->render();
		} else {
			$this->cleanUpFields();
			if ($this->AppRole->save($this->data)) {
				$this->Session->setFlash('The App Role has been saved');
				$this->redirect('/app_roles/index');
			} else {
				$this->Session->setFlash('Please correct errors below.');
			}
		}
	}

	function edit($id = null) {
		if (empty($this->data)) {
			if (!$id) {
				$this->Session->setFlash('Invalid id for App Role');
				$this->redirect('/app_roles/index');
			}
			$this->data = $this->AppRole->read(null, $id);
		} else {
			$this->cleanUpFields();
			if ($this->RequestHandler->isPost() && $this->AppRole->save($this->data)) {
				$this->Session->setFlash('The AppRole has been saved');
				$this->redirect('/app_roles/index');
			} else {
				$this->Session->setFlash('Please correct errors below.');
			}
		}
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash('Invalid id for App Role');
			$this->redirect('/app_roles/index');
		}
		if ($this->RequestHandler->isPost() && $this->AppRole->del($id)) {
			$this->Session->setFlash('The App Role deleted: id '.$id.'');
			$this->redirect('/app_roles/index');
		}else{
			$this->Session->setFlash('The role could not be deleted.');
			$this->redirect('/app_roles/index');
		}
	}


	

}
?>