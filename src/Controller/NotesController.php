<?php

namespace App\Controller;

use App\Entity\Note;
use App\Form\NoteType;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class NotesController extends AbstractController
{
    public function __construct(
        private HtmlSanitizerInterface $noteSanitizer
    ) {}

    public function index(Request $request, NoteRepository $noteRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $filters = [
            'search' => $request->query->get('search', ''),
            'tag' => $request->query->get('tag', ''),
            'sort' => $request->query->get('sort', 'updatedAt'),
            'order' => $request->query->get('order', 'DESC'),
            'archived' => $request->query->get('archived', ''),
        ];

        $notes = $noteRepository->findForUser($this->getUser(), $filters);

        return $this->render('notes/index.html.twig', [
            'notes' => $notes,
            'filters' => $filters,
        ]);
    }

    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $note = new Note();
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setUser($this->getUser());
            if ($note->getContent()) {
                $note->setContent($this->noteSanitizer->sanitize($note->getContent()));
            }
            $em->persist($note);
            $em->flush();

            $this->addFlash('success', 'Note created!');
            return $this->redirectToRoute('app_notes_index');
        }

        return $this->render('notes/form.html.twig', [
            'form' => $form,
            'note' => $note,
            'is_edit' => false,
        ]);
    }

    public function show(int $id, NoteRepository $noteRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $note = $noteRepository->findOneBy(['id' => $id, 'isDeleted' => false]);
        if (!$note || $note->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Note not found.');
        }

        return $this->render('notes/show.html.twig', ['note' => $note]);
    }

    public function edit(int $id, Request $request, NoteRepository $noteRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $note = $noteRepository->findOneBy(['id' => $id, 'isDeleted' => false]);
        if (!$note || $note->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Note not found.');
        }

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($note->getContent()) {
                $note->setContent($this->noteSanitizer->sanitize($note->getContent()));
            }
            $em->flush();

            $this->addFlash('success', 'Note updated!');
            return $this->redirectToRoute('app_notes_show', ['id' => $note->getId()]);
        }

        return $this->render('notes/form.html.twig', [
            'form' => $form,
            'note' => $note,
            'is_edit' => true,
        ]);
    }

    public function archive(int $id, NoteRepository $noteRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $note = $noteRepository->findOneBy(['id' => $id, 'isDeleted' => false]);
        if (!$note || $note->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Note not found.');
        }

        $note->setIsArchived(!$note->isArchived());
        $em->flush();

        $this->addFlash('success', $note->isArchived() ? 'Note archived.' : 'Note unarchived.');
        return $this->redirectToRoute('app_notes_index');
    }

    public function delete(int $id, NoteRepository $noteRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $note = $noteRepository->findOneBy(['id' => $id, 'isDeleted' => false]);
        if (!$note || $note->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Note not found.');
        }

        $note->setIsDeleted(true);
        $em->flush();

        $this->addFlash('success', 'Note deleted.');
        return $this->redirectToRoute('app_notes_index');
    }
}
