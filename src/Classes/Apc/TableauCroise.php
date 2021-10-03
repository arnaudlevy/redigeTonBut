<?php

namespace App\Classes\Apc;

use App\Entity\ApcParcours;
use App\Entity\Semestre;
use App\Repository\ApcNiveauRepository;
use App\Repository\ApcParcoursNiveauRepository;
use App\Repository\ApcRessourceCompetenceRepository;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeCompetenceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;

class TableauCroise
{
    protected ApcSaeParcoursRepository $apcSaeParcoursRepository;
    protected ApcRessourceParcoursRepository $apcRessourceParcoursRepository;
    protected ApcSaeCompetenceRepository $apcSaeCompetenceRepository;
    protected ApcRessourceCompetenceRepository $apcRessourceCompetenceRepository;
    protected ApcParcoursNiveauRepository $apcParcoursNiveauRepository;
    protected ApcNiveauRepository $apcNiveauRepository;
    protected ApcSaeRepository $apcSaeRepository;
    protected ApcRessourceRepository $apcRessourceRepository;
    private array $tab;
    private array $coefficients;

    private mixed $saes;
    private mixed $ressources;
    private mixed $niveaux;

    public function __construct(
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcSaeCompetenceRepository $apcSaeCompetenceRepository,
        ApcRessourceCompetenceRepository $apcRessourceCompetenceRepository,
        ApcParcoursNiveauRepository $apcParcoursNiveauRepository,
        ApcNiveauRepository $apcNiveauRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcRessourceRepository $apcRessourceRepository
    ) {
        $this->apcSaeParcoursRepository = $apcSaeParcoursRepository;
        $this->apcRessourceParcoursRepository = $apcRessourceParcoursRepository;
        $this->apcSaeCompetenceRepository = $apcSaeCompetenceRepository;
        $this->apcRessourceCompetenceRepository = $apcRessourceCompetenceRepository;
        $this->apcParcoursNiveauRepository = $apcParcoursNiveauRepository;
        $this->apcNiveauRepository = $apcNiveauRepository;
        $this->apcSaeRepository = $apcSaeRepository;
        $this->apcRessourceRepository = $apcRessourceRepository;
    }

    public function getDatas(Semestre $semestre, ?ApcParcours $parcours = null)
    {
        if ($parcours === null) {
            $this->saes = $this->apcSaeRepository->findBySemestre($semestre);
            $this->ressources = $this->apcRessourceRepository->findBySemestre($semestre);
            $this->niveaux = $this->apcNiveauRepository->findBySemestre($semestre);
        } else {
            $this->saes = $this->apcSaeParcoursRepository->findBySemestre($semestre, $parcours);
            $this->ressources = $this->apcRessourceParcoursRepository->findBySemestre($semestre, $parcours);
            $this->niveaux = $this->apcParcoursNiveauRepository->findBySemestre($semestre, $parcours);
        }

        //on prend tout... pour éviter les soucis avec le type 3
        $compSae = $this->apcSaeCompetenceRepository->findByDepartement($semestre->getDepartement());
        $compRessources = $this->apcRessourceCompetenceRepository->findByDepartement($semestre->getDepartement());

        $this->tab = [];
        $this->coefficients = [];
        $this->tab['saes'] = [];
        $this->tab['ressources'] = [];

        foreach ($this->saes as $sae) {
            $this->tab['saes'][$sae->getId()] = [];
            foreach ($sae->getApcSaeApprentissageCritiques() as $ac) {
                $this->tab['saes'][$sae->getId()][$ac->getApprentissageCritique()->getId()] = $ac;
            }
        }

        foreach ($this->ressources as $ressource) {
            $this->tab['ressources'][$ressource->getId()] = [];
            foreach ($ressource->getApcRessourceApprentissageCritiques() as $ac) {
                $this->tab['ressources'][$ressource->getId()][$ac->getApprentissageCritique()->getId()] = $ac;
            }
        }

        foreach ($compSae as $comp) {
            if (!array_key_exists($comp->getCompetence()->getId(), $this->coefficients)) {
                $this->coefficients[$comp->getCompetence()->getId()]['saes'] = [];
                $this->coefficients[$comp->getCompetence()->getId()]['ressources'] = [];
            }
            $this->coefficients[$comp->getCompetence()->getId()]['saes'][$comp->getSae()->getId()] = $comp->getCoefficient();
        }

        foreach ($compRessources as $comp) {
            if (!array_key_exists($comp->getCompetence()->getId(), $this->coefficients)) {
                $this->coefficients[$comp->getCompetence()->getId()]['saes'] = [];
                $this->coefficients[$comp->getCompetence()->getId()]['ressources'] = [];
            }
            $this->coefficients[$comp->getCompetence()->getId()]['ressources'][$comp->getRessource()->getId()] = $comp->getCoefficient();
        }
    }


    public function getTab(): array
    {
        return $this->tab;
    }


    public function getCoefficients(): array
    {
        return $this->coefficients;
    }


    public function getSaes(): mixed
    {
        return $this->saes;
    }


    public function getRessources(): mixed
    {
        return $this->ressources;
    }


    public function getNiveaux(): mixed
    {
        return $this->niveaux;
    }


}
