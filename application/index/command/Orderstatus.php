<?php
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class Orderstatus extends Command
{
    protected function configure()
    {
        $this->setName('orderstatus')->setDescription('update Orderstatus :) ');
    }

    protected function execute(Input $input, Output $output)
    {

        $output->writeln("update Orderstatus start:-----------------------\n");
        
        $realnum = (new \app\index\action\OrderAction())->checkandupdate();

        echo '~~~~~~~~~~~~~~~~update Orderstatus end :' . $realnum . '~~~~~~~~~~~~~~~~~~~'. "\n";
    }
}