<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TableDynamiqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\State\Provider\TableDynamiqueProvider;
use App\State\Processor\TableDynamiqueProcessor;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;

#[ORM\Entity(repositoryClass: TableDynamiqueRepository::class)]
##[ApiFilter(SearchFilter::class, properties: ['id' => 'exact'])]
class TableDynamique
{
    #[ORM\Id]
    #[ORM\Column()]
    #[ApiProperty(identifier: true)]
    #[ORM\GeneratedValue(strategy : "IDENTITY")] 
    private ?int $id = NULL;

    ##[ORM\Column(length: 255)]
    private ?string $name = null;

    private bool $relationClear = false;

    private array $data = [];
    public mixed $var1 = NULL;
    public mixed $var2 = NULL;
    public mixed $var3 = NULL;
    public mixed $var4 = NULL;
    public mixed $var5 = NULL;
    public mixed $var6 = NULL;
    public mixed $var7 = NULL;
    public mixed $var8 = NULL;
    public mixed $var9 = NULL;
    public mixed $var10 = NULL;
    public mixed $var11 = NULL;
    public mixed $var12 = NULL;
    public mixed $var13 = NULL;
    public mixed $var14 = NULL;
    public mixed $var15 = NULL;
    public mixed $var16 = NULL;
    public mixed $var17 = NULL;
    public mixed $var18 = NULL;
    public mixed $var19 = NULL;
    public mixed $var20 = NULL;
/*   public mixed $integer1 = NULL;
    public ?int $integer2 = NULL;
    public ?int $integer3 = NULL;
    public ?int $integer4 = NULL;
    public ?int $integer5 = NULL;
    public ?string $string1 = NULL;
    public ?string $string2 = NULL;
    public ?string $string3 = NULL;
    public ?string $string4 = NULL;
    public ?string $string5 = NULL;
    public ?float $double1 = null;
    public ?float $double2 = null;
    public ?float $double3 = null;
    public ?float $double4 = null;
    public ?float $double5 = null;
    #public ?\DateTimeInterface $date1 = null;
    public mixed $date1 = null;
    public ?\DateTimeInterface $date2 = null;
    public ?\DateTimeInterface $date3 = null;
    public ?\DateTimeInterface $date4 = null;
    public ?\DateTimeInterface $date5 = null;
 */

    #[ORM\ManyToOne(inversedBy: 'relation_one')]
    private ?TableDynamique $relation_many = null;

    #[ORM\OneToMany(mappedBy: 'relation_many', targetEntity: TableDynamique::class)]
    private Collection $relation_one;

    public function __construct()
    {
        $this->relation_one = new ArrayCollection();
        #$this->$date2 = DateTime::createFromInterface("2023-06-15T08:13:47.215Z");
        #$this->$date2 = DateTime::createFromInterface("2023-06-15T08:13:47.215Z");
        #$this->$date2 = "2023-06-15T08:13:47.215Z";
        //$date2 = new \DateTime("2023-06-15T08:13:47.215Z");
        //dd($date2);
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int
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

    public function getData()
    {
        $this->integer1 = NULL;
        return $this->data;
    }

    public function setData(array $data): self
    {
        dump("pas la");
        $this->data = $data;

        return $this;
    }

    public function retour(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    private function clearRelation()
    {
        dump($this);
        if ($this->relation_many && $this->relation_many->relation_one)
            $this->relation_many->relation_one->removeElement($this);
        dump($this->relation_one->getValues());
        if ($this->relation_one)
            foreach($this->relation_one->getValues() as $key => $value)
            {
                dump($key);
                $value->setRelationMany(NULL);
            }

        dump($this);
    }

    public function getRelationMany(): ?TableDynamique
    {
        dump($this);
        if ($this->relationClear == false)
        {
            $this->clearRelation();
            $this->relationClear = true;
        }
        dump("relation_nmany");
        return $this->relation_many;
    }

    public function setRelationMany(?TableDynamique $relation_many): self
    {
        dump("relation_nmany");
        $this->relation_many = $relation_many;

        return $this;
    }

    /**
     * @return Collection<int, TableDynamique>
     */
    public function getRelationOne(): Collection
    {
        dump("relation_nmany");
        return $this->relation_one;
    }

    public function addRelationOne(TableDynamique $relation_one): self
    {
        dump("relation_nmany");
        if (!$this->relation_one->contains($relation_one)) {
            $this->relation_one->add($relation_one);
            $relation_one->setrelation_one($this);
        }

        return $this;
    }

    public function removeRelationOne(TableDynamique $relation_one): self
    {
        if ($this->relation_one->removeElement($relation_one)) {
            // set the owning side to null (unless already changed)
            if ($relation_one->getrelation_one() === $this) {
                $relation_one->setrelation_one(null);
            }
        }

        return $this;
    }

}
