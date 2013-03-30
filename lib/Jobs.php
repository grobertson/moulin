<?

class Jobs{
    private $_jobs = FALSE;
    function __construct($config, $dbh, $notifier, $gear){
        $this->_jobs = $this->Loader($config, $dbh, $notifier, $gear);
    }
    private function Loader($config, $dbh, $notifier, $gear){
        $jobsPath = dirname(__FILE__);
        $jobsEnabledPath = preg_replace('/lib$/', 'jobs-enabled', $jobsPath);
        $jobsAvailablePath = preg_replace('/lib$/', 'jobs-available', $jobsPath);
        System_Daemon::info('Looking for Moulin jobs in ' . $jobsEnabledPath);
        $classFiles = scandir ($jobsEnabledPath);
        $foundClasses = FALSE;
        foreach($classFiles as $className){
            if($className !== '.' && $className !== '..'){
                //found a directory, look for jobs to load.
                if(is_file($jobsAvailablePath . "/" . $className . '/client/' . $className . '.php')){
                    $classFile = $jobsAvailablePath . "/" . $className . '/client/' . $className . '.php';
                    System_Daemon::info('Found a worker class at ' . $jobsAvailablePath . "/" . $className . '/client/' . $className . '.php');
                    include($classFile);
                    $foundClasses[] = new $className($config, $dbh, $notifier, $gear);
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