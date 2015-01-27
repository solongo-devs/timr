<?php

namespace TimetrackerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TimetrackerBundle\Entity\Log;
use Symfony\Component\HttpFoundation\JsonResponse;

class CardReaderController extends Controller
{
	public function storeAction($card)
	{
		$em = $this->getDoctrine()->getManager();
		$card = $em->getRepository('TimetrackerBundle:Card')->findOneBy(['signature' => $card]);

		if (!$card)
		{
			throw new \Exception('No card found');
		}

		$newLog = new Log;
		$newLog->setTime(new \DateTime('now'));
		$newLog->setCard($card);
		$em->persist($newLog);
		$em->flush();

		return new JsonResponse(['success' => true], 200);
	}
}
