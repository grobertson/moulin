<?
// Notifier for Moulin
class Notify{
    private $postmarkEnabled = FALSE;
    private $postmarkServer = "";
    private $postmarkKey = "";
    private $postmarkFrom = "";

    private $twilioEnabled = FALSE;
    private $twilioAccountSid = "";
    private $twilioAuthToken = "";
    private $twilioNumber = "";
    private $twilioServer = "";
    
    function __construct($config, $log){
        require_once('Curl.php');
        $this->log = $log;
        $this->postmarkEnabled = $config->postmarkEnabled;
        $this->postmarkServer = $config->postmarkServer;
        $this->postmarkKey = $config->postmarkKey;
        $this->postmarkFrom = $config->postmarkFrom;
        $this->twilioEnabled = $config->twilioEnabled;
        $this->twilioAccountSid = $config->twilioAccountSid;
        $this->twilioAuthToken = $config->twilioAuthToken;
        $this->twilioNumber = $config->twilioNumber;
        $this->twilioServer = $config->twilioServer;
    }
    
    public function email($email, $subject, $body){
        $message = new stdClass;
    	$message->To=$email;
    	$message->Subject=$subject;
    	$message->HtmlBody=$body;
        $message->From=$this->postmarkFrom;
        
        //Extend this for different message methods (postmark, sendgrid, sendmail, etc)
        if($this->_sendPostmark($message)){
            return true;
        }else{
            $this->log->info('Could not send message to ' . $email);
            return false;
        }
    }
    
    public function sms($number, $body){
        $message = new stdClass;
    	$message->To=$number;
        $message->Body=$body;
        $this->log->info('Sending SMS to ' . $message->To . ': ' . $message->Body);
        if($this->_sendTwilio($number, $message)){
            $this->log();
        }   
    }
    
    //send email via postmarkapp.com
    private function _sendPostmark($message){
        if(!$this->postmarkEnabled){
            $this->log->info('Postmark Disbaled in Notify::_sendPostmark -- Message not sent.');
            $this->log->info('Unsent message: To: ' . $message->To . ' Subject: ' . $message->Subject);
            $this->log->debug(strip_tags($message->HtmlBody)); // transform the html message to text for cleaner logs. 
            return true; //Return true on disabled becuase it's not really an error.
        }
        try{
            $ch = Curl::init();
            Curl::setUrl($ch, $this->postmarkServer); 
            Curl::setHeaders($ch, array("X-Postmark-Server-Token: " . $this->postmarkKey,              
            							"Content-Type: application/json",
            							"Accept: application/json"));                      
            $doc = Curl::post($ch, $message);
            $httpCode = Curl::getCode($ch);
            Curl::close($ch);
            if($httpCode == 200){
                $response = json_decode($doc);
                if($response->ErrorCode == 0){
                    $this->log->info('Sent postmark to ' . $message->To . " with MessageID " . $response->MessageID);
                    return true;    
                }else{
                    $this->log->info('HTTP 200 but, Postmark send fail: ' . $doc);
                    return false;
                }
            }else{
                $this->log->info('Postmark API returned http error ' . $httpCode);
                $this->log->info('Postmark message: ' . $doc);
                return false;
            } 
        }catch(Exception $e){
        	// you could do something with this error.
        	$this->log->info('Exception: ' . $e); 
            return false;
        }  		
    }
    
    //send sms via Twilio
    private function _sendTwilio($number, $message){
        $url = $this->twilioServer . $this->twilioAccountSid . "/SMS/Messages.json";
        $message->From = $this->twilioNumber;
        $ch = Curl::init();
        Curl::setUrl($ch, $url);                                                          
        Curl::setAuth($ch, $this->twilioAccountSid, $this->twilioAuthToken);
        //$response = Curl::post($ch, $message);
        //$twilio = json_decode($response);
        /*
        stdClass Object
        (
            [sid] => SMc46ec6904fac651ed8ec5db9b1bf22ae
            [date_created] => Sat, 30 Mar 2013 22:47:25 +0000
            [date_updated] => Sat, 30 Mar 2013 22:47:25 +0000
            [date_sent] => 
            [account_sid] => AC74853d0923256d0def13d8cadb7004ea
            [to] => +14044577299
            [from] => +17732506369
            [body] => I'm a SimpleExample running on Moulin.
            [status] => queued
            [direction] => outbound-api
            [api_version] => 2010-04-01
            [price] => 
            [uri] => /2010-04-01/Accounts/AC74853d0923256d0def13d8cadb7004ea/SMS/Messages/SMc46ec6904fac651ed8ec5db9b1bf22ae.json
        )
        */

    }
}
?>