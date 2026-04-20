<?php

return [

    'path' => env('WORDLIST_PATH', base_path('wordlist.txt')),

    'max_length' => (int) env('WORD_MAX_LENGTH', 45),

];
