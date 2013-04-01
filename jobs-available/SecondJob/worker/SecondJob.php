<?
class SecondJob extends JobWorker {

    function __construct($config, $log, $gear){
        parent::__construct($config, $log, $gear);
        //print_r($gear);
        
        $gear->addFunction("reverse", array($this, 'reverse'));
        //$gear->addFunction("sha1", array($this, 'sha1'));
        //$gear->addFunction("md5", array($this, 'md5'));
        
        //$gear->addFunction("crc32", array($this, 'crc32'));
        $log->notice("Registered reverse function.");
    }
    
    function run(){
        $this->log->notice("Trying to work.");
        $this->gear->work();
    }
    
    function reverse($job)
    {
      $this->log->notice("Workload: " . $job->workload());
      $this->log->notice("uuid     : " . $job->unique());
      $ans = strrev(unserialize($job->workload()));
      $this->log->notice("Product  : " . $ans);
      $this->writeProduct($ans, $job->unique());
    }
    function md5($job)
    {
      //$this->log->notice("Got job");
      //echo("\nWorkload: " . $job->workload() . "\n");
      //$this->log->emerg("\nmd5  : " . md5($job->workload()));
      $ans = md5($job->workload());
      //$this->writeProduct($ans);
    }
    function sha1($job)
    {
      //$this->log->notice("Got job");
      //echo("\nWorkload: " . $job->workload() . "\n");
      //$this->log->emerg("\nsha1  : " . sha1($job->workload()));
      $ans = sha1($job->workload());
      //$this->writeProduct($ans);
    }
    function crc32($job)
    {
      //$this->log->notice("Got job");
      //echo("\nWorkload: " . $job->workload() . "\n");
      $ans = crc32($job->workload());
      //$this->writeProduct($ans);
      //$this->log->emerg("\ncrc32  : " . );
    }
    function writeProduct($product, $uuid){
        $sql = "update gearman_jobs SET job_product='" . addslashes(serialize($product)) . "', job_completed=NOW(), job_status='complete' where job_uuid='" . $uuid . "'";
        $this->log->debug($sql);
        $result = $this->workerDbh->query($sql);
        
    }    
}
?>