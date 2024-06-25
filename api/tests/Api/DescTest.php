<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
#use App\Tests\Api\LoginTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use ApiPlatform\Symfony\Bundle\Test\Client;

class DescTest extends toolTestCase
{
    use ReloadDatabaseTrait;

  //------------------------------------------------------
    public function DescForRoleTestCase(string $role, int $result)
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $this->createDesc($client, $token[$role], "GDR_".$role, "my description", $this->descriptionINT, $result);
    }

    public function testForRoleCap3c()
    {
        $this->DescForRoleTestCase("cap3c", 201);
    }
    public function testForRoleEtude()
    {
        $this->DescForRoleTestCase("etude", 201);
    }
    public function testForRoleSupport()
    {
        $this->DescForRoleTestCase("support", 403);
    }
    public function testForRoleAdmin()
    {
        $this->DescForRoleTestCase("admin_ademe", 201);
    }
    public function testForRoleUser()
    {
        $this->DescForRoleTestCase("user_ademe1", 403);
    }
    public function testForRoleNull()
    {
        $this->DescForRoleTestCase("NULL", 401);
    }
    //---------------------------------------------------------------------

    public function DescAdminForParameterTestCase(?string $name, ?string $description, ?array $array, int $httpResponse)
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $this->createDesc($client, $token["admin_ademe"], $name, $description, $array, $httpResponse);
    }
    public function testEmptyName()
    {
        $this->DescAdminForParameterTestCase('', "asd", $this->descriptionINT, 422);
    }
    public function testEmptydescription()
    {
        $this->DescAdminForParameterTestCase("asd", '', $this->descriptionINT, 422);
    }
    public function testEmptyArray()
    {
        $this->DescAdminForParameterTestCase("asd", "asd", [], 422);
    }
    public function testEmptyAll()
    {
        $this->DescAdminForParameterTestCase('', '', [], 422);
    }
    /*
    public function testNULLName()
    {
        $this->DescAdminForParameterTestCase(NULL, "asd", $this->descriptionINT, 400);
    }
    public function testNULLdescription()
    {
        $this->DescAdminForParameterTestCase("asd", NULL, $this->descriptionINT, 400);
    }
    public function testNULLArray()
    {
        $this->DescAdminForParameterTestCase("asd", "asd", NULL, 400);
    }
    public function testNULLAll()
    {
        $this->DescAdminForParameterTestCase(NULL, NULL, NULL, 400);
    }
    */
    public function testDescAdminMultiCategorieValid()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDescArray($client, $token["admin_ademe"], [
                "name" => "asd", 
                "description" => "asd",
                "descriptionArray" => $this->descriptionINT]);
        $this->createDescArray($client, $token["admin_ademe"], [
                "name" => "asd2", 
                "description" => "asd2",
                "descriptionArray" => $this->descriptionINT], $desc["@id"]);
        $retrive = $this->getID($client, $token["admin_ademe"], $desc);
        $this->assertJsonContains(["hydra:member" => [
            0 => [
                "@type" => "Desc",
                "name" => "asd",
                "description" => "asd",
                "descriptionArray" => $this->descriptionINT,
                "partage_public" => false,
                "first_use" => false,
            ],
            1 => [
                "@type" => "Desc",
                "name" => "asd2",
                "description" => "asd2",
                "descriptionArray" => $this->descriptionINT,
                "partage_public" => false,
                "first_use" => false,
            ]
        ]]);

    }
    public function testDescAdminMultiCategorieSameName()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDescArray($client, $token["admin_ademe"], [
                "name" => "asd", 
                "description" => "asd",
                "descriptionArray" => $this->descriptionINT]);
        $this->createDescArray($client, $token["admin_ademe"], [
                "name" => "asd", 
                "description" => "asd2",
                "descriptionArray" => $this->descriptionINT], $desc["@id"], 422);
    }
    public function testDescAdminMultiCategorieSameCategorie()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDescArray($client, $token["admin_ademe"], [
                "name" => "asd", 
                "description" => "asd",
                "descriptionArray" => $this->descriptionINT]);
        $this->createDescArray($client, $token["admin_ademe"], [
                "name" => "asd2", 
                "description" => "asd",
                "descriptionArray" => $this->descriptionINT], $desc["@id"]);
        $this->getID($client, $token["admin_ademe"], $desc);
    }
    public function testDescAdminMultiCategorieMissName()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDescArray($client, $token["admin_ademe"], [
                "name" => "asd", 
                "description" => "asd",
                "descriptionArray" => $this->descriptionINT]);
        $this->createDescArray($client, $token["admin_ademe"], [
                "description" => "asd2",
                "descriptionArray" => $this->descriptionINT], $desc["@id"], 422);
    }
    public function testDescAdminMissArgumentNone()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $this->createDescArray($client, $token["admin_ademe"], [
                //"categorie" => "asd",
                "name" => "asd", 
                "description" => "asd",
                "descriptionArray" => $this->descriptionINT]);
    }
    public function testDescAdminMissArgumentName()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $this->createDescArray($client, $token["admin_ademe"], [
                //"categorie" => "asd",
                "description" => "asd",
                "descriptionArray" => $this->descriptionINT], httpResponse: 422);
    }
    public function testDescAdminMissArgumentDescription()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $this->createDescArray($client, $token["admin_ademe"], [
                //"categorie" => "asd",
                "name" => "asd", 
                "descriptionArray" => $this->descriptionINT], httpResponse: 422);
    }
    public function testDescAdminMissArgumentDescriptionArray()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $this->createDescArray($client, $token["admin_ademe"], [
                //"categorie" => "asd",
                "name" => "asd", 
                "description" => "asd"], httpResponse: 422);
    }
    public function testDescAdminMissArgumentAll()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $this->createDescArray($client, $token["admin_ademe"], [], httpResponse: 422);
                //"categorie" => "asd",
    }

    public function testIncorectParameter()
    {
        $this->DescAdminForParameterTestCase("asd", "asd", 
            [
                "t_int1" => "integer",
                "t_int2" => "integer",
                "t_int3" => "integer",
                "t_int4" => "integasder",
                "t_int5" => "integer",
            ]
            , 422);
    }
    public function testTooMuchParameter()
    {
        $this->DescAdminForParameterTestCase("asd", "asd", 
            [
                "t_int1" => "integer",
                "t_int2" => "integer",
                "t_int3" => "integer",
                "t_int4" => "integer",
                "t_int5" => "integer",
                "t_int6" => "integer",
                "t_int7" => "integer",
                "t_int8" => "integer",
                "t_int9" => "integer"
            ]
            , 422);
    }

    public function testSameNameTooMuchParameter()
    {
        $this->DescAdminForParameterTestCase("asd", "asd", 
            [
                "t_int1" => "integer",
                "t_int1" => "integeri",
                "t_int3" => "integer",
                "t_int4" => "integer",
                "t_int5" => "integer",
                "t_int6" => "integer",
                "t_int7" => "integer",
                "t_int8" => "integer",
                "t_int9" => "integer"
            ]
            , 422);
    }

    public function testSameNameIncorectParameter()
    {
        $this->DescAdminForParameterTestCase("asd", "asd", 
            [
                "t_int1" => "integer",
                "t_int1" => "integeri",
                "t_int3" => "integer",
                "t_int4" => "integer",
                "t_int5" => "integer",
            ]
            , 422);
    }

    public function testSameName()
    {
        $this->DescAdminForParameterTestCase("asd", "asd", 
            [
                "t_int1" => "integer",
                "t_int1" => "integer",
                "t_int3" => "integer",
                "t_int4" => "integer",
            ]
            , 201);
    }
    public function testCorrect()
    {
        $this->DescAdminForParameterTestCase("asd", "asd", $this->descriptionINT, 201);
    }

    public function testDescAdminForShare()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token["admin_ademe"], "Desc_share", "test_sharing", $this->descriptionINT, 201);
        $descs = $this->getID($client, $token["admin_ademe"], $desc);
        $desc = $descs["hydra:member"][0];
        #dd($descs);
        $orgas = $this->GetOrganisme($client, $token["admin_ademe"], httpResponse: 200);
