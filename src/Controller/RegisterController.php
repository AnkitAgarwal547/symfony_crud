<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegirtrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Security\LoginAuthenticationAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     */
    public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginAuthenticationAuthenticator $authenticator): Response
    {   
        if ($this->getUser()) {
            return $this->redirectToRoute('post');
        }
        $user = new User();
        $form = $this->createForm(RegirtrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            // return $this->json(['data' => $request->request->all(), 'title' => $request->get('title')]);
            $user->setRoles(['ROLE_USER']);
            // $user->setTitle($request->get('title'));
            $user->setCreated(new \DateTime());
            // dump($user);
            // die;
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
            return $this->redirectToRoute('/');
        }

        return $this->render('register/index.html.twig', [
            'registrationForm' => $form->createView(),
            'error' => '',
            'message' => ''
        ]);
    }
}
