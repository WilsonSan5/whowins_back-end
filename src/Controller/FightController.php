<?php

namespace App\Controller;

use App\Entity\Fight;
use App\Form\Fight1Type;
use App\Repository\FighterRepository;
use App\Repository\FightRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/fight')]
class FightController extends AbstractController
{
    #[Route('/', name: 'app_fight_index', methods: ['GET'])]
    public function index(FightRepository $fightRepository): Response
    {
        return $this->render('fight/index.html.twig', [
            'fights' => $fightRepository->findAll(),
            'numberOfFights' => count($fightRepository->findAll())
        ]);
    }

    #[Route('/new', name: 'app_fight_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fight = new Fight();
        $form = $this->createForm(Fight1Type::class, $fight);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($fight);
            $entityManager->flush();

            return $this->redirectToRoute('app_fight_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('fight/new.html.twig', [
            'fight' => $fight,
            'form' => $form,
        ]);
    }

    #[Route('/reset_all_votes', name: 'app_fight_reset_all')]
    public function resetAllVotes(FightRepository $fightRepository, EntityManagerInterface $entityManager): Response
    {
        $fights = $fightRepository->findAll();

        foreach ($fights as $fight) {
            $fight->resetVote();
        }
        $entityManager->flush();
        return $this->redirectToRoute('app_fight_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_fight_show', methods: ['GET'])]
    public function show(Fight $fight): Response
    {
        return $this->render('fight/show.html.twig', [
            'fight' => $fight,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_fight_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Fight $fight, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Fight1Type::class, $fight);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_fight_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('fight/edit.html.twig', [
            'fight' => $fight,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_fight_delete', methods: ['POST'])]
    public function delete(Request $request, Fight $fight, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $fight->getId(), $request->request->get('_token'))) {
            $entityManager->remove($fight);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_fight_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/reset_vote/{id}', name: 'app_fight_reset_vote', methods: ['GET'])]
    public function resetVote(Fight $fight, EntityManagerInterface $entityManager): Response
    {
        $fight->resetVote();
        $entityManager->flush();

        return $this->redirectToRoute('app_fight_show', ['id' => $fight->getId()], Response::HTTP_SEE_OTHER);
    }
}
