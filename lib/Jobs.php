<?

class Jobs{
    private $_jobs = FALSE;
    function __construct($config, $log, $notifier, $gear, $dbh){
        $this->_jobs = $this->Loader($config, $log, $notifier, $gear, $dbh);
    }
    private function Loader($config, $log, $notifier, $gear, $dbh){
        $jobsPath = dirname(__FILE__);
        $jobsEnabledPath = preg_replace('/lib$/', 'jobs-enabled', $jobsPath);
        $jobsAvailablePath = preg_replace('/lib$/', 'jobs-available', $jobsPath);
        $log->info('Looking for Moulin jobs in ' . $jobsEnabledPath);
        $classFiles = scandir ($jobsEnabledPath);
        $foundClasses = FALSE;
        foreach($classFiles as $className){
            if($className !== '.' && $className !== '..'){
                //found a directory, look for jobs to load.
                if(is_file($jobsAvailablePath . "/" . $className . '/client/' . $className . '.php')){
                    $classFile = $jobsAvailablePath . "/" . $className . '/client/' . $className . '.php';
                    $log->info('Found a worker class at ' . $jobsAvailablePath . "/" . $className . '/client/' . $className . '.php');
                    include($classFile);
                    $foundClasses[] = new $className($config, $log, $notifier, $gear, $dbh);
                }
            }
        }
        return $foundClasses;
    }
    public function getJobs(){
        return $this->_jobs;
    }
}
?>