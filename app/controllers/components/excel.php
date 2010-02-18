<?
class ExcelComponent
{
	var $file = "";
	var $setOutputEncoding = "UTF-8";
	var $data;
	var $controller;
	function startup( &$controller )
	{
		$this->controller = &$controller;
	}
	function read()
	{
		vendor('Excel'.DS.'reader');
		$this->SER = new Spreadsheet_Excel_Reader();
		$this->SER->setOutputEncoding = $this->setOutputEncoding;
		$this->SER->read($this->file);
	}
}

?>