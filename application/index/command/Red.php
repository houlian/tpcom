<?php
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class Red extends Command
{
    protected function configure()
    {
        $this->setName('red')->setDescription('create red :) ');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("create red start:-----------------------\n");

        $realnum = (new \app\index\action\RedAction())->createRedByConfig();

        echo '~~~~~~~~~~~~~~~~add new red :' . $realnum . '~~~~~~~~~~~~~~~~~~~'. "\n";

        

        $output->writeln("create red end:------------------------\n");
    }
}