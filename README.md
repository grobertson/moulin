le moulin (The Mill)
======

*(This complete but abandoned project may serve as a starting point for anyone wishing to create a similar job server)*

Experimental Gearman based daemon to run asynchronous tasks and receive task output asynchronously. 

Originally developed to handle asyncronous audio processing jobs with work payloads in the range of 2MB to 200MB and
to make easy work of modular, pluggable processing steps with some branch execution


Features:
  - Modular job logic, jobs are auto-detected and added to the list of jobs to check on each synchronous loop.
  - Modular worker logic. Workers function are automatically registered if enabled. 
  - Per job configuration with simple ini files
  - Abstract model for jobs and tasks makes creating a new job as simple as writing a few lines of code.
  - upstart compatible (more config tools planned)
  - Built in notification tools can send emails and sms messages. PostmarkApp and Tilow currently supported.

Future ideas (*abandonded*):
  - Async handling of worker output (Client -> gearman -> worker -> gearman -> client) - Never block waiting for output again
  - Built in model management/sync for jobs with their own data model
  - Generic file handling (ftp, wwww, S3 upload and download)
  - Inteligent throttling
  - Job intake api, job finished callbacks to arbitrary endpoints
  - Automatic worker daemon deployment with AWS compatible infrastructure and spot-instance handling/bidding
  - Web & cli management interfaces
  - Multi-headed, truly distributed task marshalling with high-tollerance for transient (spot pricing) worker daemons


