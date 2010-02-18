<?php
class PagesController extends AppController {

	var $name = 'Pages';
	var $helpers = array('Html', 'Form' );
	var $uses = array();
	
	function display(){
		$this->redirect("/Projects");
	}
}
?>