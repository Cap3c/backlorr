<?php
// api/src/Controller/CreateBaseUploaderAction.php

namespace App\Controller;

use App\Entity\BaseUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\DescRepository;
use Shuchkin\SimpleXLSX;

#[AsController]
final class CreateBaseUploaderAction extends AbstractController
{
    public function __construct(private Security $security, private DescRepository $descR)
    {}

    public function __invoke(Request $request): BaseUploader
    {
        $this->denyAccessUnlessGranted("ROLE_CREATE_BASE");
        #$role = $this->security->getUser()->getRoles()[0];
        #if (!($role == "ROLE_orga_admin" || $role == "ROLE_cap3cR&D"))
        #    throw new BadRequestHttpException("user can't send base");
        $user = $this->security->getUser();

        $uploadedFile = $request->files->get('file');
        #dd($request);
        if (!$uploadedFile)
            throw new BadRequestHttpException('"file" is required');


        if (!($xlsx = SimpleXLSX::parse($uploadedFile)))
            dd(SimpleXLSX::parseError());
        #foreach ($xlsx->rows() as $r)
        #     print_r( $r );
        $baseUploader = new BaseUploader();
        $baseUploader->file = $uploadedFile;
        $baseUploader->setProprietaire($this->security->getUser());

        return $baseUploader;
    }
}
