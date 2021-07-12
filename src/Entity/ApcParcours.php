<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/ApcParcours.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 02/06/2021 14:02
 */

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use App\Repository\ApcParcoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApcParcoursRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ApcParcours extends BaseEntity
{
    use LifeCycleTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $libelle;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private ?string $code;

    /**
     * @ORM\OneToMany(targetEntity=ApcParcoursNiveau::class, mappedBy="parcours")
     */
    private Collection $apcParcoursNiveaux;

    /**
     * @ORM\ManyToOne(targetEntity=Departement::class, inversedBy="apcParcours")
     */
    private ?Departement $departement;

    /**
     * @ORM\OneToMany(targetEntity=ApcSaeParcours::class, mappedBy="parcours")
     */
    private Collection $apcSaeParcours;

    /**
     * @ORM\OneToMany(targetEntity=ApcRessourceParcours::class, mappedBy="parcours")
     */
    private Collection $apcRessourceParcours;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $textePresentation;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $couleur;

    /**
     * @ORM\Column(type="integer")
     */
    private $ordre;

    public function __construct(Departement $departement)
    {
        $this->setDepartement($departement);
        $this->apcParcoursNiveaux = new ArrayCollection();
        $this->apcSaeParcours = new ArrayCollection();
        $this->apcRessourceParcours = new ArrayCollection();
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = trim($code);

        return $this;
    }

    /**
     * @return Collection|ApcParcoursNiveau[]
     */
    public function getApcParcoursNiveaux(): Collection
    {
        return $this->apcParcoursNiveaux;
    }

    public function addApcParcoursNiveau(ApcParcoursNiveau $apcParcoursNiveaux): self
    {
        if (!$this->apcParcoursNiveaux->contains($apcParcoursNiveaux)) {
            $this->apcParcoursNiveaux[] = $apcParcoursNiveaux;
            $apcParcoursNiveaux->setParcours($this);
        }

        return $this;
    }

    public function removeApcParcoursNiveau(ApcParcoursNiveau $apcParcoursNiveaux): self
    {
        if ($this->apcParcoursNiveaux->removeElement($apcParcoursNiveaux)) {
            // set the owning side to null (unless already changed)
            if ($apcParcoursNiveaux->getParcours() === $this) {
                $apcParcoursNiveaux->setParcours(null);
            }
        }

        return $this;
    }

    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    public function setDepartement(?Departement $departement): self
    {
        $this->departement = $departement;

        return $this;
    }

    /**
     * @return Collection|ApcSaeParcours[]
     */
    public function getApcSaeParcours(): Collection
    {
        return $this->apcSaeParcours;
    }

    public function addApcSaeParcour(ApcSaeParcours $apcSaeParcour): self
    {
        if (!$this->apcSaeParcours->contains($apcSaeParcour)) {
            $this->apcSaeParcours[] = $apcSaeParcour;
            $apcSaeParcour->setParcours($this);
        }

        return $this;
    }

    public function removeApcSaeParcour(ApcSaeParcours $apcSaeParcour): self
    {
        if ($this->apcSaeParcours->removeElement($apcSaeParcour)) {
            // set the owning side to null (unless already changed)
            if ($apcSaeParcour->getParcours() === $this) {
                $apcSaeParcour->setParcours(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ApcRessourceParcours[]
     */
    public function getApcRessourceParcours(): Collection
    {
        return $this->apcRessourceParcours;
    }

    public function addApcRessourceParcour(ApcRessourceParcours $apcRessourceParcour): self
    {
        if (!$this->apcRessourceParcours->contains($apcRessourceParcour)) {
            $this->apcRessourceParcours[] = $apcRessourceParcour;
            $apcRessourceParcour->setParcours($this);
        }

        return $this;
    }

    public function removeApcRessourceParcour(ApcRessourceParcours $apcRessourceParcour): self
    {
        if ($this->apcRessourceParcours->removeElement($apcRessourceParcour)) {
            // set the owning side to null (unless already changed)
            if ($apcRessourceParcour->getParcours() === $this) {
                $apcRessourceParcour->setParcours(null);
            }
        }

        return $this;
    }

    public function getTextePresentation(): ?string
    {
        return $this->textePresentation;
    }

    public function setTextePresentation(string $textePresentation): self
    {
        $this->textePresentation = $textePresentation;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): self
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }
}
