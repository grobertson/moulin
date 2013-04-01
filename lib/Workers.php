<?

class Workers{
    private $_workers = FALSE;
    function __construct($config, $log, $gear){
        $log->info('Initializing Workers');
        $this->_workers = $this->Loader($config, $log, $gear);
    }
    private function Loader($config, $log, $gear){
        $workersPath = dirname(__FILE__);
        $workersEnabledPath = preg_replace('/lib$/', 'workers-enabled', $workersPath);
        $workersAvailablePath = preg_replace('/lib$/', 'jobs-available', $workersPath);
        $log->info('Looking for Moulin workers in ' . $workersEnabledPath);
        $classFiles = scandir ($workersEnabledPath);
        $foundClasses = FALSE;
        foreach($classFiles as $className){
            if($className !== '.' && $className !== '..'){
                //found a directory, look for jobs to load.
                if(is_file($workersAvailablePath . "/" . $className . '/worker/' . $className . '.php')){
                    $classFile = $workersAvailablePath . "/" . $className . '/worker/' . $className . '.php';
                    $log->info('Found a worker class at ' . $workersAvailablePath . "/" . $className . '/worker/' . $className . '.php');
                    include($classFile);
                    $foundClasses[] = new $className($config, $log, $gear);
                    $log->info('Installed worker class ' . $className . '');
                }
            }
        }
        return $foundClasses;
    }
    public function getWorkers(){
        return $this->_workers;
    }
}
?>