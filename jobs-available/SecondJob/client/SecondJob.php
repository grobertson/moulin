<?
class SecondJob extends JobClient {
    public function run(){
        $this->notifier->sms("4044577299", "I'm a SimpleExample running on Moulin.");
        $uptime = trim(`uptime`);
        $this->log->notice("$uptime", "SecondJob", "run");
        parent::run();
        return true;
    }
}
?>