<?php declare(strict_types=1);

namespace ZanBaldwin\FFI;

abstract class AbstractNamedLoader extends AbstractLoader
{
    abstract protected function getCrateName(): string;

    /** {@inheritDoc} */
    public function __construct(string $rustProjectLocation, bool $debug = false)
    {
        parent::__construct($rustProjectLocation, $this->getCrateName(), $debug);
    }
}
