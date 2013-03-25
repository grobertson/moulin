<?
// Debug
$config->debug = TRUE;

//Database
$config->database->enable=TRUE;
$config->database->user="root";
$config->database->password="gw33d0";
$config->database->host="localhost";
$config->database->database="sl_voices";

$config->gearmanServer = new StdClass();
$config->gearmanServer->host="10.211.55.10";
$config->gearmanServer->port=4730;

// Notifications
$config->notifications = new StdClass();
$config->notifications->postmarkKey="dee677be-6ef6-4efd-8de8-e836ac990387";
$config->notifications->postmarkFrom="voices@spokenlayer.com";
$config->notifications->newStoryEmailSubject="[SpokenLayer Voices] A new story is available to be recorded.";
$config->notifications->newStoryEmailBody='<html><body><strong>The Voices of SpokenLayer,</strong><p>A new story has just been added to the queue.</p> <p>To claim the story for recording, please visit:<p><p>http://voices.spokenlayer.com/</p></body></html>';

// The S3 bucket
$config->amazon = new StdClass(); 
$config->amazon->bucket = "test.spokenlayer.com";
$config->amazon->bucketPath = "resource/";

// Scratch dir for dealing with files
$config->files = new StdClass();
$config->files->uploadDir = '/uploads/';

// Ivona
$config->ivona = new StdClass();
$config->ivona->saasUrl = 'http://www.ivona.com/api/saas/rest/'; 
$config->ivona->email = 'email=' . "will@spokenlayer.com";
$config->ivona->password = 'n2FQXiOkmKW8BkhU58wOe475PFurLGLN';      // Ivona calls this "api key"

//rake
$config->rake = new StdClass();
$config->rake->path = "/media/psf/Host/Volumes/Storage/Dropbox/Development/rake/pyrake/";
$config->rake->cmd = "python " . $config->rake->path . "rake.py -u"; 

//wget
$config->wget = new StdClass();
$config->wget->cmd = "wget";

?>
