<?php namespace Chipk4\Selectel\Contract;

interface Response
{
    public function getHeaders();
    public function getBody();
    public function getStatusCode();
}