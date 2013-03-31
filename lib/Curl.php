<?
class Curl {
    public function init(){
        $handle = curl_init();
        Curl::setOpt($handle, CURLOPT_NOBODY, FALSE);
        return $handle;
    }
    
    public function setUrl($handle, $url){
        Curl::setOpt($handle, CURLOPT_URL, $url); 
    }
    
    public function getCode($handle){
        return Curl::getInfo($handle, CURLINFO_HTTP_CODE);
    }
    
    public function setHeaders($handle, $headers){
        Curl::setOpt($handle, CURLOPT_HTTPHEADER, $headers);
    }
    
    public function setAuth($handle, $user, $password){
        Curl::setOpt($handle, CURLOPT_USERPWD, $user . ":" . $password);
    }

    public function post($handle, $data){
        Curl::setOpt($handle, CURLOPT_HEADER, FALSE);
        Curl::setOpt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        Curl::setOpt($handle, CURLOPT_POST, 1);
        Curl::setOpt($handle, CURLOPT_POSTFIELDS, $data);
        return Curl::exec($handle); 
    }
    
    public function setOpt($handle, $opt, $value){
        curl_setopt($handle, $opt, $value);    
    }
    
    public function getInfo($handle, $info){
        return curl_getinfo($handle, $info);
    }
        
    public function exec($handle){
        return curl_exec($handle);
    }
    
    public function close($handle){
        curl_close($handle);
    }
}
?>