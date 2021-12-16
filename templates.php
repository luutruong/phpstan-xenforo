<?php

if (!isset($_SERVER['PHPSTAN_XENFORO_ROOT_DIR'])) {
    throw new \RuntimeException('`PHPSTAN_XENFORO_ROOT_DIR` not exists');
}
$xfDir = $_SERVER['PHPSTAN_XENFORO_ROOT_DIR'];
include $xfDir . '/src/XF.php';

$appClass = 'XF\Pub\App';

\XF::start($xfDir);
\XF::setupApp($appClass);

if (!isset($_SERVER['PHPSTAN_XENFORO_ADDON_ID'])) {
    throw new \RuntimeException('`PHPSTAN_XENFORO_ADDON_ID` not exists.');
}

$start = microtime(true);

$addOnDir = $xfDir . '/src/addons/' . $_SERVER['PHPSTAN_XENFORO_ADDON_ID'];
$templates = [];
if (is_dir($addOnDir . '/_output/templates')) {
    foreach (glob($addOnDir . '/_output/templates/*/*.html') as $path) {
        $parts = explode('/', $path);
        $name = array_pop($parts);
        $type = array_pop($parts);

        $templates[$type . '::' . $name] = file_get_contents($path);
    }
} else {
    /** @var \XF\Finder\Template $finder */
    $finder = \XF::finder('XF:Template');
    $finder->fromAddOn($_SERVER['PHPSTAN_XENFORO_ADDON_ID']);
    $finder->where('style_id', 0);

    /** @var \XF\Entity\Template $template */
    foreach ($finder->fetch() as $template) {
        $templates[$template->type . '::' . $template->title] = $template->template;
    }
}

$compiler = \XF::app()->templateCompiler();
$console = new \Symfony\Component\Console\Output\ConsoleOutput();

$table = new \Symfony\Component\Console\Helper\Table($console);
$progressBar = new \Symfony\Component\Console\Helper\ProgressBar($console, count($templates));
$progressBar->setBarWidth(40);

$table->setHeaders([
   'type',
   'template',
   'phrase',
   'line'
]);
$notFoundTotal = 0;

foreach ($templates as $type => $template) {
    $progressBar->advance();

    $ast = $compiler->compileToAst($template);
    $phrases = $ast->analyzePhrases();
    $parts = explode('::', $type);

    if (count($phrases) === 0) {
        continue;
    }

    foreach ($phrases as $phraseId) {
        /** @var \XF\Finder\Phrase $phraseFinder */
        $phraseFinder = \XF::finder('XF:Phrase');
        $phraseFinder->where('language_id', 0);
        $phraseFinder->where('title', $phraseId);

        /** @var \XF\Entity\Phrase|null $phrase */
        $phrase = $phraseFinder->fetchOne();
        if ($phrase === null) {
            $notFoundTotal++;

            $table->addRow([
                $parts[0],
                $parts[1],
                $phraseId,
                find_phrase_used_in_line($phraseId, $template)
            ]);
        }
    }
}

$progressBar->finish();
$console->writeln('');

if ($notFoundTotal > 0) {
    $table->render();
    $console->writeln('<error>UNKNOWN ' . $notFoundTotal . ' PHRASE(S)</error>');
}

$timing = microtime(true) - $start;
$console->writeln(
    '<info>Timing: '
        . number_format($timing, 4)
        . ' seconds. Memory: '
        . number_format((memory_get_peak_usage() / 1024 / 1024), 2)
        . 'MB'
        . '</info>'
);

function find_phrase_used_in_line(string $phraseId, string $content): string {
    $lines = preg_split("/\r?\n/", $content, -1);
    $found = [];
    foreach ($lines as $lineNumber => $line) {
        if (strpos($line, $phraseId) !== false) {
            $found[] = ($lineNumber + 1);
        }
    }

    return implode(',', $found);
}

exit($notFoundTotal);
