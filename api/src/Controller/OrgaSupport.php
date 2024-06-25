<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Repository\OrganismeRepository;
use App\Repository\UserRepository;

/*
 *  
 *  permet aux user ROLE_cap3c_support_tech de changer d'organisme
 *  et ainsi de faire des operations pour d'autre organisme
 *
*/

class OrgaSupport extends AbstractController
{
    #[Route("/support/{id_orga}", name:"supportOrganisme", methods:["POST"])]
     
    public function supportOrganisme(int $id_orga, OrganismeRepository $orgaR, UserRepository $userR)
    {
        if (!$this->getUser())
            return NULL;
        $this->denyAccessUnlessGranted('ROLE_cap3c_support_tech');
        #if ($this->getUser()->getRoles()[0] != "ROLE_cap3c_support_tech")
        #    throw new HttpException(404, "not found");
        $orga = $orgaR->find($id_orga);
        if (!$orga)
            throw new HttpException(404, "organisme not found");
        $this->getUser()->setOrganisme($orga); 

        $userR->save($this->getUser(), true);
        return $this->json([
            "id" => $orga->getId(),
            "name" => $orga->getname(),
        ]);

    }
}
