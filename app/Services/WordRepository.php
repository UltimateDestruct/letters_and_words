<?php

namespace App\Services;

use InvalidArgumentException;

class WordRepository
{
    /** @var array<int, array<int, string>> */
    private array $wordsByLength = [];

    public function __construct(
        private readonly string $path,
        private readonly int $maxLength,
    ) {
        $this->load();
    }

    private function load(): void
    {
        if (! is_readable($this->path)) {
            throw new InvalidArgumentException("Word list not readable: {$this->path}");
        }

        $handle = fopen($this->path, 'r');
        if ($handle === false) {
            throw new InvalidArgumentException("Could not open word list: {$this->path}");
        }

        try {
            while (($line = fgets($handle)) !== false) {
                $word = trim($line);
                if ($word === '') {
                    continue;
                }

                $length = mb_strlen($word, 'UTF-8');
                if ($length < 1 || $length > $this->maxLength) {
                    continue;
                }

                $this->wordsByLength[$length][] = $word;
            }
        } finally {
            fclose($handle);
        }

        foreach ($this->wordsByLength as $length => $words) {
            sort($words, SORT_STRING);
            $this->wordsByLength[$length] = $words;
        }
    }

    /**
     * @return array{count: int, first: string|null, last: string|null, words: string[]}
     */
    public function getByLength(int $length): array
    {
        $words = $this->wordsByLength[$length] ?? [];

        if ($words === []) {
            return [
                'count' => 0,
                'first' => null,
                'last' => null,
                'words' => [],
            ];
        }

        return [
            'count' => count($words),
            'first' => $words[0],
            'last' => $words[count($words) - 1],
            'words' => $words,
        ];
    }

    /**
     * @return array{
     *     count: int,
     *     first: string|null,
     *     last: string|null,
     *     words: string[],
     *     page: int,
     *     per_page: int,
     *     total_pages: int
     * }
     */
    public function getByLengthPaginated(int $length, int $page, int $perPage): array
    {
        $base = $this->getByLength($length);
        $total = $base['count'];
        $all = $base['words'];
        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;
        $offset = ($page - 1) * $perPage;
        $pageWords = $total === 0 ? [] : array_slice($all, $offset, $perPage);

        return [
            'count' => $total,
            'first' => $base['first'],
            'last' => $base['last'],
            'words' => $pageWords,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    public function maxLength(): int
    {
        return $this->maxLength;
    }
}
