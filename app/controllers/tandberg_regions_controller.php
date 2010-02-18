<?php
class TandbergRegionsController extends AppController {

	var $name = 'TandbergRegions';
	var $helpers = array('Html', 'Form' );


	
	function index() {
		$this->TandbergRegion->recursive = 0;
		$this->set('tandbergRegions', $this->TandbergRegion->findAll());
	}

	function view($id = null) {
		if (!$id) {
			$this->Session->setFlash('Invalid id for Tandberg Region.');
			$this->redirect('/tandberg_regions/index');
		}
		$this->set('tandbergRegion', $this->TandbergRegion->read(null, $id));
	}

	function add() {
		if (empty($this->data)) {
			$this->render();
		} else {
			$this->cleanUpFields();
			if ($this->TandbergRegion->save($this->data)) {
				$this->Session->setFlash('The Tandberg Region has been saved');
				$this->redirect('/tandberg_regions/index');
			} else {
				$this->Session->setFlash('Please correct errors below.');
			}
		}
	}

	function edit($id = null) {
		if (empty($this->data)) {
			if (!$id) {
				$this->Session->setFlash('Invalid id for Tandberg Region');
				$this->redirect('/tandberg_regions/index');
			}
			$this->data = $this->TandbergRegion->read(null, $id);
		} else {
			$this->cleanUpFields();
			if ($this->RequestHandler->isPost() && $this->TandbergRegion->save($this->data)) {
				$this->Session->setFlash('The TandbergRegion has been saved');
				$this->redirect('/tandberg_regions/index');
			} else {
				$this->Session->setFlash('Please correct errors below.');
			}
		}
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash('Invalid id for Tandberg Region');
			$this->redirect('/tandberg_regions/index');
			exit();
		}
		if ($this->RequestHandler->isPost() && $this->TandbergRegion->del($id)) {
			$this->Session->setFlash('The Tandberg Region deleted: id '.$id.'');
			$this->redirect('/tandberg_regions/index');
			exit();
		}else{
			$this->redirect('/tandberg_regions/index');
			exit();
		}
	}


	

}
?>