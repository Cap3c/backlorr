<?php

namespace App\KeyOauth2;

use League\OAuth2\Client\Provider\GenericResourceOwner;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        //dd($this->response);
        foreach ($this->response['groups'] as $role) {
            dump($role);
            if (!strncmp("/organisme/",$role,11))
                $this->organisme = substr($role, 11);
            if (!strncmp("/lorrrole/",$role,10))
                $this->role = substr($role, 10);
            #$roles[] = $role['name'];
        }
    }

    public function getOrganisme(): string
    {
        if (!$this->organisme)
            $this->retriveGroups();
        if (!$this->organisme)
            throw new HttpException(403, "organisme must be set");
        return $this->organisme;
    }

    public function getRole(): string
    {
        if (!$this->role)
            $this->retriveGroups();
        if (!$this->role)
            $this->role = "user";
        return $this->role;
    }


}
