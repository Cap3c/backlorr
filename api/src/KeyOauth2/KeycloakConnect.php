<?php

namespace App\KeyOauth2;

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
use Symfony\Component\HttpKernel\Exception\HttpException;

class KeycloakConnect extends OAuth2Authenticator implements AuthenticationEntrypointInterface
{
    private $clientRegistry;
    private $entityManager;
    private $router;
    private string $redirect = "";

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE (config/packages/knpu...)
        return $request->attributes->get('_route') === 'keycloak_check';
    }

    public function authenticate(Request $request): Passport
    {
        #dd($request);

        $client = $this->clientRegistry->getClient('Keycloak');
        #dd($client);
        $accessToken = $this->fetchAccessToken($client);
        #dd($accessToken->getToken());

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                /** @var LorrUser $loorUser */
                $loorUser = $client->fetchUserFromToken($accessToken);

                if (!($email = $loorUser->getEmail()))
                        throw new HttpException(403, "email is empty");

                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $loorUser->getEmail()]);
                if ($existingUser) {
                    return $existingUser;
                }
                if (!$loorUser->getRole())
                    throw new HttpException(403, "loorrole is empty");
                $orga = $this->entityManager->getRepository(Organisme::class)->findOneBy(['name' => $loorUser->getOrganisme()]);

                if ($loorUser->getRole() == "admin")
                {
                    if ($orga)
                        throw new HttpException(403, "this organism already exist");
                    $orga = new Organisme();
                    $orga->setName($loorUser->getOrganisme());
                    $this->entityManager->getRepository(Organisme::class)->save($orga);
                    $user = new User();
                    $user->fillAdmin($loorUser->getEmail(), "", $loorUser->getName(), $orga);
                    $this->entityManager->getRepository(User::class)->save($user, true);
                }
                else
                {
                    if (!$orga)
                        throw new HttpException(403, "this organism didn't exist");
                    $user = new User();
                    if ($loorUser->getOrganisme() != "cap3c")
                        $user->setRoles(["ROLE_orga_user"]);
                    else //cap3c
                    {
                        if (in_array("support", $data->getRoles()))
                            $user->setRoles(["ROLE_cap3c_support_tech"]);
                        else if (in_array("etude", $data->getRoles()))
                            $user->setRoles(["ROLE_cap3c_R&D"]);
                        else
                            throw new HttpException(403, "you need to define role between 'support' and 'etude'");
                    }
                    $user->setPassword("");
                    $user->setName($loorUser->getName());
                    $user->setEmail($email);
                    $user->setOrganisme($orga);
                    $this->entityManager->getRepository(User::class)->save($user, true);
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($this->redirect) {
            $path = $this->redirect;
            return new RedirectResponse($path, 307);
        }

        return new RedirectResponse("/users");

        return (null);
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
            '/koauth2/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
