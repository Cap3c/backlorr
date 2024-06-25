<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\OrganismeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\State\Processor\OrganismeProcessor;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\Uuid;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;

#[ORM\Entity(repositoryClass: OrganismeRepository::class)]
#[ApiResource(
normalizationContext: ['groups' => ['orga:read']],
     validationContext: ['groups' => ['Default']]
)]

#[GetCollection(
    security: "is_granted('ROLE_CREATE_BASE') or is_granted('ROLE_cap3c_support_tech')",
    normalizationContext: ['groups' => ['orga:read', 'orgaCollection:read']],
)]

#[Get(
    security: "is_granted('ROLE_ADMIN')",
    normalizationContext: ['groups' => ['orga:read', 'orgaRoleAdmin:read']],
)]

#[Get(
    security: "user.getOrganisme().getName() == 'cap3c'",
    normalizationContext: ['groups' => ['orga:read', 'orgaCap3c:read']],
)]

/*
#[Put(
    security: "is_granted('ROLE_cap3c_support_tech')",
    processor: OrganismeProcessor::class,
    denormalizationContext: ['groups' => ['update']],
)]
 */

#[Post(
    security: "is_granted('ROLE_cap3c_support_tech')",
    processor: OrganismeProcessor::class,
    denormalizationContext: ['groups' => ['create']],
)]

class Organisme
{
    #[ORM\Id]
    #[ApiProperty(identifier: true)]
    #[ORM\Column(length : 36, unique: true)]
    ##[ORM\Column(type: Types::GUID)]
    #[Assert\Ramsey\Uuid\Uuid(groups: ['Default'])]
    private ?string $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["orga:read", "create"])]
    #[Assert\NotBlank(groups: ['create'])]
    private ?string $name = null;

    #[Groups(["orgaCap3c:read"])]
    #[ORM\OneToMany(mappedBy: 'organisme', targetEntity: User::class, orphanRemoval: true)]
    private Collection $membre;

    #[Groups(["create"])]
    #[SerializedName("admin")]
    #[Assert\NotBlank(groups: ['create'])]
    ##[Assert\NotBlank()]
    private ?array $admin = null;

    public function __construct()
    {
        $this->membre = new ArrayCollection();
        $this->id = Uuid::uuid4();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAdmin(): array
    {
        return($this->admin);
    }

    public function setAdmin(array $admin): self
    {
        $this->admin = $admin;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembre(): Collection
    {
        dump("get");
        return $this->membre;
    }

    public function addMembre(User $membre): self
    {
        dump("add");
        if (!$this->membre->contains($membre)) {
            $this->membre->add($membre);
            $membre->setOrganisme($this);
        }

        return $this;
    }

    public function removeMembre(User $membre): self
    {
        dump("rm");
        if ($this->membre->removeElement($membre)) {
            // set the owning side to null (unless already changed)
            if ($membre->getOrganisme() === $this) {
                $membre->setOrganisme(null);
            }
        }

        return $this;
    }

    #[Groups(["orga:read"])]
    public function getAdminID()
    {
        if ($this->name == "cap3c")
        {
            $tmp = [];
            foreach($this->membre as $user)
                if ($user->getRoles()[0] == "ROLE_cap3c_R&D")
                    $tmp[] = $user;
            return $tmp;
        }
        foreach($this->membre as $user)
            if ($user->getRoles()[0] == "ROLE_orga_admin")
                return $user;
        return NULL;
    }


    /*
     public function setAdminID(User $adminID): self
     {
        #$this->adminID = $adminID;

        return $this;
     }
     */


}
