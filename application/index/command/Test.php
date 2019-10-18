<?php
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class Test extends Command
{
    protected function configure()
    {
        $this->setName('test')->setDescription('Here is the remark ');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("create red start:-----------------------\n");

        $num = (new \app\index\action\RedAction())->test();
        

        $output->writeln("create red end:------------------------\n");
    }
}