<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Signature Stamp Geometry
    |--------------------------------------------------------------------------
    |
    | Controls where the client's signature is stamped on every page of a
    | signed document. All values are in millimetres (FPDI/FPDF's default
    | unit). The stamp is anchored to the bottom-right corner of each page
    | using these margins, so it adapts automatically to different page
    | sizes/orientations rather than assuming a fixed A4 position.
    |
    */

    'signature' => [
        'width'         => (float) env('DOC_SIGN_WIDTH', 30),
        'height'        => (float) env('DOC_SIGN_HEIGHT', 15),
        'margin_right'  => (float) env('DOC_SIGN_MARGIN_RIGHT', 15),
        'margin_bottom' => (float) env('DOC_SIGN_MARGIN_BOTTOM', 15),
    ],

];
