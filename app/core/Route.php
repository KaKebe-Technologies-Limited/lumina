<?php
declare(strict_types=1);

namespace App\Core;

/** A single registered route. Returned by Router::get()/post()/... for fluent configuration. */
final class Route
{
    /** Default regex per placeholder name. Anything else = [^/]+ */
    public const PATTERNS = [
        'id'    => '\d+',
        'mid'   => '\d+',       // LAF Academy: module / lesson / quiz / course ids (the controller receives an int)
        'lid'   => '\d+',
        'qid'   => '\d+',
        'courseId' => '\d+',
        'eid'   => '\d+',       // LAF Academy: challenge entry id
        'sigid' => '\d+',      // a signature row of a document (docs/DOCUMENTS-SIGNATURES.md)
        'part'  => '[a-z0-9]+',   // LAF Academy: which file of a record (cv, photo, project, or a position number)
        'slug'  => '[a-z0-9]+(?:-[a-z0-9]+)*',
        'token' => '[A-Za-z0-9_-]+',
        'year'  => '\d{4}',
        'month' => '\d{2}',
        'path'  => '.+',
    ];

    /** @var string[] */
    public array $params = [];
    public string $regex = '';
    public ?string $name = null;
    /** @var string[] */
    public array $middleware = [];
    /** @var array<string,string> */
    private array $wheres = [];

    /**
     * @param string[] $methods
     * @param mixed    $handler
     */
    public function __construct(public array $methods, public string $path, public mixed $handler, array $middleware = [])
    {
        $this->middleware = $middleware;
        $this->compile();
    }

    private function compile(): void
    {
        $this->params = [];
        $regex = preg_replace_callback('/\{(\w+)\}/', function (array $m): string {
            $this->params[] = $m[1];
            $re = $this->wheres[$m[1]] ?? self::PATTERNS[$m[1]] ?? '[^/]+';
            return '(?P<' . $m[1] . '>' . $re . ')';
        }, $this->path);
        $this->regex = '#^' . $regex . '$#u';
    }

    /** Override the regex for a placeholder: ->where('slug', '[a-z0-9-]+') */
    public function where(string $param, string $regex): self
    {
        $this->wheres[$param] = $regex;
        $this->compile();
        return $this;
    }

    /** Give the route a name for route('name', ['slug' => 'x']). */
    public function name(string $name): self
    {
        $this->name = $name;
        Router::registerName($name, $this);
        return $this;
    }

    /** Add middleware names, e.g. ->middleware('role:admin') or ->middleware(['throttle:5,60']). */
    public function middleware(string|array $names): self
    {
        foreach ((array) $names as $n) {
            $this->middleware[] = $n;
        }
        return $this;
    }

    /** Number of dynamic segments (used to make static routes win over dynamic ones). */
    public function specificity(): int
    {
        return count($this->params);
    }

    /** @return array<string,string>|null */
    public function match(string $path): ?array
    {
        if (!preg_match($this->regex, $path, $m)) {
            return null;
        }
        $out = [];
        foreach ($this->params as $p) {
            $out[$p] = rawurldecode((string) $m[$p]);
        }
        return $out;
    }

    /** Build a path from this route's pattern. */
    public function build(array $params = []): string
    {
        return (string) preg_replace_callback('/\{(\w+)\}/', static function (array $m) use ($params): string {
            return rawurlencode((string) ($params[$m[1]] ?? ''));
        }, $this->path);
    }
}
