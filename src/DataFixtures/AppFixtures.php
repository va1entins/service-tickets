<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Device;
use App\Entity\Technician;
use App\Entity\Ticket;
use App\Entity\TicketHistory;
use App\Enum\TicketPriority;
use App\Enum\TicketStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Dane testowe do środowiska deweloperskiego
 */
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // --- Urządzenia ---
        $device1 = new Device();
        $device1->setSerialNumber('SN-2024-001');
        $device1->setModel('Laptop Dell XPS 15');
        $device1->setCustomerName('Jan Kowalski');
        $manager->persist($device1);

        $device2 = new Device();
        $device2->setSerialNumber('SN-2024-002');
        $device2->setModel('Drukarka HP LaserJet');
        $device2->setCustomerName('Anna Nowak');
        $manager->persist($device2);

        // --- Technicy ---
        $tech1 = new Technician();
        $tech1->setFirstName('Piotr');
        $tech1->setLastName('Wiśniewski');
        $tech1->setEmail('p.wisniewski@serwis.pl');
        $tech1->setActive(true);
        $manager->persist($tech1);

        $tech2 = new Technician();
        $tech2->setFirstName('Marek');
        $tech2->setLastName('Zając');
        $tech2->setEmail('m.zajac@serwis.pl');
        $tech2->setActive(true);
        $manager->persist($tech2);

        $tech3 = new Technician();
        $tech3->setFirstName('Tomasz');
        $tech3->setLastName('Krawczyk');
        $tech3->setEmail('t.krawczyk@serwis.pl');
        $tech3->setActive(false);
        $manager->persist($tech3);

        // --- Zgłoszenia ---

        // Ticket 1: nowe zgłoszenie
        $ticket1 = new Ticket();
        $ticket1->setTitle('Laptop nie uruchamia się');
        $ticket1->setDescription('Po naciśnięciu przycisku power brak jakiejkolwiek reakcji.');
        $ticket1->setPriority(TicketPriority::HIGH);
        $ticket1->setDevice($device1);
        $manager->persist($ticket1);
        $manager->persist(new TicketHistory($ticket1, null, TicketStatus::NEW, 'system'));

        // Ticket 2: przypisane do technika
        $ticket2 = new Ticket();
        $ticket2->setTitle('Wymiana tonera');
        $ticket2->setDescription('Toner skończył się, wymaga wymiany.');
        $ticket2->setPriority(TicketPriority::LOW);
        $ticket2->setDevice($device2);
        $ticket2->setAssignedTechnician($tech1);
        $ticket2->setStatus(TicketStatus::ASSIGNED);
        $manager->persist($ticket2);
        $manager->persist(new TicketHistory($ticket2, null, TicketStatus::NEW, 'system'));
        $manager->persist(new TicketHistory($ticket2, TicketStatus::NEW, TicketStatus::ASSIGNED, 'admin'));

        // Ticket 3: w trakcie realizacji
        $ticket3 = new Ticket();
        $ticket3->setTitle('Błąd systemu operacyjnego');
        $ticket3->setDescription('System wyświetla niebieski ekran śmierci przy starcie.');
        $ticket3->setPriority(TicketPriority::CRITICAL);
        $ticket3->setDevice($device1);
        $ticket3->setAssignedTechnician($tech2);
        $ticket3->setStatus(TicketStatus::IN_PROGRESS);
        $manager->persist($ticket3);
        $manager->persist(new TicketHistory($ticket3, null, TicketStatus::NEW, 'system'));
        $manager->persist(new TicketHistory($ticket3, TicketStatus::NEW, TicketStatus::ASSIGNED, 'admin'));
        $manager->persist(new TicketHistory($ticket3, TicketStatus::ASSIGNED, TicketStatus::IN_PROGRESS, 'p.wisniewski@serwis.pl'));

        // Ticket 4: zakończone — createdAt przed closedAt
        $ticket4 = new Ticket();
        $ticket4->setTitle('Konfiguracja sieci Wi-Fi');
        $ticket4->setDescription('Brak połączenia z siecią bezprzewodową.');
        $ticket4->setPriority(TicketPriority::MEDIUM);
        $ticket4->setDevice($device2);
        $ticket4->setAssignedTechnician($tech1);
        $ticket4->setStatus(TicketStatus::DONE);
        $ticket4->setCreatedAt(new \DateTimeImmutable('-5 days'));
        $ticket4->setClosedAt(new \DateTimeImmutable('-2 days'));
        $manager->persist($ticket4);
        $manager->persist(new TicketHistory($ticket4, null, TicketStatus::NEW, 'system'));
        $manager->persist(new TicketHistory($ticket4, TicketStatus::NEW, TicketStatus::ASSIGNED, 'admin'));
        $manager->persist(new TicketHistory($ticket4, TicketStatus::ASSIGNED, TicketStatus::IN_PROGRESS, 'm.zajac@serwis.pl'));
        $manager->persist(new TicketHistory($ticket4, TicketStatus::IN_PROGRESS, TicketStatus::DONE, 'm.zajac@serwis.pl'));

        // Ticket 5: anulowane
        $ticket5 = new Ticket();
        $ticket5->setTitle('Aktualizacja sterowników');
        $ticket5->setDescription('Prośba o aktualizację sterowników karty graficznej.');
        $ticket5->setPriority(TicketPriority::LOW);
        $ticket5->setDevice($device1);
        $ticket5->setStatus(TicketStatus::CANCELLED);
        $manager->persist($ticket5);
        $manager->persist(new TicketHistory($ticket5, null, TicketStatus::NEW, 'system'));
        $manager->persist(new TicketHistory($ticket5, TicketStatus::NEW, TicketStatus::CANCELLED, 'admin'));

        $manager->flush();
    }
}
