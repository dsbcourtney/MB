<?php

include 'RelayLib.inc.php';
class RelayLib 
{
	protected $_curlResource;
	protected $_url;
	
	public function __construct($ip, $username, $password) 
	{
		$this->_url = "http://" . $ip . "/";
		$curl = curl_init($this->_url);
		$this->_curlResource = $curl;
		
		curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		$response = curl_exec($curl);
		if ($response != true)
			throw new Exception("Constructor curl_execution failed ");
		
		$resultStatus = curl_getinfo($curl);
		if ($resultStatus['http_code'] != 200)
			throw new Exception("Authentication failed .Server responded with: ".$resultStatus['http_code']." code");
	}

	public function status() 
	{	
		$curl = $this->_curlResource;
		
		$resultStatus = curl_getinfo($curl);
		if ($resultStatus['http_code'] != 200) 
			throw new Exception("Status connection failed .Server responded with: ".$resultStatus['http_code']." code");

		curl_setopt($curl, CURLOPT_URL, $this->_url . 'relays.cgi');
		
		$result = curl_exec($curl);
		if ($result != true)
			throw new Exception("Status check curl_execution failed ");
		
		$status = $this->loadUrlAndDom($result);
		
		return $this->getElement($status);
	}

	public function toggle($relNumber) 
	{    
		if ($relNumber != 1 && $relNumber != 2 && $relNumber != 3 && $relNumber != 4 && $relNumber != 5 && $relNumber != 6 && $relNumber != 7 && $relNumber != 8)
			throw new Exception("The toggle function input should be 1 or 2 .You have entered:".$relNumber);
		
		$curl = $this->_curlResource;
		$resultStatus = curl_getinfo($curl);
		if ($resultStatus['http_code'] != 200) 
			throw new Exception("Toggle connection failed .Server responded with: ".$resultStatus['http_code']." code");

		curl_setopt($curl, CURLOPT_URL, $this->_url .'relays.cgi?relay='. $relNumber);
		
		$result = curl_exec($curl);	
		if ($result != true)
			throw new Exception("Toggle curl_execution failed.");
	}

	protected function loadUrlAndDom($content) 
	{
		$dom = new DOMDocument;
		$dom->strictErrorChecking = false;
		
		if ($content) 
			$dom->loadHTML($content);
		
		return $dom;
	}

	protected function getElement($obj) 
	{
		if (!$obj->getElementsByTagName('div')->length)
			throw new Exception('Could not get HTML content.');	
		
		$result = $obj->getElementsByTagName('div')->item(0)->nodeValue; 
		

		$result = str_replace("\n", '', $result);
		$pattern = '/Relay1([0-9])\sRelay2([0-9])\sRelay3([0-9])\sRelay4([0-9])\sRelay5([0-9])\sRelay6([0-9])\sRelay7([0-9])\sRelay8([0-9])/';
		if (preg_match($pattern, trim(str_replace(" ", '', $result)), $matches))
		{
			array_shift($matches);
			return $matches;
		}
		
		throw new Exception("Unexpected document structure.");
	}

}

