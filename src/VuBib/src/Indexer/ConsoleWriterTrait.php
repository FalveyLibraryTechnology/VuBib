<?php
namespace VuBib\Indexer;

trait ConsoleWriterTrait
{
    protected function writeLine($str)
    {
        echo "$str\n";
    }
}