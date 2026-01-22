<?php

declare(strict_types=1);

return [
    // 'parallel' => use FastValidator as a separate, opt-in API.
    // 'override' => route Laravel's Validator::make() through the fast engine when possible.
    'mode' => 'parallel',

    // Future toggles: error detail level, caching, etc.
];
