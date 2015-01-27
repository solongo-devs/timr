<?php

namespace TimetrackerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use TimetrackerBundle\Entity\Log;
use Symfony\Component\HttpFoundation\JsonResponse;

class CardReaderController extends Controller
{
	/**
	 * @Route("/api/cardreader")
	 * @Method("GET")
	 */
	public function storeAction(Request $request)
	{
		$signature = $request->query->get('id');

		$em = $this->getDoctrine()->getManager();
		$card = $em->getRepository('TimetrackerBundle:Card')->findOneBy(['signature' => $signature]);

		if (!$card)
		{
			return new JsonResponse(['success' => false], 200);
		}

		$newLog = new Log;
		$newLog->setTime(new \DateTime('now'));
		$newLog->setCard($card);
		$newLog->setIsEdited(false);
		$em->persist($newLog);
		$em->flush();

		return new JsonResponse(['success' => true], 200);
	}
}
