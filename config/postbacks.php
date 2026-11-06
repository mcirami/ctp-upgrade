<?php

return [
    // Applied to incoming sale postbacks, not manual adjustments.
    'max_click_age_days' => (int) env('POSTBACK_MAX_CLICK_AGE_DAYS', 90),
];
