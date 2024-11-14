<?php

namespace App\Entity;

use Symdony\Component\Validator\Constraints\NotBlank;
use Symdony\Component\Validator\Constraints\NotNull;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\DescRepository;
use App\State\Processor\ShareProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Ramsey\Uuid\Uuid;

use App\State\Processor\DescProcessor;
use App\State\Provider\DescProvider;

use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;

#[ORM\Entity(repositoryClass: DescRepository::class)]
#[ORM\Table(name: '`desc`')]

#[ApiResource(
    security: "is_granted('ROLE_CREATE_BASE')",
    provider: DescProvider::class,
    normalizationContext: ['groups' => ['descCollection:read']],
    )]

//get all description permited
##[GetCollection()]
#[GetCollection(
    uriTemplate: '/descs/prive',
    normalizationContext: ['groups' => ['descCollection:read', 'getC:desc:prive']],
)]
#[GetCollection(
    uriTemplate: '/descs/partage',
)]
#[GetCollection(
    uriTemplate: '/descs/public',
)]

#[Post(
    uriTemplate: '/descs/partage/{id}/{userId}',
    requirements: ['id', 'userId'],
    uriVariables: ['id' => 'id', 'userId' => 'userId'],//todo add desc.name
    processor: ShareProcessor::class,
)]

#[ApiResource(
    validationContext: ['groups' => ['Default']],
    normalizationContext: ['groups' => ['desc:read']],
)]
//get one group of description permited
#[Get(
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    uriTemplate: '/descs/{categorie}',
    requirements: ['categorie'],
    uriVariables: ['categorie' => 'categorie'],
    normalizationContext: ['groups' => ['desc:read']],
    provider: DescProvider::class,
)]

//get one description permited
#[Get(
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    uriTemplate: '/descs/{categorie}/{name}',
    requirements: ['categorie', 'name'],
    uriVariables: ['categorie' => 'categorie', 'name' => 'name'],
    normalizationContext: ['groups' => ['desc:read']],
    provider: DescProvider::class,
)]

//update name and description all time
//and descriptionArray if never use before
#[Put(
    uriTemplate: '/descs/{categorie}/{name}',
    requirements: ['categorie', 'name'],
    uriVariables: ['categorie' => 'categorie', 'name' => 'name'],
    security: "is_granted('ROLE_CREATE_BASE')",
    denormalizationContext: ['groups' => ['descMeta:update']],
    processor: DescProcessor::class,
    provider: DescProvider::class,
    validationContext: ['groups' => ['Default']]
)]

//create one description and put it in categorie
#[Post(
    uriTemplate: '/descs/{categorie}',
    requirements: ['categorie'],
    uriVariables: ['categorie' => 'categorie'],
    security: "is_granted('ROLE_CREATE_BASE')",
    processor: DescProcessor::class,
    provider: DescProvider::class,
    denormalizationContext: ['groups' => ['desc:create']],
    #normalizationContext: ['groups' => ['desc:create']],
    normalizationContext: ['groups' => ['desc:read']],
    validationContext: ['groups' => ['Default', 'create']]
)]

//create one description and new categorie
#[Post(
    security: "is_granted('ROLE_CREATE_BASE')",
    processor: DescProcessor::class,
    #provider: DescProvider::class,
    denormalizationContext: ['groups' => ['desc:create']],
    normalizationContext: ['groups' => ['desc:read']],
    validationContext: ['groups' => ['Default', 'create']]
)]

//--------------------------------class-------------------------
class Desc
{
    public function __construct()
    {
        $this->categorie = Uuid::uuid4();
        #$this->partagePrive = new ArrayCollection();
    }

    #[Groups(["desc:read", "descCollection:read", "desc:create:read"])]
    #[ORM\Id]
    #[ApiProperty(identifier: true)]
    #[Assert\Uuid]
    #[ORM\Column(length : 36)]
    ##[ORM\Column(type: Types::GUID)]
    #[Assert\NotBlank(groups: ['create'], allowNull : true)]
    private ?string $categorie = null;

