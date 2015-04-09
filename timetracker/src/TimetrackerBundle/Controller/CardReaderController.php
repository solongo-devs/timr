<?php

namespace TimetrackerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use TimetrackerBundle\Entity\Log;
use TimetrackerBundle\Entity\Card;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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

		$response = new Response('');

		if (!$card)
		{      
	        $response->headers->set('X-Return', '5');
	        $card = new Card();
	        $card->setSignature($signature);
	        $em->persist($card);
		} else {
	    	$response->headers->set('X-Return', '2');		
		}

		$newLog = new Log;
		$newLog->setTime(new \DateTime('now'));
		$newLog->setCard($card);
		$newLog->setIsEdited(false);
		$em->persist($newLog);
		$em->flush();

	    return $response;
	}
}
