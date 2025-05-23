<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'app:add-file-header',
    description: 'Prepends Dropivio header to PHP files in src/ that start with <?php',
)]
class AddFileHeaderCommand extends Command
{
    private string $header = <<<PHP

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


PHP;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../../src')->name('*.php');

        foreach ($finder as $file) {
            $path = $file->getRealPath();
            $content = file_get_contents($path);

            if (!str_starts_with($content, "<?php")) {
                continue; // skip files not starting with <?php
            }

            if (str_contains($content, '(c) Dropivio')) {
                continue; // skip if already has the header
            }

            // Insert header after "<?php\n"
            $newContent = preg_replace(
                '/^<\?php\s*/',
                "<?php\n" . $this->header,
                $content,
                1
            );

            file_put_contents($path, $newContent);
            $output->writeln("Header added: {$path}");
        }

        $output->writeln('Header addition complete.');
        return Command::SUCCESS;
    }
}
