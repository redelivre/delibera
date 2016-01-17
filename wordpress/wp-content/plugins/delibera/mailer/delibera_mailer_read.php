<?php

class delibera_mailer_read
{
	protected $connection = false;
	
	protected function getConnection($connection)
	{
		if($connection === false)
		{
			if($this->connection === false)
			{
				return false;
			}
			else
			{
				$connection = $this->connection;
			}
		}
		return $connection;
	}
	
	function imap_login($host,$port,$user,$pass,$folder="INBOX",$ssl=false)
	{
	    $ssl=($ssl==false)?"/novalidate-cert":"/ssl/novalidate-cert";
	    $this->connection = (imap_open("{".$host.":".$port."/imap".$ssl."}".$folder,$user,$pass));
	    return $this->connection;
	}
	
	function pop3_login($host,$port,$user,$pass,$folder="INBOX",$ssl=false)
	{
	    $ssl=($ssl==false)?"/novalidate-cert":"/ssl/novalidate-cert";
	    $this->connection = (imap_open("{".$host.":".$port."/pop3".$ssl."}".$folder,$user,$pass));
	    return $this->connection;
	}
	function pop3_stat($connection = false)       
	{
		$connection = $this->getConnection($connection);
	    $check = imap_mailboxmsginfo($connection);
	    return ((array)$check);
	}
	function pop3_list($connection = false,$message="")
	{
		$connection = $this->getConnection($connection);
	    if ($message)
	    {
	        $range=$message;
	    } else {
	        $MC = imap_check($connection);
	        $range = "1:".$MC->Nmsgs;
	    }
	    $response = imap_fetch_overview($connection,$range);
	    foreach ($response as $msg) $result[$msg->msgno]=(array)$msg;
	        return $result;
	}
	function pop3_retr($connection,$message)
	{
		$connection = $this->getConnection($connection);
	    return(imap_fetchheader($connection,$message,FT_PREFETCHTEXT));
	}
	function pop3_dele($connection,$message)
	{
		$connection = $this->getConnection($connection);
	    return(imap_delete($connection,$message));
	}
	function mail_parse_headers($headers)
	{
	    $headers=preg_replace('/\r\n\s+/m', '',$headers);
	    preg_match_all('/([^: ]+): (.+?(?:\r\n\s(?:.+?))*)?\r\n/m', $headers, $matches);
	    foreach ($matches[1] as $key =>$value) $result[$value]=$matches[2][$key];
	    return($result);
	}
	function mail_mime_to_array($imap,$mid,$parse_headers=false)
	{
	    $mail = imap_fetchstructure($imap,$mid);
	    $mail = $this->mail_get_parts($imap,$mid,$mail,0);
	    if ($parse_headers) $mail[0]["parsed"]=$this->mail_parse_headers($mail[0]["data"]);
	    return($mail);
	}
	function mail_get_parts($imap,$mid,$part,$prefix)
	{   
	    $attachments=array();
	    $attachments[$prefix]=$this->mail_decode_part($imap,$mid,$part,$prefix);
	    if (isset($part->parts)) // multipart
	    {
	        $prefix = ($prefix == "0")?"":"$prefix.";
	        foreach ($part->parts as $number=>$subpart)
	            $attachments=array_merge($attachments, $this->mail_get_parts($imap,$mid,$subpart,$prefix.($number+1)));
	    }
	    return $attachments;
	}
	function mail_decode_part($connection,$message_number,$part,$prefix)
	{
		$connection = $this->getConnection($connection);
	    $attachment = array();
	
	    if($part->ifdparameters) {
	        foreach($part->dparameters as $object) {
	            $attachment[strtolower($object->attribute)]=$object->value;
	            if(strtolower($object->attribute) == 'filename') {
	                $attachment['is_attachment'] = true;
	                $attachment['filename'] = $object->value;
	            }
	        }
	    }
	
	    if($part->ifparameters) {
	        foreach($part->parameters as $object) {
	            $attachment[strtolower($object->attribute)]=$object->value;
	            if(strtolower($object->attribute) == 'name') {
	                $attachment['is_attachment'] = true;
	                $attachment['name'] = $object->value;
	            }
	        }
	    }
	
	    $attachment['data'] = imap_fetchbody($connection, $message_number, $prefix);
	    if($part->encoding == 3) { // 3 = BASE64
	        $attachment['data'] = base64_decode($attachment['data']);
	    }
	    elseif($part->encoding == 4) { // 4 = QUOTED-PRINTABLE
	        $attachment['data'] = quoted_printable_decode($attachment['data']);
	    }
	    return($attachment);
	}
}

?>