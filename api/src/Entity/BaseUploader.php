<?php
// api/src/Entity/BaseUploader.php
namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Ramsey\Uuid\Uuid;
use Doctrine\DBAL\Types\Types;
use App\Controller\CreateBaseUploaderAction;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Entity\User;
use App\Entity\Desc;
use App\State\Processor\FillTableProcessor;
use Symdony\Component\Validator\Constraints\NotBlank;
use Symdony\Component\Validator\Constraints\NotNull;

#[Vich\Uploadable]
#[ORM\Entity]
#[ApiResource(
    
    security: "is_granted('ROLE_CREATE_BASE')",
    types: ['https://schema.org/BaseUploader'],
)]
#[Get(
    normalizationContext: ['groups' => ['base_uploader:read']], 
)]

#[GetCollection(
    normalizationContext: ['groups' => ['base_uploader:read']], 
)]

#[Post(
    uriTemplate: '/uploader',
    normalizationContext: ['groups' => ['base_uploader:read']], 
    denormalizationContext: ['groups' => ['base_uploader:create']], 
    controller: CreateBaseUploaderAction::class, 
    deserialize: false, 
    validationContext: ['groups' => ['Default', 'base_uploader_create']], 
    openapiContext: [
'summary' => 'upload a lapin',
'requestBody' => [
    'content' => [
        'multipart/form-data' => [
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'file' => [
                        'type' => 'string',
                        'format' => 'binary'
                    ],
#                    'desc' => [
#                        'type' => 'string',
#                        'format' => 'path'
#                    ]
                ]]]
    
    ],
 "required" => true
]])]


#[Put(
    uriTemplate: '/uploader/{id}',
    requirements: ['id'],
    uriVariables: ['id' => 'id'],
    denormalizationContext: ['groups' => ['BaseUploader:desc:add']], 
    #processor: BaseUploader::class,
    validationContext: ['groups' => ['Default']]
)]

#[Post(
    uriTemplate: '/fill/{id}',
    requirements: ['id'],
    uriVariables: ['id' => 'id'],//todo add desc.name
    validationContext: ['groups' => ['Default', 'fill:inTable']], 
    denormalizationContext: ['groups' => ['fill:inTable']],
    security: "is_granted('ROLE_CREATE_BASE')",# or user.getOrganisme().getName() == 'cap3c'",
    processor: FillTableProcessor::class,
)]

class BaseUploader
{
    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    #[ORM\Id]
    #[ApiProperty(identifier: true)]
    #[ORM\Column(length : 36, unique: true)]
    ##[ORM\Column(type: Types::GUID, unique: true)]
    #[Assert\Uuid]
    private ?string $id = null;

    #[ApiProperty(types: ['https://schema.org/contentUrl'])]
    #[Groups(['base_uploader:read'])]
    public ?string $contentUrl = null;

    #[Vich\UploadableField(mapping: "base_uploader", fileNameProperty: "filePath")]
    #[Groups(['base_uploader:create'])]
    #[Assert\NotNull(groups: ['base_uploader_create'])]
    public ?File $file = null;

    #[ORM\Column(nullable: true)] 
    public ?string $filePath = null;

    #[ORM\ManyToOne(inversedBy: 'baseUploaders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $proprietaire = null;

    #[Groups(['fill:inTable'])]
    #[Assert\NotBlank(groups: ["fill:inTable"], allowNull: false)]
    public ?Table $table = null;

    #[ORM\Column(type: Types::GUID, nullable: true)]
    #[Groups(['BaseUploader:desc:add'])]
    private ?string $categorie = null;
    
    public function setTable(Table $table)
    {
        $this->table = $table;
        return $this;
    }
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getProprietaire(): ?User
    {
        return $this->proprietaire;
    }

    public function setProprietaire(?User $proprietaire): static
    {
        $this->proprietaire = $proprietaire;

        return $this;
    }

    public function getCategorie(): ?uuid
    {
        return $this->categorie;
    }

    public function setCategorie(uuid $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }
}
