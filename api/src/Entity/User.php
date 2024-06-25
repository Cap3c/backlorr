<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Ramsey\Uuid\Uuid;
use Doctrine\DBAL\Types\Types;

use Symfony\Component\Validator\Constraints as Assert;
use Symdony\Component\Validator\Constraints\NotBlank;
use App\Validator\IsValidOwner;

use App\State\Processor\UserProcessor;
use App\State\Provider\UserProvider;

use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;

#[UniqueEntity(fields: ["email"], message: "There is already an account with this email")]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]

#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
)]

#[GetCollection(
    uriTemplate: '/users/descs/{id}',
    requirements: ['id'],
    uriVariables: ['id' => 'id'],
    security: "is_granted('ROLE_CREATE_BASE')",
    provider: UserProvider::class,
)]

#[GetCollection(
    uriTemplate: '/users/tables/{id}',
    requirements: ['id'],
    uriVariables: ['id' => 'id'],
    security: "is_granted('ROLE_CREATE_BASE')",
    provider: UserProvider::class,
)]

#[GetCollection(
    security: "is_granted('ROLE_ADMIN')",
    provider: UserProvider::class,
    normalizationContext: ['groups' => ['userCollection:read', 'user:read']],
)]

#[Get(
#security: "(is_granted('ROLE_orga_admin') and
#    (object.getOrganisme() == user.getOrganisme() or object.getRoles()[0] == user.getRoles()[0]))",
    provider: UserProvider::class,
    normalizationContext: ['groups' => ['userAdmin:read', 'user:read']],
)]//todo orga public/private

#[Get(
    uriTemplate: '/user',
    provider: UserProvider::class,
    security: "object == user",
    normalizationContext: ['groups' => ['userIndividual:read', 'user:read']],
)]

#[Post(
    security: "(is_granted('ROLE_ADMIN'))",
    processor: UserProcessor::class,
    denormalizationContext: ['groups' => ['user:create']],
    validationContext: ['groups' => ['Default', 'user:create']]
)]

