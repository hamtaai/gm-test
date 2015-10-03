gm-test
=======

Notes:
* Written in NetBeans 8.0.2 IDE, with PSR-2 coding standard.
* Coding standard settings imported from https://github.com/bobsta63/netbeans-psr-formatting
* Environment: 
 - Windows7 64bit
 - Apache 2.4.9 (Win64) [Installed from WAMP]
 - PHP 5.5.12 [Installed from WAMP]
 - MySQL Community Server (GPL) 5.6.17 [Installed from WAMP]

Emails:
* Sending emails is achieved with SwiftMail.
* Usage:
 - Send the request to the API
 - Use this command in the command-line:    php app/console swiftmailer:spool:send --env=dev

* Notes
 - I use Mandrill as my mail server.
 - mailer_transport: smtp
 - mailer_host: smtp.mandrillapp.com
 - mailer_port: 587
 - mailer_user: havelant.mate@gmail.com
 - mailer_password: dfMdvig0VJkYl-EVxNFhPQ

Tests with cURL:
    curl -i -H "Accept: application/json" http://localhost:8000/api/current_temperature
    curl -i -H "Content-Type:application/json" -X POST http://localhost:8000/api/email_temperature -d '{"to":"havelant.mate@gmail.com"}'

