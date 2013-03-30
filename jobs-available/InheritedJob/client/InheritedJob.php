<?

class InheritedJob extends JobClient {
    public function run(){
        $email = 'me@grantrobertson.com';
        $subject = 'InheritedJob says hello.';
        $hostname = `hostname`;
        $message = 'InheritedJob running on ' . $hostname . ' says howdy.';
        
        $this->notifier->email($email, $subject, $message);
        // Call parent's ->run() to set our heartbeat. 
        parent::run();
        return true;
    }
}
?>