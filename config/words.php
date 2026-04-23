<?php

return [

    'path' => env('WORDLIST_PATH', base_path('wordlist.txt')),

    'max_length' => (int) env('WORD_MAX_LENGTH', 45),

    'results_per_page' => max(1, min(200, (int) env('WORD_RESULTS_PER_PAGE', 10))),

];
