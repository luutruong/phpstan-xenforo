<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpParser\Error;
use PhpParser\ParserFactory;

$code = '
<?php

class TetsClass {
    public function phrases() {
        \XF::phrase(\'explicit_phrase_name\');
        $id = \'something_here\';
        \XF::phrase(\'implicit_phrase_\' . $id . \'__foo\');
        \XF::phrase(\'phrase_with_args\', [\'name\' => 123]);
    }
}
';


$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
$traverser = new \PhpParser\NodeTraverser();

$xfNodeVisitor = new \App\Node\XFNodeVisitor();
$traverser->addVisitor($xfNodeVisitor);

try {
    $stmts = $parser->parse($code);
    $traverser->traverse($stmts);
} catch (\Throwable $e) {
    echo $e->getMessage();
}

var_dump($xfNodeVisitor->getPhrases());die;
