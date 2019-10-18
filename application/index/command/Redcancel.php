<?php
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class Redcancel extends Command
{
    protected function configure()
    {
        $this->setName('redcancel')->setDescription('cancel red :) ');
    }

    protected function execute(Input $input, Output $output)
    {

        $output->writeln("enable red start:-----------------------\n");
        
        $realnum = (new \app\index\action\RedAction())->enablered();

        echo '~~~~~~~~~~~~~~~~enable old red :' . $realnum . '~~~~~~~~~~~~~~~~~~~'. "\n";
    }
}