<?php

namespace App\Oauth2;

use League\OAuth2\Client\Provider\GenericResourceOwner;

class ResourceOwner extends GenericResourceOwner
{

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(array $response)
    {
        parent::__construct($response, "id");
    }

    public function getEmail(): string
    {
        return $this->response['email'];
    }

    public function getName(): string
    {
        return $this->response['name'];
    }

    public function getnickname(): string
    {
        return $this->response['nickname'];
    }

    /**
     * @return void
     */
    private ?string $role = NULL;
    private ?string $organisme = NULL;
    private function retriveGroups(): void
    {
        #dd($this->response);
        foreach ($this->response['groups'] as $role) {
            dump($role);
            if (!strncmp("orga_",$role,5))
                $this->organisme = substr($role, 5);
            if (!strncmp("role_",$role,5))
                $this->role = substr($role, 5);
            #$roles[] = $role['name'];
        }
    }

    public function getOrganisme(): string
    {
        if (!$this->organisme)
            $this->retriveGroups();
        return $this->organisme;
    }

    public function getRole(): string
    {
        if (!$this->role)
            $this->retriveGroups();
        return $this->role;
    }


}
