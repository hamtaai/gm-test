##General:    
* The application was written in NetBeans 8.0.2 IDE, with PSR-2 coding standard.    
 - Coding standard settings imported from https://github.com/bobsta63/netbeans-psr-formatting    
* It's built on the Symfony 2.7.5 framework.    
* Dev. environment:     
 - Windows7 64bit    
 - Apache 2.4.9 (Win64) [Installed from WAMP]    
 - PHP 5.5.12 [Installed from WAMP]    
 - MySQL Community Server (GPL) 5.6.17 [Installed from WAMP]    

##Installation:    
###Prerequisites   
* The project needs ~60 MB of storage space.
* The application requires WAMP/LAMP to be installed and running.     
* You also need composer.    
* For testing I used PHPUnit.

###Installation    
When everything is up and running, follow these steps [read first, act later!]:    
 1. Download and extract/clone the repo to the desirable folder.    
 2. Open terminal/command line and navigate to the project folder.    
 3. Use mysql -u [user] -p[pass] < Create_Database.sql to prepare the database and tables.    
     * Note: no space is needed between -p and your password.
 4. Execute the *composer install* command. This will download the dependencies. During the installation, you'll be asked to fill out database and mailer information: 
     * The database name should be GMTest
     * For other informations (database host, etc.) use your own settings.
     * For switmailer you can use your own information or my settings from *Sending information*.

##Starting the application:    
* Navigate to the project folder in the terminal (linux) or command line (windows) and excute the command:    
    php app/console server:run     
* Now you can use the api. Base path is 127.0.0.1/api/{rest_function_name}.    
* Replace {rest_function_name} in your request with the needed function.    

##API function list:    
1. *current_temperature*    
   * Method: GET    
   * Description: Returns the current temperature (in celsius) in Budapest.    
    
2. *email_temperature*    
   * Method: POST    
   * Description: Sends the current temperature to the specified email address.    
   * Data type: json    
   * Data format: {"to": "email of the recipient"}    
    
3. *current_temperature*    
   * Method: GET    
   * Description: Send the current temperature to the specified email address in every hour.    
   * Data type: json    
   * Data format: {"to": "email of the subscriber"}    

##Using cURL to send requests to the API:        
* **Get**: *curl -i -H "Accept: application/json" http://localhost:8000/api/current_temperature*    
* **Send**: *curl -i -H "Content-Type:application/json" -X POST http://localhost:8000/api/email_temperature -d '{"to":"havelant.mate@gmail.com"}'*    
* **Subscribe**: *curl -i -H "Content-Type:application/json" -X POST http://localhost:8000/api/subscribe_temperature -d '{"to":"havelant.mate@gmail.com"}'*    

##Sending information:    
* Sending emails is achieved with SwiftMail.    
* I use Mandrill as my mail server.    
* My settings    
 - mailer_transport: smtp    
 - mailer_host: smtp.mandrillapp.com    
 - mailer_user: havelant.mate@gmail.com    
 - mailer_password: dfMdvig0VJkYl-EVxNFhPQ    
 - mailer_port: 587    
    
###Manual sending:    
 - I defined a command in the project: crontasks:run    
 - In the terminal (linux) or command line (windows) go to the project folder, and execute: php app/console crontasks:run    
 - If the email for the subscriber is expected to be sent, the the command will send it. Otherwise the email is skipped.    

###Automate sending    
 - When using the 'email' or 'subscribe' methods, the recipient automatically gets a response mail.    
 - On Linux, add crontasks:run as a Cron job on, that gets executed once every hour. ( See http://www.adminschoice.com/crontab-quick-reference )    
 - On Windows, add crontasks:run as a Scheduled Task. ( See http://windows.microsoft.com/en-au/windows/schedule-task#1TC=windows-7 )    
   - Note: For Windows, you need to use advanced options (in the wizard, if available, or by editing the created task) to be able to run it hourly.    


###Automatic tests    
* All three functions (are going to) have unit tests.
* Navigate to the projectfolder, and execute *php phpunit.phar -c app* to run all tests.
* Execute *php phpunit.phar -c app --coverage-html chtml* to also get the coverage analysis of the tests.
* The coverage is generated to ../ProjectFolder/chtml and you can view it by opening the index.html in your browser.
* Note: Since some parts of the code require the used weather API to be unavailable, 100% coverage is unlikely.

##Other notes    
* I wrote the project in symfony, because i'm the most comfortable with this framework. If required, I will rewrite it in Laravel.    
* The CronTaskRunCommand class needs heavy refactoring. I just copypasted code from the API controller, to speed things up.    
* Having those 2 tables in the database might also be redundant.    
* ToDo: Use better and more exception handling.
