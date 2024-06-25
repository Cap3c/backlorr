<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Entity\Organisme;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class toolTestCase extends ApiTestCase
{
    protected array $descriptionALL =
            [
                "t_int1" => "integer",
                "t_int2" => "integer",
                "t_int3" => "integer",
                "t_int4" => "integer",
                "t_str1" => "text",
                "t_str2" => "text",
                "t_str3" => "text",
                "t_str4" => "text",
                "t_date1" => "date",
                "t_date2" => "date",
                "t_date3" => "date",
                "t_date4" => "date",
                "t_float1" => "float",
                "t_float2" => "float",
                "t_float3" => "float",
                "t_float4" => "float",
            ];
    protected array $descriptionFLOAT =
            [
                "t_float1" => "float",
                "t_float2" => "float",
                "t_float3" => "float",
                "t_float4" => "float",
            ];
    protected array $descriptionDATE =
            [
                "t_date1" => "date",
                "t_date2" => "date",
                "t_date3" => "date",
                "t_date4" => "date",
            ];
    protected array $descriptionINT_STR =
            [
                "t_int1" => "integer",
                "t_int2" => "integer",
                "t_int3" => "integer",
                "t_int4" => "integer",
                "t_str1" => "text",
                "t_str2" => "text",
                "t_str3" => "text",
                "t_str4" => "text"
            ];
    protected array $descriptionINT =
            [
                "t_int1" => "integer",
                "t_int2" => "integer",
                "t_int3" => "integer",
                "t_int4" => "integer",
            ];
    protected array $descriptionSTR =
            [
                "t_str1" => "text",
                "t_str2" => "text",
                "t_str3" => "text",
                "t_str4" => "text"
            ];
    #    use ReloadDatabaseTrait;

    protected function createBase()
    {
        self::getContainer()->get('App\Command\InitBaseCommand')->core();
        return ;
    }

    protected function headers(?string $token = NULL)
    {
        if ($token)
            return ([
                'authorization' => "Bearer $token",
                'Content-Type' => 'application/json'
            ]);
        return ([
            'Content-Type' => 'application/json'
        ]);
    }

/*    protected function changeOrganisme(Client $client, ?string $token, string $idOrga, $ret)
    {
        $client->request('POST', '/support/'.$idOrga, [
            'headers' => ['Content-Type' => 'application/json'],
            'headers' => $this->headers($token),
        ]);
        $this->assertResponseStatusCodeSame($ret);
    }
 */
    protected function auth(Client $client, string $username, string $email, string $password, int $httpResponse = 200)
    {
        $client->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                #'username' => $username,
                'email' => $email,
                'password' => $password
            ],
        ]);
        $this->assertResponseStatusCodeSame($httpResponse);
        $this->assertSame($httpResponse, $client->getResponse()->getStatusCode());
        $this->isJson();
        if ($httpResponse == 200)
            return(json_decode($client->getResponse()->getContent(), true)["token"]);
        return NULL;
    }

    protected function logIn(Client $client, ?string $token, string $role = "ROLE_orga_user", string $orga = "cap3c", int $httpResponse = 200)
    {
        $client->request('GET', '/login', [
            'headers' => $this->headers($token),
        ]);
        #dump($client->getResponse()->getContent());
        $this->assertResponseStatusCodeSame($httpResponse);
        $this->assertSame($httpResponse, $client->getResponse()->getStatusCode());
        if ($httpResponse == 200)
            $this->assertJsonContains([
                "role" => [$role],
                "orga" => $orga,
            ]);
    }

    public function makeAllRole(Client $client): array
    {
        $this->createBase();
        $token["cap3c"] = $this->auth($client, "admin", "admin@cap3c.net", "1234", 200);

        $this->createUser($client, $token["cap3c"], "etude@cap3c.net", "pass", "etude", role: "ROLE_cap3c_R&D");
        $token["etude"] = $this->auth($client, "etude", "etude@cap3c.net", "1234", 200);
        $this->createUser($client, $token["cap3c"], "support@cap3c.net", "pass", "support", role: "ROLE_cap3c_support_tech");
        $token["support"] = $this->auth($client, "support", "support@cap3c.net", "1234", 200);
        $ademe = $this->createOrga($client, $token["support"], "ademe", "admin@ademe.a", "pass", "admin+ademe", role: "ROLE_cap3c_support_tech");
        $emmaus = $this->createOrga($client, $token["support"], "emmaus", "admin@emmaus.a", "pass", "admin+emmaus", role: "ROLE_cap3c_support_tech");

        $token["admin_ademe"] = $this->auth($client, "admin+ademe", "admin@ademe.a", "1234", 200);
        $token["admin_emmaus"] = $this->auth($client, "admin+emmaus", "admin@emmaus.a", "1234", 200);
        $this->createUser($client, $token["admin_ademe"], "user1@ademe.a", "pass", "user");
        $this->createUser($client, $token["admin_ademe"], "user2@ademe.a", "pass", "user2");
        $this->createUser($client, $token["admin_emmaus"], "user1@emmaus.a", "pass", "user3");
        $this->createUser($client, $token["admin_emmaus"], "user2@emmaus.a", "pass", "user4");

        $token["user_ademe1"] = $this->auth($client, "user", "user1@ademe.a", "1234", 200);
        $token["user_ademe2"] = $this->auth($client, "user", "user2@ademe.a", "1234", 200);
        $token["user_emmaus1"] = $this->auth($client, "user", "user1@emmaus.a", "1234", 200);
        $token["user_emmaus2"] = $this->auth($client, "user", "user2@emmaus.a", "1234", 200);
        $token["NULL"] = NULL;
        $token["empty"] = "";
        return $token;
    }

    //-----------------------------------create----------------------------


    protected function createID(Client $client, ?string $token, array $data, string $id, int $httpResponse = 201)
    {
        $client->request('POST', $id, [
            'headers' => $this->headers($token),
            'json' => $data]);
        $this->assertResponseStatusCodeSame($httpResponse);
        $this->assertSame($httpResponse, $client->getResponse()->getStatusCode());
        if ($httpResponse == 201)
            return(json_decode($client->getResponse()->getContent(), true));
        return NULL;
    }

    protected function createInTable(Client $client, ?string $token, array $data, array $permObject, int $idDesc, int $httpResponse = 201)
    {
        return $this->createID($client, $token, ["data" => $data], $permObject['tables'].'/'.$permObject["descCategorie"][$idDesc]["name"], $httpResponse);
    }

    protected function createUser(Client $client, ?string $token, string $email, string $password, string $username, ?string $orga = NULL, ?string $role = NULL, int $httpResponse = 201)
    {
        return $this->createID($client, $token, [
                "email" => $email,
                "password" => $password, //useless
                "username" => $username,
                "organisme" => $orga,#"/organismes/".$orga->getId(),
                "roles" => [$role],
        ], '/users', $httpResponse);
    }

    protected function createOrga(Client $client, ?string $token, string $nameOrga, string $email, string $password, string $username, ?string $role = NULL, int $httpResponse = 201)
    {
        return $this->createID($client, $token, [
                "name" => $nameOrga,
                "admin" => [
                    "email" => $email,
                    "password" => $password,
                    //need to be useless because overwrite by `1234` or something better
                    "username" => $username,
                    "roles" => [$role],//useless, admin is ROLE_orga_admin
                ]
        ], '/organismes', $httpResponse);
    }

    protected function createDescArray(Client $client, ?string $token, array $array = [], string $id = "/descs", int $httpResponse = 201)
    {
        return $this->createID($client, $token, $array, $id, $httpResponse);
    }

    protected function createDesc(Client $client, ?string $token, ?string $name, ?string $description, ?array $array, int $httpResponse = 201)
    {
        return $this->createID($client, $token, [
                'name' => $name,
                'description' => $description,
                'descriptionArray' => $array,
        ], '/descs', $httpResponse);
    }

    protected function createTable(Client $client, ?string $token, ?string $name, ?string $categorieDesc, int $httpResponse = 201)
    {
        return $this->createID($client, $token, [
                'name' => $name,
                'categorie' => $categorieDesc
        ], '/tables', $httpResponse);
    }

    protected function createPermission(Client $client, ?string $token, string $userId, string $tableId, string $value, int $httpResponse = 201)
    {
        return $this->createID($client, $token, [
                  "users" => $userId,
                  "tables" => $tableId,
                  "value" => $value
        ], '/permissions', $httpResponse);
    }

    //-------------------------------get---------------------------
    //

    protected function getIDstr(Client $client, ?string $token, string $id, int $httpResponse = 200)
    {
        $client->request('GET', $id, [
            'headers' => ['Content-Type' => 'application/json'],
            'headers' => $this->headers($token),
        ]);
        #$this->assertResponseStatusCodeSame($httpResponse);
        $this->assertSame($httpResponse, $client->getResponse()->getStatusCode());

        if ($httpResponse == 200)
            return(json_decode($client->getResponse()->getContent(), true));
        return NULL;
    }
    protected function getID(Client $client, ?string $token, array $object, int $httpResponse = 200)
    {
        return $this->getIDstr($client, $token, $object["@id"], $httpResponse);
    }

    protected function getInTable(Client $client, ?string $token, array $object, int $idDesc, int $httpResponse = 200)
    {
        return $this->getIDstr($client, $token, $object['tables'].'/'.$object["descCategorie"][$idDesc]["name"], $httpResponse);
    }

    protected function getDesc(Client $client, ?string $token, ?string $descId = "/descs/prive", int $httpResponse = 200)
    {

        return $this->getIDstr($client, $token, $descId, $httpResponse);
    }
    protected function getUser(Client $client, ?string $token, int $httpResponse = 200, ?int $userId = NULL)
    {
        return $this->getIDstr($client, $token, '/users'.(($userId) ? '/'.$userId : ''), $httpResponse);
    }

    protected function GetOrganisme(Client $client, ?string $token, ?int $organismeId = NULL, int $httpResponse = 200)
    {
        return $this->getIDstr($client, $token, '/organismes'.(($organismeId) ? '/'.$organismeId : ''), $httpResponse);
    }

    //------------------------------update---------------------------------

    protected function updateIDstr(Client $client, ?string $token, array $data, string $id, int $httpResponse = 200)
    {

        $client->request('PUT', $id, [
            'headers' => $this->headers($token),
            'json' => $data
        ]);
        $this->assertResponseStatusCodeSame($httpResponse);
        $this->assertSame($httpResponse, $client->getResponse()->getStatusCode());

        if ($httpResponse == 200)
            return(json_decode($client->getResponse()->getContent(), true));
        return NULL;
    }

    protected function updateID(Client $client, ?string $token, array $data, array $object, int $httpResponse = 200)
    {
        return $this->updateIDstr($client, $token, $data, $object["@id"], $httpResponse);
    }
    protected function updateID2(Client $client, ?string $token, array $data, array $object, string $id2, int $httpResponse = 200)
    {
        return $this->updateIDstr($client, $token, $data, $object["@id"].'/'.$id2, $httpResponse);
    }

    protected function updateInTable(Client $client, ?string $token, array $data, array $permObject, int $idDesc, int $httpResponse = 200)
    {
        return $this->updateIDstr($client, $token, ["data" => $data], $permObject['tables'].'/'.$permObject["descCategorie"][$idDesc]["name"].'/1', $httpResponse);
    }

    protected function updateDescMeta(Client $client, ?string $token, array $data, string $categorie, string $name, int $httpResponse = 200)
    {
        return $this->updateIDstr($client, $token, $data, '/descs/'.$categorie.'/'.$name, $httpResponse);
    }

    protected function updateDescIsPublic(Client $client, ?string $token, array $data, string $categorie, int $httpResponse = 200)
    {
        return $this->updateIDstr($client, $token, $data, '/descs/'.$categorie, $httpResponse);
    }

    protected function updatePermission(Client $client, ?string $token, array $data, string $idPerm = "/permission", int $httpResponse = 200)
    {
        return $this->updateIDstr($client, $token, $data, $idPerm, $httpResponse);
    }
    //-------------------------------delete---------------------------
    protected function deleteIDstr(Client $client, ?string $token, string $id, int $httpResponse = 204)
    {

        $client->request('DELETE', $id, [
            'headers' => $this->headers($token),
        ]);
        $this->assertResponseStatusCodeSame($httpResponse);
        $this->assertSame($httpResponse, $client->getResponse()->getStatusCode());
        return NULL;
    }

    protected function deleteID(Client $client, ?string $token, array $id, int $httpResponse = 204)
    {
        return $this->deleteIDstr($client, $token, $id["@id"], $httpResponse);
    }

    protected function deleteInTable(Client $client, ?string $token, array $permObject, int $idDesc, int $httpResponse = 204)
    {
        return $this->deleteIDstr($client, $token, $permObject['tables'].'/'.$permObject["descCategorie"][$idDesc]["name"].'/1', $httpResponse);
    }



    //-------------------------------share----------------------------------

    protected function ShareTable(Client $client, ?string $token, string $user, string $categorie, int $httpResponse = 204)
    {

        $client->request('POST', '/shareTable', [
            'headers' => $this->headers($token),
            'json' => [
                'userId' => $user,
                'shareId' => $categorie
            ],
        ]);
        $this->assertResponseStatusCodeSame($httpResponse);
        $this->assertSame($httpResponse, $client->getResponse()->getStatusCode());

        if ($httpResponse == 204)
            return(json_decode($client->getResponse()->getContent(), true));
        return NULL;
    }
    protected function ShareDescription(Client $client, ?string $token, string $user, string $categorie, int $httpResponse = 204)
    {

        $client->request('POST', '/shareDesc', [
            'headers' => ['Content-Type' => 'application/json'],
            'headers' => $this->headers($token),
            'json' => [
                'userId' => $user,
                'shareId' => $categorie
            ],
        ]);
        $this->assertResponseStatusCodeSame($httpResponse);
        $this->assertSame($httpResponse, $client->getResponse()->getStatusCode());

        if ($httpResponse == 204)
            return(json_decode($client->getResponse()->getContent(), true));
        return NULL;
    }


}
