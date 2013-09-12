<?php

namespace Nantarena\ContactBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Nantarena\ContactBundle\Entity\Category;

class LoadContactCategoryData extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // Add one active header news
        $cat = new Category();
        $cat
            ->setName('-- Divers --')
            ->setTag('DIVERS');
        $manager->persist($cat);

        $cat = new Category();
        $cat
            ->setName('Sponsor')
            ->setTag('SPONSOR');
        $manager->persist($cat);

        $cat = new Category();
        $cat
            ->setName('League of Legend')
            ->setTag('LOL');
        $manager->persist($cat);

        $cat = new Category();
        $cat
            ->setName('Bug forum')
            ->setTag('FORUM');
        $manager->persist($cat);

        $cat = new Category();
        $cat
            ->setName('Erreur de paiement')
            ->setTag('PAIEMENT');
        $manager->persist($cat);

        $manager->flush();
    }
}
