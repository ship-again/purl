Purl
====

Purl is a simple Object Oriented URL manipulation library for PHP 7.2+

[![CI](https://github.com/ship-again/purl/actions/workflows/ci.yml/badge.svg)](https://github.com/ship-again/purl/actions/workflows/ci.yml)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=ship-again_purl&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=ship-again_purl)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=ship-again_purl&metric=coverage)](https://sonarcloud.io/summary/new_code?id=ship-again_purl)

This repository is the Ship Again continuation of the abandoned [`jwage/purl`](https://github.com/jwage/purl) package. The original API and attribution are preserved, including credit to the original author, Jonathan H. Wage.

The library keeps the `Purl\` namespace and supports PHP 7.2 through PHP 8.x. It is released under the MIT license; see [LICENSE](LICENSE) for the original attribution and terms.

## Installation

The suggested installation method is via [composer](https://getcomposer.org/):

```sh
composer require ship-again/purl
```

Development
-----------

The repository includes a PHP 8.5 Dev Container with the isolated QA toolchain. After opening it, the same entrypoints used by CI are available:

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

`make bench` runs the local PHPBench baseline in the PHP 8.5 Dev Container. Its
time and memory values are meaningful for comparisons made with the same PHP
version, machine, and runtime configuration; they are diagnostic measurements,
not a CI acceptance gate. The benchmark subprocess suppresses only PHP
deprecation notices from the legacy PHP 7.2-compatible API; PHPUnit and the
quality checks continue to report those notices.

Benchmark metrics
-----------------

`make bench` prints PHPBench's aggregate report. The current configuration uses
5 iterations and 1,000 revolutions per iteration, so every subject is sampled
five times and its code is executed 1,000 times inside each sample.

| Metric | Meaning |
| --- | --- |
| `benchmark` | Benchmark class; currently `UrlBench`. |
| `subject` | Method being measured, such as `benchParseAndBuild`. |
| `set` | Parameter set name. It is empty because the current benchmarks use fixed inputs. |
| `revs` | Number of consecutive subject executions in one iteration. |
| `its` | Number of measured iterations. |
| `mem_peak` | Peak memory reported for the benchmark process. Compare it only under the same PHP/runtime setup. |
| `mode` | The representative (modal) execution time per revolution, shown in the configured time unit. Lower is faster. |
| `rstdev` | Relative standard deviation across iteration timings. Lower usually means a more stable measurement; a high value suggests background noise or an insufficiently stable environment. |

For example, `7.2μs` in `mode` means that one execution of the subject took
about 7.2 microseconds in that run's representative sample. It does not mean
that the complete 1,000-revolution iteration took 7.2 microseconds. Use
`revs` and `its` together with `mode` and `rstdev` when comparing two runs, and
keep the PHP version, machine, extensions, and runtime configuration constant.

The compatibility workflow also exercises PHP 7.2 through 8.5 with the Composer dependency lines supported by each runtime.

To enable the trusted SonarCloud step, configure repository variables `SONAR_ORGANIZATION` and `SONAR_PROJECT_KEY`, plus the `SONAR_TOKEN` repository secret. Fork pull requests still run the compatibility and local quality gates, while the Sonar upload is skipped because their secrets are unavailable.

Using Purl
----------

Creating Url instances is easy. You can specify the URL you want, or just use the current URL:

```php
use Purl\Url;

$url = new Url('http://jwage.com');
$currentUrl = Url::fromCurrent();
```

You can chain methods together after creating the `Url` like this:

```php
$url = (new Url('http://jwage.com'))
    ->set('scheme', 'https')
    ->set('port', '443')
    ->set('user', 'jwage')
    ->set('pass', 'password')
    ->set('path', 'about/me')
    ->set('query', 'param1=value1&param2=value2')
    ->set('fragment', 'about/me?param1=value1&param2=value2');

echo $url->getUrl(); // https://jwage:password@jwage.com:443/about/me?param1=value1&param2=value2#about/me?param1=value1&param2=value2

// $url->path becomes instanceof Purl\Path
// $url->query becomes instanceof Purl\Query
// $url->fragment becomes instanceof Purl\Fragment
```

### Path Manipulation

```php
$url = new Url('http://jwage.com');

// add path segments one at a time
$url->path->add('about')->add('me');

// set the path data from a string
$url->path = 'about/me/another_segment'; // $url->path becomes instanceof Purl\Path

// get the path segments
print_r($url->path->getData()); // array('about', 'me', 'another_segment')
```

### Query Manipulation

```php
$url = new Url('http://jwage.com');
$url->query->set('param1', 'value1');
$url->query->set('param2', 'value2');

echo $url->query; // param1=value1&param2=value2
echo $url; // http://jwage.com?param1=value1&param2=value2

// set the query data from an array
$url->query->setData([
    'param1' => 'value1',
    'param2' => 'value2'
]);

// set the query data from a string
$url->query = 'param1=value1&param2=value2'; // $url->query becomes instanceof Purl\Query
print_r($url->query->getData()); //array('param1' => 'value1', 'param2' => 'value2')
```

### Fragment Manipulation

```php
$url = new Url('http://jwage.com');
$url->fragment = 'about/me?param1=value1&param2=value2'; // $url->fragment becomes instanceof Purl\Fragment
```

A Fragment is made of a path and a query and comes after the hashmark (#).

```php
echo $url->fragment->path; // about/me
echo $url->fragment->query; // param1=value1&param2=value2
echo $url; // http://jwage.com#about/me?param1=value1&param2=value2
```

### Extract URLs

You can easily extract urls from a string of text using the `extract` method:

```php
$string = 'some text http://google.com http://jwage.com';
$urls = Url::extract($string);

echo $urls[0]; // http://google.com/
echo $urls[1]; // http://jwage.com/
```

### Join URLs

You can easily join two URLs together using Purl:

```php
$url = new Url('http://jwage.com/about?param=value#fragment');
$url->join('http://about.me/jwage');

echo $url; // http://about.me/jwage?param=value#fragment
```

Or if you have another `Url` object already:

```php
$url1 = new Url('http://jwage.com/about?param=value#fragment');
$url2 = new Url('http://about.me/jwage');
$url1->join($url2);

echo $url1; // http://about.me/jwage?param=value#fragment
```
