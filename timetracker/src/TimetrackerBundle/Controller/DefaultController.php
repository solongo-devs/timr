<?php

namespace TimetrackerBundle\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use TimetrackerBundle\Entity\Employee;
use TimetrackerBundle\Entity\Card;
use TimetrackerBundle\Entity\Log;
use Faker\Factory as Faker;

class DefaultController extends Controller
{
    public function uploadLogfileAction()
    {
    	return $this->render('TimetrackerBundle:Default:upload.html.twig');
    }

    /**
     * Just to quickly seed the DB
     */
    public function storeLogfileAction(Request $request)
    {
    	// Truncate tables
    	$em = $this->getDoctrine()->getManager();
		$connection = $em->getConnection();
		$tables = ['Note', 'Log', 'Card', 'Employee'];

		$query = 'SET foreign_key_checks = 0;';
		foreach($tables as $table) {
			$query .= 'TRUNCATE TABLE ' . $table . ';';
		}
		$connection->executeQuery($query);
		$em->flush();

		// Seed Tables
		$faker = Faker::create();

    	$csvLog = $request->files->get('log');
    	$csvLog = file_get_contents($csvLog->getPathName());
        $csvLog = explode("\n", $csvLog);

        $cards = [];
        $logs = [];
        foreach ($csvLog as $line)
        {
            $entry = str_getcsv($line, ';');
            $logs[] = $entry;

            if ( ! in_array($entry[1], $cards))
            {
            	$cards[] = $entry[1];
            }
		}

		foreach ($cards as $cardSignature)
		{
			$employee = new Employee;
			$employee->setFirstName($faker->firstName);
			$employee->setLastName($faker->lastName);
			$em->persist($employee);

			$card = new Card;
			$card->setSignature($cardSignature);
			$card->setEmployee($employee);
			$card->setIsActive(true);
			$em->persist($card);

			foreach ($logs as $log)
			{
				if ($log[1] == $cardSignature)
				{
					$date = DateTime::createFromFormat('d.m.Y H:i', $log[2]);

					$newLog = new Log;
					$newLog->setTime($date);
					$newLog->setCard($card);
					$em->persist($newLog);
				}
			}
		}

		$em->flush();

		return $this->redirect($this->generateUrl('employees_index'));
    }
}
