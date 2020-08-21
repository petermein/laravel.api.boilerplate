<?php

declare(strict_types = 1);



namespace Api\Infrastructure\Bus\Interfaces;


interface QueryInterface
{
    public function getData(): array;

    public function handler(): ?string;

    public function validators(): array;

    public function senders(): array;
}