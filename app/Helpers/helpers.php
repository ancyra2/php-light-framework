<?php

namespace App\Helpers; 

use Psr\Http\Message\StreamInterface;

class Helpers{
    /**
     * load a view
     */
    static function view($view = null, $data = []){
        include __DIR__.'/../../views/' . $view . ".view.php";
        return $data; 
    }

    /**
     * Loads all Enviroment Variables.
     */
    static function loadEnv(){
        $envFile = __DIR__.'/../../.env';
        if(file_exists($envFile)){
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line){
                if(strpos($line, '=') !== false){
                    list($key, $value) = explode('=' , $line, 2 );
                    $key = trim($key);
                    $value = trim($value);
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }

    }

      /**
     * Create a new stream based on the input type.
     *
     * Options is an associative array that can contain the following keys:
     * - metadata: Array of custom metadata.
     * - size: Size of the stream.
     *
     * This method accepts the following `$resource` types:
     * - `Psr\Http\Message\StreamInterface`: Returns the value as-is.
     * - `string`: Creates a stream object that uses the given string as the contents.
     * - `resource`: Creates a stream object that wraps the given PHP stream resource.
     * - `Iterator`: If the provided value implements `Iterator`, then a read-only
     *   stream object will be created that wraps the given iterable. Each time the
     *   stream is read from, data from the iterator will fill a buffer and will be
     *   continuously called until the buffer is equal to the requested read size.
     *   Subsequent read calls will first read from the buffer and then call `next`
     *   on the underlying iterator until it is exhausted.
     * - `object` with `__toString()`: If the object has the `__toString()` method,
     *   the object will be cast to a string and then a stream will be returned that
     *   uses the string value.
     * - `NULL`: When `null` is passed, an empty stream object is returned.
     * - `callable` When a callable is passed, a read-only stream object will be
     *   created that invokes the given callable. The callable is invoked with the
     *   number of suggested bytes to read. The callable can return any number of
     *   bytes, but MUST return `false` when there is no more data to return. The
     *   stream object that wraps the callable will invoke the callable until the
     *   number of requested bytes are available. Any additional bytes will be
     *   buffered and used in subsequent reads.
     *
     * @param resource|string|int|float|bool|StreamInterface|callable|\Iterator|null $resource Entity body data
     * @param array{size?: int, metadata?: array}                                    $options  Additional options
     *
     * @throws \InvalidArgumentException if the $resource arg is not valid.
     */
    public static function streamFor($resource = '', array $options = []): StreamInterface
    {
        if (is_scalar($resource)) {
            $stream = self::tryFopen('php://temp', 'r+');
            if ($resource !== '') {
                fwrite($stream, (string) $resource);
                fseek($stream, 0);
            }
            return new Stream($stream, $options);
        }

        switch (gettype($resource)) {
            case 'resource':
                /*
                 * The 'php://input' is a special stream with quirks and inconsistencies.
                 * We avoid using that stream by reading it into php://temp
                 */

                /** @var resource $resource */
                if ((\stream_get_meta_data($resource)['uri'] ?? '') === 'php://input') {
                    $stream = self::tryFopen('php://temp', 'w+');
                    stream_copy_to_stream($resource, $stream);
                    fseek($stream, 0);
                    $resource = $stream;
                }
                return new Stream($resource, $options);
            case 'object':
                /** @var object $resource */
                if ($resource instanceof StreamInterface) {
                    return $resource;
                } elseif ($resource instanceof \Iterator) {
                    return new PumpStream(function () use ($resource) {
                        if (!$resource->valid()) {
                            return false;
                        }
                        $result = $resource->current();
                        $resource->next();
                        return $result;
                    }, $options);
                } elseif (method_exists($resource, '__toString')) {
                    return self::streamFor((string) $resource, $options);
                }
                break;
            case 'NULL':
                return new Stream(self::tryFopen('php://temp', 'r+'), $options);
        }

        if (is_callable($resource)) {
            return new PumpStream($resource, $options);
        }

        throw new \InvalidArgumentException('Invalid resource type: ' . gettype($resource));
    }

    /**
     * Safely opens a PHP stream resource using a filename.
     *
     * When fopen fails, PHP normally raises a warning. This function adds an
     * error handler that checks for errors and throws an exception instead.
     *
     * @param string $filename File to open
     * @param string $mode     Mode used to open the file
     *
     * @return resource
     *
     * @throws \RuntimeException if the file cannot be opened
     */
    public static function tryFopen(string $filename, string $mode)
    {
        $ex = null;
        set_error_handler(static function (int $errno, string $errstr) use ($filename, $mode, &$ex): bool {
            $ex = new \RuntimeException(sprintf(
                'Unable to open "%s" using mode "%s": %s',
                $filename,
                $mode,
                $errstr
            ));

            return true;
        });
}

}