<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcNiveauController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 01/06/2021 19:12
 */

namespace App\Controller\competences;

use App\Controller\BaseController;
use App\Entity\ApcCompetence;
use App\Entity\ApcNiveau;
use App\Entity\Constantes;
use App\Entity\Departement;
use App\Form\ApcNiveauType;
use App\Repository\ApcNiveauRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/apc/niveau")
 */
class ApcNiveauController extends BaseController
{
    /**
     * @Route("/{departement}/synchro-niveau-annee", name="administration_apc_niveau_annee_synchro", methods="GET")
     */
    public function synchroNiveauAnnee(
        ApcNiveauRepository $apcNiveauRepository,
        Departement $departement,
    ): Response {
        $annees = $departement->getAnnees();
        $t = [];
        foreach ($annees as $annee) {
            $t[$annee->getOrdre()] = $annee;
        }

        $niveaux = $apcNiveauRepository->findByDepartement($departement);
        foreach ($niveaux as $niveau) {
            if (array_key_exists($niveau->getOrdre(), $t)) {
                $niveau->setAnnee($t[$niveau->getOrdre()]);
            }
        }

        $this->entityManager->flush();
        $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Synchronisation effectuée');

        return $this->redirectToRoute('administration_apc_referentiel_index', [
            'departement' => $departement->getId(),
        ]);
    }

    /**
     * @Route("/{competence}/new", name="administration_apc_niveau_new", methods={"GET","POST"})
     */
    public function new(Request $request, ApcCompetence $competence): Response
    {
        $apcNiveau = new ApcNiveau($competence);
        $form = $this->createForm(ApcNiveauType::class, $apcNiveau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($apcNiveau);
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Niveau de compétence ajouté avec succès.');

            return $this->redirectToRoute('administration_apc_competence_show', ['id' => $competence->getId()]);
        }

        return $this->render('competences/apc_niveau/new.html.twig', [
            'apc_niveau' => $apcNiveau,
            'form' => $form->createView(),
            'competence' => $competence,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="administration_apc_niveau_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ApcNiveau $apcNiveau): Response
    {
        $form = $this->createForm(ApcNiveauType::class, $apcNiveau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Niveau de compétence modifié avec succès.');

            return $this->redirectToRoute('administration_apc_competence_show',
                ['id' => $apcNiveau->getCompetence()->getId()]);
        }

        return $this->render('competences/apc_niveau/edit.html.twig', [
            'apc_niveau' => $apcNiveau,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="administration_apc_niveau_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ApcNiveau $apcNiveau): Response
    {
        $competence = $apcNiveau->getCompetence();

        if ($this->isCsrfTokenValid('delete' . $apcNiveau->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($apcNiveau);
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Niveau de compétence supprimé avec succès.');
        }
        $this->addFlashBag(Constantes::FLASHBAG_ERROR, 'Erreur lors de la suppression du niveau de compétence.');

        return $this->redirectToRoute('administration_apc_competence_show', ['id' => $competence->getId()]);
    }
}
