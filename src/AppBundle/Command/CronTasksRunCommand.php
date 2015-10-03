<?php namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;

/*
 * Credit: https://inuits.eu/blog/creating-automated-interval-based-cron-tasks-symfony2
 */

class CronTasksRunCommand extends ContainerAwareCommand
{

    private $output;

    protected function configure()
    {
        $this
            ->setName('crontasks:run')
            ->setDescription('Runs Cron Tasks if needed')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Email spooling initiated...</comment>');

        $this->output = $output;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $crontasks = $em->getRepository('AppBundle:CronTask')->findAll();

        foreach ($crontasks as $crontask) {
            // Get the last run time of this task, and calculate when it should run next
            $lastrun = $crontask->getLastRun() ? $crontask->getLastRun()->format('U') : 0;
            $nextrun = $lastrun + $crontask->getTaskInterval();

            // We must run this task if:
            // * time() is larger or equal to $nextrun
            $run = (time() >= $nextrun);

            if ($run) {
                $output->writeln(sprintf('Adding email to spool: <info>%s</info>', $crontask->getName()));
                //ToDo: use API instead
                $this->sendEmail($crontask->getName(), $this->emailMsgFactory($crontask->getName(), "using"));
                            
                // Set $lastrun for this crontask
                $crontask->setLastRun(new \DateTime());

                // Persist crontask
                $em->persist($crontask);
            } else {
                $output->writeln(sprintf('Skipping spooling of <info>%s</info>', $crontask->getName()));
            }
        }

        // Flush database changes
        $em->flush();

        $kernel = $this->getContainer()->get('kernel');
        $this->runCommand("swiftmailer:spool:send --env=" . $kernel->getEnvironment());
        $output->writeln('<comment>Done!</comment>');
    }

    private function runCommand($string)
    {
        // Split namespace and arguments
        $namespace = split(' ', $string)[0];

        // Set input
        $command = $this->getApplication()->find($namespace);
        $input = new StringInput($string);

        // Send all output to the console
        $returnCode = $command->run($input, $this->output);

        return $returnCode != 0;
    }

    private function sendEmail($to, $mailMsg)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Budapest Weather')
            ->setFrom(array("havelant.mate@gmail.com" => "Havelant Máté"))
            ->setTo(array($to => "Receiver"))
            ->setBody($mailMsg);

        echo "\n-----------------\n";
        echo $mailMsg;
        echo "\n-----------------\n";

        return $this->get('mailer')->send($message);
    }

    private function getCurrentTemperature($unit = "metric")
    {
        $units = "";

        if ($unit === "metric" || $unit === "imperial") {
            $units = "&units=" . $unit;
        }

        //List of city ID city.list.json.gz can be downloaded here http://bulk.openweathermap.org/sample/
        //$cityID from http://openweathermap.org/help/city_list.txt
        //Should automate this.. But won't.. For now..
        $cityID = "3054643";

        $jsonurl = "http://api.openweathermap.org/data/2.5/weather?id=" . $cityID . $units;
        $json = file_get_contents($jsonurl);

        //If everything goes wrong, we presume that the API is unavailable.
        $retData = array(
            "message" => "The openweathermap API is unavailable.",
            "code" => 503
        );

        if ($json !== FALSE) {
            $weather = json_decode($json);
            $retData["code"] = $weather->cod;
            //If we can get the file from the API ($json is not FALSE)
            //And the returned data is OK (code is 200)
            if ($retData["code"] === 200) {
                $retData["message"] = $weather->main->temp;
            } else {
                $retData["message"] = "Error!";
            }
        }

        return $retData;
    }

    private function emailMsgFactory($to, $why)
    {
        $currTemp = $this->getCurrentTemperature()["message"];
        $msg = "Hello, " . $to . "!\n"
            . "\nThank you for " . trim($why) . " the API."
            . "\nThe temperature in budapest is currently $currTemp degree celsius.";

        return $msg;
    }
}
