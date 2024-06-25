<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
#use App\Tests\Api\LoginTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Tests\Api\toolTestCase;

class OrgaTest extends toolTestCase
{
    use ReloadDatabaseTrait;
    public function testCreateBase()
    {
        $client = static::createClient();
        $this->createBase();
        $token = $this->auth($client, "admin", "admin@cap3c.net", "1234", 200);
        $this->logIn($client, $token, "ROLE_orga_admin");
    }
    //------------------------------------------------------

    public function testCreateSupport(?Client $client = NULL)
    {
        $client = $client ? $client : static::createClient();
        $this->createBase();
        $token = $this->auth($client, "admin", "admin@cap3c.net", "1234");

        $this->createUser($client, $token, "support@cap3c.net", "pass", "support", role: "ROLE_cap3c_support_tech");

        #$this->assertSame(201, $client->getResponse()->getStatusCode());
        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            #"email" => "support@cap3c.net",
            #"roles" => ["ROLE_cap3c_support_tech"],
        ]);
        $token = $this->auth($client, "suport", "support@cap3c.net", "1234", 200);
        $this->logIn($client, $token, "ROLE_cap3c_support_tech");
        return $token;
    }
    //------------------------------------------------------

    public function testCreateOrga(?Client $client = NULL)
    {
        $client = $client ? $client : static::createClient();
        $token = $this->testCreateSupport($client);

        $orga = $this->createOrga($client, $token, "ademe", "admin@ca.a", "pass", "admin+orga", role: "ROLE_cap3c_support_tech");

        #$this->assertSame(201, $client->getResponse()->getStatusCode());
        #$this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/Organisme',
            '@type' => 'Organisme',
        ]);
        $token = $this->auth($client, "admin+orga", "admin@ca.a", "1234", 200);
        $this->logIn($client, $token, "ROLE_orga_admin", "ademe");
        return $orga;
    }
    //------------------------------------------------------

    /*
    public function testChangeOrgaSupport($client = NULL): void
    {
        $client = ($client) ? $client : static::createClient();
        $orga = $this->testCreateOrga($client);

        $token = $this->auth($client, "support", "support@cap3c.net", "1234", 200);
        $this->logIn($client, $token, "ROLE_cap3c_support_tech");

        $this->changeOrganisme($client, $token, $orga["id"], 200);
    }
     */
