<?php

declare(ticks=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ArticleRepository;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 */
#[ApiResource(
    collectionOperations: [
        'get',
        'post' => ['security' => 'is_granted("ROLE_USER")']
    ],
    itemOperations: [
        'get',
        'put' => [
            'security' => 'is_granted("ROLE_USER") and object.getOwner() == user',
            'security_message' => 'only the creator can edit post'
        ],
        'delete' => ['security' => 'is_granted("ROLE_ADMIN")']
    ],
    attributes: ['pagination_items_per_page' => 10],
    denormalizationContext: ['groups' => ['article:write'], 'swagger_definition_name' => 'Write'],
    normalizationContext: ['groups' => ['article:read'], 'swagger_definition_name' => 'Read'],
)]
#[ApiFilter(BooleanFilter::class, properties: ['isPublished'])]
#[ApiFilter(SearchFilter::class, properties: ['title' => 'partial', 'longContent' => 'partial'])]
#[ApiFilter(PropertyFilter::class)]
class Article
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"article:read", "article:write", "user:read"})
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=2,
     *     max=100
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"article:read", "user:read"})
     */
    private $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"article:read", "article:write", "user:read"})
     */
    private $shortContent;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"article:read"})
     */
    private $longContent;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"article:write", "user:read"})
     */
    private $isPublished = false;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"article:read", "article:write"})
     */
    private $owner;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="article")
     */
    private $comments;

    public function __construct(string $title = null)
    {
        $this->title = $title;
        $this->createdAt = new \DateTimeImmutable();
        $this->slug = strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('', '-', ''), trim($title)));
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getShortContent(): ?string
    {
        return $this->shortContent;
    }

    public function setShortContent(?string $shortContent): self
    {
        $this->shortContent = $shortContent;

        return $this;
    }

    public function getLongContent(): ?string
    {
        return $this->longContent;
    }

    public function setLongContent(?string $longContent): self
    {
        $this->longContent = $longContent;

        return $this;
    }

    /**
     * The article as raw text.
     *
     * @Groups({"article:write"})
     */
    public function setTextLongContent(?string $longContent): self
    {
        $this->longContent = nl2br($longContent);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * How long ago article was added.
     *
     * @Groups({"article:read"})
     */
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setArticle($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getArticle() === $this) {
                $comment->setArticle(null);
            }
        }

        return $this;
    }
}
