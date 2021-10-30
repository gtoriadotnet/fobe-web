<?php

/*
	Alphaland 2021
	Easy utility for pages
*/

class page_handler {
    public $siteName = "Alphaland";
	public $sheader;
	public $pagetitle;
	public $navbar;
	public $body;
	public $bodyStructure;
	public $footer;
	public $studio;
	public $addheader;
	
	public $pageStructure = '<!DOCTYPE html>
<html>
	<head>
		<noscript>
			<meta http-equiv="Refresh" content="0; URL=noJS" />
		</noscript>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>%s</title>
		%s
	</head>
	<body>
		%s
		%s
		%s
	</body>
</html>';

	function pageTitle($string) {
		$this->pagetitle .= (string)$string." | ".$this->siteName;
	}
	function changebody($string) {
		$this->bodyStructure .= (string)$string;
	}
	function addHeader($string) {
		if($this->addheader === null) {
			$this->addheader = "
		";
		}
		$this->addheader .= (string)$string;
	}
	function output() {
		if($this->sheader === null) {
			$this->sheader = getCSS($this->studio);
		}
		if($this->pagetitle === null) {
			$this->pagetitle = $GLOBALS['siteName'];
		}
		if($this->navbar === null) {
			$this->navbar = getNav();
		}
		if($this->footer === null) {
			$this->footer = getFooter();
		}
		echo sprintf(
			$this->pageStructure, 
			$this->pagetitle, 
			$this->sheader.$this->addheader, 
			$this->navbar, 
			$this->body,
			$this->footer
		);
	}
}