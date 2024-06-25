<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PermissionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\State\Provider\PermissionProvider;
use App\State\Processor\PermissionProcessor;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
//----------------------------permission def---------------------------
#[ApiResource(
    normalizationContext: ['groups' => ['permission:read']],
    #security: "is_granted('ROLE_orga_user')",
#security: "(!is_granted('ROLE_orga_user') and object.users.getOrganisme() == user.getOrganisme()) or
#    (is_granted('ROLE_orga_user') and object.users == user)"
)]

#[GetCollection(
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    #normalizationContext: ['groups' => ['permission:read']],
    provider: PermissionProvider::class, 
    ##security: "is_granted('ROLE_orga_admin') or user.getOrganisme().getName() == 'cap3c'"
)]

#[Get(
    security: "object.getUsers().getOrganisme() == user.getOrganisme()",
    normalizationContext: ['groups' => ['permission:read', "permission:get"]],
    provider: PermissionProvider::class, 
#    denormalizationContext: ['groups' => ['user:read']]
)]

#[Put(
    security: "is_granted('ROLE_orga_admin') and object.getUsers().getOrganisme() == user.getOrganisme()",
    denormalizationContext: ['groups' => ['update:permission']],
    processor: PermissionProcessor::class, 
    provider: PermissionProvider::class, 
)]

#[Post(
    security: "is_granted('ROLE_ADMIN')",
    denormalizationContext: ['groups' => ['create:permission']],
    processor: PermissionProcessor::class, 
    provider: PermissionProvider::class, 
)]
//-----------------------------permission class------------------------
class Permission
{
    #[ORM\Id]
    #[ApiProperty(identifier: true)]
    #[ORM\Column(length : 36, unique: true)]
    ##[ORM\Column(type: Types::GUID, unique: true)]
    #[Assert\Uuid]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity:"User", inversedBy: 'Upermissions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["create:permission"])]
    #[Assert\NotBlank(create:permission)]
    private ?User $users = null;

    #[ORM\ManyToOne(inversedBy: 'permissions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["permission:read", "create:permission"])]
    #[Assert\NotBlank(create:permission)]
    private ?Table $tables = null;

    #[ORM\Column(type: 'string')]
    #[Groups(["permission:read", "create:permission", "update:permission"])]
    #[Assert\NotBlank()]
    private ?string $value = null;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUsers(): ?User
    {
        dump("get");
        return $this->users;
    }

    public function setUsers(?User $users): self
    {
        //throw new Exception("user");
        //dump($users);
        $this->users = $users;

        return $this;
    }

    #[Groups(["permission:read"])]
    public function getTableName(): string
    {
        return $this->tables->getName();
    }

    public function getTables(): ?Table
    {
        return $this->tables;
    }

    public function setTables(?Table $tables): self
    {
        $this->tables = $tables;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }


    public array $desc;

    #[Groups(["permission:get"])]
    public function getDescCategorie()
    {
        return($this->desc);
        dd($this->desc);
        return "asd";
    }
}
