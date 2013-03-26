<?
class SecondJob{
    private $notifier = FALSE;
    function __construct($config){
        $this->notifier = new Notify($config->notifications);
        System_Daemon::info("Registered job client SimpleExample");
    }
    public function run(){
        //$this->notifier->email("me@grantrobertson.com", "I'm a SimpleExample running on Moulin.", "I sent this message when I was instantiated.");
        $uptime = trim(`uptime`);
        System_Daemon::info("SecondJob: $uptime");
        return true;
    }
}
?>