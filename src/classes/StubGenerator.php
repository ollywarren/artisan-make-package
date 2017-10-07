<?php

//namespace SebastiaanLuca\StubGenerator;

namespace Ollywarren\Makepackage\Classes;

use RuntimeException;


/**
 * Class StubGenerator
 *
 * Stub Generator by Sebastiaan Luca.
 *
 * @package SebastiaanLuca\StubGenerator
 * @author Sebastiaan Luca <https://www.sebastiaanluca.com>
 */
class StubGenerator
{
    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $target;

    /**
     * @param string $source
     * @param string $target
     */
    public function __construct(string $source, string $target)
    {
        $this->source = $source;
        $this->target = $target;
    }

    /**
     * @param array $replacements
     *
     * @throws \RuntimeException
     */
    public function render(array $replacements)
    {
        if (file_exists($this->target)) {
            throw new RuntimeException('Cannot generate file. Target ' . $this->target . ' already exists.');
        }

        $contents = file_get_contents($this->source);

        // Standard replacements
        collect($replacements)->each(function (string $replacement, string $tag) use (&$contents) {
            $contents = str_replace($tag, $replacement, $contents);
        });

        $path = pathinfo($this->target, PATHINFO_DIRNAME);

        if (! file_exists($path)) {
            mkdir($path, 0776, true);
        }

        file_put_contents($this->target, $contents);
    }
}
