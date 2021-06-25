<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ParticipantRepository::class)
 */
class Participant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $email;

    

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_anomymous;

    /**
     * @ORM\ManyToOne(targetEntity=Campaign::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $campaign_id;


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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }


    public function getIsAnomymous(): ?bool
    {
        return $this->is_anomymous;
    }

    public function setIsAnomymous(bool $is_anomymous): self
    {
        $this->is_anomymous = $is_anomymous;

        return $this;
    }

    public function getCampaignId(): ?Campaign
    {
        return $this->campaign_id;
    }

    public function setCampaignId(?Campaign $campaign_id): self
    {
        $this->campaign_id = $campaign_id;

        return $this;
    }
}
