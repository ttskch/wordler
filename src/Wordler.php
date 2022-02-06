<?php

declare(strict_types=1);

namespace Ttskch\Wordler;

use Facebook\WebDriver\WebDriverKeys;
use Symfony\Component\Panther\Client;

final class Wordler
{
    public const STATE_CORRECT = 'correct';
    public const STATE_PRESENT = 'present';
    public const STATE_ABSENT = 'absent';

    public function __construct(private ?Guesser $guesser = null, private ?CandidateProvider $candidateProvider = null)
    {
        $dictionary = explode("\n", trim(file_get_contents(__DIR__ . '/../assets/dictionary.txt')));
        $this->candidateProvider = $candidateProvider ?? new CandidateProvider($dictionary);
        $this->guesser = $guesser ?? new Guesser($this->candidateProvider);
    }

    public function run(): void
    {
        $client = Client::createChromeClient();
        $driver = $client->getWebDriver();
        $crawler = $client->request('GET', 'https://www.powerlanguage.co.uk/wordle/');

        // hide popup
        $crawler->filter('body')->click();

        // try 6 times
        for ($i = 0; $i < 6; $i++) {
            $candidate = $this->guesser->guess();
            $crawler->sendKeys($candidate)->sendKeys(WebDriverKeys::ENTER);

            echo sprintf("%d: Try \"%s\"\n", $i + 1, $candidate);

            sleep(3);

            // check states of 5 characters
            $states = [];
            for ($j = 0; $j < 5; $j++) {
                $state = $driver->executeScript(sprintf('return document.querySelector("game-app").shadowRoot.querySelector("game-row:nth-of-type(%d)").shadowRoot.querySelector("game-tile:nth-of-type(%d)").shadowRoot.querySelector(".tile").dataset.state', $i + 1, $j + 1));

                // if candidate is not in word list of wordle, try again with other candidate
                if ($state === 'tbd') {
                    $this->guesser->addInvalidWord($candidate);
                    $crawler->sendKeys(array_fill(0, 5, WebDriverKeys::BACKSPACE)); // remove inputted word
                    $i--;
                    continue 2;
                }

                $states[] = $state;
            }

            $statesLabel = implode('', array_map(static fn (string $state) => match ($state) {
                self::STATE_CORRECT => '!',
                self::STATE_PRESENT => '?',
                self::STATE_ABSENT => '_',
            }, $states));

            echo sprintf("Feedback: %s\n", $statesLabel);

            $this->takeScreenshot($client);

            if (!in_array(self::STATE_PRESENT, $states) && !in_array(self::STATE_ABSENT, $states)) {
                break;
            }

            $this->guesser->addHistory($candidate, $states);
        }

        sleep(5);

        // @todo
        /*
        // copy game result to clipboard
        $driver->executeScript('document.querySelector("game-app").shadowRoot.querySelector("game-stats").shadowRoot.querySelector("button#share-button").click()'); // somehow this doesn't work and get toast message "Share failed" :(
        sleep(1);

        $this->takeScreenshot($client);

        // paste game result to a textarea and get it
        $crawler = $client->request('GET', 'https://getbootstrap.com/docs/5.1/forms/form-control/');
        $textarea = $crawler->filter('textarea#exampleFormControlTextarea1');
        $client->getKeyboard()->pressKey(WebDriverKeys::CONTROL)->sendKeys('v'); // paste from clipboard
        $result = $textarea->text();

        echo sprintf("%s\n", $result);
        */

        $this->takeScreenshot($client);
    }

    private function takeScreenshot(Client $client): void
    {
        $client->takeScreenshot(sprintf(__DIR__ . '/../screenshots/%s.png', date('YmdHis')));
    }
}
