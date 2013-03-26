<?
// Notifier for Moulin
class Notify{
    private $postmarkServer = "";
    private $postmarkKey = "";
    private $postmarkFrom = "";
    function __construct($config){
        $this->postmarkServer = $config->postmarkServer;
        $this->postmarkKey = $config->postmarkKey;
        $this->postmarkFrom = $config->postmarkFrom;
    }
    public function email($email, $subject, $message){
        //Extend this for different message methods (email, twitter, etc)
        if($this->_sendPostmark($email, $subject, $message)){
            return true;
        }else{
            System_Daemon::notice('Could not send message to ' . $email);
            return false;
        }
    }
    //send postmark
    private function _sendPostmark($email, $subject, $message){
    	$data = new stdClass;
    	$data->To=$email;
    	$data->Subject=$subject;
    	$data->HtmlBody=$message;
        $data->From=$this->postmarkFrom;
        if(!$this->config->postmarkEnabled){
            System_Daemon::notice('Postmark Disbaled in Notify::_sendPostmark -- Message not sent.');
            System_Daemon::notice('Unsent message: To: ' . $email . ' Subject: ' . $subject);
            return true;
        }
      try{
    	$ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $this->postmarkServer); 
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array(	"X-Postmark-Server-Token: " . $this->postmarkKey,              
    												'Content-Type: application/json',
    												"Accept: application/json"));                      
        curl_setopt($ch, CURLOPT_NOBODY, FALSE); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    		curl_setopt($ch, CURLOPT_POST, 1); 
        $doc = curl_exec($ch); 
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($httpCode == 200){
            $response = json_decode($doc);
            if($response->ErrorCode == 0){
                System_Daemon::notice('Sent postmark to ' . $email . " with MessageID " . $response->MessageID);
                return true;    
            }else{
                System_Daemon::crit('Postmark send fail: ' . $doc);
                return false;
            }
        }else{
            System_Daemon::crit('Postmark API returned http error ' . $httpCode);
            return false;
        }

    	}catch(Exception $e){
    		// you could do something with this error. 
    	    return false;
    	}  		
    }
}
?>