<?php

namespace App\Command;

use App\Entity\Pcproducts;
use App\Entity\InventoryLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:backfill-inventory-logs',
    description: 'Generate inventory logs for existing products that have no log yet and reset IDs.'
)]
class BackfillInventoryLogsCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->em->getConnection();

        // ✅ Reset AUTO_INCREMENT for both tables
        $connection->executeStatement('ALTER TABLE pcproducts AUTO_INCREMENT = 1;');
        $connection->executeStatement('ALTER TABLE inventory_log AUTO_INCREMENT = 1;');

        $products = $this->em->getRepository(Pcproducts::class)->findAll();
        $count = 0;

        foreach ($products as $product) {
            // Skip products that already have InventoryLogs
            $existingLog = $this->em->getRepository(InventoryLog::class)
                ->findOneBy(['productname' => $product]);

            if ($existingLog) {
                continue;
            }

            // Create InventoryLog
            $log = new InventoryLog();
            $log->setProductname($product);
            $log->setImage($product->getImage());
            
            $stock = method_exists($product, 'getStock') ? $product->getStock() : null;
            $log->setStock($stock ?? 1); // fallback to 1
            $log->setCreatedAt(new \DateTimeImmutable());

            // Update Pcproducts availability based on stock
            $product->setIsAvailable(($stock ?? 1) > 0);

            $this->em->persist($log);
            $count++;
        }

        $this->em->flush();

        $output->writeln("<info>✅ $count inventory logs created and AUTO_INCREMENT reset.</info>");
        return Command::SUCCESS;
    }
}
