<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */ 

namespace App\Service\Database;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TransactionManager implements TransactionManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {
    }

    public function executeInTransaction(callable $operation): mixed
    {
        try {
            $this->entityManager->beginTransaction();
            $result = $operation();
            $this->entityManager->commit();
            return $result;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $this->logger->error('Transaction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
