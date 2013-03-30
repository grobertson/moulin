<?
class SimpleExample extends JobClient {
    public function run(){
        $this->notifier->email("me@grantrobertson.com", "I'm a SimpleExample running on Moulin.", "I sent this message when I was instantiated.");
        
        parent::run();
        return true;
    }
}
?>