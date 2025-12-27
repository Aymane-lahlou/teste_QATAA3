<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Rediriger si déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // Rediriger si déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $client = new Client();
        $form = $this->createForm(RegistrationFormType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si l'email existe déjà
            $existingClient = $entityManager->getRepository(Client::class)->findOneBy(['email' => $client->getEmail()]);
            if ($existingClient) {
                $this->addFlash('error', 'Cet email est déjà utilisé. Veuillez vous connecter ou utiliser un autre email.');
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            // Hasher le mot de passe
            $client->setMotDePasse(
                $passwordHasher->hashPassword(
                    $client,
                    $form->get('plainPassword')->getData()
                )
            );

            // Définir le rôle par défaut
            $client->setRoles(['ROLE_CLIENT']);
            $client->setStatutCompte('actif');

            // Générer un token de vérification email
            $client->setEmailVerificationToken(bin2hex(random_bytes(32)));

            $entityManager->persist($client);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte a été créé avec succès ! Veuillez vérifier votre email.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $submitted = false;
        $email = '';

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $submitted = true;

            $client = $entityManager->getRepository(Client::class)->findOneBy(['email' => $email]);

            if ($client) {
                // Générer un token de réinitialisation
                $resetToken = bin2hex(random_bytes(32));
                $client->setPasswordResetToken($resetToken);
                $client->setPasswordResetRequestedAt(new \DateTimeImmutable());
                $entityManager->flush();

                // Générer le lien de réinitialisation
                $resetLink = $this->generateUrl(
                    'app_reset_password',
                    ['token' => $resetToken],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                // Créer et envoyer l'email
                $emailMessage = (new Email())
                    ->from('noreply@qataa3.ma')
                    ->to($client->getEmail())
                    ->subject('Réinitialisation de votre mot de passe - Qataa3')
                    ->html($this->renderView('emails/password_reset.html.twig', [
                        'client' => $client,
                        'reset_link' => $resetLink,
                        'token' => $resetToken,
                    ]));

                try {
                    $mailer->send($emailMessage);
                    $this->addFlash('success', 'Un email de réinitialisation a été envoyé à votre adresse.');
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Email envoyé (mode test). Vérifiez la console pour le lien.');
                }
            } else {
                // Ne pas révéler si l'email existe (sécurité)
                $this->addFlash('success', 'Si ce compte existe, un email de réinitialisation sera envoyé.');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig', [
            'submitted' => $submitted,
            'email' => $email,
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $client = $entityManager->getRepository(Client::class)->findOneBy(['passwordResetToken' => $token]);

        if (!$client) {
            $this->addFlash('error', 'Lien de réinitialisation invalide ou expiré.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que le token n'a pas expiré (24 heures)
        if ($client->getPasswordResetRequestedAt() < new \DateTimeImmutable('-24 hours')) {
            $this->addFlash('error', 'Ce lien de réinitialisation a expiré.');
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');

            if (strlen($newPassword) < 8) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères.');
            } elseif ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            } else {
                $client->setMotDePasse($passwordHasher->hashPassword($client, $newPassword));
                $client->setPasswordResetToken(null);
                $client->setPasswordResetRequestedAt(null);
                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/reset_password.html.twig', [
            'token' => $token,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
