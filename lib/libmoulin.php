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

            
            require_once('Log.php');            //Import PEAR Log before setting up logging
            $this->probeEnvironment();
            $this->configureLogs($config);
            
            require_once('Util.php');           //Simple functions that don't belong other places.
            require_once('JobClient.php');      //Abstract class upon which Job clients are created.
            require_once('Jobs.php');           //Finds jobs with loader
            require_once('Notify.php');         //Provides notification services
            
            #start runtime
            $this->main($config);               //main responsible for starting shared instances and managing
                                                //the job loop
        }
            
        // Main must be a public function.
        public function main($config){
             global $isDying;
             if($config['moulin']['debug']){
                 $this->log->emerg('Debug mode on.');
                 error_reporting(4096);
             }else{
                 error_reporting(0);
             }

             if($config['database']['enabled']){
                 $this->log->debug('Open Moulin\'s database connection.');
                 $db = (object) $config['database'];
                 $this->dbh = $this->initDb($db);
             }else{
                 $this->log->debug('Database connection disabled for Moulin. Jobs may still create their own.'); 
             }
             
             # Connect to gearman
             $this->log->debug('Create GearmanClient instance.');
             $this->gear = new GearmanClient();
             $this->gear->addServer($config['gearman']['host'], $config['gearman']['port']);
             
             #get a notifier
             $this->log->debug('Create Notifier instance');
             $this->notifier = new Notify($config, $this->log, $this->dbh);
             
             $this->log->debug('Create Jobs instance');
             // Load up the available job types for this client. 
             $this->jobs = new Jobs($config, $this->log, $this->notifier, $this->gear, $this->dbh);
             
              
             while(!$isDying){
                 $this->log->debug('Call _loop()');
                 $running = false;          // set true to only run one loop
                 $this->upCount();          // increment the private _loopCounter
                 $this->_loop($config);     // call the loop
                 sleep($config['moulin']['loop_interval']); // sleep for the configured loop delay time
             }
             
             //TODO: Graceful shutdown things would happen here.
             $this->log->crit('Caught SIGINT: Dying gracefully.'); 
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
        
        private function probeEnvironment(){
            $this->pid = getmypid();
            $this->hostname = `hostname`;
        }
        
        private function configureLogs($config){
            // Open a composite log instance to add child logs in
            $this->log = Log::factory('composite', '', 'moulin [' . $this->pid . ']', array(), $config['log.console']['log_level']);
            
            if($config['log.console']['enabled']){
                $console = Log::factory('console', '', 'moulin [' . $this->pid . ']', array(), $config['log.console']['log_level']);
                $this->log->addChild($console);
            }else{
                echo('Console logging is disabled.');
            }
            
            if($config['log.file']['enabled']){
                $filelog = Log::factory('file', $config['log.file']['log_file'], 'moulin [' . $this->pid . ']', array(), $config['log.file']['log_level']);
                $this->log->addChild($filelog);
            }
            
            $this->log->notice('-- Moulin Started --');    
        }
        public function log($message, $module="", $function=""){
            echo('Moulin::log Deprecated. use shared instance');
        }

}

class MoulinWorker {
        private $_loopCounter = 0;
        public $dbh = FALSE;
        public $notifier = FALSE;
        public $gear = FALSE;
        public $log = FALSE;
        private $workers = FALSE;
        private $hostname = FALSE;
        private $pid = FALSE;
        
        function __construct($config) { 
            #start runtime
            $this->main($config);               //main responsible for starting shared instances and managing
                                                            //the job loop
        }
        
        public function main($config){
             global $isDying;
             require_once('Log.php');            //Import PEAR Log before setting up logging
             $this->probeEnvironment();
             $this->configureLogs($config);     
                         
             if($config['moulin-worker']['debug']){
                 $this->log->emerg('Debug mode on.');
                 error_reporting(4096);
             }else{
                 error_reporting(0);
             }
             
             require_once('JobWorker.php');      //Abstract class upon which Job clients are created.
             require_once('Workers.php');        //Finds workers with loader
             # Connect to gearman
             $this->log->debug('Create GearmanWorker instance.');
             $this->gear = new GearmanWorker();
             
             $this->log->debug('Adding Gearman server: ' . $config['gearman']['host'] . ":" . $config['gearman']['port']);
             $this->gear->addServer($config['gearman']['host'], $config['gearman']['port']);
             
             /*
             #get a notifier
             $this->log->debug('Create Notifier instance');
             $this->notifier = new Notify($config, $this->log, $this->dbh);
             
             
             */
             $this->log->debug('Create Workers instance');
             // Load up the available workers for this instance. 
             $this->workers = new Workers($config, $this->log, $this->gear);
             
             $this->log->debug('Workers instance started.'); 
              
             while(!$isDying){
                 $this->log->debug('Call _loop()');
                 $this->upCount();          // increment the private _loopCounter
                 $this->_loop($config);     // call the loop
                 sleep($config['moulin-worker']['loop_interval']); // sleep for the configured loop delay time
             }
             
             //TODO: Graceful shutdown things would happen here.
             $this->log->crit('Caught SIGINT: Dying gracefully.'); 
        }

        
        private function _loop($config){
            $this->log->info("Looking for work: " .  $this->getCount());
            if(!$this->workers->getWorkers()){
                $this->log->crit("No enabled workers found.");
            }
            //$this->log->debug($this->workers->getWorkers());
            foreach($this->workers->getWorkers() as $worker){
                $output = $worker->run();
            }
            
        }
        
        public function getCount(){
            return $this->_loopCounter;
        }
                
        private function upCount(){
            $this->_loopCounter++;
        }
       
        private function probeEnvironment(){
            $this->pid = getmypid();
            $this->hostname = `hostname`;
        }
                        
        private function configureLogs($config){
            // Open a composite log instance to add child logs in
            $this->log = Log::factory('composite', '', 'moulin [' . $this->pid . ']', array(), $config['log.console']['log_level']);
            
            if($config['log.console']['enabled']){
                $console = Log::factory('console', '', 'moulin [' . $this->pid . ']', array(), $config['log.console']['log_level']);
                $this->log->addChild($console);
            }else{
                echo('Console logging is disabled.');
            }
            
            if($config['log.file']['enabled']){
                $filelog = Log::factory('file', $config['log.file']['log_file'], 'moulin [' . $this->pid . ']', array(), $config['log.file']['log_level']);
                $this->log->addChild($filelog);
            }
            
            $this->log->notice('-- Moulin Started --');    
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
}
?>