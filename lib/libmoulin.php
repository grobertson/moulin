<?

class Moulin {
        private $_loopCounter = 0;
        public $dbh = FALSE;
        public $notifier = FALSE;
        public $gear = FALSE;
        private $jobs = FALSE;

        function __construct($config) {
            require_once('JobClient.php');  //Abstract class upon which Job clients are created.
            require_once("Jobs.php");
            require_once("Notify.php");  
            
            #start runtime
            $this->main($config);                 
         
        }
            
        // Main must be a public function.
        public function main($config){
             error_reporting(1024);
             $hostname = trim(`hostname`);
             
             if($config->database->enable){
                 $this->dbh = $this->initDb($config->database);
                 echo("Connected to: mysql://" . $config->database->user . "@" . $config->database->host . "/" . $config->database->database);
             }
             # Connect to gearman
             $this->gear = new GearmanClient();
             $this->gear->addServer($config->gearmanServer->host, $config->gearmanServer->port);
             
             #get a notifier
             $this->notifier = new Notify($config->notifications);

             // Load up the available job types for this client. 
             $this->jobs = new Jobs($config, $this->dbh, $this->notifier, $this->gear);
              
             while(1){
                 $this->upCount();          // increment the private _loopCounter
                 $this->_loop($config);     // call the loop
                 sleep($config->loopDelay); // sleep for the configured loop delay time
             }
        }
        private function _loop($config){
            echo("Looking for work: " .  $this->getCount());
            foreach($this->jobs->getJobs() as $job){
                $output = $job->run();
            }
        }
        private function upCount(){
            $this->_loopCounter++;
        }

        private function initDb($database){
            if(include('MDB2.php')){
    
            }else{
                echo("Could not load MDB2\n");
            }
            $dsn = "mysql://" . $database->user . ":" . $database->password  . "@" . $database->host . "/" . $database->database;
            $dbh = MDB2::factory($dsn);
            if(PEAR::isError($dbh)) {
            	die("Error : " . $dbh->getMessage());
            }
            $dbh->setFetchMode(MDB2_FETCHMODE_OBJECT);
            return $dbh;
        }
        public function getCount(){
            return $this->_loopCounter;
        }

}

?>