<?php
class State extends AppModel {

	var $name = 'State';
	var $validate = array(
		'id' => VALID_NOT_EMPTY,
		'cd' => VALID_NOT_EMPTY,
		'name' => VALID_NOT_EMPTY,
	);

}
?>