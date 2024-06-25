<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Exception\HttpException;

/*
 *  return les informations de l'individu connecter
*/
class SecurityC extends AbstractController
{
    #[Route('/login', name: 'app_login', methods:["GET"])]
    public function login()
    {
        dd($this->getUser());
            throw new HttpException(418, "you are not connected");
        if (!$this->getUser())
            throw new HttpException(400, "you are not connected");
        return $this->json([
            #'user' => $this->security->getUser() ? $this->security->getUser()->getId() : null]
            'user' => $this->getUser()->getId(),
            'role' => $this->getUser()->getRoles(),
            'orga' => $this->getUser()->getOrganisme()->getName(),
        ]);
    }
}
