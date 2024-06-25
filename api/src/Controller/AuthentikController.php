<?php
# src/Controller/FortyTwoController.php

namespace App\Controller;

use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\Core\OpenApi\OpenApi;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuthentikController extends AbstractController
{

    #[Route('/oauth2', name: 'auth_authentik_start')]
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        dump("asd");
        return $clientRegistry
            ->getClient('authentik')//config/package/knu..
            ->redirect([], []);
    }

    #[Route('/oauth2/login/authentik/check', name: 'authentik_check')]
    public function connectCheck(Request $request, ClientRegistry $clientRegistry): void
    {
        throw new \LogicException("This should be caught by the guard authenticator");
    #    return ;
    }
}
