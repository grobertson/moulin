<?

abstract class JobClient
{ 
    private $jobClientName = "Unregistered";
    public $jobConfig = FALSE;
    public $jobConfigLoaded = FALSE;
    public $_dbh = FALSE;
    public $jobDbh = FALSE;
    public $notifier = FALSE;
    public $gear = FALSE;
    public $log = FALSE;
    
    function __construct($config, $log, $notifier, $gear, $dbh){
        $this->log = $log; 
        $this->setJobClientName();
        $this->log->info("Registered job client " . $this->getJobClientName());
        $this->jobConfigLoaded = $this->_readConfig();
        if($this->jobConfig['database']['database']){
            $database = (object) $this->jobConfig['database'];
            $this->jobDbh = Moulin::initDb($database);
        }
        
        $this->notifier = $notifier;
        $this->gear = $gear;
        $this->_dbh = $dbh;
    }

    public function run(){
        // Run can't be private. So instead, let's use a little traceback trickery to find
        // the name of our calling class. If it's anything other than Moulin, we're overridden
        // and being called correctly. 
        if($this->get_calling_class() == 'Moulin'){
            $this->log->warning("Override the " . $this->getJobClientName() . "->run() method to execute your client job.");
        }
        
        //write a heartbeat at the end of every execution. 
        $this->writeHeartbeat();
        return true;
    }
    
    private function _readConfig(){
        $this->log->debug('_readConfig');
        $moulinLibPath = dirname(__FILE__);
        $moulinRootPath = preg_replace("/lib$/", '', $moulinLibPath);
        $jobClientRoot = $moulinRootPath . 'jobs-available/' . $this->jobClientName . "/";
        $jobClientEtc = $jobClientRoot . "etc/";
        $this->log->info('Probing config files in : ' . $jobClientEtc);
        $etcFiles = scandir ($jobClientEtc);
        foreach($etcFiles as $etcFile){
            if($etcFile !== '.' && $etcFile !== '..'){
                //look for config files to load.
                //only parse files ending with .ini
                if(preg_match('/\.ini/', $etcFile)){
                    $this->log->info('Loading configuration file from ' . $jobClientEtc . $etcFile);
                    $this->jobConfig = parse_ini_file($jobClientEtc . $etcFile, TRUE);
                }
            }
        }
        if(!$this->jobConfig){
            $this->log->info('No configuration loaded for ' . $this->jobClientName);
            return FALSE;
        }else{
            return TRUE;
        } 
    }
    
    private function writeHeartbeat(){
        $sql = "insert into job_registry set job_name='". $this->getJobClientName() . "', last_run=NOW() on duplicate key update last_run=NOW()";
        $result = $this->_dbh->queryAll($sql);
    }
    
    private function getJobClientName(){
        return $this->jobClientName;
    }                           

    private function setJobClientName(){
        $this->jobClientName = get_called_class();
    }
    
    function get_calling_class() {
        // https://gist.github.com/kylefarris/5188645
        // https://gist.github.com/hamstar/1122679
        //get the trace
        $trace = debug_backtrace();
        // Get the class that is asking for who awoke it
        $class = ( isset( $trace[1]['class'] ) ? $trace[1]['class'] : NULL );
        // +1 to i cos we have to account for calling this function
        for ( $i=1; $i<count( $trace ); $i++ ) {
            if ( isset( $trace[$i] ) && isset( $trace[$i]['class'] ) ) // is it set?
                 if ( $class != $trace[$i]['class'] ) // is it a different class
                     return $trace[$i]['class'];
        }
    }
    
    private function _initDb($database){
        $dsn = "mysql://" . $database->user . ":" . $database->password  . "@" . $database->host . "/" . $database->database;
        $dbh = MDB2::factory($dsn);
        if(PEAR::isError($dbh)) {
        	die("Error : " . $dbh->getMessage());
        }
        $dbh->setFetchMode(MDB2_FETCHMODE_OBJECT);
        return $dbh;
    }   
}  

?>
