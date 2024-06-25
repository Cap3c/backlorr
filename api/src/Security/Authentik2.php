<?php

namespace App\Security;

use App\Entity\User; // your user entity
use App\Entity\Organisme;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;

class Authentik2 extends OAuth2Authenticator implements AuthenticationEntrypointInterface
{
    private $clientRegistry;
    private $entityManager;
    private $router;
    private string $redirect = "";
    private $payload = NULL;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE (config/packages/knpu...)

        #dd($request->headers);
        return ($request->headers->has('authorization'));
        return $request->attributes->get('_route') === 'authentik_check';
    }


    public function getOrganisme(): string
    {
        foreach ($this->payload->groups as $role) {
            if (!strncmp("orga_",$role,5))
                return ($this->organisme = substr($role, 5));
        }
        dd("asd");
    }

    public function getRole(): string
    {
        foreach ($this->payload->groups as $role) {
            if (!strncmp("role_",$role,5))
                return ($this->organisme = substr($role, 5));
        }
        dd("asd");
    }

    public function getName(): string
    {
        return $this->payload->name;
    }

    public function getEmail(): string
    {
        return $this->payload->email;
    }

    public function userByMail(): User
    {

        if ($existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $this->getEmail()])) {
            return $existingUser;
        }
        #$existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $loorUser->getEmail()]);
        $orga = $this->entityManager->getRepository(Organisme::class)->findOneBy(['name' => $this->getOrganisme()]);
        if (!$orga)
        {
            $orga = new Organisme();
            $orga->setName($loorUser->getOrganisme());
            $this->entityManager->getRepository(Organisme::class)->save($orga);
        }
        $user = new User();
        $user->fillAdmin($loorUser->getEmail(), "", $this->getName(), $orga);
        $this->entityManager->getRepository(User::class)->save($user, true);

        #dd($user);
        return $user;
    }
    public function authenticate(Request $request): Passport
    {
        $accessToken = $request->headers->get('authorization');
        dump( $accessToken);

$tokenParts = explode(".", $accessToken);
$tokenHeader = base64_decode($tokenParts[0]);
$tokenPayload = base64_decode($tokenParts[1]);
$jwtHeader = json_decode($tokenHeader);
$this->payload = json_decode($tokenPayload);
$payload = json_decode($tokenPayload);
dump($jwtHeader);
dd($payload);
#dd($payload->name);

        #dump($request);
        #if (strpos($request->getRequestUri(),"/oauth2/login/authentik/") == true)
        #    dd("asd");

        #$client = $this->clientRegistry->getClient('authentik');
        #dd($client);
        #$accessToken = $this->fetchAccessToken($client);
        #dd($accessToken);

        return new SelfValidatingPassport(
            new UserBadge($this->getEmail(), function() {
                #dd($this->userByMail());
                return $this->userByMail();
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return (null);
        if ($this->redirect) {
            $path = $this->redirect;
            return new RedirectResponse($path, 307);
        }

        return new RedirectResponse("/login");

        dump($token);
        dd("asd");
        $a = new AuthenticationSuccessHandler();

        return new JWTAuthenticationSuccessResponse($token);
        dump($firewallName);
        dump($request);
        return (null);
        #return new Response("ok", 200);
        dd("asd");
        // change "app_homepage" to some route in your app
        $targetUrl = $this->router->generate('app_homepage');

        return new RedirectResponse($targetUrl);

        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

   /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $path = $request->getPathInfo();
        $this->redirect = $path;

        return new RedirectResponse(
            '/oauth2/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
