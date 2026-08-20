# PHPBench journal

This directory contains the performance harness and the evidence journal for
the optimization experiments in `.llm-data/PLAN.md`. The benchmark suite is
run in the repository's PHP 8.5 environment; it is not a compatibility claim
for the library's PHP 7.2--8.5 support range.

## Reproducible commands

From the repository root, with `tools/bench/vendor` installed:

```sh
php -v
php -i | grep -E '^(opcache.enable|opcache.jit|xdebug.mode)'
tools/bench/vendor/bin/phpbench run --report=aggregate --store \
  --tag=H0_baseline_<commit> --iterations=10 --warmup=2 \
  --retry-threshold=2
tools/bench/vendor/bin/phpbench run --report=aggregate --store \
  --tag=H1_candidate_1_<commit> --ref=H1_baseline_<commit> \
  --iterations=10 --warmup=2 --retry-threshold=2
```

Time subjects use 10 iterations and 1,000 revolutions unless overridden. The
three `MemoryBench` subjects use `@Revs(1)` and the
`memory_centric_microtime` executor. Their 10,000-element batch is retained in
the benchmark object's `$batch` property so `mem.final` remains meaningful;
the default aggregate table reports `mem_peak`, while the stored XML/raw run
contains the per-variant `mem.final` value as well.

The baseline/candidate comparison is valid only when it records the same PHP
binary, lock files, PHPBench binary, INI settings, and benchmark filter. Keep
the raw XML or console output beside the run notes when recording a result.

## Experiment journal

The harness was expanded in a follow-up to `0252acc` before any source
optimization. This section is append-only: each H1--H6 entry records the
baseline commit/tag, both independent candidate runs, targeted and key
subjects, `rstdev`, memory values, and the acceptance decision. A run that does
not meet the plan's threshold must be reverted completely before the next
experiment. No result is inferred from a single run or from the aggregate
`mem_peak` of the old three-subject harness.

| Experiment | Baseline | Candidate runs | Decision | Evidence |
| --- | --- | --- | --- | --- |
| Harness | `0252acc` | `H0_harness_worktree` | recorded | 16 subjects: 13 time + 3 memory; 10 iterations, warmup 2, retry threshold 2; memory batch 10,000 retained `Url` objects. This row is not an optimization claim. |
| H1 part-only normalization | `H0_harness_worktree` | `H1_candidate_1_worktree`, `H1_candidate_2_worktree` | accepted | Absolute `6.678us -> 5.900us/-11.64%`, `5.866us/-12.16%`; relative `5.761us -> 5.062us/-12.13%`, `5.100us/-11.48%`; batch parse+build `69.764ms -> 61.111ms/-12.40%`, `62.205ms/-10.83%`. All listed rstdev <=2%; memory final unchanged. |
| H2a specialized builder | `H1_candidate_2_worktree` | `H2a_candidate_1_worktree`, `H2a_candidate_2_worktree` | accepted | Absolute `5.866us -> 5.207us/-11.23%`, `5.230us/-10.84%`; relative `5.100us -> 4.607us/-9.66%`, `4.683us/-8.17%`; initialized `2.375us -> 1.774us/-25.29%`, `1.863us/-21.55%`. Batch parse+build `62.205ms -> 54.691ms/-12.08%`, `54.473ms/-12.43%`; no key regression >3%, rstdev <=2%. |
| H2b concatenation | `H2a_candidate_2_worktree` | `H2b_candidate_1_worktree`, `H2b_candidate_2_worktree` | rejected | Absolute changed only `+1.23%` and `+0.83%`; no >=5% target gain. Diff removed completely; port `%d` behavior was restored. |
| H3 single-pass join | `H2a_candidate_2_worktree` | `H3_candidate_1_worktree`, `H3_candidate_2_worktree` | rejected | `joinAndBuild` was `+0.33%` and `-0.93%`; protocol-relative join `+1.86%` and `+1.06%`; no >=5% target gain. Diff removed completely. |
| H4 non-capturing extract regex | `H2a_candidate_2_worktree` | `H4_candidate_1_worktree`, `H4_candidate_2_worktree` | accepted after raw-XML review | Sparse extraction `35.339us -> 31.577us/-10.64%`, `31.712us/-10.26%`; dense `179.410us -> 163.488us/-8.85%`, `165.346us/-7.82%`; memory peak/final unchanged. The earlier manual `mutateAndBuild -3.01%` caveat was a transcription error: raw XML records `3.8148us -> 3.7925us/-0.58%`, then `3.7364us/-2.06%` (both faster). Independent final runs are recorded in `.llm-data/PLAN-final.md`. |
| H5 shared default parser | `H4_candidate_2_worktree` | `H5_candidate_1_worktree`, `H5_candidate_2_worktree` | rejected | Batch parse+build memory peak improved only `-2.11%` (below -10%); both candidates showed protocol-relative join regression (`+5.73%`, `+4.82%`). Diff removed completely; public parser identity test remains. |
| H6 Path/Query serialization cache | `H4_candidate_2_worktree` | `H6_candidate_1_worktree`, `H6_candidate_2_worktree` | rejected | Repeated part build improved `-65.75%`, `-66.51%`, but batch parse+build memory peak grew `+6.20%` in both candidates, above the +3% guardrail. Diff removed completely; mutation invalidation characterization tests remain. |

The raw XML runs are stored in the ignored `.phpbench/storage/` tree. The
PHPBench run UUIDs printed in the console and XML include:

```text
H0  13527d404ff0a21f8c6496978bf7c21228b70b3c
H1  13527d4827763d1c8fde6709d6ba1296fb4cba82,
    13527d42875033f3284c2e492caac60b4042e142
H2a 13527d4c847a2830e1e74a94a3265f430ff124a2,
    13527d44ce42458635736c34fa67ddfb4553e9f0
H2b 13527d4b39d1c00f7ec394ef9f0477f40701b6b0,
    13527d426f28ddc21f60ade03fa071e7932d483b
H3  13527d48a03c1509b0ce172bb8c7141aac5e5862,
    13527d405c48f26ba2dc544f44f05c9a59b46279
H4  13527d49998008e79c85b3660389dbe562d24886,
    13527d480a7cbdc74b32b8cfbe1e07279395830d
H5  13527d42d99e58bfaa8b021901baaf4b61bbe797,
    13527d406b963dd14416d55ff49c0826a39dda1d
H6  13527d4cd146c6904f0ff2b73841a0cf1d65f4c6,
    13527d426d311e0ae2f310ba73042854359bad78
```

Each XML iteration records `mem-peak`, `mem-real`, and `mem-final`; the
aggregate console table intentionally shows `mem_peak` only. The raw H0 XML
also records the source SHA `0252acc7f1b5c6ce072a71ba3c8e6f85b094971a` and
the PHPBench environment (`PHP 8.5.9`, arm64 Darwin, Xdebug disabled,
OPcache disabled as reported by PHPBench).
