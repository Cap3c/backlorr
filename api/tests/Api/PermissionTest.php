<?php

namespace App\Tests\Api;

use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class PermissionTest extends toolTestCase
{
    use ReloadDatabaseTrait;

    //------------------------------------------------------

    public function forRoleNoAdminTestCase(string $role, int $httpResponse = 403)
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        for($i = 0; $i < $users_orga['hydra:totalItems']; ++$i)
            $this->createPermission($client, $token[$role], $users_orga['hydra:member'][$i]['@id'], $table['@id'], '2', $httpResponse);
    }

    public function testForRoleCap3c()
    {
        $this->forRoleNoAdminTestCase('cap3c', 403);
    }

    public function testForRoleEtude()
    {
        $this->forRoleNoAdminTestCase('etude', 403);
    }

    public function testForRoleSupport()
    {
        $this->forRoleNoAdminTestCase('support', 403);
    }

    public function testForRoleAdminEmmaus()//it is the other admin, fail because it cant access user
    {
        $this->forRoleNoAdminTestCase('admin_emmaus', 403);
    }

    public function testForRoleUser()
    {
        $this->forRoleNoAdminTestCase('user_ademe1', 403);
    }

    public function testForRoleEmpty()
    {
        $this->forRoleNoAdminTestCase('empty', 401);
    }

    public function testAddPermissionAdminToAdmin()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);
        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);

        for ($i = 0; $i < 2; $i++)
            if ($users_orga['hydra:member'][$i]["email"] == "admin@ademe.a")
                $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][$i]['@id'], $table['@id'], '2', 422);
    }

	public function testAddPermissionAdminToUser()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        for ($i = 0; $i < 2; $i++)
            if ($users_orga['hydra:member'][$i]["email"] != "admin@ademe.a")
                $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][$i]['@id'], $table['@id'], '2');
    }

	public function testAddPermissionAdminSameUser()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        for ($i = 0; $i < 2; $i++)
            if ($users_orga['hydra:member'][$i]["email"] != "admin@ademe.a")
            {
        $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][$i]['@id'], $table['@id'], '2');
        $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][$i]['@id'], $table['@id'], '2', 422);
            }
    }

	public function testAddPermissionAdminToNonExistantUser()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        $this->createPermission($client, $token['etude'], '/users/2', $table['@id'], '2', 403);//this user do not exist
    }

	public function testAddPermissionAdminMaxValue()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);
        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][2]['@id'], $table['@id'], '15');
    }
	public function testAddPermissionAdminBigValue()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);
        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][2]['@id'], $table['@id'], '16', 422);
    }
    //---------------------------------update-------------------------------------------------

    public function testUpdatePermission()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        $perm = $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][1]['@id'], $table['@id'], '2');

        #dd($perm);
        $this->updatePermission($client, $token['admin_ademe'], ['value' => '1'], $perm['@id']);
    }

	public function testUpdatePermissionValueInt()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        $perm = $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][1]['@id'], $table['@id'], '2');

        #dd($perm);
        $this->updatePermission($client, $token['admin_ademe'], ['value' => 1], $perm['@id'], 400);
    }

	public function testUpdatePermissionAllValue()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        $perm = $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][1]['@id'], $table['@id'], '2');

        #dd($perm);
        $i = 0;
        for (; $i < 16; $i++)
            $this->updatePermission($client, $token['admin_ademe'], ['value' => strval($i)], $perm['@id']);
        for (; $i < 20; $i++)
            $this->updatePermission($client, $token['admin_ademe'], ['value' => strval($i)], $perm['@id'], 422);
    }

	public function testUpdatePermissionByUser()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        $perm = $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][1]['@id'], $table['@id'], '2');

        #dd($perm);
        $this->updatePermission($client, $token['user_ademe1'], ['value' => '1'], $perm['@id'], 403);
    }

	public function testUpdatePermissionWrongId()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        $perm = $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][1]['@id'], $table['@id'], '2');

        #dd($perm);
        $this->updatePermission($client, $token['admin_ademe'], ['value' => '1'], 'permissions/a', 404);
        $this->updatePermission($client, $token['admin_ademe'], ['value' => '1'], 'permissions/1', 404);
    }

    //--------------------------------------multi desc------------------------------------
    public function testCreatePermissionMultiDesc()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $this->createDescArray($client, $token['admin_ademe'], [
                'name' => 'asd2',
                'description' => 'asd2',
                'descriptionArray' => $this->descriptionINT], $desc['@id']);

        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        $perm = $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][1]['@id'], $table['@id'], '2');
    }

	public function testCreatePermissionMultiDescSameUser()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $this->createDescArray($client, $token['admin_ademe'], [
                'name' => 'asd2',
                'description' => 'asd2',
                'descriptionArray' => $this->descriptionINT], $desc['@id']);

        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        $perm = $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][1]['@id'], $table['@id'], '2');
        $perm = $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][1]['@id'], $table['@id'], '2', 422);
    }

	public function testCreatePermissionMultiDescMaxValue()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $this->createDescArray($client, $token['admin_ademe'], [
                'name' => 'asd2',
                'description' => 'asd2',
                'descriptionArray' => $this->descriptionINT], $desc['@id']);

        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        $perm = $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][1]['@id'], $table['@id'], '255');
    }
	public function testCreatePermissionMultiDescBigValue()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $this->createDescArray($client, $token['admin_ademe'], [
                'name' => 'asd2',
                'description' => 'asd2',
                'descriptionArray' => $this->descriptionINT], $desc['@id']);

        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        $perm = $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][1]['@id'], $table['@id'], '256', 422);
    }

	public function testCreatePermissionMultiDescMidValue()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description', $this->descriptionINT);
        $this->createDescArray($client, $token['admin_ademe'], [
                'name' => 'asd2',
                'description' => 'asd2',
                'descriptionArray' => $this->descriptionINT], $desc['@id']);

        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        $perm = $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][1]['@id'], $table['@id'], '6');
    }

	public function testUpdatePermissionMultiDesc()
    {
        $client = static::createClient();
        $token = $this->makeAllRole($client);

        $desc = $this->createDesc($client, $token['admin_ademe'], 'GDR_admin', 'my description',
            $this->descriptionINT);
        $this->createDescArray($client, $token['admin_ademe'], [
                'name' => 'asd2',
                'description' => 'asd2',
                'descriptionArray' => $this->descriptionINT], $desc['@id']);

        $table = $this->createTable($client, $token['admin_ademe'], 'Gdr_admin', $desc['categorie']);
        $users_orga = $this->GetUser($client, $token['admin_ademe']);
        $perm = $this->createPermission($client, $token['admin_ademe'], $users_orga['hydra:member'][1]['@id'], $table['@id'], '2');

        #dd($perm);
        $this->updatePermission($client, $token['admin_ademe'], ['value' => '1'], 'permissions/a', 404);
        $this->updatePermission($client, $token['admin_ademe'], ['value' => '1'], 'permissions/1', 404);
        $i = 0;
        for (; $i < 256; $i++)
            $this->updatePermission($client, $token['admin_ademe'], ['value' => strval($i)], $perm['@id']);
        for (; $i < 260; $i++)
            $this->updatePermission($client, $token['admin_ademe'], ['value' => strval($i)], $perm['@id'], 422);
    }
}
