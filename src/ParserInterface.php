<?php

namespace Udger;

interface ParserInterface
{
    public function parse();
    
    public function setUA(?string $ua): bool;
    
    public function setIP(string $ip): bool;
    
    public function setDataFile(string $path): bool;

    public function setCacheSize(int $size): bool;
}