#[Put(
    uriTemplate: '/user',
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    #provider: UserProvider::class,
    processor: UserProcessor::class,
    validationContext: ['groups' => ['Default', 'user:update']],
    denormalizationContext: ['groups' => ['user:update']],
)]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Id]
    #[ApiProperty(identifier: true)]
    #[ORM\Column(length : 36, unique: true)]
    ##[ORM\Column(type: Types::GUID, unique: true)]
    #[Groups(['user:read', "orga:read"])]
    #[Assert\Uuid()]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups(['user:read', "orga:read", "user:create", "user:update"])]
    #[Assert\NotBlank(groups: ['user:create'])]
    #[Assert\Email()]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    #[Groups(["user:create"])]//write is overwrite
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private ?string $password;

    #[Groups(["user:update"])]
    #[SerializedName("password")]
    ##[Assert\NotBlank(groups: ['user:create'])]
    private $plainPassword;

    #[Groups(['user:read', "user:create", "user:update"])]
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(groups: ['user:create'])]
    #[SerializedName("username")]
    private ?string $name = null;

    #[ORM\Column(type: "boolean")]
    public bool $newPass = True;

    #[ORM\Column(type: "boolean")]
    private bool $isVerified = false;//maybe remove

    #[Groups(['userIndividual:read'])]
    #[ORM\OneToMany(mappedBy: 'users', targetEntity: Permission::class, orphanRemoval: true)]
    private Collection $Upermissions;

    ##[Groups([])]
    #[ORM\ManyToOne(inversedBy: 'membre')]
    #[ORM\JoinColumn(nullable: false)]
    private Organisme $organisme;

    #[ORM\Column(type: 'array', nullable: true)]
    private ?array $descs = [];

    #[ORM\Column(type: 'array', nullable: true)]
    private ?array $relatedDescs = [];

    #[ORM\Column(type: 'array', nullable: true)]
    private ?array $tables = [];

    #[ORM\Column(type: 'array', nullable: true)]
    private ?array $relatedTables = [];

    #[ORM\OneToMany(mappedBy: 'proprietaire', targetEntity: BaseUploader::class)]
    private Collection $baseUploaders;

    public function __construct()
    {
        $this->Upermissions = new ArrayCollection();
        #$this->descs = new ArrayCollection();
        #$this->relatedDesc = new ArrayCollection();
        #$this->tables = new ArrayCollection();
        #$this->relateTable = new ArrayCollection();
        $this->baseUploaders = new ArrayCollection();
        $this->id = Uuid::uuid4();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        #$roles[] = 'ROLE_USER';
        #$role[] = $roles;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setPlainPassword(string $password): self
    {
        $this->plainPassword = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->email;
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

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }


    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->Upermissions;
    }

    public function addPermission(Permission $permission): self
    {
        if (!$this->Upermissions->contains($permission)) {
            $this->Upermissions->add($permission);
            $permission->setUsers($this);
        }

        return $this;
    }

    public function removePermission(Permission $permission): self
    {
        if ($this->Upermissions->removeElement($permission)) {
            // set the owning side to null (unless already changed)
            if ($permission->getUsers() === $this) {
                $permission->setUsers(null);
            }
        }

        return $this;
    }

    public function getOrganisme(): ?Organisme
    {
        return $this->organisme;
    }

    public function setOrganisme(?Organisme $organisme): self
    {
        dump($organisme);
        $this->organisme = $organisme;

        return $this;
    }

    public function fillAdmin(string $email, string $password, string $name, Organisme $orga): self
    {
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->isVerified = true;
        $this->roles = ["ROLE_orga_admin"];
        $this->organisme = $orga;
        return $this;
    }

    /**
     * @return Collection<int, Desc>
     */

    public function getDescs(): array|NULL
    {
        //return array_unique($this->descs);
        return ($this->descs);
    }

    public function addDesc(string $desc): self
    {
        $this->descs[] = $desc;
        dump($this->descs);
        return $this;
        //end
        if (!$this->descs->contains($desc)) {
            $this->descs->add($desc);
            $desc->setProprietaire($this);
        }

        return $this;
    }

    public function removeDesc(string $desc): self
    {
        if (($key = array_search($desc, $this->descs)) !== false) {
            unset($this->descs[$desc]);
            return $this;
        }
        return NULL;
    #    //end
        if ($this->descs->removeElement($desc)) {
            // set the owning side to null (unless already changed)
            if ($desc->getProprietaire() === $this) {
                $desc->setProprietaire(null);
            }
        }

        return $this;
    }

    public function getTables(): array|NULL
    {
        //return array_unique($this->tables);
        return ($this->tables);
    }

    public function addTable(string $table): self
    {
        $this->tables[] = $table;
        dump($this->tables);
        return $this;
    }

    public function removeTable(string $table): self
    {
        if (($key = array_search($table, $this->tables)) !== false) {
            unset($this->tables[$table]);
            return $this;
        }
        return NULL;
    }

    public function getRelatedTables(): array | NULL
    {
        //return array_unique($this->relatedTables);
        return ($this->relatedTables);
    }

    public function addRelatedTable(string $table): self
    {
        $this->relatedTables[] = $table;
        dump($this->tables);
        return $this;
        //end
    }

    public function removeRelatedTable(string $table): self
    {
        if (($key = array_search($table, $this->relatedTables)) !== false) {
            unset($this->relatedTables[$table]);
            return $this;
        }
        return NULL;
    }

    public function getRelatedDescs(): array | NULL
    {
        //return array_unique($this->relatedDescs);
        return ($this->relatedDescs);
    }

    public function addRelatedDesc(string $desc): self
    {
        $this->relatedDescs[] = $desc;
        dump($this->descs);
        return $this;
        //end
    }

    public function removeRelatedDesc(string $desc): self
    {
        if (($key = array_search($desc, $this->relatedDescs)) !== false) {
            unset($this->relatedDescs[$desc]);
            return $this;
        }
        return NULL;
    }


    /**
     * @return Collection<int, Table>
     */
    /*
    public function getTables(): Collection
    {
        return $this->tables;
    }

    public function addTable(Table $table): self
    {
        if (!$this->tables->contains($table)) {
            $this->tables->add($table);
            $table->setProprietaire($this);
        }

        return $this;
    }

    public function removeTable(Table $table): self
    {
        if ($this->tables->removeElement($table)) {
            // set the owning side to null (unless already changed)
            if ($table->getProprietaire() === $this) {
                $table->setProprietaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Table>
     */
    /*
    public function getRelateTable(): Collection
    {
        return $this->relateTable;
    }

    public function addRelateTable(Table $relateTable): self
    {
        if (!$this->relateTable->contains($relateTable)) {
            $this->relateTable->add($relateTable);
            $relateTable->addPartagePrive($this);
        }

        return $this;
    }

    public function removeRelateTable(Table $relateTable): self
    {
        if ($this->relateTable->removeElement($relateTable)) {
            $relateTable->removePartagePrive($this);
        }

        return $this;
    }
     */

    /**
     * @return Collection<int, BaseUploader>
     */
    public function getBaseUploaders(): Collection
    {
        return $this->baseUploaders;
    }

    public function addBaseUploader(BaseUploader $baseUploader): static
    {
        if (!$this->baseUploaders->contains($baseUploader)) {
            $this->baseUploaders->add($baseUploader);
            $baseUploader->setProprietaire($this);
        }

        return $this;
    }

    public function removeBaseUploader(BaseUploader $baseUploader): static
    {
        if ($this->baseUploaders->removeElement($baseUploader)) {
            // set the owning side to null (unless already changed)
            if ($baseUploader->getProprietaire() === $this) {
                $baseUploader->setProprietaire(null);
            }
        }

        return $this;
    }
}
