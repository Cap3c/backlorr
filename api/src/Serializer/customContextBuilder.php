<?php
// api/src/Serializer/BookContextBuilder.php
namespace App\Serializer;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
#use App\Entity\Book;

final class customContextBuilder implements SerializerContextBuilderInterface
{
    private $decorated;
    private $authorizationChecker;

    public function __construct(SerializerContextBuilderInterface $decorated, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        $context['groups'] = $context['groups'] ?? [];
        #dd($context);
        #dump($context['groups']);
        if($this->authorizationChecker->isGranted('ROLE_orga_admin'))
            $context['groups'][] = "admin:".($normalization ? 'read' : 'write');
        else if($this->authorizationChecker->isGranted('ROLE_orga_user'))
            $context['groups'][] = "member:".($normalization ? 'read' : 'write');
        else if($this->authorizationChecker->isGranted('ROLE_cap3c_support_tech'))
        #    $context['groups'][] = "support:".($normalization ? 'read' : 'write');
            $context['groups'][] = "tech:".($normalization ? 'read' : 'write');
        else if($this->authorizationChecker->isGranted('ROLE_cap3c_R&D'))
            $context['groups'][] = "etude:".($normalization ? 'read' : 'write');
        if($this->authorizationChecker->isGranted('ROLE_USER'))
            $context['groups'][] = "individual:".($normalization ? 'read' : 'write');
        //make swicht
        #dump($context['groups']);

        return $context;
    }
}
