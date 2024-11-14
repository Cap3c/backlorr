<?php
namespace App\Controller;


use Symfony\Component\HttpClient\HttpClient;
use BenjaminFavre\OAuthHttpClient\OAuthHttpClient;
use BenjaminFavre\OAuthHttpClient\GrantType\ClientCredentialsGrantType;

class KeycloakUser
{
    public function __construct(
    ) {
    }

    function sendUser($user)
    {
        $httpClient = HttpClient::create();

        $grantType = new ClientCredentialsGrantType(
            $httpClient,
            'http://192.168.1.70:7080/realms/lorr/protocol/openid-connect/token',
            'admin-cli',
            '6Ewg1y3aKf4cjPS7hnggBhfxXYufM5Zd'
        );

        $httpClient = new OAuthHttpClient($httpClient, $grantType);

        $body = array(
            "username" => $user->getName(),
            "groups" => [],
            "email" => $user->getEmail(),
            "enabled" => "true",
            "emailVerified" => "false",
            "credentials" => [[
                "type" => "password",
                "value" => "qweqwe",
                "temporary" => "true"
            ]]
        );
        #dd(json_encode($body));
        $response = $httpClient->request(

            'POST',
            'http://192.168.1.70:7080/admin/realms/lorr/users',
            ['headers' => ['Content-Type: application/json'],
            'body' => json_encode($body)]
        );
        #dd($response->getStatusCode());
        #dd($response->getContent());
        #dd("asd");
    }
}
