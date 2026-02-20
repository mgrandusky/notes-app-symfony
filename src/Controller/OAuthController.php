<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class OAuthController extends AbstractController
{
    public function connectGoogle(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry->getClient('google')->redirect(['email', 'profile']);
    }

    public function connectGoogleCheck(): void
    {
        // Handled by GoogleAuthenticator
    }

    public function connectGithub(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry->getClient('github')->redirect(['user:email']);
    }

    public function connectGithubCheck(): void
    {
        // Handled by GithubAuthenticator
    }
}
