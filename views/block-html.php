<?php

// this attribute can only be edited by users with the `unfiltered_html` capability
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $attributes['rawHtml'];