foreach($orgas["hydra:member"] as $orga)
{
$httpResponse = 204;
if($orga["name"] == "ademe")
$httpResponse = 404;
        $this->ShareDescription($client, $token["admin_ademe"], $orga["adminID"]["@id"], $desc["categorie"], $httpResponse);
}

    }
    //--------------------------------------------------------------------------------

    public function testDescAdminUpdate()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token["admin_ademe"], "name_desc", "description_desc", $this->descriptionINT);
        $desc2 = $this->updateID2($client, $token["admin_ademe"], ["name" => "Ints", "description" => "string", "descriptionArray" => $this->descriptionSTR], $desc, "name_desc");
        #$desc2 = $this->updateDescMeta($client, $token["admin_ademe"], ["name" => "Ints", "description" => "string", "descriptionArray" => $this->descriptionSTR], $desc["categorie"], "name_desc");
        $desc3 = $this->getID($client, $token["admin_ademe"], $desc, 200);
    }

    public function testDescAdminUpdateMultiCategorieDescriptionArray()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDescArray($client, $token["admin_ademe"], [
                "name" => "asd", 
                "description" => "asd",
                "descriptionArray" => $this->descriptionINT]);
        $this->createDescArray($client, $token["admin_ademe"], [
                "name" => "asd2", 
                "description" => "asd2",
                "descriptionArray" => $this->descriptionSTR], $desc["@id"]);

        $this->updateID2($client, $token["admin_ademe"], ["name" => "asdfads", "description" => "stringasdf"], $desc, "asd");
        $this->updateID2($client, $token["admin_ademe"], ["name" => "asdasdfads", "description" => "stringasdf"], $desc, "asd2");


        $desc3 = $this->getID($client, $token["admin_ademe"], $desc, 200);
        $this->assertJsonContains(["hydra:member" => [
            0 => [
                "@type" => "Desc",
                "name" => "asdasdfads",
                "description" => "stringasdf",
                "descriptionArray" => $this->descriptionSTR,
                "partage_public" => false,
                "first_use" => false,
            ],
            1 => [
                "@type" => "Desc",
                "name" => "asdfads",
                "description" => "stringasdf",
                "descriptionArray" => $this->descriptionINT,
                "partage_public" => false,
                "first_use" => false,
            ]
        ]]);
    }

    public function testDescAdminUpdateMultiCategorieIsPublicChange()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDescArray($client, $token["admin_ademe"], [
                "name" => "asd", 
                "description" => "asd",
                "descriptionArray" => $this->descriptionINT]);
        $this->createDescArray($client, $token["admin_ademe"], [
                "name" => "asd2", 
                "description" => "asd2",
                "descriptionArray" => $this->descriptionSTR], $desc["@id"]);

        $this->updateID($client, $token["admin_ademe"], ["partagePublic" => true], $desc);

        $desc3 = $this->getID($client, $token["admin_ademe"], $desc, 200);
        #dd($desc3);
        $this->assertJsonContains(["hydra:member" => [
            [
                "@type" => "Desc",
                "descriptionArray" => $this->descriptionINT,
                "name" => "asd",
                "description" => "asd",
                "partage_public" => true,
                "first_use" => false,
            ],
            [
                "@type" => "Desc",
                "name" => "asd2",
                "description" => "asd2",
                "descriptionArray" => $this->descriptionSTR,
                "partage_public" => true,
                "first_use" => false,
            ]
        ]]);
    }
    public function testDescAdminUpdateMultiCategorieSameName()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDescArray($client, $token["admin_ademe"], [
                "name" => "asd", 
                "description" => "asd",
                "descriptionArray" => $this->descriptionINT]);
        $this->createDescArray($client, $token["admin_ademe"], [
                "name" => "asd2", 
                "description" => "asd2",
                "descriptionArray" => $this->descriptionINT], $desc["@id"]);

        $this->updateID2($client, $token["admin_ademe"], ["name" => "asdfads", "description" => "stringasdf"], $desc, "asd");
        $this->updateID2($client, $token["admin_ademe"], ["name" => "asdfads", "description" => "stringasdf"], $desc, "asd2", 422);


        $desc3 = $this->getID($client, $token["admin_ademe"], $desc, 200);
    }

}
