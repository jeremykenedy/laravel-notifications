<?php

/**
 * Walks every shipped Blade view looking for user facing text that was written
 * straight into the markup instead of coming from the translation file.
 */
function shippedViews(): array
{
    $views = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../../resources/views'));

    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $views[] = $file->getPathname();
        }
    }

    sort($views);

    return $views;
}

it('ships at least one view per css framework', function () {
    expect(count(shippedViews()))->toBeGreaterThanOrEqual(10);
});

it('has no hardcoded body text in any view', function () {
    $offenders = [];

    foreach (shippedViews() as $view) {
        $markup = preg_replace('/\{\{--.*?--\}\}/s', '', file_get_contents($view));
        preg_match_all('/>([A-Z][A-Za-z][^<>{}]{2,})</', $markup, $matches);

        foreach ($matches[1] as $text) {
            $offenders[] = basename(dirname($view, 2)).'/'.basename($view).': '.trim($text);
        }
    }

    expect($offenders)->toBeEmpty();
});

it('has no hardcoded placeholder title or aria-label in any view', function () {
    $offenders = [];

    foreach (shippedViews() as $view) {
        $markup = preg_replace('/\{\{--.*?--\}\}/s', '', file_get_contents($view));
        preg_match_all('/(?:placeholder|title|aria-label)="([A-Z][^"]*)"/', $markup, $matches);

        foreach ($matches[1] as $text) {
            $offenders[] = basename(dirname($view, 2)).'/'.basename($view).': '.$text;
        }
    }

    expect($offenders)->toBeEmpty();
});
