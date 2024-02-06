<?php

namespace App\Http;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface{

    /** @var string Uri scheme. */
    private $scheme = '';

    /** @var string Uri user info. */
    private $userInfo = '';

    /** @var string Uri host. */
    private $host = '';

    /** @var int|null Uri port. */
    private $port;

    /** @var string Uri path. */
    private $path = '';

    /** @var string Uri query string. */
    private $query = '';

    /** @var string Uri fragment. */
    private $fragment = '';

    /** @var string|null String representation */
    private $composedComponents;

    public function __construct(string $uri = '')
    {
        if($uri !== ''){
            $parts = self::parse($uri);
        }
    }
    private static function parse(string $url){
        $prefix = '';
        if (preg_match('%^(.*://\[[0-9:a-f]+\])(.*?)$%', $url, $matches)) {
            /** @var array{0:string, 1:string, 2:string} $matches */
            $prefix = $matches[1];
            $url = $matches[2];
        }

    }
    public function getScheme(): string{
        return $this->scheme;
    }

    public function getAuthority(): string{
    
    }

    public function getUserInfo(): string{

    }

    public function getHost(): string{

    }

    public function getPort(): ?int{

    }

    public function getPath(): string{

    }

    public function getQuery(): string{

    }

    public function getFragment(): string{

    }

    public function withScheme(string $scheme): UriInterface{

    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface{

    }

    public function withHost(string $host): UriInterface{

    }

    public function withPort(?int $port): UriInterface{

    }

    public function withPath(string $path): UriInterface{

    }
    public function withQuery(string $query): UriInterface{

    }

    
    public function withFragment(string $fragment): UriInterface{

    }

    public function __toString(): string{
        strstr()
    }

}