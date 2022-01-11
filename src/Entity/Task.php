<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    public const TITLE_MIN_LENGTH = 3;
    public const TITLE_MIN_MESSAGE = 'Le titre doit faire au moins {{ limit }} caractères.';
    public const TITLE_MAX_LENGTH = 255;
    public const TITLE_MAX_MESSAGE = 'Le titre ne peut pas faire plus de {{ limit }} caractères.';

    public const CONTENT_MAX_LENGTH = 8192;
    public const CONTENT_MAX_MESSAGE = 'Le contenu ne peut pas faire plus de {{ limit }} caractères.';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank(message: 'Vous devez saisir un titre.')]
    #[Assert\Length(min: self::TITLE_MIN_LENGTH, max: self::TITLE_MAX_LENGTH, minMessage: self::TITLE_MIN_MESSAGE, maxMessage: self::TITLE_MAX_MESSAGE)]
    private $title;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Vous devez saisir du contenu.')]
    #[Assert\Length(max: self::CONTENT_MAX_LENGTH, maxMessage: self::CONTENT_MAX_MESSAGE)]
    private $content;

    #[ORM\Column(type: 'boolean')]
    private $isDone;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tasks')]
    private $author;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'datetime')]
    private $lastUpdate;

    public function __construct()
    {
        $this->setCreatedAt(new \Datetime());
        $this->setLastUpdate(new \Datetime());
        $this->isDone = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function toggle(): self
    {
        $this->setIsDone(!$this->getIsDone());

        return $this;
    }

    public function getIsDone(): ?bool
    {
        return $this->isDone;
    }

    public function setIsDone(bool $isDone): self
    {
        $this->isDone = $isDone;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLastUpdate(): ?\DateTimeInterface
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(\DateTimeInterface $lastUpdate): self
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }
}
