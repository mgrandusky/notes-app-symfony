<?php

namespace App\Tests\Repository;

use App\Entity\Note;
use App\Entity\User;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class NoteRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private NoteRepository $noteRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->noteRepository = $this->em->getRepository(Note::class);
    }

    public function testFindForUserReturnsOnlyUserNotes(): void
    {
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user1 = new User();
        $user1->setEmail('repo_test_user1_' . uniqid() . '@example.com');
        $user1->setPassword($hasher->hashPassword($user1, 'password'));
        $this->em->persist($user1);

        $user2 = new User();
        $user2->setEmail('repo_test_user2_' . uniqid() . '@example.com');
        $user2->setPassword($hasher->hashPassword($user2, 'password'));
        $this->em->persist($user2);

        $note1 = new Note();
        $note1->setTitle('User1 Note');
        $note1->setUser($user1);
        $this->em->persist($note1);

        $note2 = new Note();
        $note2->setTitle('User2 Note');
        $note2->setUser($user2);
        $this->em->persist($note2);

        $this->em->flush();

        $results = $this->noteRepository->findForUser($user1);

        $this->assertCount(1, $results);
        $this->assertEquals('User1 Note', $results[0]->getTitle());

        // Cleanup
        $this->em->remove($note1);
        $this->em->remove($note2);
        $this->em->remove($user1);
        $this->em->remove($user2);
        $this->em->flush();
    }

    public function testFindForUserFiltersDeletedNotes(): void
    {
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('repo_test_deleted_' . uniqid() . '@example.com');
        $user->setPassword($hasher->hashPassword($user, 'password'));
        $this->em->persist($user);

        $note = new Note();
        $note->setTitle('Deleted Note');
        $note->setIsDeleted(true);
        $note->setUser($user);
        $this->em->persist($note);

        $this->em->flush();

        $results = $this->noteRepository->findForUser($user);
        $this->assertCount(0, $results);

        // Cleanup
        $this->em->remove($note);
        $this->em->remove($user);
        $this->em->flush();
    }
}
