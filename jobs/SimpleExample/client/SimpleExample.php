<?
class SimpleExample{
    private $notifier = FALSE;
    function __construct($config){
        $this->notifier = new Notify($config->notifications);
        System_Daemon::info("Registered job client SimpleExample");
    }
    public function run(){
        $this->notifier->email("me@grantrobertson.com", "I'm a SimpleExample running on Moulin.", "I sent this message when I was instantiated.");
        return true;
    }
}
?>