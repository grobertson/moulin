<?

class Jobs{
    function __construct($config){
        return $this->Loader($config);
    }
    private function Loader($config){
        $jobsPath = dirname(__FILE__);
        $jobsPath = preg_replace('/lib$/', 'jobs', $jobsPath);
        System_Daemon::info('Looking for Moulin jobs in ' . $jobsPath);
        $classFiles = scandir ($jobsPath);
        
        $foundClasses = FALSE;
        foreach($classFiles as $className){
            if($className !== '.' && $className !== '..'){
                //found a directory, look for jobs to load.
                if(is_file($jobsPath . "/" . $className . '/client/' . $className . '.php')){
                    $classFile = $jobsPath . "/" . $className . '/client/' . $className . '.php';
                    System_Daemon::info('Found a worker class at ' . $jobsPath . "/" . $className . '/client/' . $className . '.php');
                    include($classFile);
                    $foundClasses[] = new $className($config);
                }
            }
        }
        return true;
    }
}
?>