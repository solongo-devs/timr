<?php

namespace TimetrackerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use TimetrackerBundle\Entity\Employee;
use TimetrackerBundle\Entity\Log;
use TimetrackerBundle\Form\LogType;

/**
 * @Route("/employee/{employee}/log")
 */
class LogController extends Controller
{
	/**
	 * @Route("/new", name="log_new")
	 * @Method("GET")
	 */
	public function newAction(Employee $employee, Request $request)
	{
		$log = new Log;
		if ($request->query->has('time'))
		{
			$time = new \DateTime($request->query->get('time'));
			$log->setTime($time);
		}

		$form = $this->createForm(new LogType, $log);
		$form->add('card', 'entity', [
			'class' => 'TimetrackerBundle:Card',
			'choices' => $employee->getCards(),
			'property'=> 'signature'
		]);

		return $this->render('TimetrackerBundle:Log:new.html.twig', [
			'employee'	=> $employee,
			'time'		=> $time,
			'form'		=> $form->createView(),
		]);
	}

	/**
	 * @Route("/", name="log_store")
	 * @Method("POST")
	 */
	public function storeAction(Employee $employee, Request $request)
	{
		$log = new Log;
		$form = $this->createForm(new LogType, $log);
		$form->add('card', 'entity', [
			'class' => 'TimetrackerBundle:Card',
			'choices' => $employee->getCards(),
			'property' => 'signature',
		]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid() )
		{
			$em = $this->getDoctrine()->getManager();
			$log->setIsEdited(false);
			$em->persist($log);
			$em->flush();

			return $this->redirect($this->generateUrl('calendar_show', $this->getLogDateArray($log) ));
		}

		return $this->render('TimetrackerBundle:Log:new.html.twig', [
			'form' => $form->createView(),
		]);
	}

	/**
	 * @Route("/{log}/edit", name="log_edit")
	 */
	public function editAction(Log $log, Employee $employee)
	{
		$edit_form = $this->createForm(new LogType(), $log);
		$delete_form = $this->createDeleteForm($log, $employee);

		return $this->render('TimetrackerBundle:Log:edit.html.twig', [
			'employee'	=> $employee,
			'log'		=> $log,
			'edit_form'	=> $edit_form->createView(),
			'delete_form' => $delete_form->createView(),
		]);
	}

	/**
	 * @Route("/{log}/update", name="log_update")
	 */
	public function updateAction(Log $log, Employee $employee, Request $request)
	{
		$form = $this->createForm(new LogType(), $log);
		$form->handleRequest($request);

		if ($form->isValid())
		{
			$em = $this->getDoctrine()->getManager();
			$em->flush();

			return $this->redirect($this->generateUrl('calendar_show', $this->getLogDateArray($log) ));
		}

		return $this->render('TimetrackerBundle:Log:edit.html.twig', [
			'log'		=> $log,
			'edit_form' => $form->createView(),
		]);
	}

	/**
	 * @Route("/{log}/delete", name="log_delete")
	 * @Method("DELETE")
	 */
	public function deleteAction(Log $log, Employee $employee, Request $request)
	{
		$form = $this->createDeleteForm($log, $employee);
		$form->handleRequest($request);

		$redirectData = $this->getLogDateArray($log);

		if ($form->isSubmitted() && $form->isValid())
		{
			$em = $this->getDoctrine()->getManager();
			$em->remove($log);
			$em->flush();
		}

		return $this->redirect($this->generateUrl('calendar_show', $redirectData));
	}

	public function getLogDateArray(Log $log)
	{
		return [
			'employee'	=> $log->getEmployee()->getId(),
			'year'		=> $log->getTime()->format('Y'),
			'month'		=> $log->getTime()->format('m'),
			'day'       => $log->getTime()->format('d')
		];
	}

	/*=============================
	=            Forms            =
	=============================*/
	
	public function createDeleteForm(Log $log, Employee $employee)
	{
		return $this->createFormBuilder()
			->setAction( $this->generateUrl('log_delete', ['log' => $log->getId(), 'employee' => $employee->getId()]) )
			->setMethod('DELETE')
			->getForm();
	}
}