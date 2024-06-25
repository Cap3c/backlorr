<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
#use App\Tests\Api\LoginTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use ApiPlatform\Symfony\Bundle\Test\Client;

class TableTest extends toolTestCase
{
    use ReloadDatabaseTrait;

    //------------------------------------------------------
    public function TableForRoleTestCase(string $role, int $result)
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token[$role], "GDR_admin", "my description", $this->descriptionINT);

        #dd($token[$role]);
        $this->createTable($client, $token[$role], "Gdr".$role, $desc["categorie"], $result);
    }

    public function TableAdminForRoleTestCase(string $role, int $result)
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token["admin_ademe"], "GDR_admin", "my description", $this->descriptionINT);

        #dd($desc["categorie"]);
        $this->createTable($client, $token[$role], "Gdr".$role, $desc["categorie"], $result);
    }

    public function testForRoleCap3c()
    {
        $this->TableAdminForRoleTestCase("cap3c", 401);
        $this->TableForRoleTestCase("cap3c", 201);
    }
    public function testForRoleEtude()
    {
        $this->TableAdminForRoleTestCase("etude", 401);
        $this->TableForRoleTestCase("etude", 201);
    }
    public function testForRoleSupport()
    {
        $this->TableAdminForRoleTestCase("support", 403);
    }
    public function testForRoleAdmin()
    {
        #$this->TableAdminForRoleTestCase("admin_ademe", 201);
        $this->TableForRoleTestCase("admin_ademe", 201);
    }
    public function testForRoleUser()
    {
        $this->TableAdminForRoleTestCase("user_ademe1", 403);
    }
    public function testForRoleNull()
    {
        $this->TableAdminForRoleTestCase("NULL", 401);
    }

    public function TableAdminForDescTestCase(?string $descC, ?string $name, int $result)
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);


        #$desc = $this->createDesc($client, $token["admin_ademe"], "GDR_admin", "my description", $this->descriptionINT);

        $this->createTable($client, $token["admin_ademe"], $name, $descC, $result);
    }

    public function testTableAdminForName()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);


        $desc1 = $this->createDesc($client, $token["admin_ademe"], "GDR_admin", "my description", $this->descriptionINT);
        $desc2 = $this->createDesc($client, $token["admin_ademe"], "GDR_admin", "my description", $this->descriptionINT);

        $this->createTable($client, $token["admin_ademe"], "name1", $desc1["categorie"], 201);
        $this->createTable($client, $token["admin_ademe"], "name1", $desc1["categorie"], 401);
        $this->createTable($client, $token["admin_ademe"], "name1", $desc2["categorie"], 201);
    }

    public function testTableAdminShare()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);


        $desc1 = $this->createDesc($client, $token["admin_ademe"], "GDR_admin", "my description", $this->descriptionINT);
        $desc2 = $this->createDesc($client, $token["admin_ademe"], "GDR_admin", "my description", $this->descriptionINT);

        $this->createTable($client, $token["admin_ademe"], "name1", $desc1["categorie"], 201);
        $this->createTable($client, $token["admin_ademe"], "name1", $desc1["categorie"], 401);
        $this->createTable($client, $token["admin_ademe"], "name1", $desc2["categorie"], 201);
    }

    public function testForDescBadFormat()
    {
        $this->TableAdminForDescTestCase("5156e44a-e5b9-4698-bf97-d6609c7914e2a", "gdr", 401);
    }
    public function testForDescUnknow()
    {
        $this->TableAdminForDescTestCase("5156e44a-e5b9-4698-bf97-d6609c7914e2", "gdr", 401);
    }
    /*
    public function testForDescNull()
    {
        $this->TableAdminForDescTestCase(NULL, 400);
    }
     */
    public function testForDescEmpty()
    {
        $this->TableAdminForDescTestCase("", "sad", 401);
    }


    public function testDescAdminShare()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token["admin_ademe"], "Desc_share", "test_sharing", $this->descriptionINT, 201);
        $orgas = $this->GetOrganisme($client, $token["admin_ademe"], httpResponse: 200);

        $this->createTable($client, $token["admin_ademe"], "name11", $desc["categorie"], 201);
        $this->createTable($client, $token["admin_emmaus"], "name12", $desc["categorie"], 401);
        $this->createTable($client, $token["cap3c"], "name13", $desc["categorie"], 401);

        foreach($orgas["hydra:member"] as $orga)
        {
            if($orga["name"] == "cap3c")
                continue;
            $httpResponse = 204;
            if($orga["name"] == "ademe")
                $httpResponse = 404;

            $this->ShareDescription($client, $token["admin_ademe"], $orga["adminID"]["@id"], $desc["categorie"], $httpResponse);
        }

        $this->createTable($client, $token["admin_ademe"], "name21", $desc["categorie"], 201);
        $this->createTable($client, $token["admin_emmaus"], "name22", $desc["categorie"], 201);
        $this->createTable($client, $token["cap3c"], "name23", $desc["categorie"], 401);
    }

    public function testDescAdminTableForShare()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token["admin_ademe"], "Desc_share", "test_sharing", $this->descriptionINT, 201);
        $orgas = $this->GetOrganisme($client, $token["admin_ademe"], httpResponse: 200);

        $table = $this->createTable($client, $token["admin_ademe"], "name11", $desc["categorie"], 201);
        foreach($orgas["hydra:member"] as $orga)
        {
            $httpResponse = 204;
            if($orga["name"] == "ademe")
                $httpResponse = 404;
            $this->ShareTable($client, $token["admin_ademe"], $orga["adminID"]["@id"], $table["id"], $httpResponse);
        }
    }

    public function testDescAdminShareForShare()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token["admin_ademe"], "Desc_share", "test_sharing", $this->descriptionINT, 201);
        $orgas = $this->GetOrganisme($client, $token["admin_ademe"], httpResponse: 200);

        foreach($orgas["hydra:member"] as $orga)
        {
            $httpResponse = 204;
            if($orga["name"] == "ademe") //cant share with themself
                $httpResponse = 404;
            $this->ShareDescription($client, $token["admin_ademe"], $orga["adminID"]["@id"], $desc["categorie"], $httpResponse);
        }
        $table = $this->createTable($client, $token["admin_emmaus"], "name12", $desc["categorie"], 201);
        foreach($orgas["hydra:member"] as $orga)
        {
            $httpResponse = 204;
            if($orga["name"] == "emmaus") //cant share with themself
                $httpResponse = 404;
            $this->ShareTable($client, $token["admin_emmaus"], $orga["adminID"]["@id"], $table["id"], $httpResponse);
        }
    }


    public function testTableAdminMultiDescriptionCreate()
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
        $this->createTable($client, $token["admin_ademe"], "name11", $desc["categorie"], 201);
    }
    public function testTableAdminMultiDescriptionShare()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);
        $orgas = $this->GetOrganisme($client, $token["admin_ademe"]);

        $desc = $this->createDescArray($client, $token["admin_ademe"], [
            "name" => "asd", 
            "description" => "asd",
            "descriptionArray" => $this->descriptionINT]);
        $this->createDescArray($client, $token["admin_ademe"], [
            "name" => "asd2", 
            "description" => "asd2",
            "descriptionArray" => $this->descriptionINT], $desc["@id"]);
        $table = $this->createTable($client, $token["admin_ademe"], "name11", $desc["categorie"], 201);
        foreach($orgas["hydra:member"] as $orga)
        {
            $httpResponse = 204;
            if($orga["name"] == "ademe") //cant share with themself
                $httpResponse = 404;
            $this->ShareTable($client, $token["admin_ademe"], $orga["adminID"]["@id"], $table["id"], $httpResponse);
        }
    }

    public function testTableAdminMultiDescriptionTables()
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
        $this->createTable($client, $token["admin_ademe"], "name1k", $desc["categorie"], 201);
        $this->createTable($client, $token["admin_ademe"], "name12", $desc["categorie"], 201);
        $this->createTable($client, $token["admin_ademe"], "name13", $desc["categorie"], 201);
        $this->createTable($client, $token["admin_ademe"], "name14", $desc["categorie"], 201);
        $this->createTable($client, $token["admin_ademe"], "name15", $desc["categorie"], 201);
    }

    public function testTableAdminMultiDescriptionSameName()
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
        $this->createTable($client, $token["admin_ademe"], "name11", $desc["categorie"], 201);
        $this->createTable($client, $token["admin_ademe"], "name11", $desc["categorie"], 401);
    }
    //----------------------------------delete------------------------


    public function testTableAdminDelete()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);


        $desc1 = $this->createDesc($client, $token["admin_ademe"], "GDR_admin", "my description", $this->descriptionINT);
        $desc2 = $this->createDesc($client, $token["admin_ademe"], "GDR_admin", "my description", $this->descriptionINT);

        $this->createTable($client, $token["admin_ademe"], "name1", $desc1["categorie"], 201);
        $this->getIDstr($client, $token["admin_ademe"], "tables");

        $this->assertSame("ok", "not ok");
        //todo finish
    }

}
