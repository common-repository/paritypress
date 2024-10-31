<?php

declare(strict_types=1);

namespace ParityPress\Contracts;

interface IpInformationProvider
{
    public function getData(string $ip): ?array;
}
