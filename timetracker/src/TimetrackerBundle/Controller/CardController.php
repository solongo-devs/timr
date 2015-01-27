<?php

namespace TimetrackerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use TimetrackerBundle\Entity\Card;
use TimetrackerBundle\Form\CardType;

/**
 * @Route("/card")
 */
class CardController extends Controller
{
	/**
	 * @Route("/", name="card")
	 * @Method("GET")
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();
		$cards = $em->getRepository('TimetrackerBundle:Card')->findAll();

		return $this->render('TimetrackerBundle:Card:index.html.twig', compact('cards'));
	}

	/**
	 * @Route("/new", name="card_new")
	 * @Method("GET")
	 */
	public function newAction()
	{
		$card = new Card;
		$form = $this->createForm(new CardType, $card);

		return $this->render('TimetrackerBundle:Card:new.html.twig', [
			'form'	=> $form->createView(),
		]);
	}

	/**
	 * @Route("/", name="card_store")
	 * @Method("POST")
	 */
	public function storeAction(Request $request)
	{
		$card = new Card;
		$form = $this->createForm(new CardType, $card);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$em = $this->getDoctrine()->getManager();
			$em->persist($card);
			$em->flush();

			return $this->redirect($this->generateUrl('card'));
		}

		return $this->render('TimetrackerBundle:Card:new.html.twig', [
			'form'	=> $form->createView(),
		]);
	}

	/**
	 * @Route("/{card}/edit", name="card_edit")
	 * @Method("GET")
	 */
	public function editAction(Card $card)
	{
		$edit_form = $this->createForm(new CardType, $card);
		$delete_form = $this->createDeleteForm($card);

		return $this->render('TimetrackerBundle:Card:edit.html.twig', [
			'card'	=> $card,
			'edit_form'   => $edit_form->createView(),
			'delete_form' => $delete_form->createView(),
		]);
	}

	/**
	 * @Route("/{card}", name="card_update")
	 * @Method("POST")
	 */
	public function updateAction(Card $card, Request $request)
	{
		$form = $this->createForm(new CardType, $card);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$em = $this->getDoctrine()->getManager();
			$em->persist($card);
			$em->flush();

			return $this->redirect($this->generateUrl('card'));
		}

		return $this->render('TimetrackerBundle:Card:edit.html.twig', [
			'card'	=> $card,
			'form'	=> $form->createView(),
		]);
	}

	/**
	 * @Route("/{card}/delete", name="card_delete")
	 * @Method("DELETE")
	 */
	public function deleteAction(Card $card, Request $request)
	{
		$form = $this->createDeleteForm($card);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($card);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('card'));
	}

	public function createDeleteForm(Card $card)
	{
		return $this->createFormBuilder()
            ->setAction($this->generateUrl('card_delete', ['card' => $card->getSignature()]))
            ->setMethod('DELETE')
            ->getForm();
	}
}