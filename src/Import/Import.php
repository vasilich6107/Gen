<?php

namespace Gen\Import;

/**
 * Class Import
 *
 * @package Gen\Import
 */
abstract class Import
{
    /**
     * Performs required import actions.
     *
     * @param string $message
     * @return \Generator
     */
    abstract function import(string $message):\Generator;
}