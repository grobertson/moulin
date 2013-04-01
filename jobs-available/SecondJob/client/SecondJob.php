<?
class SecondJob extends JobClient {

    public function run(){
        $result = $this->claimPendingJobs();
        $claimed_jobs = $this->getClaimedJobs();
        
        foreach($claimed_jobs as $claimed_job){
            $uuid = $this->uuid->get();
            //print_r($claimed_job);
            $this->log->debug($claimed_job->job_function . ", " . $claimed_job->job_work . "," . $uuid);
            $handle = $this->gear->doBackground($claimed_job->job_function, serialize($claimed_job->job_work), $uuid);
            if($this->gear->returnCode() != GEARMAN_SUCCESS){
                $this->log->notice("bad return code dispatching gearman job");
            }else{
                $this->setJobDispatched($claimed_job->moulin_id, $handle, $uuid);
            }    
        }
        
        parent::run();
        return true;
    }
}
?>