<?php

namespace App\Controller\Visitor\Registration;

use DateInterval;
use App\Entity\User;
use DateTimeImmutable;
use App\Service\SendEmailService;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'visitor.registration.register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager,
        TokenGeneratorInterface $tokenGenerator,
        SendEmailService $sendEmailService  
    ): Response
    {
        if ($this->getUser()) {
           return $this->redirectToRoute('visitor.welcome.index');
        }
        
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
         {
            // generate deadLineForEmailVerification
            $deadline =(new DateTimeImmutable('now'))->add(new DateInterval('P1D'));
            $user->setDeadLineForEmailVerification($deadline);
            // generate tokenForEmailVerification
            $tokenGenerated = $tokenGenerator->generateToken();
            $user->setTokenForEmailVerification($tokenGenerated);
            // hash the password
            $user->setPassword($userPasswordHasher->hashPassword($user,$form->get('password')->getData()
                )
            );

            // insert new user table in the database 
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            $sendEmailService->send([
                "sender_email"   => "souley_chaabane@gmail.com",
                "sender_name"    => "Souleiman Chaabane",
                "recipient_email"=> $user->getEmail(),
                "subject"        => "Vérification de votre compte sur le site blog foot.",
                "html_template"  =>"email/email_verification.html.twig",
                "context"        =>[
                    "user_id"    => $user->getId(),
                    "token_for_email_verification" => $user->getTokenForEmailVerification(),
                    "deadline_for_email_verification" => $user->getDeadlineForEmailVerification()->format('d/m/Y à H:i:s'),
                    
                ]
            ]);

            return $this->redirectToRoute('visitor.registration.waiting_for_email_verification');
        }

        return $this->render('pages/visitor/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route('/inscription/en-attente-de-la-verification-du-compte-par-email ', name :'visitor.registration.waiting_for_email_verification')]
    public function waiting_for_email_verification()
    {
        return $this->render("pages/visitor/registration/waiting_for_email_verification.html.twig");
    }
    #[Route('/inscription/verification-du-compte-par-email/{id}/{token} ', name :'visitor.registration.email_verification')]
    public function email_verification(User $user, $token, UserRepository $userRepository)
    {
        if (! $user)
         {
            throw new AccessDeniedException();
        }
        if ($user->getIsVerified()) 
        {
            $this->addFlash("warning","Votre compte est déja vérifié.");
            return $this->redirectToRoute('visitor.welcome.index');
        }
        if (empty($token)|| ($user->getTokenForEmailVerification() == null ) || ($token
        !== $user->getTokenForEmailVerification()) )
         {
            throw new AccessDeniedException();
        }
        if (new \DateTimeImmutable('now') > $user->getDeadLineForEmailVerification())
         {
            $deadline = $user->getDeadLineForEmailVerification();
            $userRepository->remove($user, true );
            throw new CustomUserMessageAccountStatusException("Votre délai de vérification du compte a expiré le : 
            $deadline! Veuillez créer un nouveau compte.");
        }

        $user->setIsVerified(true);
        $user->setTokenForEmailVerification('');
        $user->setVerifiedAt(new \DateTimeImmutable('now'));

        $userRepository->add($user, true);

        $this->addFlash("success","Votre compte est activé, veuillez vous connecter!");
        return $this->redirectToRoute('visitor.welcome.index');

    }
}
