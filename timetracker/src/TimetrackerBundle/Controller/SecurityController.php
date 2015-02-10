<?php
namespace TimetrackerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;
use TimetrackerBundle\Entity\Employee;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        $session = $request->getSession();
		$has_error = 0;

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            #$error = $request->attributes->get(
            #    SecurityContext::AUTHENTICATION_ERROR
            #);
            $error = array("message" => "Falscher Benutzername / Passwort");
            $has_error = 1;
        }
        if ($request->attributes->has(SecurityContext::ACCESS_DENIED_ERROR)) {
            #$error = $request->attributes->get(
            #    SecurityContext::ACCESS_DENIED_ERROR
            #);
            $error = array("message" => "Keine Zugangsberechtigung");
            $has_error = 1;
        } 
        if( $has_error == 0 ) {
            $error = $session->get(SecurityContext::ACCESS_DENIED_ERROR);
            $session->remove(SecurityContext::ACCESS_DENIED_ERROR);
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render(
            'TimetrackerBundle:Security:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
                'msg'			=> ''
            )
        );
    }
    
    public function registerAction(Request $request) {

        $user = new Employee();
        
        $form = $this->CreateFormBuilder($user)
        	->add('id', 'hidden')
        	->add('firstname', 'text')
        	->add('lastname', 'text')
        	->add('username', 'text')
        	->add('email', 'text')
        	->add('password', 'repeated', array( 'type' => 'password', 'first_name' => 'password', 'second_name' => 'confirm', 'invalid_message' => 'the passwords must match' ))
        	->add('register', 'submit')
        	->getForm();
    
    	$form->handleRequest($request);
    	
    	if($form->isValid()) {
    		$em = $this->getDoctrine()->getManager();
			if( $user->getId() ) {
				$old_user = $em->getRepository('TimetrackerBundle:Employee')->find($user->getId());
			} else {
				$old_user = false;
			}

			# doesnt exist, use new object
			if (!$old_user) {
    			$encoder = $this->get('security.encoder_factory')->getEncoder($user);
				$hashed_password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
    			$user->setPassword($hashed_password);
    			
    			$em->persist($user);
    			$em->flush();
    		
    			return $this->redirect($this->generateUrl('login', array('msg' => 'Erfolgreich angemeldet, für eine Freischaltung bitte an einen Adinistrator wenden!')));
    		}
    	}
    
        return $this->render(
            'TimetrackerBundle:Security:register.html.twig',
            array('form' => $form->createView())
        );
    
    }
    
    public function newPasswordAction(Request $request) {

        $user = new Employee();
        
        $form = $this->CreateFormBuilder($user)
        	->add('email', 'text')
        	->add('send', 'submit')
        	->getForm();
    
    	$form->handleRequest($request);

		$error = '';

		if($form->isValid()) {
    		$em = $this->getDoctrine()->getManager();
			if( $user->getEmail() ) {
				$old_user = $em->getRepository('TimetrackerBundle:Employee')->findOneByEmail($user->getEmail());
				if( !$old_user ) {
					$error = 'Kein Benutzer mit dieser Email gefunden';
				} else {
    				$encoder = $this->get('security.encoder_factory')->getEncoder($old_user);
					$new_pass = substr(md5(uniqid()), 0, 8);
					$hashed_password = $encoder->encodePassword($new_pass, $old_user->getSalt());
    				$old_user->setPassword($hashed_password);
    			
    				$em->persist($old_user);
    				$em->flush();
					$error = 'Dein neues Passwort wurde an die eingegebene Email versandt';
					
					mail($old_user->getEmail(), 'Timr neues Passwort', 'Dein Passwort für timr wurde zurückgesetzt. Dein Benutzername ist \''.$old_user->getUserName().'\' und dein neues Passwort lautet: '.$new_pass);
				}
			}		
		}

        return $this->render(
            'TimetrackerBundle:Security:newpassword.html.twig',
            array('form' => $form->createView(), 'error' => $error)
        );
    
    }
}
?>