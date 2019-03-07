<?php

return [
    'adminEmail' => 'admin@example.com',

    /** The time before a confirmation token becomes invalid. */
    'confirmWithin' => 86400, // 24 hours

    /** @var int The time you want the user will be remembered without asking for credentials. */
   'rememberFor' => 1209600, // two weeks
 
    /** @var int The time before a recovery token becomes invalid. */
    'recoverWithin' => 21600, // 6 hours

];