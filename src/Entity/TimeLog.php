<?php

namespace App\Entity;

use App\Config\TimeLogEvent;
use App\Repository\TimeLogRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: TimeLogRepository::class)]
class TimeLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $created = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $tracker = null;

    #[ORM\Column(type: 'string', enumType: TimeLogEvent::class)]
    private TimeLogEvent $event;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    public function setCreated(\DateTime $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getTracker(): ?UserInterface
    {
        return $this->tracker;
    }

    public function setTracker(?UserInterface $tracker): static
    {
        $this->tracker = $tracker;

        return $this;
    }

    public function getEvent(): ?TimeLogEvent
    {
        return $this->event;
    }

    public function setEvent(TimeLogEvent $event): static
    {
        $this->event = $event;

        return $this;
    }
}
