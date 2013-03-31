<?

abstract class JobWorker
{ 
    private $jobWorkerName = "Unregistered";
    public $workerConfig = FALSE;
    public $workerConfigLoaded = FALSE;
    public $workerDbh = FALSE;
    public $gear = FALSE;
    public $log = FALSE;
    
    function __construct($config, $log, $gear){
        $this->log = $log; 
        $this->setJobWorkerName();
        $this->log->info("Registered worker " . $this->getJobWorkerName());
        $this->workerConfigLoaded = $this->_readConfig();
        if($this->workerConfig['database']['database']){
            $database = (object) $this->workerConfig['database'];
            $this->workerDbh = MoulinWorker::initDb($database);
        }
        
        $this->gear = $gear;
        $this->_dbh = $dbh;
    }

    public function run(){
        // Run can't be private. So instead, let's use a little traceback trickery to find
        // the name of our calling class. If it's anything other than Moulin, we're overridden
        // and being called correctly. 
        if($this->get_calling_class() == 'MoulinWorker'){
            $this->log->warning("Override the " . $this->getJobWorkerName() . "->run() method to execute your worker.");
        }
        
        return true;
    }
    
    private function _readConfig(){
        $this->log->debug('_readConfig');
        $moulinLibPath = dirname(__FILE__);
        $moulinRootPath = preg_replace("/lib$/", '', $moulinLibPath);
        $jobWorkerRoot = $moulinRootPath . 'jobs-available/' . $this->jobWorkerName . "/";
        $jobWorkerEtc = $jobWorkerRoot . "etc/";
        $this->log->info('Probing config files in : ' . $jobWorkerEtc);
        $etcFiles = scandir ($jobWorkerEtc);
        foreach($etcFiles as $etcFile){
            if($etcFile !== '.' && $etcFile !== '..'){
                //look for config files to load.
                //only parse files ending with .ini
                if(preg_match('/\.ini/', $etcFile)){
                    $this->log->notice('Loading configuration file from ' . $jobWorkerEtc . $etcFile);
                    $this->workerConfig = parse_ini_file($jobWorkerEtc . $etcFile, TRUE);
                }
            }
        }
        if(!$this->workerConfig){
            $this->log->info('No configuration loaded for ' . $this->jobWorkerName);
            return FALSE;
        }else{
            return TRUE;
        } 
    }
    
    private function getJobWorkerName(){
        return $this->jobWorkerName;
    }                           

    private function setJobWorkerName(){
        $this->jobWorkerName = get_called_class();
    }
    

    public function get_calling_class() {
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
