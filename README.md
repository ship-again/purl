# Purl

[![CI](https://github.com/ship-again/purl/actions/workflows/ci.yml/badge.svg)](https://github.com/ship-again/purl/actions/workflows/ci.yml)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=ship-again_purl&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=ship-again_purl)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=ship-again_purl&metric=coverage)](https://sonarcloud.io/summary/new_code?id=ship-again_purl)

Purl is an object-oriented URL manipulation library for PHP 7.2 through PHP
8.x.

This repository is the Ship Again continuation of the abandoned
[`jwage/purl`](https://github.com/jwage/purl) package. It preserves the `Purl\`
namespace, original API, commit history, MIT license, and attribution to the
original author, Jonathan H. Wage.

## Installation

```sh
composer require ship-again/purl
```

The package replaces `jwage/purl` at the installed version for Composer
dependency resolution.

## Quick start

```php
use Purl\Url;

$url = (new Url('http://example.com'))
    ->set('scheme', 'https')
    ->set('port', '443')
    ->set('user', 'username')
    ->set('pass', 'password')
    ->set('path', 'about/me')
    ->set('query', 'page=2')
    ->set('fragment', 'profile');

echo $url->getUrl();
// https://username:password@example.com:443/about/me?page=2#profile
```

## URL methods

| Method | Description |
| --- | --- |
| `new Url(?string $url = null, ?ParserInterface $parser = null)` | Creates a lazily parsed URL. |
| `Url::parse(string $url): Url` | Creates a URL from a string. |
| `Url::fromCurrent(): Url` | Creates a URL from the current request's server variables. |
| `Url::extract(string $text): array` | Extracts HTTP, HTTPS, FTP, and FTPS URLs from text. |
| `getUrl(): string` | Returns the complete URL string. |
| `setUrl(string $url): void` | Replaces the URL and resets its parsed state. |
| `isAbsolute(): bool` | Returns whether both the scheme and host are present. |
| `getNetloc(): string` | Returns the authority component: credentials, host, and port. |
| `join(string\|Url $url): Url` | Joins another absolute, relative, or protocol-relative URL into this URL. |
| `getParser(): ParserInterface` | Returns the parser used by the URL. |
| `setParser(ParserInterface $parser): void` | Replaces the parser. |
| `getPath(): Path` / `setPath(Path $path)` | Gets or replaces the path object. |
| `getQuery(): Query` / `setQuery(Query $query)` | Gets or replaces the query object. |
| `getFragment(): Fragment` / `setFragment(Fragment $fragment)` | Gets or replaces the fragment object. |

URL fields are also available through `get()`, `set()`, `has()`, `remove()`,
magic properties, and `ArrayAccess`:

```php
$url = new Url('https://example.com/docs?page=1');

echo $url->get('host');       // example.com
echo $url->path;              // docs
echo $url['query'];           // page=1

$url->set('fragment', 'top');
$url->port = 8443;
unset($url['user']);
```

## Path methods

| Method | Description |
| --- | --- |
| `new Path(?string $path = null)` | Creates a path from slash-separated segments. |
| `getPath(): string` | Returns the encoded path string. |
| `setPath(string $path): void` | Replaces the path string. |
| `getSegments(): array` | Returns the path segments. |
| `add($value): AbstractPart` | Appends a path segment. |
| `get(string $key)` / `set(string $key, $value)` | Gets or replaces a segment by key. |
| `remove(string $key): AbstractPart` | Removes a segment by key. |

```php
$url = new Url('https://example.com');
$url->path = 'about';
$url->path->add('me');

echo $url->path->getPath(); // about/me
```

## Query methods

| Method | Description |
| --- | --- |
| `new Query(?string $query = null)` | Creates a query from a query string. |
| `getQuery(): string` | Returns the encoded query string. |
| `setQuery(string $query): void` | Replaces the query string. |
| `getData(): array` / `setData(array $data): void` | Gets or replaces all query parameters. |
| `get(string $key)` / `set(string $key, $value)` | Gets or replaces a query parameter. |
| `has(string $key): bool` | Returns whether a query parameter exists. |
| `remove(string $key): AbstractPart` | Removes a query parameter. |

```php
$url = new Url('https://example.com');
$url->query->set('page', 2)->set('sort', 'name');

echo $url->query->getQuery(); // page=2&sort=name
```

## Fragment methods

A fragment contains its own `Path` and `Query` objects.

| Method | Description |
| --- | --- |
| `new Fragment(string\|Path|null $fragment = null, ?Query $query = null)` | Creates a fragment from a string or part objects. |
| `getFragment(): string` | Returns the complete fragment string. |
| `setFragment(string $fragment): void` | Replaces the fragment string. |
| `getPath(): Path` / `setPath(Path $path)` | Gets or replaces the fragment path. |
| `getQuery(): Query` / `setQuery(Query $query)` | Gets or replaces the fragment query. |

```php
$url = new Url('https://example.com');
$url->fragment = 'section?page=2';

echo $url->fragment->path;  // section
echo $url->fragment->query; // page=2
```

## Extract URLs

```php
$urls = Url::extract('See https://example.com and ftp://files.example.com.');

echo $urls[0]; // https://example.com/
echo $urls[1]; // ftp://files.example.com/
```

## Join URLs

```php
$url = new Url('https://example.com/about?page=1#top');
$url->join('/contact');

echo $url; // https://example.com/contact?page=1#top
```

`join()` also accepts another `Url` object.

## Benchmarks

`make bench` runs 13 time-oriented and 3 memory-oriented PHPBench subjects in
the PHP 8.5 Dev Container. Results are intended for comparisons made in the
same runtime environment. Reproducible comparison commands are documented in
[benchmarks/README.md](benchmarks/README.md).

## Development

The PHP 8.5 Dev Container provides the QA and benchmark tools used by the
project:

```sh
make install
make test
make bench
make coverage
make cs-check
make cs-fix
make psalm
make qa
```
