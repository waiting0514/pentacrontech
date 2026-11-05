<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product PDF attachments
    |--------------------------------------------------------------------------
    |
    | Max size per file in kilobytes (10240 = 10 MB) and max number of
    | attachments per product. Make sure PHP upload_max_filesize,
    | post_max_size and nginx client_max_body_size are not lower.
    |
    */

    'product_attachment_max_kb' => (int) env('PRODUCT_ATTACHMENT_MAX_KB', 10240),

    'product_attachment_max_per_product' => (int) env('PRODUCT_ATTACHMENT_MAX_PER_PRODUCT', 10),

];
