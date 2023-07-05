<?php

namespace App\Http;

use Exception;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface{

    private const READABLE_MODES = '/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/';
    private const WRITABLE_MODES = '/a|w|r\+|rb\+|rw|x|c/';

     /** @var resource */
     private $stream;
     /** @var int|null */
     private $size;
     /** @var bool */
     private $seekable;
     /** @var bool */
     private $readable;
     /** @var bool */
     private $writable;
     /** @var string|null */
     private $uri;
     /** @var mixed[] */
     private $customMetadata;

    public function __construct($stream, array $options = [])
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('Stream must be a resource');
        }

        if(isset($options['size'])){
            $this->size = $options['size'];
        }

        $this->customMetadata = $options['metadata'] ?? [];
        $this->stream = $stream;
        $meta = stream_get_meta_data($this->stream);
        $this->seekable = $meta['seakable'];
        $this->readable = (bool)preg_match(self::READABLE_MODES, $meta['mode']);
        $this->writable = (bool)preg_match(self::WRITABLE_MODES, $meta['mode']);
        $this->uri = $this->getMetadata('uri');

    }
     
    public function __toString(): string{
        try{
            if ($this->isSeekable()) {
                $this->seek(0);
        }
        return $this->getContents();
        }catch(\Throwable $e){
            if (\PHP_VERSION_ID >= 70400) {
                throw $e;
            }
            trigger_error(sprintf('%s::__toString exception: %s', self::class, (string) $e), E_USER_ERROR);
                return '';
        }
    }
    
    public function close(): void{
        if(isset($this->stream)){
            if(is_resource($this->stream)){
                fclose($this->stream);
            }
            $this->detach();
        }
    }

    public function detach(){
        if(!isset($this->stream)){
            return null;
        }
        $result = $this->stream;
        unset($result);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

    }

    public function getSize(): ?int{
        if($this->size !== null){
            return $this->size;
        }
        
        if(!isset($this->stream)){
            return null;
        }

    }

    public function tell(): int{
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        $result = ftell($this->stream);

        if($result === false){
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    public function eof(): bool{
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        return feof($this->stream);
    }

    public function isSeekable(): bool{
        return $this->seekable;
    }


    public function seek(int $offset, int $whence = SEEK_SET): void{
        $whence = (int) $whence;

        if(!isset($this->stream)){
            throw new \RuntimeException("Stream is detached");
        }
        if(!$this->seekable){
            throw new \RuntimeException("Stream is unseakable");
        }
        if(fseek($this->stream, $offset, $whence) === -1){
            throw new \RuntimeException('Unable to seek to stream position' . $offset . ' with whence ' . var_export($whence, true));
        }
    }

    public function rewind(): void{
        $this->seek(0);
    }

    public function isWritable(): bool{
        return $this->writable;
    }

    public function write(string $string): int{
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->writable) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }

        // We can't know the size after writing anything
        $this->size = null;
        $result = fwrite($this->stream, $string);

        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }

        return $result;
    }


    public function isReadable(): bool{
        return $this->readable;
    }

    public function read(int $length): string{
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }
        if ($length < 0) {
            throw new \RuntimeException('Length parameter cannot be negative');
        }
        if (0 === $length) {
            return '';
        }
        
        try {
            $string = fread($this->stream, $length);
        }catch(Exception $e){
            throw new \RuntimeException('Unable to read from stream', 0, $e);
        }

        if (false === $string) {
            throw new \RuntimeException('Unable to read from stream');
        }

        return $string;
    }

    public function getContents(): string{
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }

        try{
            $contents = stream_get_contents($this->stream);
            if ($contents === false) {
                $ex = new \RuntimeException('Unable to read stream contents');
            }
        }catch(\Throwable $e){
            $ex = new \RuntimeException(sprintf('Unable to read stream contents %s', $e->getMessage()), 0, $e);
        }

        if($ex){
            throw $ex;
        }
        
        return $contents;
    }

    public function getMetadata(?string $key = null){
        if(!isset($this->stream)){
            return $key ? null : [];
        }elseif(!$key){
            return $this->customMetadata + stream_get_meta_data($this->stream);
        }elseif(isset($this->customMetadata[$key])){
            return $this->customMetadata[$key];
        }

        $meta = stream_get_meta_data($this->stream);

        return $meta[$key] ?? null;
    }

}