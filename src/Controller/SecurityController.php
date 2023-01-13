<?php

namespace App\Controller;

use App\Constant\CodeErreurConstant;
use App\Entity\User;
use App\Form\SendMailType;
use App\Manager\MailSecurityManager;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\ErrorMappingException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $superAdmin = $userRepository->findBy(['email' => 'admin@kh-immobilier.com']);
        if (!$superAdmin){
            $renewSuperAdmin = new User();
            $renewSuperAdmin->setRoles(['ROLE_SUPER_ADMIN']);
            $renewSuperAdmin->setEmail('admin@kh-immobilier.com');
            $password = $passwordHasher->hashPassword($renewSuperAdmin,'121090cb.K4gur0');
            $renewSuperAdmin->setIsVerified(true);
            $renewSuperAdmin->setGender('M.');
            $renewSuperAdmin->setLastName('BOUNGOU');
            $renewSuperAdmin->setFirstName('Christian');
            $renewSuperAdmin->setPassword($password);
            $em->persist($renewSuperAdmin);
            $em->flush();
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/auth-login-basic.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    /**
     * @Route("/send-mail/{code_situation}", name="app_send_mail")
     */
    public function sendMail(Request $request, UserRepository $userRepository, $code_situation, MailSecurityManager $mailSecurityManager)
    {
        $form = $this->createForm(SendMailType::class);
        $form->handleRequest($request);
        $error = null;
        if ($form->isSubmitted() && $form->isValid()){
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);
            if ($user === null)
            {
                $error = new ErrorMappingException('Cette email n\'existe pas dans notre base',CodeErreurConstant::EMAIL_NOT_FOUND);
            }else {
                $mailSecurityManager->manage($user, $code_situation);
                return $this->redirectToRoute('app_login');
            }
        }
        return $this->render('security/send-mail.html.twig',[
            'form' => $form->createView(),
            'error' => $error,
            'code_error' => $code_situation,
        ]);
    }

//    /**
//     * @Route("/forgot-password", name="app_forgot_password")
//     */
//    public function forgotPassword(Request $request, UserRepository $userRepository, EmailVerifier $emailVerifier)
//    {
//        $form = $this->createForm(SendMailType::class);
//        $form->handleRequest($request);
//        $error = null;
//        if ($form->isSubmitted() && $form->isValid()){
//            $email = $form->get('email')->getData();
//            $user = $userRepository->findOneBy(['email' => $email]);
//            if ($user === null)
//            {
//                $error = new ErrorMappingException('Cette email n\'existe pas dans notre base',CodeErreurConstant::EMAIL_NOT_FOUND);
//            }else{
//                $emailVerifier->sendEmailConfirmation('app_verify_email', $user,
//                    (new TemplatedEmail())
//                        ->from(new Address('admin@kh-immobilier.com', 'Kingd\'home Immobilier'))
//                        ->to($email)
//                        ->subject('Confirmation de votre Email')
//                        ->htmlTemplate('registration/forgot_password_email.html.twig'),
//                    $user->getPassword()
//                );
//                $this->addFlash('success', 'Un mail de réinitialisation de mot de passe vient d\'être envoyé à l\'adresse : <a href="#" target="_blank">' . $user->getEmail() . '</a>');
//                return $this->redirectToRoute('app_login');
//            }
//        }
//        return $this->render('security/send-mail.html.twig',[
//            'form' => $form->createView(),
//            'error' => $error,
//        ]);
//    }

}
