<?php

declare(strict_types=1);

namespace Ttskch\Wordler;

use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverKeys;
use Symfony\Component\Panther\Client;
use Ttskch\Wordler\Exception\NoMoreCandidatesException;

final class Wordler
{
    public const STATE_CORRECT = 'correct';
    public const STATE_PRESENT = 'present';
    public const STATE_ABSENT = 'absent';
    public const STATE_IDLE = 'idle';

    public function __construct(private ?Guesser $guesser = null, private ?CandidateProvider $candidateProvider = null)
    {
        $dictionary = explode("\n", trim(file_get_contents(__DIR__ . '/../assets/dictionary.txt')));
        $this->candidateProvider ??= new CandidateProvider($dictionary);
        $this->guesser ??= new Guesser($this->candidateProvider);
    }

    public function run(): void
    {
        // $client = Client::createChromeClient(); // somehow get toast message "Share failed" when click "Share" button after solved
        $client = Client::createFirefoxClient();
        $client->manage()->window()->setSize(new WebDriverDimension(1000, 1500));
        $driver = $client->getWebDriver();
        $crawler = $client->request('GET', 'https://www.nytimes.com/games/wordle/index.html');

        // hide popup
        $client->getMouse()->clickTo('[class*="Modal-module_closeIcon"]');

        // try 6 times
        for ($i = 0; $i < 6; $i++) {
            try {
                $candidate = $this->guesser->guess();
            } catch (NoMoreCandidatesException) {
                echo "No more candidates in dictionary :(";
                return;
            }

            $crawler->sendKeys($candidate)->sendKeys(WebDriverKeys::ENTER);

            echo "{$candidate}\n";

            sleep(3);

            // check states of 5 characters
            $states = [];
            for ($j = 0; $j < 5; $j++) {
                $state = $driver->executeScript(sprintf('return document.querySelector("[class*=\'Row-module_row\']:nth-child(%d) > div:nth-child(%d) > div").dataset.state', $i + 1, $j + 1));

                // if candidate is not in word list of wordle, try again with other candidate
                if ($state === self::STATE_IDLE) {
                    $this->guesser->addInvalidWord($candidate);
                    $crawler->sendKeys(array_fill(0, 5, WebDriverKeys::BACKSPACE)); // remove inputted word
                    $i--;
                    continue 2;
                }

                $states[] = $state;
            }

            $statesLabel = implode('', array_map(fn (string $state) => match ($state) {
                self::STATE_CORRECT => '🟩',
                self::STATE_PRESENT => '🟨',
                self::STATE_ABSENT => '⬜',
            }, $states));

            echo "{$statesLabel}\n\n";

            $this->takeScreenshot($client);

            if (!in_array(self::STATE_PRESENT, $states, true) && !in_array(self::STATE_ABSENT, $states, true)) {
                break;
            }

            $this->guesser->addHistory($candidate, $states);
        }

        $client->waitFor('#share-button');

        // copy game result to clipboard
        $client->getMouse()->clickTo('#share-button');

        // paste game result to a textarea and get it
        $client->request('GET', 'https://getbootstrap.com/docs/5.1/forms/form-control/');
        $client->getMouse()->clickTo('textarea#exampleFormControlTextarea1'); // focus textarea
        $client->getKeyboard()->pressKey(WebDriverKeys::COMMAND)->sendKeys('v')->releaseKey(WebDriverKeys::COMMAND); // paste from clipboard
        $result = $client->getCrawler()->filter('textarea#exampleFormControlTextarea1')->attr('value'); // get value of textarea

        echo "--\n{$result}\n";
    }

    private function takeScreenshot(Client $client): void
    {
        $client->takeScreenshot(sprintf(__DIR__ . '/../screenshots/%s.png', date('YmdHis')));
    }
}
