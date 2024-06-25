<?php

//change doamin name
namespace App\Oauth2;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Oauth2\ResourceOwner;

use App\Entity\Organisme;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\OrganismeRepository;

class Authentik extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected $BASE_URL = 'http://192.168.1.70:9000';

    public function getBaseAuthorizationUrl(): string
    {
        return $this->BASE_URL . "/application/o/authorize/";
    }

    /**
     * @param array<string, mixed> $params
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->BASE_URL . "/application/o/token/";
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->BASE_URL . "/application/o/userinfo/";
    }


    /**
     * @return string[]
     */
    protected function getDefaultScopes(): array
    {
        return ["openid profile email"];
        #return ['public_profile', 'email'];
    }

        /**
     * @param array<string, mixed>|string $data
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() !== 200) {
            $errorDescription = '';
            $error = '';
            if (\is_array($data) && !empty($data)) {
                $errorDescription = $data['error_description'] ?? $data['message'];
                $error = $data['error'];
            }
        #dd($response);
            throw new HttpException(
                $response->getStatusCode(),
                sprintf("%d - %s: %s", $response->getStatusCode(), $error, $errorDescription)
                #$data
            );
        }
    }

    /**
     * @param array<string, mixed> $response
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwner
    {
        #$test = new User();
        #dump($test);
        #dump($response);
        #dd($token);
        return new ResourceOwner($response);
    }

}

