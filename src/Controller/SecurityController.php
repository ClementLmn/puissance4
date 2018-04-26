<?php

namespace App\Controller;

use App\Entity\Player;
use App\Form\PlayerType;
use App\Form\RegisterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends Controller
{
    /**
     * @Template()
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {

        $player = new Player();

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(PlayerType::class, $player);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($lastUsername);
            die();

        }

        return [
            'last_username' => $lastUsername,
            'error' => $error,
            'form' => $form->createView(),
        ];

    }

    /**
     * @Template()
     * @Route("/register", name="register")
     */
    public function register(Request $request, AuthenticationUtils $authenticationUtils, UserPasswordEncoderInterface $passwordEncoder)
    {

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $player = new Player();

        $form = $this->createForm(RegisterType::class, $player);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);



            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Your are now registered !'
            );

            return $this->redirectToRoute('login');

        }

        return ['form' => $form->createView(), 'error' => $error, 'last_username' => $lastUsername];

    }


}
