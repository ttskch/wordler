<?php

declare(strict_types=1);

namespace Ttskch\Wordler;

use Facebook\WebDriver\WebDriverKeys;
use Symfony\Component\Panther\Client;

final class Wordler
{
    private array $dictionary;

    public function __construct(private ?Guesser $guesser = null)
    {
        $this->dictionary = explode("\n", trim(file_get_contents(__DIR__ . '/../assets/dictionary.txt')));
        $this->guesser = $this->guesser ?? new Guesser($this->dictionary);
    }

    public function run(): void
    {
        $invalidWords = [];

        $client = Client::createChromeClient();
        $crawler = $client->request('GET', 'https://www.powerlanguage.co.uk/wordle/');
        $driver = $client->getWebDriver();

        // hide popup
        $crawler->filter('body')->click();

        // try 6 times
        for ($i = 0; $i < 6; $i++) {
            $candidate = $this->guesser->guess($invalidWords);
            $crawler->sendKeys($candidate)->sendKeys(WebDriverKeys::ENTER);

            echo sprintf("%d: Try \"%s\"\n", $i + 1, $candidate);

            sleep(2);

            // check states of 5 characters
            $states = [];
            for ($j = 0; $j < 5; $j++) {
                $state = $driver->executeScript(sprintf('return document.querySelector("game-app").shadowRoot.querySelector("game-row:nth-of-type(%d)").shadowRoot.querySelector("game-tile:nth-of-type(%d)").shadowRoot.querySelector(".tile").dataset.state', $i + 1, $j + 1));

                // if candidate is not in word list of wordle, try again with other candidate
                if ($state === 'tbd') {
                    $invalidWords[] = $candidate;
                    $crawler->sendKeys(array_fill(0, 5, WebDriverKeys::BACKSPACE)); // remove inputted word
                    $i--;
                    continue 2;
                }

                $states[] = $state;
            }

            $statesLabel = implode('', array_map(static fn (string $state) => match ($state) {
                'correct' => '!',
                'present' => '?',
                'absent' => ' ',
            }, $states));

            echo sprintf("Result: [%s]\n", $statesLabel);

            $client->takeScreenshot(sprintf(__DIR__ . '/../screenshots/%s.png', date('YmdHis')));

            $invalidWords[] = $candidate;
        }

        sleep(5);

        $client->takeScreenshot(sprintf(__DIR__ . '/../screenshots/%s.png', date('YmdHis')));
    }
}
