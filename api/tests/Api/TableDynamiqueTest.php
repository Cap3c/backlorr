<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use ApiPlatform\Symfony\Bundle\Test\Client;

const CREATE_PERM = "1";
const READ_PERM = "2";
const CREATE_READ_PERM = "3";
const UPDATE_PERM = "4";
const DELETE_PERM = "8";
const ALL_PERM = "15";

class TableDynamiqueTest extends toolTestCase
{
	use ReloadDatabaseTrait;

	public function prepareTableAdmin(Client $client, array $token, array $descArray, string $perm, string $user)
	{
		$desc = $this->createDesc($client, $token["admin_ademe"], "GDRradmin", "my description", $descArray);
		$table = $this->createTable($client, $token["admin_ademe"], "GdrAdmin", $desc["categorie"]);
		$this->createPermission($client, $token["admin_ademe"], $user, $table["@id"], $perm);
	}

	public function testCreate()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, CREATE_PERM, $user["@id"]);

		#$token["user_ademe1"]
	}
	//------------------------------------insert------------------------------

	public function testInsertCorrect()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, CREATE_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264214,
		], $perm2, 0);
	}
	public function testInsertTypeIntCorrect()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, CREATE_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264214,
		], $perm2, 0);
	}
	public function testInsertTypeIntWrong()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, CREATE_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 4.5,
		], $perm2, 0, 422);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => "asd",
		], $perm2, 0, 422);
	}
	public function testInsertTypeStrCorrect()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionSTR, CREATE_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_str1" => "text1",
			"t_str2" => "text2",
			"t_str3" => "text3",
			"t_str4" => "text"
		], $perm2, 0);
	}
	public function testInsertTypeStrWrong()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionSTR, CREATE_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_str1" => "text1",
			"t_str2" => "text2",
			"t_str3" => "text3",
			"t_str4" => 343
		], $perm2, 0, 422);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_str1" => "text1",
			"t_str2" => "text2",
			"t_str3" => "text3",
			"t_str4" => 3.42
		], $perm2, 0, 422);
	}
	public function testInsertTypeFloatCorrect()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionFLOAT, CREATE_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_float1" => 3.43,
			"t_float2" => 525.63,
			"t_float3" => 3264.745,
			"t_float4" => 3264214.77223,
		], $perm2, 0);
	}
	public function testInsertTypeFloatWrong()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionFLOAT, CREATE_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_float1" => 3.43,
			"t_float2" => 525.63,
			"t_float3" => 3264.745,
			"t_float4" => 32642,
		], $perm2, 0, 422);//float != float
		$this->createInTable($client, $token["user_ademe1"], [
			"t_float1" => 3.43,
			"t_float2" => 525.63,
			"t_float3" => 3264.745,
			"t_float4" => "asd",
		], $perm2, 0, 422);
	}
	public function testInsertTypeDateCorrect()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionDATE, CREATE_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_date1" => "now",
			"t_date2" => "today",
			"t_date3" => "12/12/12",
			"t_date4" => "2023-06-15T05:04:03+02:01",
		], $perm2, 0);
	}

	public function testInsertMultiData()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, CREATE_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 4,
			"t_int2" => 525,
			"t_int3" => 3265,
			"t_int4" => 3264214,
		], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 41351353,
			"t_int2" => 521245,
			"t_int3" => 3267,
			"t_int4" => 3264214,
		], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 5343,
			"t_int2" => 529,
			"t_int3" => 3264,
			"t_int4" => 3264214,
		], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 1323,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3232,
		], $perm2, 0);
	}

	public function testInsertSameData()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, CREATE_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264214,
		], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264214,
		], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264214,
		], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264214,
		], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264214,
		], $perm2, 0);
	}
	public function testInsertAttrWrongType()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, CREATE_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => '3sd',
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264,
		], $perm2, 0, 422);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => '3',
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264,
		], $perm2, 0, 422);
	}
	public function testInsertIncompleteDescription()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, CREATE_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
		], $perm2, 0, 422);
	}
	public function testInsertSameNameDescription()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, CREATE_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264214,
			"t_int4" => 3264214,
		], $perm2, 0);
	}
	public function testInsertNotPermited()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, "0", $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264214,
		], $perm2, 0, 401);
	}
	//-------------------------read---------------------


	public function testReadCorrect()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, CREATE_READ_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, ], $perm2, 0);

		$result = $this->getInTable($client, $token["user_ademe1"], $perm2, 0);

		#dd($result);
		$this->assertJsonContains([
			"@context" => "/contexts/Table",
			"@type" => "hydra:Collection",
			"hydra:member" => [            
				0 => ["id" => 1, "t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214],
			],
			"hydra:totalItems" => 1,
		]);
	}

	public function testReadALLCorrect()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionALL, CREATE_READ_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 1840532,
			"t_int2" => 1840532,
			"t_int3" => 1840532,
			"t_int4" => 1840532,
			"t_str1" => "textf8q8wkl",
			"t_str2" => "textf8q8wkl",
			"t_str3" => "textf8q8wkl",
			"t_str4" => "textf8q8wkl",
			"t_date1" => "2023-06-20T00:00:00+00:00",
			"t_date2" => "2023-06-20T00:00:00+00:00",
			"t_date3" => "2023-06-20T00:00:00+00:00",
			"t_date4" => "2023-06-20T00:00:00+00:00",
			"t_float1" => 09.8097,
			"t_float2" => 09.8097,
			"t_float3" => 09.8097,
			"t_float4" => 09.8097,
		], $perm2, 0);

		$result = $this->getInTable($client, $token["user_ademe1"], $perm2, 0);

		#dd($result);
		$this->assertJsonContains([
			"@context" => "/contexts/Table",
			"@type" => "hydra:Collection",
			"hydra:member" => [            
				0 => [
					"id" => 1,
					"t_int1" => 1840532, "t_int2" => 1840532, "t_int3" => 1840532, "t_int4" => 1840532,
					"t_str1" => "textf8q8wkl", "t_str2" => "textf8q8wkl", "t_str3" => "textf8q8wkl", "t_str4" => "textf8q8wkl",
					"t_date1" => "2023-06-20T00:00:00+00:00", "t_date2" => "2023-06-20T00:00:00+00:00", "t_date3" => "2023-06-20T00:00:00+00:00", "t_date4" => "2023-06-20T00:00:00+00:00",
					"t_float1" => 9.8097, "t_float2" => 9.8097, "t_float3" => 9.8097, "t_float4" => 9.8097,
				]
			],
			"hydra:totalItems" => 1,
		]);
	}
	public function testReadCorrectMultiInsert()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, CREATE_READ_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, "t_int4" => 32363246 ], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 413, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, ], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, ], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, ], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, ], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, ], $perm2, 0);

		$result = $this->getInTable($client, $token["user_ademe1"], $perm2, 0);

		#dd($result);
		$this->assertJsonContains([
			"@context" => "/contexts/Table",
			"@type" => "hydra:Collection",
			"hydra:member" =>  [
				0 =>  [ "id" => 1, "t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 32363246],
				1 =>  [ "id" => 2, "t_int1" => 413, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214],
				2 =>  [ "id" => 3, "t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214],
				3 =>  [ "id" => 4, "t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214],
				4 =>  [ "id" => 5, "t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214],
				5 =>  [ "id" => 6, "t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214],
			], "hydra:totalItems" => 6,

		]);
	}
	public function testReadnotPermited()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, CREATE_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264214,
		], $perm2, 0);

		$result = $this->getInTable($client, $token["user_ademe1"], $perm2, 0, 401);
	}
	//------------------------------update-----------------------------

	public function testUpdateCorrect()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, ALL_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, 
		], $perm2, 0);

		$this->updateInTable($client, $token["user_ademe1"], [
			"t_int1" => 423, "t_int3" => 3264, "t_int4" => 314, 
		], $perm2, 0);
		$result = $this->getInTable($client, $token["user_ademe1"], $perm2, 0);

		$this->assertJsonContains([
			"@context" => "/contexts/Table",
			"@type" => "hydra:Collection",
			"hydra:member" => [            
				0 => ["id" => 1, "t_int1" => 423, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 314],
			],
			"hydra:totalItems" => 1,
		]);
	}
	public function testUpdateNotPermited()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, "11", $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, 
		], $perm2, 0);

		$this->updateInTable($client, $token["user_ademe1"], [
			"t_int1" => 423, "t_int3" => 3264, "t_int4" => 314, 
		], $perm2, 0, 401);
	}

	//-------------------------------delete-----------------------------------

	public function testDeleteCorrect()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, ALL_PERM, $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, 
		], $perm2, 0);

		$this->deleteInTable($client, $token["user_ademe1"], $perm2, 0);
		$result = $this->getInTable($client, $token["user_ademe1"], $perm2, 0);

		$this->assertJsonContains([
			"@context" => "/contexts/Table",
			"@type" => "hydra:Collection",
			"hydra:member" => [],
			"hydra:totalItems" => 0,
		]);
	}

	public function testDeleteNotPermited()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdmin($client, $token, $this->descriptionINT, "11", $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, 
		], $perm2, 0);

		$this->deleteInTable($client, $token["user_ademe1"], $perm2, 0, 401);
	}

	//-----------------------multidesc-----------------------------------------


	public function prepareTableAdminMultiDesc(Client $client, array $token, array $descArray1, array $descArray2, string $perm, string $user)
	{
		$desc = $this->createDesc($client, $token["admin_ademe"], "GDRradmin", "my description", $descArray1);
		$this->createDescArray($client, $token['admin_ademe'], [
			'name' => 'asd2',
			'description' => 'asd2',
			'descriptionArray' => $descArray2], $desc['@id']);
		$table = $this->createTable($client, $token["admin_ademe"], "GdrAdmin", $desc["categorie"]);
		$this->createPermission($client, $token["admin_ademe"], $user, $table["@id"], $perm);
	}

	public function testCreateMultiDesc()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);

		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdminMultiDesc($client, $token, $this->descriptionINT, $this->descriptionSTR, "255", $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264214,
		], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_str1" => "asd",
			"t_str2" => "asd",
			"t_str3" => "asd",
			"t_str4" => "saetewt",
		], $perm2, 1);
	}
	public function testCreateMultiDescpermissionOnlyCreate()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);

		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdminMultiDesc($client, $token, $this->descriptionINT, $this->descriptionSTR, "34", $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264214,
		], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_str1" => "asd",
			"t_str2" => "asd",
			"t_str3" => "asd",
			"t_str4" => "saetewt",
		], $perm2, 1);
	}
	public function testCreateMultiDescPermissionNotCreate()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);

		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdminMultiDesc($client, $token, $this->descriptionINT, $this->descriptionSTR, "221", $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264214,
		], $perm2, 0, 401);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_str1" => "asd",
			"t_str2" => "asd",
			"t_str3" => "asd",
			"t_str4" => "saetewt",
		], $perm2, 1, 401);
	}
	public function testCreateMultiDescAttrMissing()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);

		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdminMultiDesc($client, $token, $this->descriptionINT, $this->descriptionSTR, "255", $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
		], $perm2, 0, 422);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_str1" => "asd",
			"t_str2" => "asd",
			"t_str3" => "asd",
		], $perm2, 1, 422);
	}
	//-------------------------------multi read------------------------------------


	public function testReadMultiDescCorrect()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdminMultiDesc($client, $token, $this->descriptionINT, $this->descriptionSTR, "255", $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, ], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_str1" => "asd", "t_str2" => "asd", "t_str3" => "asd", "t_str4" => "a.,czsd", ], $perm2, 1);

		$result = $this->getInTable($client, $token["user_ademe1"], $perm2, 0);

		#dd($result);
		$this->assertJsonContains([
			"@context" => "/contexts/Table",
			"@type" => "hydra:Collection",
			"hydra:member" => [            
				0 => ["id" => 1, "t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214],
			],
			"hydra:totalItems" => 1,
		]);
		$result = $this->getInTable($client, $token["user_ademe1"], $perm2, 1);

		$this->assertJsonContains([
			"@context" => "/contexts/Table",
			"@type" => "hydra:Collection",
			"hydra:member" => [            
				0 => ["id" => 1, "t_str1" => "asd", "t_str2" => "asd", "t_str3" => "asd", "t_str4" => "a.,czsd", ],
			],
			"hydra:totalItems" => 1,
		]);
	}

	public function testReadMultiDescCorrectMultiInsert()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdminMultiDesc($client, $token, $this->descriptionINT, $this->descriptionSTR, "255", $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, "t_int4" => 32363246 ], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 413, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, ], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, ], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, ], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, ], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214, ], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_str1" => "asd", "t_str2" => "asd", "t_str3" => "asd", "t_str4" => "a.,czsd", ], $perm2, 1);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_str1" => "asd", "t_str2" => "asd", "t_str3" => "asd", "t_str4" => "asilhid", ], $perm2, 1);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_str1" => "asd", "t_str2" => "asd", "t_str3" => "asd", "t_str4" => "aaslksd", ], $perm2, 1);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_str1" => "aasdlsd", "t_str2" => "assadwrd", "t_str3" => "lkhiasd", "t_str4" => "vajgasd", ], $perm2, 1);

		$result = $this->getInTable($client, $token["user_ademe1"], $perm2, 0);

		#dd($result);
		$this->assertJsonContains([
			"@context" => "/contexts/Table",
			"@type" => "hydra:Collection",
			"hydra:member" =>  [
				0 =>  [ "id" => 1, "t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 32363246],
				1 =>  [ "id" => 2, "t_int1" => 413, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214],
				2 =>  [ "id" => 3, "t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214],
				3 =>  [ "id" => 4, "t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214],
				4 =>  [ "id" => 5, "t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214],
				5 =>  [ "id" => 6, "t_int1" => 3, "t_int2" => 525, "t_int3" => 3264, "t_int4" => 3264214],
			], "hydra:totalItems" => 6,

		]);
		$result = $this->getInTable($client, $token["user_ademe1"], $perm2, 1);

		#dd($result);
		$this->assertJsonContains([
			"@context" => "/contexts/Table",
			"@type" => "hydra:Collection",
			"hydra:member" =>  [
				0 =>  [ "id" => 1, "t_str1" => "asd", "t_str2" => "asd", "t_str3" => "asd", "t_str4" => "a.,czsd", ],
				1 =>  [ "id" => 2, "t_str1" => "asd", "t_str2" => "asd", "t_str3" => "asd", "t_str4" => "asilhid", ],
				2 =>  [ "id" => 3, "t_str1" => "asd", "t_str2" => "asd", "t_str3" => "asd", "t_str4" => "aaslksd", ],
				3 =>  [ "id" => 4, "t_str1" => "aasdlsd", "t_str2" => "assadwrd", "t_str3" => "lkhiasd", "t_str4" => "vajgasd", ],
			], "hydra:totalItems" => 4,
		]);
	}
	public function testReadMultiDescNotPermited()
	{
		$client = static::createClient();
		$token = $this->makeAllRole($client);
		$user = $this->GetUser($client, $token["admin_ademe"])["hydra:member"][1];
		$this->prepareTableAdminMultiDesc($client, $token, $this->descriptionINT, $this->descriptionSTR, "238", $user["@id"]);

		$perm = $this->GetIDstr($client, $token["user_ademe1"], '/permissions');
		$perm2 = $this->GetID($client, $token["user_ademe1"], $perm['hydra:member'][0]);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_int1" => 3,
			"t_int2" => 525,
			"t_int3" => 3264,
			"t_int4" => 3264214,
		], $perm2, 0);
		$this->createInTable($client, $token["user_ademe1"], [
			"t_str1" => "asd",
			"t_str2" => "asd",
			"t_str3" => "asd",
			"t_str4" => "asd",
		], $perm2, 1);

		$result = $this->getInTable($client, $token["user_ademe1"], $perm2, 0, 401);
		$result = $this->getInTable($client, $token["user_ademe1"], $perm2, 1, 401);
	}
}
