<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcSaeController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 19/05/2021 15:55
 */

namespace App\Controller\formation;

//use App\Classes\Matieres\SaeManager;
//use App\Classes\Pdf\MyPDF;
//use App\Classes\Word\MyWord;
use App\Controller\BaseController;
use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcCompetence;
use App\Entity\ApcSae;
use App\Entity\ApcSaeApprentissageCritique;
use App\Entity\ApcSaeCompetence;
use App\Entity\ApcSaeRessource;
use App\Entity\Constantes;
use App\Entity\Departement;
use App\Form\ApcSaeType;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeApprentissageCritiqueRepository;
use App\Repository\ApcSaeCompetenceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRessourceRepository;
use App\Repository\SemestreRepository;
use App\Utils\Convert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/formation/api/sae", name="formation_")
 */
class ApcAjaxSaeController extends BaseController
{
    /**
     * @Route("/ajax-ac", name="apc_sae_ajax_ac", methods={"POST"}, options={"expose":true})
     */
    public function ajaxAc(
        SemestreRepository $semestreRepository,
        ApcSaeApprentissageCritiqueRepository $apcSaeApprentissageCritiqueRepository,
        ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        Request $request
    ): Response {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }


        $semestre = $semestreRepository->find($parametersAsArray['semestre']);
        $competences = $parametersAsArray['competences'];
        if (null !== $semestre && count($competences) > 0) {
            if (null !== $parametersAsArray['sae']) {
                $tabAcSae = $apcSaeApprentissageCritiqueRepository->findArrayIdAc($parametersAsArray['sae']);
            } else {
                $tabAcSae = [];
            }

            $datas = $apcApprentissageCritiqueRepository->findBySemestreAndCompetences($semestre->getAnnee(),
                $competences);

            $t = [];
            $t['competences'] = [];
            foreach ($datas as $d) {
                $b = [];

                $b['id'] = $d->getId();
                $b['libelle'] = $d->getLibelle();
                $b['code'] = $d->getCode();
                $b['checked'] = true === in_array($d->getId(), $tabAcSae);
                if (null !== $d->getNiveau() && null !== $d->getNiveau()->getCompetence() && !array_key_exists($d->getNiveau()->getCompetence()->getId(),
                        $t)) {
                    $t[$d->getNiveau()->getCompetence()->getId()] = [];
                }
                $t[$d->getNiveau()->getCompetence()->getId()][] = $b;
                $t['competences'][$d->getNiveau()->getCompetence()->getId()] = $d->getNiveau()->getCompetence()->getNomCourt();
            }

            return $this->json($t);
        }

        return $this->json(false);
    }

    /**
     * @Route("/ajax-ressources", name="apc_ressources_ajax", methods={"POST"}, options={"expose":true})
     */
    public function ajaxRessources(
        SemestreRepository $semestreRepository,
        ApcSaeRessourceRepository $apcSaeRessourceRepository,
        ApcRessourceRepository $apcRessourceRepository,
        Request $request
    ): Response {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        $semestre = $semestreRepository->find($parametersAsArray['semestre']);
        if (null !== $semestre) {
            if (null !== $parametersAsArray['sae']) {
                $tabAcSae = $apcSaeRessourceRepository->findArrayIdRessources($parametersAsArray['sae']);
            } else {
                $tabAcSae = [];
            }

            $datas = $apcRessourceRepository->findBySemestre($semestre);

            $t = [];
            foreach ($datas as $d) {
                $b = [];

                $b['id'] = $d->getId();
                $b['libelle'] = $d->getLibelle();
                $b['code'] = $d->getCodeMatiere();
                $b['checked'] = true === in_array($d->getId(), $tabAcSae);
                $t[] = $b;
            }

            return $this->json($t);
        }

        return $this->json(false);
    }

    /**
     * @Route("/ajax-parcours", name="apc_sae_parcours_ajax", methods={"POST"}, options={"expose":true})
     */
    public function ajaxParcours(
        SemestreRepository $semestreRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        Request $request
    ): Response {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        $semestre = $semestreRepository->find($parametersAsArray['semestre']);
        if (null !== $semestre && (($semestre->getOrdreLmd() > 2 && $this->getDepartement()->getTypeStructure() !== Departement::TYPE3) || $this->getDepartement()->getTypeStructure() === Departement::TYPE3)) {
            $datas = $this->getDepartement()->getApcParcours();
            if (count($datas) > 0) {
                if (null !== $parametersAsArray['sae']) {
                    $tabSaeParcours = $apcSaeParcoursRepository->findArrayIdSae($parametersAsArray['sae']);
                } else {
                    $tabSaeParcours = [];
                }

                $t = [];
                foreach ($datas as $d) {
                    $b = [];
                    $b['id'] = $d->getId();
                    $b['libelle'] = $d->getLibelle();
                    $b['code'] = $d->getCode();
                    $b['checked'] = true === in_array($d->getId(), $tabSaeParcours);
                    $t[] = $b;
                }

                return $this->json($t);
            }
        }

        return $this->json(false);
    }

    /**
     * @Route("/{sae}/{ac}/update_ajax", name="apc_sae_ac_update_ajax", methods="POST", options={"expose":true})
     */
    public function updateAc(
        ApcSaeApprentissageCritiqueRepository $apcSaeApprentissageCritiqueRepository,
        Request $request, ApcSae $sae, ApcApprentissageCritique $ac) {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        //regarde si déjà existant
        $acSae = $apcSaeApprentissageCritiqueRepository->findOneBy(['sae' => $sae->getId(), 'apprentissageCritique' => $ac->getId()]);

        if ($acSae !== null) {
            //selon la valeur, on supprime
            if ((bool)$parametersAsArray['value'] === false) {
                $this->entityManager->remove($acSae);
            }
        } else {
            //selon la valeur, on ajoute
            if ((bool)$parametersAsArray['value'] === true) {
                $acSae = new ApcSaeApprentissageCritique($sae, $ac);
                $this->entityManager->persist($acSae);
            }
        }
        $this->entityManager->flush();

        return $this->json(true);
    }

    /**
     * @Route("/{sae}/{competence}/update_coeff_ajax", name="apc_sae_coeff_update_ajax", methods="POST", options={"expose":true})
     */
    public function updateCoeff(
        ApcSaeCompetenceRepository $apcSaeCompetenceRepository,
        Request $request, ApcSae $sae, ApcCompetence $competence) {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        //regarde si déjà existant
        $acRessource = $apcSaeCompetenceRepository->findOneBy(['sae' => $sae->getId(), 'competence' => $competence->getId()]);

        if ($acRessource !== null) {
            //on modifie
            $acRessource->setCoefficient(Convert::convertToFloat($parametersAsArray['valeur']));
        } else {
            //on ajoute
            $acRessource = new ApcSaeCompetence($sae, $competence);
            $acRessource->setCoefficient($parametersAsArray['valeur']);
            $this->entityManager->persist($acRessource);

        }
        $this->entityManager->flush();

        return $this->json(true);
    }

    /**
     * @Route("/{sae}/{type}/update_heures_ajax", name="apc_sae_heure_update_ajax", methods="POST", options={"expose":true})
     */
    public function updateHeures(
        Request $request, ApcSae $sae, string $type) {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        switch ($type)
        {
            case 'heures_totales':
                $sae->setHeuresTotales($parametersAsArray['valeur']);
                break;
            case 'heures_tp':
                $sae->setTpPpn($parametersAsArray['valeur']);
                break;
            case 'heures_projet':
                $sae->setProjetPpn($parametersAsArray['valeur']);
                break;
        }
        $this->entityManager->flush();

        return $this->json(true);
    }
}
