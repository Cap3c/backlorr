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

class VerifEmail extends OAuth2Authenticator implements AuthenticationEntrypointInterface
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

        #dd($request->headers);
        return ($request->headers->has('x-grafana-user'));
    }

    public function authenticate(Request $request): Passport
    {

        $email = $request->headers->get('x-grafana-user');
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        #dd( $accessToken);

        dump($email);
        //dd($user->getId());
        return new SelfValidatingPassport(
            new UserBadge("", function() use ($user) {
                return $user;
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
            '/koauth2/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
