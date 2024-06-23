<?php

namespace ntentan\interfaces;

use ntentan\Controller;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


interface ControllerFactoryInterface
{
    public function create(ServerRequestInterface $request): Controller;
    public function setup(array $config): void;
    public function run(): ResponseInterface;
}
