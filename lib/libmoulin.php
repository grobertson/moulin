<?

class Moulin {
        private $_loopCounter = 0;
        public $dbh = FALSE;
        public $notifier = FALSE;
        public $gear = FALSE;
        public $log = FALSE;
        private $jobs = FALSE;
        private $hostname = FALSE;
        private $pid = FALSE;
        
        function __construct($config) {
            require_once('Log.php');
            $this->pid = getmypid();
            $this->hostname = `hostname`;
            require_once('JobClient.php');  //Abstract class upon which Job clients are created.
            require_once('Jobs.php');
            require_once('Notify.php');
              
            
            $this->log = Log::factory('composite', '', 'moulin [' . $this->pid . ']', array(), $config['logging']['log_level']);
            $console = Log::factory('console', '', 'moulin [' . $this->pid . ']', array(), $config['logging']['log_level']);
            $filelog = Log::factory('file', $config['logging']['log_file'], 'moulin [' . $this->pid . ']', array(), $config['logging']['log_level']);
            $this->log->addChild($console);
            $this->log->addChild($filelog);
            /*
            $this->log->debug('7');
            $this->log->info('6');
            $this->log->notice('5');
            $this->log->warning('4');
            $this->log->err('3');
            $this->log->crit('2');
            $this->log->alert('1');
            $this->log->emerg('0'); 
            */
            
            #start runtime
            $this->main($config);                 
        }
            
        // Main must be a public function.
        public function main($config){
             error_reporting(1024);
             
             $hostname = trim(`hostname`);
             
             if($config['database']){
                 $db = (object) $config['database'];
                 $this->dbh = $this->initDb($db);
             }
             
             # Connect to gearman
             $this->log->debug('Start Gearman');
             $this->gear = new GearmanClient();
             $this->gear->addServer($config['gearman']['host'], $config['gearman']['port']);
             
             #get a notifier
             $this->log->debug('Start Notifier');
             $this->notifier = new Notify($config, $this->log, $this->dbh);
             
             $this->log->debug('Load jobs');
             // Load up the available job types for this client. 
             $this->jobs = new Jobs($config, $this->log, $this->notifier, $this->gear, $this->dbh);
             
              
             while(!$running){
                 $this->log->debug('Call _loop()');
                 $running = false;          // set true to only run one loop
                 $this->upCount();          // increment the private _loopCounter
                 $this->_loop($config);     // call the loop
                 sleep($config['moulin']['loop_interval']); // sleep for the configured loop delay time
             }
        }
        
        private function _loop($config){
            $this->log->info("Looking for work: " .  $this->getCount());
            foreach($this->jobs->getJobs() as $job){
                $output = $job->run();
            }
        }
        
        private function upCount(){
            $this->_loopCounter++;
        }
        
        
        public function initDb($database){
            if(require_once('MDB2.php')){
    
            }else{
                $this->log->info("Could not load MDB2\n");
            }
            $dsn = "mysql://" . $database->user . ":" . $database->password  . "@" . $database->host . "/" . $database->database;
            $dbh = MDB2::factory($dsn);
            
            if(PEAR::isError($dbh)) {
            	die("Error : " . $dbh->getMessage());
            }else{
                $this->log->info("Connected to: mysql://" . $database->user . "@" . $database->host . "/" . $database->database);
            }
            $dbh->loadModule('Extended', null, true);
            $dbh->loadModule('Reverse', null, true);
            $dbh->loadModule('Manager');
            $dbh->setFetchMode(MDB2_FETCHMODE_OBJECT);
            return $dbh;
        }
        
        public function getCount(){
            return $this->_loopCounter;
        }

        public function log($message, $module="", $function=""){;
            echo('Moulin::log Deprecated. use shared instance');
        }

}

?>