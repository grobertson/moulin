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
    public $uuid = FALSE;
    public $handles = FALSE;
    public $completed = 0;
    public $dispatched = 0;
    public $max_dispatch_per_loop = 100;
    
    function __construct($config, $log, $notifier, $gear, $dbh){
        require_once('Util.php');
        $this->uuid = new uuid();
        $this->log = $log; 
        $this->setJobClientName();
        $this->handles = array();
        $this->completed = 0;
        $this->dispatched = 0; 
        $this->max_dispatch_per_loop = 100;
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
                    $this->log->notice('Loading configuration file from ' . $jobClientEtc . $etcFile);
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
    
    public function claimPendingJobs(){
        $sql = "update gearman_jobs set job_status='". $this->getJobClientName() . "' where job_class='". $this->getJobClientName() . "' AND job_status='pending' limit " . $this->max_dispatch_per_loop . "";
        $this->log->debug($sql);
        $result = $this->jobDbh->query($sql);
    }
    
    public function getClaimedJobs(){
        $sql = "select * from gearman_jobs where job_status='". $this->getJobClientName() . "' AND  job_class='". $this->getJobClientName() . "' limit 0, " . $this->max_dispatch_per_loop . "";
        $this->log->debug($sql);
        $result = $this->jobDbh->queryAll($sql);
        return $result;
    }
    
    public function setJobDispatched($moulin_id, $job_handle, $uuid){
        $sql = "update gearman_jobs set job_status='dispatched', job_handle='$job_handle', job_uuid='$uuid', job_dispatched=NOW() where moulin_id='$moulin_id' ";
        $this->log->debug($sql);
        $result = $this->jobDbh->query($sql);
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
}  

?>