/*
    public function testCreateUserInOrgaSupportOrga(): void
    {
        $client = static::createClient();
        $orga = $this->testCreateOrga($client);

        $token = $this->auth($client, "support", "support@cap3c.net", "1234", 200);
        $this->logIn($client, $token, "ROLE_cap3c_support_tech");

        $this->changeOrganisme($client, $token, $orga["id"], 200);

        $this->createUser($client, $token, "user@ca.a", "pass", "user", role: "ROLE_cap4c_support_tech", orga: $orga["@id"]);

        $token = $this->auth($client, "user", "user@ca.a", "1234", 200);
        $this->logIn($client, $token, "ROLE_orga_user", "ademe");
    }
*/
/*
    public function testCreateUserInOrgaSupportFail(): void
    {
        $client = static::createClient();
        $orga = $this->testCreateOrga($client);

        $token = $this->auth($client, "support", "support@cap3c.net", "1234", 200);
        $this->logIn($client, $token, "ROLE_cap3c_support_tech");

        $this->createUser($client, $token, "user@ca.a", "pass", "user", role: "ROLE_cap4c_support_tech", orga: $orga["@id"], httpResponse: 400);

        $this->assertJsonContains([
            '@context' => '/contexts/Error',
            'hydra:description' => 'need to change organisme',
        ]);

    }
*/
    //------------------------------------------------------
    public function testCreateUserInOrgaAdmin(): void
    {
        $client = static::createClient();
        $orga = $this->testCreateOrga($client);

        $token = $this->auth($client, "admin+orga", "admin@ca.a", "1234", 200);
        $this->logIn($client, $token, "ROLE_orga_admin", "ademe");

        $this->createUser($client, $token, "user@ca.a", "pass", "user", role: "ROLE_cap4c_support_tech", orga: $orga["@id"]);

        $token = $this->auth($client, "user", "user@ca.a", "1234", 200);
        $this->logIn($client, $token, "ROLE_orga_user", "ademe");
    }
    //-------------------------------------------------------
    public function testMakeAllRole(): void
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);
        $this->logIn($client, $token["cap3c"], "ROLE_orga_admin", "cap3c");
        $this->logIn($client, $token["etude"], "ROLE_cap3c_R&D", "cap3c");
        $this->logIn($client, $token["support"], "ROLE_cap3c_support_tech", "cap3c");
        $this->logIn($client, $token["admin_ademe"], "ROLE_orga_admin", "ademe");
        $this->logIn($client, $token["admin_emmaus"], "ROLE_orga_admin", "emmaus");
        $this->logIn($client, $token["user_ademe1"], "ROLE_orga_user", "ademe");
        $this->logIn($client, $token["user_ademe2"], "ROLE_orga_user", "ademe");
        $this->logIn($client, $token["user_emmaus1"], "ROLE_orga_user", "emmaus");
        $this->logIn($client, $token["user_emmaus2"], "ROLE_orga_user", "emmaus");
        $this->logIn($client, $token["NULL"], "ROLE_orga_user", "ademe", 400);
        $this->logIn($client, $token["empty"], "ROLE_orga_user", "ademe", 400);

    }

    public function GetOrgaTestCase(string $role, int $nbItem = 3, ?int $httpResponse = 200): array|NULL
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $orga = $this->GetOrganisme($client, $token[$role], NULL, $httpResponse);

        #dd($orga);
        if ($httpResponse != 200)
            return NULL;
        $this->assertJsonContains([
            "@context" => "/contexts/Organisme",
            "@id" => "/organismes",
            "@type" => "hydra:Collection",
            "hydra:totalItems" => $nbItem,
            
        ]);
        return $orga["hydra:member"][0];
        #dd($client->getResponse()->getContent());
    }

    public function testForRoleCap3c()
    {
        $this->GetOrgaTestCase("cap3c", 3);
    }
    public function testForRoleEtude()
    {
        $this->GetOrgaTestCase("etude", 3);
    }
    public function testForRoleSupport()
    {
        $this->GetOrgaTestCase("support", 3);
    }
    public function testForRoleAdmin()
    {
        $this->GetOrgaTestCase("admin_ademe", 3);
    }
    public function testForRoleUser()
    {
        $this->GetOrgaTestCase("user_ademe1", httpResponse: 403);
    }
    public function testForRoleNull()
    {
        $this->GetOrgaTestCase("NULL", httpResponse: 401);
    }
    public function testForRoleEmpty()
    {
        $this->GetOrgaTestCase("empty", httpResponse: 401);
    }



    public function getUserTestCase(string $role, int $nbItem = 3, array $json = [], ?int $httpResponse = 200): array|NULL
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);
        $user = $this->GetUser($client, $token[$role], $httpResponse);
        #
        #dd($user);
        if ($httpResponse == 200)
        {
            $this->assertJsonContains([
                "@context" => "/contexts/User",
                "hydra:totalItems" => $nbItem,
                "hydra:member" => [$json]
            ]);
            return(json_decode($client->getResponse()->getContent(), true));
        }
        return NULL;
        #$response = ($client->getResponse()->getContent());
        #return $response["hydra:member"];
    }

    public function testUserForRoleAdmin()
    {
        $this->getUserTestCase("admin_ademe", 3, [
            "@type" => "User",
            "email" => "admin@ademe.a",
            "username" => "admin+ademe"
        ]);
    }
    public function testUserForRoleCap3c()
    {
        $this->GetUserTestCase("cap3c", 3, [
            "@type" => "User",
            "email" => "admin@cap3c.net",
            "username" => "admin"
        ]);
    }
    public function testUserForRoleEtude()
    {
        $this->GetUserTestCase("etude", httpResponse: 403);
    }
    public function testUserForRoleSupport()
    {
        $this->GetUserTestCase("support", httpResponse: 403);
    }
    public function testUSerForRoleUser()
    {
        $this->GetUserTestCase("user_ademe1", httpResponse: 403);
    }
    public function testUserForRoleNull()
    {
        $this->GetUserTestCase("NULL", httpResponse: 401);
    }
    public function testUserForRoleEmpty()
    {
        $this->GetUserTestCase("empty", httpResponse: 401);
    }
}
