<?

class InheritedJob extends JobClient {
    public function run(){
        //$email = 'me@grantrobertson.com';
        //$subject = 'InheritedJob says hello.';
        //$hostname = `hostname`;
        //$message = 'InheritedJob running on ' . $hostname . ' says howdy.';
        //$this->notifier->email($email, $subject, $message);
        
        //get a random chunk of text from a file and pass it to one of the test functions via the gearman job table. 
        $path = '/media/psf/Host/Volumes/Storage/Dropbox/Development/moulin/testdata/';
        $files = array('pg8800.txt', 'pg135.txt',  'pg1661.txt',  'pg768.txt', 'pg98.txt');
        
        for($i = 1; $i <= 100; $i++){
            $filetoread = mt_rand(0, count($files) -1);
            $startchar = mt_rand(0, 65535 * 4);
            $endchar = mt_rand($startchar, $startchar + 65535);
            $chunk = file_get_contents($path . $files[$filetoread], FALSE, NULL, $startchar, $endchar);
            $sql = "INSERT INTO gearman_jobs SET job_class='SecondJob', job_function='reverse', job_status='pending', job_work='" . addslashes($chunk) . "'";
            $result = $this->jobDbh->query($sql);
        }
            // Call parent's ->run() to set our heartbeat. 
        parent::run();
        return true;
    }
}
?>