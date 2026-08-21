# PHPBench harness

This directory contains the performance harness for Purl. The benchmark suite
runs in the repository's PHP 8.5 development environment; it is not a
compatibility claim for the library's PHP 7.2--8.5 support range.

## Run the suite

From the Dev Container, run:

```sh
make bench
```

The suite contains 13 time-oriented `UrlBench` subjects and 3 memory-oriented
`MemoryBench` subjects. The default configuration uses 10 iterations, 2 warmup
runs, and a retry threshold of 2. Time subjects use 1,000 revolutions per
iteration. Memory subjects use one revolution over a retained batch of 10,000
values.

## Compare two revisions

With `tools/bench/vendor` installed, store a baseline and compare a candidate:

```sh
tools/bench/vendor/bin/phpbench run --report=aggregate --store \
  --tag=baseline_<commit> --iterations=10 --warmup=2 \
  --retry-threshold=2
tools/bench/vendor/bin/phpbench run --report=aggregate --store \
  --tag=candidate_<commit> --ref=baseline_<commit> \
  --iterations=10 --warmup=2 --retry-threshold=2
```

Use the same PHP binary, lock files, PHPBench binary, INI settings, and
benchmark filter for both runs. Preserve the raw XML or console output with
the comparison report when keeping performance evidence. Local PHPBench
storage is intentionally ignored by Git.
