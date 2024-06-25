<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TableRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\State\Provider\TableDynamiqueProvider;
use App\State\Processor\TableDynamiqueProcessor;
use App\State\Processor\FillTableProcessor;
use App\State\Provider\TableProvider;
use App\State\Processor\TableProcessor;
use App\State\Processor\ShareProcessor;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use App\Resolver\TableDynamiqueCollectionResolver;
use App\Resolver\TableDynamiqueResolver;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\GraphQl\Query;

use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;

#[ORM\Entity(repositoryClass: TableRepository::class)]
#[ORM\Table(name: '`table`')]

//---------------------------------Table-----------------------------
#[ApiResource(
    security: "is_granted('ROLE_CREATE_BASE')",
    provider: TableProvider::class, 
    normalizationContext: ['groups' => ['get:table']],
    )]

//get all description permited
#[GetCollection(
    security: "is_granted('ROLE_INTERACT_BASE')",
    normalizationContext: ['groups' => ['get:table', 'getC:table']],
)] // /tables

#[GetCollection(
    uriTemplate: '/tables/prive',
    normalizationContext: ['groups' => ['get:table', 'getC:table', 'getC:table:prive']],
)]

#[GetCollection(
    uriTemplate: '/tables/partage',
    normalizationContext: ['groups' => ['get:table', 'getC:table']],
)]

#[GetCollection(
    uriTemplate: '/tables/public',
    normalizationContext: ['groups' => ['get:table', 'getC:table']],
)]

#[Get(
    normalizationContext: ['groups' => ['get:table', 'getI:table']],
)]

#[Post(
    processor: TableProcessor::class,
    normalizationContext: ['groups' => ['get:table']],
    denormalizationContext: ['groups' => ['create:table']],
)]

//share description to public
#[Put(
    processor: TableProcessor::class,
    denormalizationContext: ['groups' => ['table:update']],
    normalizationContext: ['groups' => ['get:table']],
    validationContext: ['groups' => ['Default', 'update']],
)]

#[Post(
    uriTemplate: '/tables/partage/{id}/{userId}',
    requirements: ['id', 'userId'],
    uriVariables: ['id' => 'id', 'userId' => 'userId'],//todo add desc.name
    processor: ShareProcessor::class,
)]

#[Post(
    uriTemplate: '/fill/{id}/{name}',
    requirements: ['id', 'name', 'idInTable'],
    uriVariables: ['id' => 'id', 'name' => 'name'],//todo add desc.name
    denormalizationContext: ['groups' => ['fillIN:table']],
    normalizationContext: ['groups' => ['get:table']],
    processor: FillTableProcessor::class,
)]

//-----------------------------TableDynamique-------------------------

#[ApiResource(
    uriTemplate: '/tables/{id}/{name}',
    requirements: ['id', 'name'],
    uriVariables: ['id' => 'id', 'name' => 'name'],//todo add desc.name
    provider: TableDynamiqueProvider::class,
    security: "is_granted('ROLE_INTERACT_BASE')",
    normalizationContext: ['groups' => ['getIN:table', 'get:table']],
)]

#[Put(
    denormalizationContext: ['groups' => ['updateIN:table']],
    uriTemplate: '/tables/{id}/{name}/{idInTable}',
    requirements: ['id', 'name', 'idInTable'],
    uriVariables: ['id' => 'id', 'name' => 'name', 'idInTable' => 'idInTable'],//todo add desc.name
    processor: TableDynamiqueProcessor::class,
)]

#[Delete(
    uriTemplate: '/tables/{id}/{name}/filter',
    requirements: ['id', 'name'],
    uriVariables: ['id' => 'id', 'name' => 'name'],//todo add desc.name
    processor: TableDynamiqueProcessor::class,
)]
#[Delete(
    uriTemplate: '/tables/{id}/{name}/{idInTable}',
    requirements: ['id', 'name', 'idInTable'],
    uriVariables: ['id' => 'id', 'name' => 'name', 'idInTable' => 'idInTable'],//todo add desc.name
)]

#[Post(
    denormalizationContext: ['groups' => ['createIN:table']],
    normalizationContext: ['groups' => ['createIN:table']],
    processor: TableDynamiqueProcessor::class,
    openapiContext: [
        'summary' => 'Create a rabbit', 
        'description' => '#create a entry  in table',
        'requestBody' => [
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object', 
                        'properties' => [
                            'description' => ['data' => 'array']
                        ]
                    ], 
                    'example' => [
                        'data' => '{\'asd\' : \'asd\'}',
                    ]
                ]
            ]
        ]
    ]
        
)]

#[GetCollection()]

//----------------------------class---------------------------------

class Table
{
    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    #[ORM\Id]
    #[ApiProperty(identifier: true)]
    #[ORM\Column(length : 36, unique: true)]
    ##[ORM\Column(type: Types::GUID, unique: true)]
    #[Groups(["get:table"])]
    #[Assert\Uuid]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["create:table", "get:table", 'table:update'])]
    private ?string $name = null;

    #[Groups(["create:table", "get:table"])]
    #[ORM\Column(type: Types::GUID)]
    private ?string $categorie = null;

    #[Groups(["createIN:table", "getIN:table", "updateIN:table"])]
    public ?array $data = null;

    #[Groups(["admin:write", "get:table"])]
    #[ORM\OneToMany(mappedBy: 'tables', targetEntity: Permission::class, orphanRemoval: true)]
    private Collection $permissions;

    #[ORM\Column]
    #[Groups(["individual:read", "individual:write", 'table:update', 'get:table'])]
    #[SerializedName("isPublicShared")]
    private bool $partagePublic = false;


    #[Groups(['getC:table:prive'])]
    public bool $isShared = false;

    public function getCategorie(): ?string
    {
        dump($this->name);
        dump($this->categorie);
        return $this->categorie;
    }

    public function setCategorie(string $name): self
    {
        $this->categorie = $name;

        dump($this->name);
        return $this;
    }

    public function getName(): ?string
    {
        dump($this->name);
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        dump($this->name);
        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
            $permission->setTables($this);
        }

        return $this;
    }

    public function removePermission(Permission $permission): self
    {
        if ($this->permissions->removeElement($permission)) {
            // set the owning side to null (unless already changed)
            if ($permission->getTables() === $this) {
                $permission->setTables(null);
            }
        }

        return $this;
    }

    public function getPartagePublic(): ?bool
    {
        return $this->partagePublic;
    }

    public function setPartagePublic(bool $partagePublic): self
    {
        $this->partagePublic = $partagePublic;

        return $this;
    }
}