    #[ORM\Id]
    #[Groups(["desc:read", "descCollection:read", "desc:create", "descMeta:update", 'permission:read'])]
    #[ApiProperty(identifier: true)]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['create'])]
    ##[Assert\Length(min: 5, groups: ['create'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $permNumber = 0;//1 perm public

    #[ORM\Column(length: 255)]
    #[Groups(["desc:read", "descCollection:read", "desc:create", "descMeta:update"])]
    #ValidationGroup({"desc:create"})
    #[Assert\NotBlank(groups: ["create"])]
    #[Assert\NotNull(groups: ["create"])]
    private ?string $description = null;//describe what is this desc

    #[ORM\Column(type: Types::ARRAY)]
    #[Assert\NotBlank(groups: ["create"])]
    #[Assert\NotNull(groups: ["create"])]
    #[Groups(["desc:read", "desc:create", "descData:update", 'permission:read'])]
    private array $descriptionArray = [];

    #[ORM\Column]
    ##[Groups(["individual:read", "individual:write"])]
    #[Groups(["desc:read", "descCollection:read", 'descMeta:update'])]
    #[SerializedName("isPublicShared")]
    private bool $partagePublic = false;

    #[ORM\Column(type: "boolean")]
    #[Groups(["desc:read"])]
    public bool $first_use = false;

    #[ORM\Column(nullable: true)]
    private ?int $cleanOperation = null;

    #[ORM\Column()] //edit in command/setWorkingId
    public int $workingId = 0;

    #[Groups(['getC:desc:prive'])]
    public bool $isShared = false;

    ##[ORM\Column(type: Types::GUID)]
    ##[ORM\ManyToOne(inversedBy: 'descs', mappedBy: "user")]
    ##[ORM\JoinColumn(nullable: false, referencedColumnName: "id")]
    #private ?User $proprietaire = null;

    ##[Groups(["individual:read", "individual:write"])]
    ##[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'relatedDesc')]
    #private Collection $partagePrive;


    public function setCategorie(string $n): self
    {
        dump("categorie existe");
        #$this->permNumber = 1;//if categorie is sending permnumber is not 1 or dont register
        $this->categorie = $n;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    #[Groups(["desc:read", "descCollection:read"])]
    public function getPerm(): ?int
    {
        return $this->permNumber;
    }

    public function setPerm(int $name): self
    {
        $this->permNumber = $name;
        dump($name);
        dump($this->permNumber);

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescriptionArray(): array
    {
        return $this->descriptionArray;
    }

    public function setDescriptionArray(array $descriptionArray): self
    {
        $this->descriptionArray = $descriptionArray;

        return $this;
    }
/*
    public function isPartagePublic(): ?bool
    {
        return $this->partagePublic;
    }
*/

    public function getPartagePublic(): ?bool
    {
        return $this->partagePublic;
    }

    public function setPartagePublic(bool $partagePublic): self
    {
        $this->partagePublic = $partagePublic;

        return $this;
    }

    public function getProprietaire(): ?User
    {
        return $this->proprietaire;
    }

    public function setProprietaire(?User $proprietaire): self
    {
        $this->proprietaire = $proprietaire;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getPartagePrive(): Collection
    {
        return $this->partagePrive;
    }

    public function addPartagePrive(User $partagePrive): self
    {
        if (!$this->partagePrive->contains($partagePrive)) {
            $this->partagePrive->add($partagePrive);
        }

        return $this;
    }

    public function removePartagePrive(User $partagePrive): self
    {
        $this->partagePrive->removeElement($partagePrive);

        return $this;
    }

    public function getCleanOperation(): ?int
    {
        return $this->cleanOperation;
    }

    public function setCleanOperation(?int $cleanOperation): static
    {
        $this->cleanOperation = $cleanOperation;

        return $this;
    }
}
