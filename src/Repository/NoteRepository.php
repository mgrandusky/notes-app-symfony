<?php

namespace App\Repository;

use App\Entity\Note;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Note>
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    public function findForUser(User $user, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->andWhere('n.isDeleted = false')
            ->setParameter('user', $user);

        if (!empty($filters['search'])) {
            $qb->andWhere('n.title LIKE :search OR n.content LIKE :search OR n.tags LIKE :search')
               ->setParameter('search', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['tag'])) {
            $qb->andWhere('n.tags LIKE :tag')
               ->setParameter('tag', '%' . $filters['tag'] . '%');
        }

        if (isset($filters['archived']) && $filters['archived'] !== '') {
            $qb->andWhere('n.isArchived = :archived')
               ->setParameter('archived', (bool) $filters['archived']);
        }

        $sortField = $filters['sort'] ?? 'updatedAt';
        $sortOrder = strtoupper($filters['order'] ?? 'DESC');

        $allowedSorts = ['title', 'createdAt', 'updatedAt'];
        $allowedOrders = ['ASC', 'DESC'];

        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'updatedAt';
        }
        if (!in_array($sortOrder, $allowedOrders)) {
            $sortOrder = 'DESC';
        }

        $qb->orderBy('n.' . $sortField, $sortOrder);

        return $qb->getQuery()->getResult();
    }
}
