<?php

use App\Contracts\Reporter;
use App\Reporting\ConsoleReporter;
use App\Reporting\NullReporter;
use App\Services\FileOperator;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    app()->bind(Reporter::class, fn () => new NullReporter);

    $this->filesystem = new Filesystem;
    $this->tmpDir = sys_get_temp_dir().'/file_operator_test_'.uniqid();

    $this->filesystem->ensureDirectoryExists($this->tmpDir);
});

afterEach(function () {
    $this->filesystem->deleteDirectory($this->tmpDir);
});

it('does not copy or move in dry run', function () {
    $source = "{$this->tmpDir}/source.txt";
    $target = "{$this->tmpDir}/target.txt";

    file_put_contents($source, 'test');

    $operator = app(FileOperator::class)->withDryRun(true);

    expect($operator->copy($source, $target))->toBeFalse();
    expect($operator->move($source, $target))->toBeFalse();

    expect(file_exists($source))->toBeTrue();
    expect(file_exists($target))->toBeFalse();
});

it('copies file when not dry run', function () {
    $source = "{$this->tmpDir}/source.txt";
    $target = "{$this->tmpDir}/target.txt";

    file_put_contents($source, 'test');

    $operator = app(FileOperator::class)->withDryRun(false);

    expect($operator->copy($source, $target))->toBeTrue();

    expect(file_exists($source))->toBeTrue();
    expect(file_exists($target))->toBeTrue();
    expect(file_get_contents($target))->toBe('test');
});

it('moves file when not dry run', function () {
    $source = "{$this->tmpDir}/source.txt";
    $target = "{$this->tmpDir}/target.txt";

    file_put_contents($source, 'test');

    $operator = app(FileOperator::class)->withDryRun(false);

    expect($operator->move($source, $target))->toBeTrue();

    expect(file_exists($source))->toBeFalse();
    expect(file_exists($target))->toBeTrue();
    expect(file_get_contents($target))->toBe('test');
});

it('returns true when dry run is enabled', function () {
    $op = (new FileOperator(new NullReporter))->withDryRun(true);
    expect($op->isDryRun())->toBeTrue();
});

it('returns false when dry run is not enabled', function () {
    $op = (new FileOperator(new NullReporter))->withDryRun(false);
    expect($op->isDryRun())->toBeFalse();
});

it('constructs with console reporter via forConsole', function () {
    $op = FileOperator::forConsole(new Command);

    expect($op)->toBeInstanceOf(FileOperator::class);
    expect($op->getReporter())->toBeInstanceOf(ConsoleReporter::class);
});
