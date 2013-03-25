<?

class Moulin {
        private $_loopCounter = 0;
        private $dbh = FALSE;
        private $notifier = FALSE;
        private $gear = FALSE;

        function __construct($config) {
            require_once("System/Daemon.php");
            require_once("Notify.php");  
            $config->runmode = $this->getRunmode();
            $this->setDaemonOptions($config);
            // If runmode --write-initd, this program will write a startup script
            // This will make sure your daemon will be started on reboot
            if ($config->runmode['write-initd']) {
                if (($initd_location = System_Daemon::writeAutoRun()) === false) {
                    System_Daemon::notice('unable to write init.d script');
                } else {
                    System_Daemon::info('Sucessfully created startup script: %s', $initd_location);
                }   
            }else{ 
                if($config->database->enable){
                    $this->dbh = $this->initDb($config->database);
                    System_Daemon::info("Connected to: mysql://" . $config->database->user . "@" . $config->database->host . "/" . $config->database->database);
                }
                # Connect to gearman
                $this->gear = new GearmanClient();
                $this->gear->addServer($config->gearmanServer->host, $config->gearmanServer->port);
                #get a notifier
                $this->notifier = new Notify($config->notifications);
                
                #start runtime
                $this->main($config);
            }            
        }    
        // Main must be a public function.
        public function main($config){
            // This program can also be run in the forground with runmode --interactive
             if (!$config->runmode['interactive']){
                 // Spawn Daemon
                 error_reporting(4096);
                 $hostname = trim(`hostname`);
                 $this->notifier->email($config->adminEmail, "[Moulin] Daemon restarted on $hostname at " . date('l, F d, H:i:s'), "<p>The Moulin controller daemon started on " . $hostname . ". Restart occurred on " . date('l, F d, H:i:s') . ".</p>");
             	 System_Daemon::start();
             }else{
                 error_reporting(512);
             }

             while(!System_Daemon::isDying()){
                 $this->upCount();          // increment the private _loopCounter
                 $this->_loop($config);     // call the loop
                 sleep($config->loopDelay); // sleep for the configured loop delay time
             }
        }
        private function _loop($config){
            System_Daemon::notice("Looking for work: " .  $this->getCount());
            $result = $this->dbh->queryAll("select * from auth where username in ('grant@spokenlayer.com')");
            foreach($result as $record){
                System_Daemon::notice("User: " .  $record->username);
            }
        }
        private function upCount(){
            $this->_loopCounter++;
        }
        private function getRunmode(){
            global $argv;
            
            $runmode = array(
                'interactive' => false,
            	'reset-all' => false,
            	'rebuild-all' => false,
            	'force' => false,
            	'help' => false,
                'write-initd' => false
            );

            // Scan command line attributes for allowed arguments
            foreach ($argv as $k=>$arg) {
                if (substr($arg, 0, 2) == '--' && isset($runmode[substr($arg, 2)])) {
                    $runmode[substr($arg, 2)] = true;
                }
            }
            return $runmode;
        }        
        private function setDaemonOptions($config){
            // Daemon Setup
            $options = array(
            	'appName' => "moulin",
                'appDir' => dirname(__FILE__),
                'appDescription' => 'Pushin rhymes like weight.',
                'appExecutable' => 'moulin',
                'appDir' => $config->exePath,
                'authorName' => 'Grant Robertson',
                'authorEmail' => 'grant@spokenlayer.com',
                'sysMaxExecutionTime' => '0',
                'sysMaxInputTime' => '0',
                'sysMemoryLimit' => '1024M',
                'appRunAsGID' => 0,
                'appRunAsUID' => 0
            );

            System_Daemon::setOptions($options);

            return TRUE;
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