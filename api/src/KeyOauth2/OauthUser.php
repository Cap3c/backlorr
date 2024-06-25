<?php
# src/Controller/FortyTwoController.php

namespace App\KeyOauth2;

use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\Core\OpenApi\OpenApi;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\Organisme;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\OrganismeRepository;

class OauthUser extends AbstractController
{
    public function __construct(
        private UserRepository $userR,
        private OrganismeRepository $orgaR
    )
    {
        parent::__construct();
    }

    public function test()
    {
        dd("asd");
    }

}
