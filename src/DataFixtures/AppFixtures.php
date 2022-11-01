<?php

namespace App\DataFixtures;

use App\Factory\CategoryFactory;
use App\Factory\FortuneCookieFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    /**
     * Many fortunes provided by: https://www.huffpost.com/entry/29-fortune-cookies-that-will-surprise-you_n_2109491
     */
    public function load(ObjectManager $manager): void
    {
        $jobCategory = CategoryFactory::new()->create([
            'name' => 'Job',
            'iconKey' => 'fa-dollar',
        ]);
        foreach ([
            'It would be best to maintain a low profile for now.',
            '404 Fortune not found. Abort, Retry, Ignore?',
            'You laugh now, wait til you get home.',
            'If your work is not finished, blame it on the computer.',
        ] as $fortune) {
            FortuneCookieFactory::new()->create([
                'fortune' => $fortune,
                'category' => $jobCategory,
            ]);
        }

        $lunchCategory = CategoryFactory::new()->create([
            'name' => 'Lunch',
            'iconKey' => 'fa-utensils',
        ]);
        foreach ([
            'You will be hungry again in one hour.',
            'Vampires will soon strike you if you do not order again',
            'A nice cake is waiting for you',
            'Warning: Do not eat your fortune',
        ] as $fortune) {
            FortuneCookieFactory::new()->create([
                'fortune' => $fortune,
                'category' => $lunchCategory,
            ]);
        }

        $proverbCategory = CategoryFactory::new()->create([
            'name' => 'Proverbs',
            'iconKey' => 'fa-quote-left',
        ]);
        foreach ([
            'A conclusion is simply the place where you got tired of thinking.',
            'Cookie said: "You really crack me up"',
            'When you squeeze an orange, orange juice comes out. Because that\'s what\'s inside.',
        ] as $fortune) {
            FortuneCookieFactory::new()->create([
                'fortune' => $fortune,
                'category' => $proverbCategory,
            ]);
        }

        $petsCategory = CategoryFactory::new()->create([
            'name' => 'Pets',
            'iconKey' => 'fa-paw',
        ]);
        foreach ([
            'There\'s no such thing as an ordinary cat',
            'That wasn\'t chicken',
        ] as $fortune) {
            FortuneCookieFactory::new()->create([
                'fortune' => $fortune,
                'category' => $petsCategory,
            ]);
        }

        $loveCategory = CategoryFactory::new()->create([
            'name' => 'Love',
            'iconKey' => 'fa-heart',
        ]);
        foreach ([
            'An alien of some sort will be appearing to you shortly!',
            'Are your legs tired? You\'ve been running through someone\'s mind all day long.',
            'run',
        ] as $fortune) {
            FortuneCookieFactory::new()->create([
                'fortune' => $fortune,
                'category' => $loveCategory,
            ]);
        }

        $luckyNumberCategory = CategoryFactory::new()->create([
            'name' => 'Lucky Number',
            'iconKey' => 'fa-clover',
        ]);
        foreach ([
            42,
            12,
            '10^2',
            'Jar Jar Binks',
            'Pi',
        ] as $fortune) {
            FortuneCookieFactory::new()->create([
                'fortune' => $fortune,
                'category' => $luckyNumberCategory,
            ]);
        }
    }
}
