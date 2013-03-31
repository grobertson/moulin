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
    private $_dbh = FALSE;
    function __construct($config, $log, $dbh){
        require_once('Curl.php');
        $this->log = $log;
        $this->_dbh = $dbh;
        $this->postmarkEnabled = $config['notifications.postmark']['enabled'];
        $this->postmarkServer = $config['notifications.postmark']['api_server'];
        $this->postmarkKey = $config['notifications.postmark']['api_key'];
        $this->postmarkFrom = $config['notifications.postmark']['from'];
        $this->twilioEnabled = $config['notifications.twilio']['enabled'];
        $this->twilioAccountSid = $config['notifications.twilio']['account_sid'];
        $this->twilioAuthToken = $config['notifications.twilio']['auth_token'];
        $this->twilioNumber = $config['notifications.twilio']['number'];
        $this->twilioServer = $config['notifications.twilio']['server'];
        if($this->twilioEnabled){
            $this->syncTwilioSchema();
        }
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
            $this->log->info('SMS sent to: ' . $number);
        }else{
            $this->log->error('SMS failed to: ' . $number);
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
            $doc = Curl::post($ch, json_encode($message));
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
        if(!$this->twilioEnabled){
            $this->log->warning('SMS not sent because Twilio is disabled in moulin.ini');
            $this->log->debug('SMS to: ' . $number);
            $this->log->debug('SMS: ' . $message->Body);
            return true;
        }
        
        $url = $this->twilioServer . $this->twilioAccountSid . "/SMS/Messages.json";
        $message->From = $this->twilioNumber;
        $ch = Curl::init();
        Curl::setUrl($ch, $url);                                                          
        Curl::setAuth($ch, $this->twilioAccountSid, $this->twilioAuthToken);
        $response = Curl::post($ch, $message);
        $twilio = (array) json_decode($response);
        $twilio['from_number'] = $twilio['from'];
        $twilio['to_number'] = $twilio['to'];
        unset($twilio['to']);
        unset($twilio['from']);
        $this->log->debug($response);
        if($twilio['status'] === "queued"){
            $this->log->info('SMS Sent successfully to: ' . $twilio['to_number']);
            $sth = $this->_dbh->extended->autoExecute('notify_twilio', $twilio, MDB2_AUTOQUERY_INSERT, null);
            $this->log->debug('Insert to notify_twilio autoExecuted.');
            return true; 
        }else{
            return false;
        }
    }
    
    function createTwilioSchema(){
        $table_options = array(
            'comment' => 'Twilio messages',
            'charset' => 'utf8',
            'collate' => 'utf8_unicode_ci',
            'type'    => 'innodb',
        );
        $definition = array (
            'sid' => array ('type' => 'text', 'length' => 128),
            'date_created' => array ('type' => 'text', 'length' => 128),
            'date_updated' => array ('type' => 'text', 'length' => 128),
            'date_sent' => array ('type' => 'text', 'length' => 128),
            'account_sid' => array ('type' => 'text', 'length' => 128),
            'to_number' => array ('type' => 'text', 'length' => 128),
            'from_number' => array ('type' => 'text', 'length' => 128),
            'body' => array ('type' => 'text', 'length' => 255),
            'status' => array ('type' => 'text', 'length' => 128),
            'direction' => array ('type' => 'text', 'length' => 128),
            'api_version' => array ('type' => 'text', 'length' => 128),
            'price' => array ('type' => 'text', 'length' => 128),
            'uri' => array ('type' => 'text', 'length' => 128)
        );

        $result = $this->_dbh->createTable('notify_twilio', $definition, $table_options);
        return true;
    }
    
    function syncTwilioSchema(){
        $tables = $this->_dbh->manager->listTables();
        if(array_search('notify_twilio', $tables) === FALSE){
            $this->log->info('Creating table notify_twilio');
            $this->createTwilioSchema();
        }else{
            //TODO: Schema Check
            return true;
        }
    
    }

}
?>