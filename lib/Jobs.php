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
                //found a directory, look for workers.
                if(is_file($jobsPath . "/" . $className . '/worker/' . $className . '.php')){
                    $classFile = $jobsPath . "/" . $className . '/worker/' . $className . '.php';
                    System_Daemon::info('Found a worker class at ' . $jobsPath . "/" . $className . '/worker/' . $className . '.php');
                    include($classFile);
                    $foundClasses[] = new $className($config);
                }
            }
        }
        return true;
    }
}
?>