<?
class SecondJob extends JobClient {
    public function run(){
        $this->notifier->email("me@grantrobertson.com", "I'm a SimpleExample running on Moulin.", "I sent this message when I was instantiated.");
        $uptime = trim(`uptime`);
        System_Daemon::info("SecondJob: $uptime");
        parent::run();
        return true;
    }
}
?>