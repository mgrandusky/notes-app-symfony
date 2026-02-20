<?php

namespace App\Tests\Controller;

use App\Entity\Note;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class NotesControllerTest extends WebTestCase
{
    public function testNotesIndexRedirectsWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/notes/');

        $this->assertResponseRedirects('/login');
    }

    public function testCreateNoteWhenAuthenticated(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('test_notes_' . uniqid() . '@example.com');
        $user->setPassword($hasher->hashPassword($user, 'password123'));
        $em->persist($user);
        $em->flush();

        $client->loginUser($user);

        $client->request('GET', '/notes/new');
        $this->assertResponseIsSuccessful();

        $uniqueTitle = 'Test Note Title ' . uniqid();
        $client->submitForm('Create Note', [
            'note[title]' => $uniqueTitle,
            'note[content]' => 'Test content',
            'note[tags]' => 'test',
        ]);

        $this->assertResponseRedirects('/notes/');

        $note = $em->getRepository(Note::class)->findOneBy(['title' => $uniqueTitle]);
        $this->assertNotNull($note);
        $this->assertEquals($user->getId(), $note->getUser()->getId());

        // Cleanup - re-find entities as they may be detached after the kernel reboot
        $freshUser = $em->find(User::class, $user->getId());
        $em->remove($note);
        $em->remove($freshUser);
        $em->flush();
    }
}
